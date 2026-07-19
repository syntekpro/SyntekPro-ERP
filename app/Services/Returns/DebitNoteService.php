<?php

namespace App\Services\Returns;

use App\Enums\SupplierBillStatus;
use App\Models\Account;
use App\Models\DebitNote;
use App\Models\DebitNoteItem;
use App\Models\SupplierBill;
use App\Models\WarehouseStock;
use App\Services\Accounting\JournalEntryService;
use App\Services\Inventory\UnitConversionService;
use App\Services\Numbering\DocumentNumberService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DebitNoteService
{
    public function __construct(
        protected JournalEntryService $journalEntryService,
        protected DocumentNumberService $documentNumberService,
        protected UnitConversionService $unitConversionService,
    ) {
    }

    public function record(int $supplierBillId, string $noteDate, array $returnLines, ?string $notes = null, ?int $userId = null): DebitNote
    {
        return DB::transaction(function () use ($supplierBillId, $noteDate, $returnLines, $notes, $userId): DebitNote {
            $bill = SupplierBill::query()
                ->whereKey($supplierBillId)
                ->lockForUpdate()
                ->with(['items.product', 'supplier', 'purchaseOrder'])
                ->firstOrFail();

            $linesByBillItemId = collect($returnLines)
                ->keyBy(fn (array $line) => (int) ($line['supplier_bill_item_id'] ?? 0));

            $debitNoteItems = [];
            $subtotal = 0.0;
            $vatTotal = 0.0;

            foreach ($bill->items as $billItem) {
                $requestedQty = round((float) Arr::get($linesByBillItemId->get($billItem->id, []), 'quantity', 0), 3);
                $unitId = Arr::get($linesByBillItemId->get($billItem->id, []), 'unit_id');
                $unitId = $unitId !== null ? (int) $unitId : (int) $billItem->unit_id;

                if ($requestedQty <= 0) {
                    continue;
                }

                $alreadyReturnedQty = round((float) DebitNoteItem::query()
                    ->where('supplier_bill_item_id', $billItem->id)
                    ->sum('base_quantity'), 3);

                $requestedBaseQty = $this->unitConversionService->toBaseQuantity($billItem->product, $requestedQty, $unitId);
                $remainingQty = round((float) $billItem->base_quantity - $alreadyReturnedQty, 3);

                if ($requestedBaseQty > $remainingQty) {
                    throw new \RuntimeException('Return quantity cannot exceed the received quantity remaining for that supplier bill item.');
                }

                $warehouseStock = WarehouseStock::query()
                    ->where('warehouse_id', $bill->warehouse_id)
                    ->where('product_id', $billItem->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($warehouseStock === null || round((float) $warehouseStock->quantity, 3) < $requestedBaseQty) {
                    throw new \RuntimeException('Warehouse stock on hand cannot cover the requested supplier return quantity.');
                }

                $warehouseStock->update([
                    'quantity' => round((float) $warehouseStock->quantity - $requestedBaseQty, 3),
                ]);

                $perBaseUnitVat = (float) $billItem->base_quantity > 0
                    ? round((float) $billItem->vat_amount / (float) $billItem->base_quantity, 6)
                    : 0.0;
                $baseUnitCost = (float) $billItem->base_quantity > 0
                    ? round(((float) $billItem->unit_cost * (float) $billItem->quantity) / (float) $billItem->base_quantity, 6)
                    : (float) $billItem->unit_cost;
                $unitCost = round($baseUnitCost * $this->unitConversionService->factorFor($billItem->product, $unitId), 2);

                $netAmount = round($requestedBaseQty * $baseUnitCost, 2);
                $lineVatAmount = round($requestedBaseQty * $perBaseUnitVat, 2);
                $grossAmount = round($netAmount + $lineVatAmount, 2);

                $subtotal += $netAmount;
                $vatTotal += $lineVatAmount;

                $debitNoteItems[] = [
                    'supplier_bill_item_id' => $billItem->id,
                    'product_id' => $billItem->product_id,
                    'unit_id' => $unitId,
                    'description' => $billItem->description,
                    'quantity' => $requestedQty,
                    'base_quantity' => $requestedBaseQty,
                    'unit_cost' => $unitCost,
                    'vat_rate' => $billItem->vat_rate,
                    'net_amount' => $netAmount,
                    'vat_amount' => $lineVatAmount,
                    'gross_amount' => $grossAmount,
                ];
            }

            if ($debitNoteItems === []) {
                throw new \RuntimeException('At least one returned supplier bill item with positive quantity is required.');
            }

            $subtotal = round($subtotal, 2);
            $vatTotal = round($vatTotal, 2);
            $total = round($subtotal + $vatTotal, 2);
            $appliedToBillBalance = round(min($total, (float) $bill->outstanding_balance), 2);
            $excessAmount = round($total - $appliedToBillBalance, 2);
            $remainingBalance = round((float) $bill->outstanding_balance - $appliedToBillBalance, 2);

            $bill->update([
                'outstanding_balance' => $remainingBalance,
                'status' => $remainingBalance <= 0
                    ? SupplierBillStatus::Paid
                    : ($remainingBalance < (float) $bill->total ? SupplierBillStatus::PartiallyPaid : SupplierBillStatus::Open),
            ]);

            $debitNote = DebitNote::query()->create([
                'debit_note_number' => $this->documentNumberService->next('debit_note'),
                'supplier_bill_id' => $bill->id,
                'purchase_order_id' => $bill->purchase_order_id,
                'supplier_id' => $bill->supplier_id,
                'warehouse_id' => $bill->warehouse_id,
                'note_date' => $noteDate,
                'subtotal' => $subtotal,
                'vat_total' => $vatTotal,
                'total' => $total,
                'applied_to_bill_balance' => $appliedToBillBalance,
                'excess_amount' => $excessAmount,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            foreach ($debitNoteItems as $debitNoteItem) {
                $debitNote->items()->create($debitNoteItem);
            }

            $journalEntry = $this->journalEntryService->create([
                'shop_id' => null,
                'entry_date' => $noteDate,
                'reference' => $debitNote->debit_note_number,
                'description' => 'Auto-posted debit note for supplier bill '.$bill->bill_number,
                'source' => 'debit_note',
                'created_by' => $userId,
            ], $this->buildJournalLines($subtotal, $vatTotal, $appliedToBillBalance, $excessAmount));

            $debitNote->update([
                'journal_entry_id' => $journalEntry->id,
            ]);

            return $debitNote->fresh(['items', 'journalEntry', 'supplierBill']);
        });
    }

    protected function buildJournalLines(float $subtotal, float $vatTotal, float $appliedToBillBalance, float $excessAmount): array
    {
        $inventoryAccount = $this->resolveRequiredAccount(config('accounting.purchasing.inventory_account_code'));
        $vatReceivableAccount = $this->resolveRequiredAccount(config('accounting.purchasing.input_vat_receivable_account_code'));
        $accountsPayableAccount = $this->resolveRequiredAccount(config('accounting.purchasing.accounts_payable_account_code'));
        $dueFromSupplierAccount = $this->resolveRequiredAccount(config('accounting.returns.due_from_supplier_account_code'));

        $lines = [[
            'account_id' => $inventoryAccount->id,
            'debit' => 0,
            'credit' => $subtotal,
            'description' => 'Inventory returned to supplier',
        ]];

        if ($appliedToBillBalance > 0) {
            $lines[] = [
                'account_id' => $accountsPayableAccount->id,
                'debit' => $appliedToBillBalance,
                'credit' => 0,
                'description' => 'Reverse accounts payable for supplier return up to open bill balance',
            ];
        }

        if ($excessAmount > 0) {
            $lines[] = [
                'account_id' => $dueFromSupplierAccount->id,
                'debit' => $excessAmount,
                'credit' => 0,
                'description' => 'Supplier return excess awaiting manual recovery or settlement',
            ];
        }

        if ($vatTotal > 0) {
            $lines[] = [
                'account_id' => $vatReceivableAccount->id,
                'debit' => 0,
                'credit' => $vatTotal,
                'description' => 'Reverse input VAT receivable for supplier return',
            ];
        }

        return $lines;
    }

    protected function resolveRequiredAccount(?string $code): Account
    {
        $account = Account::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if ($account === null) {
            throw new \RuntimeException("Required return account with code [{$code}] is missing or inactive.");
        }

        return $account;
    }
}