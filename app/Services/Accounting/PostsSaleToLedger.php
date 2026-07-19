<?php

namespace App\Services\Accounting;

use App\Enums\SalePaymentMethod;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Sale;

class PostsSaleToLedger
{
    public function __construct(protected JournalEntryService $journalEntryService)
    {
    }

    public function handle(Sale $sale, int $userId): JournalEntry
    {
        $existing = JournalEntry::query()
            ->forAllShops()
            ->where('sale_id', $sale->id)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $cashAccount = $this->resolveRequiredAccount(config('accounting.pos.cash_account_code'));
        $accountsReceivableAccount = $this->resolveRequiredAccount(config('accounting.receivables.accounts_receivable_account_code'));
        $salesRevenueAccount = $this->resolveRequiredAccount(config('accounting.pos.sales_revenue_account_code'));
        $vatPayableAccount = $this->resolveRequiredAccount(config('accounting.pos.vat_payable_account_code'));
        $exciseTaxPayableAccount = $this->resolveRequiredAccount(config('accounting.pos.excise_tax_payable_account_code'));
        $cogsAccount = $this->resolveRequiredAccount(config('accounting.pos.cogs_account_code'));
        $inventoryAccount = $this->resolveRequiredAccount(config('accounting.purchasing.inventory_account_code'));

        $isCreditSale = $sale->payment_method === SalePaymentMethod::CreditAccount;
        $debitAccount = $isCreditSale ? $accountsReceivableAccount : $cashAccount;
        $debitDescription = $isCreditSale ? 'POS sale on customer credit account' : 'POS sale cash receipt';

        $lines = [[
            'account_id' => $debitAccount->id,
            'debit' => $sale->total,
            'credit' => 0,
            'description' => $debitDescription,
        ], [
            'account_id' => $salesRevenueAccount->id,
            'debit' => 0,
            'credit' => $sale->subtotal,
            'description' => 'POS sale revenue',
        ]];

        if ((float) $sale->vat_total > 0) {
            $lines[] = [
                'account_id' => $vatPayableAccount->id,
                'debit' => 0,
                'credit' => $sale->vat_total,
                'description' => 'Output VAT',
            ];
        }

        if ((float) $sale->excise_total > 0) {
            $lines[] = [
                'account_id' => $exciseTaxPayableAccount->id,
                'debit' => 0,
                'credit' => $sale->excise_total,
                'description' => 'Excise Tax Payable',
            ];
        }

        $cogsTotal = round((float) $sale->items()->sum(\Illuminate\Support\Facades\DB::raw('base_quantity * unit_cost')), 2);

        if ($cogsTotal > 0) {
            $lines[] = [
                'account_id' => $cogsAccount->id,
                'debit' => $cogsTotal,
                'credit' => 0,
                'description' => 'Cost of goods sold',
            ];

            $lines[] = [
                'account_id' => $inventoryAccount->id,
                'debit' => 0,
                'credit' => $cogsTotal,
                'description' => 'Inventory reduction for sold goods',
            ];
        }

        if ($isCreditSale && (float) $sale->outstanding_balance <= 0) {
            $dueDate = $sale->due_date !== null
                ? (string) $sale->due_date
                : now()->addDays((int) ($sale->customer?->payment_terms_days ?? 30))->toDateString();

            $sale->update([
                'due_date' => $dueDate,
                'outstanding_balance' => $sale->total,
            ]);
        }

        return $this->journalEntryService->create([
            'shop_id' => $sale->shop_id,
            'sale_id' => $sale->id,
            'entry_date' => $sale->sold_at?->toDateString() ?? now()->toDateString(),
            'reference' => 'POS-'.$sale->id,
            'description' => 'Auto-posted POS sale ledger entry',
            'source' => 'pos_sale',
            'created_by' => $userId,
        ], $lines);
    }

    protected function resolveRequiredAccount(?string $code): Account
    {
        $account = Account::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if ($account === null) {
            throw new \RuntimeException("Required accounting account with code [{$code}] is missing or inactive.");
        }

        return $account;
    }
}
