<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use App\Services\Banking\BankReconciliationMatcher;
use Illuminate\Console\Command;

/**
 * Read-only: prints suggested matches without confirming any of them.
 * Usage: php artisan bank:reconcile-preview {bank_account_id}
 */
class BankReconcilePreview extends Command
{
    protected $signature = 'bank:reconcile-preview {bank_account_id}';

    protected $description = 'Preview auto-suggested reconciliation matches for a bank account (read-only)';

    public function handle(BankReconciliationMatcher $matcher): int
    {
        $bankAccount = BankAccount::find($this->argument('bank_account_id'));
        if ($bankAccount === null) {
            $this->error('Bank account not found.');

            return self::FAILURE;
        }

        $suggestions = $matcher->suggestMatches($bankAccount);

        if ($suggestions->isEmpty()) {
            $this->info('No auto-matchable pairs found (either everything is matched, or nothing lines up on amount+date).');

            return self::SUCCESS;
        }

        $this->table(
            ['Statement Line', 'Date', 'Amount', 'Journal Entry', 'GL Date', 'Day Diff'],
            $suggestions->map(fn (array $s): array => [
                $s['statement_line']->description,
                $s['statement_line']->transaction_date->format('Y-m-d'),
                $s['statement_line']->amount,
                $s['journal_entry_line']->description ?: "JE #{$s['journal_entry_line']->journal_entry_id}",
                $s['journal_entry_line']->journalEntry->entry_date,
                $s['date_diff_days'],
            ])->toArray()
        );

        $this->info("{$suggestions->count()} suggested match(es). Nothing has been confirmed - this is a preview only.");

        return self::SUCCESS;
    }
}
