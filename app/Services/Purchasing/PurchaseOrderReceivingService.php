<?php

namespace App\Services\Purchasing;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SupplierBillStatus;
use App\Models\PurchaseOrder;
use App\Models\SupplierBill;
use App\Models\WarehouseStock;
use App\Services\Accounting\PostsSupplierBillToLedger;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReceivingService
{
    public function __construct(protected PostsSupplierBillToLedger $postsSupplierBillToLedger)
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

                if ($requestedQty <= 0) {
                    continue;
                }

                $remaining = round((float) $item->quantity_ordered - (float) $item->quantity_received, 3);

                if ($requestedQty > $remaining) {
                    throw new \RuntimeException('Received quantity cannot exceed the remaining ordered quantity.');
                }

                $warehouseStock = WarehouseStock::query()
                    ->where('warehouse_id', $lockedPurchaseOrder->warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($warehouseStock) {
                    $warehouseStock->update([
                        'quantity' => round((float) $warehouseStock->quantity + $requestedQty, 3),
                    ]);
                } else {
                    WarehouseStock::query()->create([
                        'warehouse_id' => $lockedPurchaseOrder->warehouse_id,
                        'product_id' => $item->product_id,
                        'quantity' => $requestedQty,
                    ]);
                }

                $item->update([
                    'quantity_received' => round((float) $item->quantity_received + $requestedQty, 3),
                ]);

                $netAmount = round($requestedQty * (float) $item->unit_cost, 2);
                $lineVat = round($netAmount * ((float) $item->vat_rate / 100), 2);
                $grossAmount = round($netAmount + $lineVat, 2);

                $subtotal += $netAmount;
                $vatTotal += $lineVat;

                $billLines[] = [
                    'product_id' => $item->product_id,
                    'description' => $item->product->name,
                    'quantity' => $requestedQty,
                    'unit_cost' => $item->unit_cost,
                    'vat_rate' => $item->vat_rate,
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
                'bill_number' => $this->nextBillNumber(),
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
                ->whereRaw('quantity_received < quantity_ordered')
                ->exists();

            $lockedPurchaseOrder->update([
                'status' => $hasRemaining ? PurchaseOrderStatus::PartiallyReceived : PurchaseOrderStatus::Received,
            ]);

            return $bill->fresh(['items', 'journalEntry']);
        });
    }

    protected function nextBillNumber(): string
    {
        $nextId = (int) SupplierBill::query()->max('id') + 1;

        return 'BILL-'.str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);
    }
}
