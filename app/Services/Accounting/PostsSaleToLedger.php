<?php

namespace App\Services\Accounting;

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
        $salesRevenueAccount = $this->resolveRequiredAccount(config('accounting.pos.sales_revenue_account_code'));
        $vatPayableAccount = $this->resolveRequiredAccount(config('accounting.pos.vat_payable_account_code'));

        $lines = [[
            'account_id' => $cashAccount->id,
            'debit' => $sale->total,
            'credit' => 0,
            'description' => 'POS sale cash receipt',
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
