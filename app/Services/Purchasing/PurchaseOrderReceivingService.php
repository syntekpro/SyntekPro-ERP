<?php

namespace App\Services\Purchasing;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierBillStatus;
use App\Models\PurchaseOrder;
use App\Models\SupplierBill;
use App\Models\WarehouseStock;
use App\Services\Accounting\PostsSupplierBillToLedger;
use App\Services\Inventory\UnitConversionService;
use App\Services\Numbering\DocumentNumberService;
use App\Services\Settings\BusinessSettingsService;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReceivingService
{
    public function __construct(
        protected PostsSupplierBillToLedger $postsSupplierBillToLedger,
        protected DocumentNumberService $documentNumberService,
        protected BusinessSettingsService $businessSettingsService,
        protected UnitConversionService $unitConversionService,
    )
    {
    }

    public function receive(PurchaseOrder $purchaseOrder, array $receiptLines, ?int $userId = null): SupplierBill
    {
        return DB::transaction(function () use ($purchaseOrder, $receiptLines, $userId): SupplierBill {
            $lockedPurchaseOrder = PurchaseOrder::query()
                ->whereKey($purchaseOrder->id)
                ->lockForUpdate()
                ->with(['items.product', 'supplier'])
                ->firstOrFail();

            if (! in_array($lockedPurchaseOrder->status, [PurchaseOrderStatus::Submitted, PurchaseOrderStatus::PartiallyReceived], true)) {
                throw new \RuntimeException('Purchase order cannot be received from its current status.');
            }

            $linesByItemId = collect($receiptLines)
                ->keyBy(fn (array $line) => (int) ($line['purchase_order_item_id'] ?? 0));

            $billLines = [];
            $subtotal = 0.0;
            $vatTotal = 0.0;

            foreach ($lockedPurchaseOrder->items as $item) {
                $requestedQty = round((float) ($linesByItemId[$item->id]['quantity_received'] ?? 0), 3);
                $receiptUnitId = isset($linesByItemId[$item->id]['unit_id']) && $linesByItemId[$item->id]['unit_id'] !== null
                    ? (int) $linesByItemId[$item->id]['unit_id']
                    : (int) ($item->unit_id ?: $this->unitConversionService->baseUnitId($item->product));

                if ($requestedQty <= 0) {
                    continue;
                }

                $requestedBaseQty = $this->unitConversionService->toBaseQuantity($item->product, $requestedQty, $receiptUnitId);
                $remainingBaseQty = round((float) $item->base_quantity_ordered - (float) $item->base_quantity_received, 3);

                if ($requestedBaseQty > $remainingBaseQty) {
                    throw new \RuntimeException('Received quantity cannot exceed the remaining ordered quantity.');
                }

                $warehouseStock = WarehouseStock::query()
                    ->where('warehouse_id', $lockedPurchaseOrder->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($warehouseStock) {
                    $warehouseStock->update([
                        'quantity' => round((float) $warehouseStock->quantity + $requestedBaseQty, 3),
                    ]);
                } else {
                    WarehouseStock::query()->create([
                        'warehouse_id' => $lockedPurchaseOrder->warehouse_id,
                        'product_id' => $item->product_id,
                        'quantity' => $requestedBaseQty,
                    ]);
                }

                $receivedInOrderUnit = $this->unitConversionService->fromBaseQuantity($item->product, $requestedBaseQty, (int) $item->unit_id);

                $item->update([
                    'quantity_received' => round((float) $item->quantity_received + $receivedInOrderUnit, 3),
                    'base_quantity_received' => round((float) $item->base_quantity_received + $requestedBaseQty, 3),
                ]);

                $unitFactor = $this->unitConversionService->factorFor($item->product, $receiptUnitId);
                $baseUnitCost = $unitFactor > 0 ? round((float) $item->unit_cost / $unitFactor, 6) : (float) $item->unit_cost;

                $this->updateProductAverageCost((int) $item->product_id, $requestedBaseQty, $baseUnitCost);

                $netAmount = round($requestedQty * (float) $item->unit_cost, 2);
                $vatRate = $this->businessSettingsService->vatRate();
                $lineVat = round($netAmount * ($vatRate / 100), 2);
                $grossAmount = round($netAmount + $lineVat, 2);

                $subtotal += $netAmount;
                $vatTotal += $lineVat;

                $billLines[] = [
                    'product_id' => $item->product_id,
                    'unit_id' => $receiptUnitId,
                    'description' => $item->product->name,
                    'quantity' => $requestedQty,
                    'base_quantity' => $requestedBaseQty,
                    'unit_cost' => $item->unit_cost,
                    'vat_rate' => $vatRate,
                    'net_amount' => $netAmount,
                    'vat_amount' => $lineVat,
                    'gross_amount' => $grossAmount,
                ];
            }

            if ($billLines === []) {
                throw new \RuntimeException('At least one item with positive received quantity is required.');
            }

            $total = round($subtotal + $vatTotal, 2);

            $billDate = now()->toDateString();
            $dueDate = now()->addDays((int) $lockedPurchaseOrder->supplier->payment_terms_days)->toDateString();

            $bill = SupplierBill::query()->create([
                'bill_number' => $this->documentNumberService->next('supplier_bills'),
                'supplier_id' => $lockedPurchaseOrder->supplier_id,
                'purchase_order_id' => $lockedPurchaseOrder->id,
                'warehouse_id' => $lockedPurchaseOrder->warehouse_id,
                'bill_date' => $billDate,
                'due_date' => $dueDate,
                'subtotal' => $subtotal,
                'vat_total' => $vatTotal,
                'total' => $total,
                'outstanding_balance' => $total,
                'status' => SupplierBillStatus::Open,
                'notes' => 'Auto-generated from PO receiving',
                'created_by' => $userId,
            ]);

            foreach ($billLines as $billLine) {
                $bill->items()->create($billLine);
            }

            $this->postsSupplierBillToLedger->handle($bill, $userId);

            $hasRemaining = $lockedPurchaseOrder->items()
                ->whereRaw('base_quantity_received < base_quantity_ordered')
                ->exists();

            $lockedPurchaseOrder->update([
                'status' => $hasRemaining ? PurchaseOrderStatus::PartiallyReceived : PurchaseOrderStatus::Received,
            ]);

            return $bill->fresh(['items', 'journalEntry']);
        });
    }

    protected function updateProductAverageCost(int $productId, float $receivedQuantity, float $receivedUnitCost): void
    {
        $product = \App\Models\Product::query()
            ->whereKey($productId)
            ->lockForUpdate()
            ->firstOrFail();

        $totalOnHandAfter = (float) \App\Models\WarehouseStock::query()
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->sum('quantity');

        $totalOnHandBefore = round($totalOnHandAfter - $receivedQuantity, 3);

        $currentAverageCost = (float) ($product->average_cost ?? 0);
        $existingValue = $currentAverageCost * max($totalOnHandBefore, 0);
        $receivedValue = $receivedUnitCost * $receivedQuantity;
        $newTotalQuantity = max($totalOnHandBefore, 0) + $receivedQuantity;

        if ($newTotalQuantity <= 0) {
            return;
        }

        $newAverage = round(($existingValue + $receivedValue) / $newTotalQuantity, 4);

        $product->update([
            'average_cost' => $newAverage,
            'cost_price' => round($newAverage, 2),
        ]);
    }
}
