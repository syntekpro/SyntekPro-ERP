<?php

namespace App\Http\Controllers\Api;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ShopStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosSaleSyncController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sales' => ['required', 'array', 'min:1'],
            'sales.*.idempotency_key' => ['required', 'string', 'max:120'],
            'sales.*.shop_id' => ['required', 'integer', 'exists:shops,id'],
            'sales.*.cashier_id' => ['required', 'integer', 'exists:users,id'],
            'sales.*.sold_at' => ['required', 'date'],
            'sales.*.subtotal' => ['required', 'numeric', 'min:0'],
            'sales.*.vat_total' => ['required', 'numeric', 'min:0'],
            'sales.*.total' => ['required', 'numeric', 'min:0'],
            'sales.*.items' => ['required', 'array', 'min:1'],
            'sales.*.items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'sales.*.items.*.product_name' => ['required', 'string', 'max:255'],
            'sales.*.items.*.sku' => ['nullable', 'string', 'max:255'],
            'sales.*.items.*.barcode' => ['nullable', 'string', 'max:255'],
            'sales.*.items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'sales.*.items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'sales.*.items.*.vat_rate' => ['required', 'numeric', 'min:0'],
            'sales.*.items.*.vat_amount' => ['required', 'numeric', 'min:0'],
            'sales.*.items.*.line_total' => ['required', 'numeric', 'min:0'],
        ]);

        $user = $request->user();

        if ($user === null || $user->shop_id === null) {
            throw ValidationException::withMessages([
                'sales' => 'A shop-assigned cashier is required to sync sales.',
            ]);
        }

        $results = [];

        foreach ($validated['sales'] as $salePayload) {
            $results[] = $this->syncSale($salePayload, $user->id, $user->shop_id);
        }

        return response()->json([
            'results' => $results,
        ]);
    }

    protected function syncSale(array $salePayload, int $cashierId, int $shopId): array
    {
        if ((int) $salePayload['shop_id'] !== $shopId || (int) $salePayload['cashier_id'] !== $cashierId) {
            return [
                'idempotency_key' => $salePayload['idempotency_key'],
                'status' => SaleStatus::Rejected->value,
                'message' => 'Sale shop or cashier does not match the authenticated session.',
            ];
        }

        $payloadHash = hash('sha256', json_encode($this->normaliseSalePayload($salePayload), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $existing = Sale::query()
            ->where('shop_id', $shopId)
            ->where('idempotency_key', $salePayload['idempotency_key'])
            ->first();

        if ($existing !== null) {
            if ($existing->payload_hash !== null && $existing->payload_hash !== $payloadHash) {
                return [
                    'idempotency_key' => $salePayload['idempotency_key'],
                    'status' => SaleStatus::Rejected->value,
                    'message' => 'The same idempotency key was submitted with a different payload.',
                ];
            }

            return [
                'idempotency_key' => $salePayload['idempotency_key'],
                'status' => SaleStatus::Duplicate->value,
                'sale_id' => $existing->id,
            ];
        }

        try {
            $sale = DB::transaction(function () use ($salePayload, $cashierId, $shopId, $payloadHash): Sale {
                $sale = Sale::query()->create([
                    'shop_id' => $shopId,
                    'cashier_id' => $cashierId,
                    'idempotency_key' => $salePayload['idempotency_key'],
                    'status' => SaleStatus::Queued,
                    'sold_at' => $salePayload['sold_at'],
                    'subtotal' => $salePayload['subtotal'],
                    'vat_total' => $salePayload['vat_total'],
                    'total' => $salePayload['total'],
                    'payload_hash' => $payloadHash,
                ]);

                foreach ($salePayload['items'] as $item) {
                    $stock = ShopStock::query()
                        ->where('shop_id', $shopId)
                        ->where('product_id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if ($stock === null) {
                        throw new \RuntimeException("No local stock row exists for product {$item['product_id']}.");
                    }

                    if ((float) $stock->quantity < (float) $item['quantity']) {
                        throw new \RuntimeException("Insufficient shop stock for product {$item['product_id']}.");
                    }

                    $stock->update([
                        'quantity' => (float) $stock->quantity - (float) $item['quantity'],
                    ]);

                    SaleItem::query()->create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'sku' => $item['sku'] ?? null,
                        'barcode' => $item['barcode'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'vat_rate' => $item['vat_rate'],
                        'vat_amount' => $item['vat_amount'],
                        'line_total' => $item['line_total'],
                    ]);
                }

                $sale->update([
                    'status' => SaleStatus::Synced,
                    'synced_at' => now(),
                ]);

                return $sale;
            });
        } catch (\RuntimeException $exception) {
            return [
                'idempotency_key' => $salePayload['idempotency_key'],
                'status' => SaleStatus::Rejected->value,
                'message' => $exception->getMessage(),
            ];
        }

        return [
            'idempotency_key' => $salePayload['idempotency_key'],
            'status' => SaleStatus::Synced->value,
            'sale_id' => $sale->id,
        ];
    }

    protected function normaliseSalePayload(array $salePayload): array
    {
        $normalisedItems = array_map(static function (array $item): array {
            return [
                'product_id' => (int) $item['product_id'],
                'product_name' => (string) $item['product_name'],
                'sku' => isset($item['sku']) ? (string) $item['sku'] : null,
                'barcode' => isset($item['barcode']) ? (string) $item['barcode'] : null,
                'quantity' => (string) $item['quantity'],
                'unit_price' => (string) $item['unit_price'],
                'vat_rate' => (string) $item['vat_rate'],
                'vat_amount' => (string) $item['vat_amount'],
                'line_total' => (string) $item['line_total'],
            ];
        }, $salePayload['items']);

        return [
            'idempotency_key' => (string) $salePayload['idempotency_key'],
            'shop_id' => (int) $salePayload['shop_id'],
            'cashier_id' => (int) $salePayload['cashier_id'],
            'sold_at' => (string) $salePayload['sold_at'],
            'subtotal' => (string) $salePayload['subtotal'],
            'vat_total' => (string) $salePayload['vat_total'],
            'total' => (string) $salePayload['total'],
            'items' => $normalisedItems,
        ];
    }
}
