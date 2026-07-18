<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\SupplierPayment;

class PostsSupplierPaymentToLedger
{
    public function __construct(protected JournalEntryService $journalEntryService)
    {
    }

    public function handle(SupplierPayment $payment, ?int $userId = null)
    {
        if ($payment->journal_entry_id !== null) {
            return $payment->journalEntry;
        }

        $accountsPayableAccount = $this->resolveRequiredAccount(config('accounting.purchasing.accounts_payable_account_code'));
        $cashOrBankAccount = $this->resolveRequiredAccount(config('accounting.purchasing.payment_cash_or_bank_account_code'));

        $entryDate = $payment->paid_at !== null ? (string) $payment->paid_at : now()->toDateString();

        $journalEntry = $this->journalEntryService->create([
            'shop_id' => null,
            'entry_date' => $entryDate,
            'reference' => 'AP-PAY-'.$payment->id,
            'description' => 'Auto-posted supplier payment',
            'source' => 'supplier_payment',
            'created_by' => $userId,
        ], [
            [
                'account_id' => $accountsPayableAccount->id,
                'debit' => $payment->amount,
                'credit' => 0,
                'description' => 'Reduce accounts payable',
            ],
            [
                'account_id' => $cashOrBankAccount->id,
                'debit' => 0,
                'credit' => $payment->amount,
                'description' => 'Cash or bank outflow',
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
            throw new \RuntimeException("Required purchasing account with code [{$code}] is missing or inactive.");
        }

        return $account;
    }
}
