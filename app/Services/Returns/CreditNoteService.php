<?php

namespace App\Services\Returns;

use App\Enums\SalePaymentMethod;
use App\Models\Account;
use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\Sale;
use App\Models\ShopStock;
use App\Services\Accounting\JournalEntryService;
use App\Services\Numbering\DocumentNumberService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreditNoteService
{
    public function __construct(
        protected JournalEntryService $journalEntryService,
        protected DocumentNumberService $documentNumberService,
    ) {
    }

    public function record(int $saleId, string $noteDate, array $returnLines, ?string $notes = null, ?int $userId = null): CreditNote
    {
        return DB::transaction(function () use ($saleId, $noteDate, $returnLines, $notes, $userId): CreditNote {
            $sale = Sale::query()
                ->whereKey($saleId)
                ->lockForUpdate()
                ->with(['items.product', 'customer'])
                ->firstOrFail();

            $linesBySaleItemId = collect($returnLines)
                ->keyBy(fn (array $line) => (int) ($line['sale_item_id'] ?? 0));

            $creditNoteItems = [];
            $subtotal = 0.0;
            $vatTotal = 0.0;
            $sellableCostTotal = 0.0;
            $damagedCostTotal = 0.0;

            foreach ($sale->items as $saleItem) {
                $requestedQty = round((float) Arr::get($linesBySaleItemId->get($saleItem->id, []), 'quantity', 0), 3);

                if ($requestedQty <= 0) {
                    continue;
                }

                $condition = (string) Arr::get($linesBySaleItemId->get($saleItem->id, []), 'condition');

                if (! in_array($condition, ['sellable', 'damaged'], true)) {
                    throw new \RuntimeException('Credit note item condition must be sellable or damaged.');
                }

                $alreadyReturnedQty = round((float) CreditNoteItem::query()
                    ->where('sale_item_id', $saleItem->id)
                    ->sum('quantity'), 3);

                $remainingQty = round((float) $saleItem->quantity - $alreadyReturnedQty, 3);

                if ($requestedQty > $remainingQty) {
                    throw new \RuntimeException('Return quantity cannot exceed the sold quantity remaining for that sale item.');
                }

                $perUnitVat = (float) $saleItem->quantity > 0
                    ? round((float) $saleItem->vat_amount / (float) $saleItem->quantity, 6)
                    : 0.0;

                $netAmount = round($requestedQty * (float) $saleItem->unit_price, 2);
                $lineVatAmount = round($requestedQty * $perUnitVat, 2);
                $grossAmount = round($netAmount + $lineVatAmount, 2);
                $lineCostAmount = round($requestedQty * (float) $saleItem->unit_cost, 2);

                if ($condition === 'sellable') {
                    $shopStock = ShopStock::query()
                        ->where('shop_id', $sale->shop_id)
                        ->where('product_id', $saleItem->product_id)
                        ->lockForUpdate()
                        ->first();

                    if ($shopStock !== null) {
                        $shopStock->update([
                            'quantity' => round((float) $shopStock->quantity + $requestedQty, 3),
                        ]);
                    } else {
                        ShopStock::query()->create([
                            'shop_id' => $sale->shop_id,
                            'product_id' => $saleItem->product_id,
                            'quantity' => $requestedQty,
                        ]);
                    }

                    $sellableCostTotal += $lineCostAmount;
                } else {
                    $damagedCostTotal += $lineCostAmount;
                }

                $subtotal += $netAmount;
                $vatTotal += $lineVatAmount;

                $creditNoteItems[] = [
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'product_name' => $saleItem->product_name,
                    'quantity' => $requestedQty,
                    'condition' => $condition,
                    'unit_price' => $saleItem->unit_price,
                    'unit_cost' => $saleItem->unit_cost,
                    'vat_rate' => $saleItem->vat_rate,
                    'net_amount' => $netAmount,
                    'vat_amount' => $lineVatAmount,
                    'gross_amount' => $grossAmount,
                ];
            }

            if ($creditNoteItems === []) {
                throw new \RuntimeException('At least one returned sale item with positive quantity is required.');
            }

            $subtotal = round($subtotal, 2);
            $vatTotal = round($vatTotal, 2);
            $total = round($subtotal + $vatTotal, 2);

            $appliedToSaleBalance = 0.0;
            $refundAmount = $total;

            if ($sale->payment_method === SalePaymentMethod::CreditAccount) {
                $appliedToSaleBalance = round(min($total, (float) $sale->outstanding_balance), 2);
                $refundAmount = round($total - $appliedToSaleBalance, 2);

                $sale->update([
                    'outstanding_balance' => round((float) $sale->outstanding_balance - $appliedToSaleBalance, 2),
                ]);
            }

            $creditNote = CreditNote::query()->create([
                'credit_note_number' => $this->documentNumberService->next('credit_note'),
                'sale_id' => $sale->id,
                'shop_id' => $sale->shop_id,
                'customer_id' => $sale->customer_id,
                'note_date' => $noteDate,
                'subtotal' => $subtotal,
                'vat_total' => $vatTotal,
                'total' => $total,
                'applied_to_sale_balance' => $appliedToSaleBalance,
                'refund_amount' => $refundAmount,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            foreach ($creditNoteItems as $creditNoteItem) {
                $creditNote->items()->create($creditNoteItem);
            }

            $journalEntry = $this->journalEntryService->create([
                'shop_id' => $sale->shop_id,
                'entry_date' => $noteDate,
                'reference' => $creditNote->credit_note_number,
                'description' => 'Auto-posted credit note for sale '.$sale->invoice_number,
                'source' => 'credit_note',
                'created_by' => $userId,
            ], $this->buildJournalLines($sale, $subtotal, $vatTotal, $appliedToSaleBalance, $refundAmount, $sellableCostTotal, $damagedCostTotal));

            $creditNote->update([
                'journal_entry_id' => $journalEntry->id,
            ]);

            return $creditNote->fresh(['items', 'journalEntry', 'sale']);
        });
    }

    protected function buildJournalLines(Sale $sale, float $subtotal, float $vatTotal, float $appliedToSaleBalance, float $refundAmount, float $sellableCostTotal, float $damagedCostTotal): array
    {
        $salesRevenueAccount = $this->resolveRequiredAccount(config('accounting.pos.sales_revenue_account_code'));
        $vatPayableAccount = $this->resolveRequiredAccount(config('accounting.pos.vat_payable_account_code'));
        $cogsAccount = $this->resolveRequiredAccount(config('accounting.pos.cogs_account_code'));
        $inventoryAccount = $this->resolveRequiredAccount(config('accounting.purchasing.inventory_account_code'));
        $receivablesAccount = $this->resolveRequiredAccount(config('accounting.receivables.accounts_receivable_account_code'));
        $refundAccount = $this->resolveRequiredAccount(config('accounting.receivables.payment_cash_or_bank_account_code'));

        $lines = [[
            'account_id' => $salesRevenueAccount->id,
            'debit' => $subtotal,
            'credit' => 0,
            'description' => 'Sales return revenue reversal',
        ]];

        if ($vatTotal > 0) {
            $lines[] = [
                'account_id' => $vatPayableAccount->id,
                'debit' => $vatTotal,
                'credit' => 0,
                'description' => 'Sales return VAT reversal',
            ];
        }

        if ($appliedToSaleBalance > 0) {
            $lines[] = [
                'account_id' => $receivablesAccount->id,
                'debit' => 0,
                'credit' => $appliedToSaleBalance,
                'description' => 'Reduce receivable on original credit sale',
            ];
        }

        if ($sale->payment_method !== SalePaymentMethod::CreditAccount || $refundAmount > 0) {
            $lines[] = [
                'account_id' => $refundAccount->id,
                'debit' => 0,
                'credit' => $refundAmount,
                'description' => 'Customer refund for credit note',
            ];
        }

        if ($sellableCostTotal > 0) {
            $lines[] = [
                'account_id' => $inventoryAccount->id,
                'debit' => $sellableCostTotal,
                'credit' => 0,
                'description' => 'Sellable goods returned to inventory',
            ];

            $lines[] = [
                'account_id' => $cogsAccount->id,
                'debit' => 0,
                'credit' => $sellableCostTotal,
                'description' => 'Reverse cost of goods sold for returned inventory',
            ];
        }

        if ($damagedCostTotal > 0) {
            $damagedGoodsAccount = $this->resolveRequiredAccount(config('accounting.returns.damaged_goods_account_code'));

            $lines[] = [
                'account_id' => $damagedGoodsAccount->id,
                'debit' => $damagedCostTotal,
                'credit' => 0,
                'description' => 'Damaged returned goods write-off',
            ];

            $lines[] = [
                'account_id' => $cogsAccount->id,
                'debit' => 0,
                'credit' => $damagedCostTotal,
                'description' => 'Reverse cost of goods sold for damaged return',
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