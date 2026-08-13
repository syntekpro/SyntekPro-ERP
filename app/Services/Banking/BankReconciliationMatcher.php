<?php

namespace App\Services\Banking;

use App\Models\BankAccount;
use App\Models\BankStatementLine;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Suggests matches between unmatched bank statement lines and unmatched GL
 * journal entry lines posted to the same bank account's GL account.
 *
 * Matching strategy (intentionally simple - exact amount is load-bearing,
 * date is a tiebreaker/confidence signal, not a hard filter, since a
 * transaction can clear the bank a day or two after it's journaled):
 *   1. Exact amount match (to the cent) is required - no fuzzy amount matching.
 *   2. Among same-amount candidates, prefer the one closest in date.
 *   3. Only 1:1 matches are auto-suggested. Anything ambiguous (multiple
 *      GL lines with the same amount near the same date) is left for manual
 *      review rather than guessed - a wrong auto-match on money is worse
 *      than a slower manual one.
 *
 * This only *suggests* matches (confirmMatch persists one); nothing here
 * writes to the GL.
 */
class BankReconciliationMatcher
{
    private const DATE_WINDOW_DAYS = 10;

    /**
     * @return Collection<int, array{statement_line: BankStatementLine, journal_entry_line: JournalEntryLine, date_diff_days: int}>
     */
    public function suggestMatches(BankAccount $bankAccount): Collection
    {
        $unmatchedStatementLines = BankStatementLine::query()
            ->where('bank_account_id', $bankAccount->id)
            ->where('match_status', 'unmatched')
            ->get();

        if ($unmatchedStatementLines->isEmpty()) {
            return collect();
        }

        $alreadyMatchedLineIds = BankStatementLine::query()
            ->where('bank_account_id', $bankAccount->id)
            ->whereNotNull('matched_journal_entry_line_id')
            ->pluck('matched_journal_entry_line_id');

        $candidateJournalLines = JournalEntryLine::query()
            ->where('account_id', $bankAccount->account_id)
            ->whereNotIn('id', $alreadyMatchedLineIds)
            ->with('journalEntry')
            ->get();

        $suggestions = collect();
        $consumedJournalLineIds = [];

        foreach ($unmatchedStatementLines as $statementLine) {
            $candidates = $candidateJournalLines
                ->reject(fn (JournalEntryLine $line): bool => in_array($line->id, $consumedJournalLineIds, true))
                ->filter(function (JournalEntryLine $line) use ($statementLine): bool {
                    $netAmount = (float) $line->debit - (float) $line->credit;
                    // Statement amount: positive = money in. Journal line on
                    // an asset (bank) account: debit increases the balance
                    // (money in), so compare against debit-minus-credit.
                    return abs($netAmount - (float) $statementLine->amount) < 0.005;
                })
                ->filter(function (JournalEntryLine $line) use ($statementLine): bool {
                    $entryDate = Carbon::parse($line->journalEntry->entry_date);
                    return abs($entryDate->diffInDays($statementLine->transaction_date)) <= self::DATE_WINDOW_DAYS;
                });

            if ($candidates->count() !== 1) {
                // Zero matches (nothing to suggest) or multiple ambiguous
                // matches (don't guess) - either way, skip auto-suggestion.
                continue;
            }

            $match = $candidates->first();
            $consumedJournalLineIds[] = $match->id;

            $suggestions->push([
                'statement_line' => $statementLine,
                'journal_entry_line' => $match,
                'date_diff_days' => abs(
                    Carbon::parse($match->journalEntry->entry_date)->diffInDays($statementLine->transaction_date)
                ),
            ]);
        }

        return $suggestions;
    }

    public function confirmMatch(BankStatementLine $statementLine, JournalEntryLine $journalEntryLine, ?int $matchedByUserId = null): void
    {
        $statementLine->update([
            'match_status' => 'matched',
            'matched_journal_entry_line_id' => $journalEntryLine->id,
            'matched_by' => $matchedByUserId,
            'matched_at' => now(),
        ]);
    }

    public function ignore(BankStatementLine $statementLine): void
    {
        $statementLine->update(['match_status' => 'ignored']);
    }
}
