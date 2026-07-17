<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\SupplierBill;

class PostsSupplierBillToLedger
{
    public function __construct(protected JournalEntryService $journalEntryService)
    {
    }

    public function handle(SupplierBill $bill, ?int $userId = null)
    {
        if ($bill->journal_entry_id !== null) {
            return $bill->journalEntry;
        }

        $inventoryAccount = $this->resolveRequiredAccount(config('accounting.purchasing.inventory_account_code'));
        $vatReceivableAccount = $this->resolveRequiredAccount(config('accounting.purchasing.input_vat_receivable_account_code'));
        $accountsPayableAccount = $this->resolveRequiredAccount(config('accounting.purchasing.accounts_payable_account_code'));

        $lines = [[
            'account_id' => $inventoryAccount->id,
            'debit' => $bill->subtotal,
            'credit' => 0,
            'description' => 'Inventory received from supplier',
        ]];

        if ((float) $bill->vat_total > 0) {
            $lines[] = [
                'account_id' => $vatReceivableAccount->id,
                'debit' => $bill->vat_total,
                'credit' => 0,
                'description' => 'Input VAT receivable',
            ];
        }

        $lines[] = [
            'account_id' => $accountsPayableAccount->id,
            'debit' => 0,
            'credit' => $bill->total,
            'description' => 'Accounts payable to supplier',
        ];

        $entryDate = $bill->bill_date !== null ? (string) $bill->bill_date : now()->toDateString();

        $journalEntry = $this->journalEntryService->create([
            'shop_id' => $this->resolvePostingShopId(),
            'entry_date' => $entryDate,
            'reference' => 'AP-BILL-'.$bill->id,
            'description' => 'Auto-posted supplier bill from PO receiving',
            'source' => 'supplier_bill',
            'created_by' => $userId,
        ], $lines);

        $bill->update([
            'journal_entry_id' => $journalEntry->id,
        ]);

        return $journalEntry;
    }

    protected function resolveRequiredAccount(?string $code): Account
    {
        $account = Account::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if ($account === null) {
            throw new \RuntimeException("Required purchasing account with code [{$code}] is missing or inactive.");
        }

        return $account;
    }

    protected function resolvePostingShopId(): int
    {
        $configuredShopId = config('accounting.purchasing.posting_shop_id');

        if ($configuredShopId !== null && $configuredShopId !== '') {
            return (int) $configuredShopId;
        }

        $firstShopId = \App\Models\Shop::query()->orderBy('id')->value('id');

        if ($firstShopId === null) {
            throw new \RuntimeException('Purchasing ledger posting requires at least one shop.');
        }

        return (int) $firstShopId;
    }
}
