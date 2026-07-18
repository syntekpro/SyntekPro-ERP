<?php

namespace App\Services\Accounting;

use App\Exceptions\UnbalancedJournalEntryException;
use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class JournalEntryService
{
    public function create(array $header, array $lines): JournalEntry
    {
        if ($lines === []) {
            throw new UnbalancedJournalEntryException('A journal entry must include at least one line.');
        }

        $debitCents = 0;
        $creditCents = 0;

        $accountIds = collect($lines)
            ->pluck('account_id')
            ->filter()
            ->map(fn ($accountId) => (int) $accountId)
            ->unique()
            ->values();

        $accounts = Account::query()
            ->whereIn('id', $accountIds)
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        $activeAccountIds = array_map('intval', $accounts);

        $normalizedLines = [];

        foreach ($lines as $line) {
            $accountId = (int) Arr::get($line, 'account_id');
            $debit = $this->normalizeAmount(Arr::get($line, 'debit', 0));
            $credit = $this->normalizeAmount(Arr::get($line, 'credit', 0));

            if (! in_array($accountId, $activeAccountIds, true)) {
                throw new UnbalancedJournalEntryException('All journal lines must reference an active account.');
            }

            if (($debit > 0 && $credit > 0) || ($debit <= 0 && $credit <= 0)) {
                throw new UnbalancedJournalEntryException('Each journal line must have either debit or credit amount.');
            }

            $debitCents += $this->toCents($debit);
            $creditCents += $this->toCents($credit);

            $normalizedLines[] = [
                'account_id' => $accountId,
                'debit' => $debit,
                'credit' => $credit,
                'description' => Arr::get($line, 'description'),
            ];
        }

        if ($debitCents !== $creditCents) {
            throw new UnbalancedJournalEntryException('Journal entry is not balanced.');
        }

        $entryDate = (string) Arr::get($header, 'entry_date', now()->toDateString());

        $closedPeriod = FiscalPeriod::query()
            ->whereDate('period_start', '<=', $entryDate)
            ->whereDate('period_end', '>=', $entryDate)
            ->where('is_closed', true)
            ->exists();

        if ($closedPeriod) {
            throw new UnbalancedJournalEntryException('Cannot post journal entry into a closed fiscal period.');
        }

        $shopIdRaw = Arr::get($header, 'shop_id');
        $shopId = ($shopIdRaw === null || $shopIdRaw === '') ? null : (int) $shopIdRaw;

        return DB::transaction(function () use ($header, $normalizedLines, $shopId, $entryDate): JournalEntry {
            $entry = JournalEntry::query()->create([
                'shop_id' => $shopId,
                'sale_id' => Arr::get($header, 'sale_id'),
                'entry_date' => $entryDate,
                'reference' => Arr::get($header, 'reference'),
                'description' => Arr::get($header, 'description'),
                'source' => Arr::get($header, 'source', 'manual'),
                'created_by' => Arr::get($header, 'created_by'),
            ]);

            foreach ($normalizedLines as $line) {
                $entry->lines()->create($line);
            }

            return $entry;
        });
    }

    protected function normalizeAmount(mixed $amount): float
    {
        return round((float) $amount, 2);
    }

    protected function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
