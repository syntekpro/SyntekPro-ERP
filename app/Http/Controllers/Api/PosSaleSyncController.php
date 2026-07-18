<?php

namespace App\Http\Controllers\Api;

use App\Enums\SalePaymentMethod;
use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Services\Accounting\PostsSaleToLedger;
use App\Services\Numbering\DocumentNumberService;
use App\Support\ZatcaQrEncoder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'sales.*.payment_method' => ['required', 'string', 'in:cash,card,credit_account'],
            'sales.*.customer_id' => ['nullable', 'integer', 'exists:customers,id'],
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
        $paymentMethod = SalePaymentMethod::from((string) $salePayload['payment_method']);

        if ($paymentMethod === SalePaymentMethod::CreditAccount && empty($salePayload['customer_id'])) {
            return [
                'idempotency_key' => $salePayload['idempotency_key'],
                'status' => SaleStatus::Rejected->value,
                'message' => 'Customer is required for credit-account sales.',
            ];
        }

        if ($paymentMethod !== SalePaymentMethod::CreditAccount && ! empty($salePayload['customer_id'])) {
            return [
                'idempotency_key' => $salePayload['idempotency_key'],
                'status' => SaleStatus::Rejected->value,
                'message' => 'Customer can only be set when payment method is credit_account.',
            ];
        }

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
            $sale = DB::transaction(function () use ($salePayload, $cashierId, $shopId, $payloadHash, $paymentMethod): Sale {
                $customer = null;
                $dueDate = null;
                $outstandingBalance = 0;

                if ($paymentMethod === SalePaymentMethod::CreditAccount) {
                    $customer = Customer::query()
                        ->whereKey((int) $salePayload['customer_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (! $customer->is_active) {
                        throw new \RuntimeException('Customer is inactive and cannot be used for credit sales.');
                    }

                    $existingOutstanding = (float) Sale::query()
                        ->where('customer_id', $customer->id)
                        ->where('payment_method', SalePaymentMethod::CreditAccount->value)
                        ->where('outstanding_balance', '>', 0)
                        ->lockForUpdate()
                        ->sum('outstanding_balance');

                    $newOutstanding = round($existingOutstanding + (float) $salePayload['total'], 2);

                    if ($customer->credit_limit !== null && $newOutstanding > (float) $customer->credit_limit) {
                        throw new \RuntimeException('Credit limit exceeded for this customer.');
                    }

                    $dueDate = Carbon::parse($salePayload['sold_at'])
                        ->addDays((int) $customer->payment_terms_days)
                        ->toDateString();

                    $outstandingBalance = $salePayload['total'];
                }

                $sale = Sale::query()->create([
                    'shop_id' => $shopId,
                    'cashier_id' => $cashierId,
                    'idempotency_key' => $salePayload['idempotency_key'],
                    'invoice_number' => app(DocumentNumberService::class)->next('sales', 'INV-'),
                    'status' => SaleStatus::Queued,
                    'sold_at' => $salePayload['sold_at'],
                    'subtotal' => $salePayload['subtotal'],
                    'vat_total' => $salePayload['vat_total'],
                    'total' => $salePayload['total'],
                    'payment_method' => $paymentMethod,
                    'customer_id' => $customer?->id,
                    'due_date' => $dueDate,
                    'outstanding_balance' => $outstandingBalance,
                    'payload_hash' => $payloadHash,
                    'invoice_uuid' => (string) Str::uuid(),
                ]);

                $sellerData = $this->resolveSellerData($shopId);

                foreach ($salePayload['items'] as $item) {
                    $product = Product::query()->find($item['product_id']);

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
                        'unit_cost' => $product?->cost_price ?? 0,
                        'vat_rate' => $item['vat_rate'],
                        'vat_amount' => $item['vat_amount'],
                        'line_total' => $item['line_total'],
                    ]);
                }

                app(PostsSaleToLedger::class)->handle($sale, $cashierId);

                $qrPayload = ZatcaQrEncoder::encode([
                    1 => $sellerData['seller_name'],
                    2 => $sellerData['vat_number'],
                    3 => $sale->sold_at?->toIso8601String() ?? now()->toIso8601String(),
                    4 => number_format((float) $sale->total, 2, '.', ''),
                    5 => number_format((float) $sale->vat_total, 2, '.', ''),
                ]);

                $sale->update([
                    'status' => SaleStatus::Synced,
                    'zatca_qr_payload' => $qrPayload,
                    'invoice_hash' => hash('sha256', implode('|', [
                        $sale->id,
                        $sale->invoice_uuid,
                        $sale->total,
                        $sale->vat_total,
                        $sale->sold_at,
                    ])),
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
            'payment_method' => (string) ($salePayload['payment_method'] ?? SalePaymentMethod::Cash->value),
            'customer_id' => isset($salePayload['customer_id']) ? (int) $salePayload['customer_id'] : null,
            'items' => $normalisedItems,
        ];
    }

    protected function resolveSellerData(int $shopId): array
    {
        $shop = Shop::query()->find($shopId);

        $sellerName = $shop?->legal_name
            ?: config('zatca.seller_legal_name')
            ?: $shop?->name
            ?: 'SyntekPro';
        $vatNumber = $shop?->vat_registration_number
            ?: config('zatca.seller_vat_registration_number')
            ?: '000000000000000';

        return [
            'seller_name' => $sellerName,
            'vat_number' => $vatNumber,
        ];
    }
}
