<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\CustomerPayment;

class PostsCustomerPaymentToLedger
{
    public function __construct(protected JournalEntryService $journalEntryService)
    {
    }

    public function handle(CustomerPayment $payment, ?int $userId = null)
    {
        if ($payment->journal_entry_id !== null) {
            return $payment->journalEntry;
        }

        $cashOrBankAccount = $this->resolveRequiredAccount(config('accounting.receivables.payment_cash_or_bank_account_code'));
        $accountsReceivableAccount = $this->resolveRequiredAccount(config('accounting.receivables.accounts_receivable_account_code'));

        $entryDate = $payment->paid_at !== null ? (string) $payment->paid_at : now()->toDateString();

        $journalEntry = $this->journalEntryService->create([
            'shop_id' => $this->resolvePostingShopId(),
            'entry_date' => $entryDate,
            'reference' => 'AR-PAY-'.$payment->id,
            'description' => 'Auto-posted customer payment',
            'source' => 'customer_payment',
            'created_by' => $userId,
        ], [
            [
                'account_id' => $cashOrBankAccount->id,
                'debit' => $payment->amount,
                'credit' => 0,
                'description' => 'Cash or bank receipt',
            ],
            [
                'account_id' => $accountsReceivableAccount->id,
                'debit' => 0,
                'credit' => $payment->amount,
                'description' => 'Reduce accounts receivable',
            ],
        ]);

        $payment->update([
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
            throw new \RuntimeException("Required receivables account with code [{$code}] is missing or inactive.");
        }

        return $account;
    }

    protected function resolvePostingShopId(): int
    {
        $configuredShopId = config('accounting.receivables.posting_shop_id');

        if ($configuredShopId !== null && $configuredShopId !== '') {
            return (int) $configuredShopId;
        }

        $firstShopId = \App\Models\Shop::query()->orderBy('id')->value('id');

        if ($firstShopId === null) {
            throw new \RuntimeException('Receivables ledger posting requires at least one shop.');
        }

        return (int) $firstShopId;
    }
}
