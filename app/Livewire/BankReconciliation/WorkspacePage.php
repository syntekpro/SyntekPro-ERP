<?php

namespace App\Livewire\BankReconciliation;

use App\Models\BankAccount;
use App\Models\BankStatementLine;
use App\Models\JournalEntryLine;
use App\Services\Banking\BankReconciliationMatcher;
use App\Services\Banking\BankStatementCsvImporter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class WorkspacePage extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    #[Locked]
    public BankAccount $bankAccount;

    // --- Upload + column mapping state ---
    public $csvFile = null;

    public array $previewHeaderRow = [];

    public bool $hasHeaderRow = true;

    public string $dateFormat = 'd/m/Y';

    public ?int $dateCol = null;

    public ?int $descCol = null;

    public bool $useSplitDebitCredit = true;

    public ?int $amountCol = null;

    public ?int $debitCol = null;

    public ?int $creditCol = null;

    public ?int $refCol = null;

    public ?int $balanceCol = null;

    // --- Manual match state ---
    public ?int $manualMatchStatementLineId = null;

    public function mount(BankAccount $bankAccount): void
    {
        $this->authorize('view', $bankAccount);
        $this->bankAccount = $bankAccount;
    }

    public function updatedCsvFile(): void
    {
        $this->previewHeaderRow = [];

        if ($this->csvFile === null) {
            return;
        }

        $handle = fopen($this->csvFile->getRealPath(), 'r');
        if ($handle === false) {
            return;
        }

        $firstRow = fgetcsv($handle);
        fclose($handle);

        $this->previewHeaderRow = $firstRow !== false ? $firstRow : [];
    }

    public function import(BankStatementCsvImporter $importer)
    {
        $this->authorize('update', $this->bankAccount);

        $this->validate([
            'csvFile' => ['required', 'file', 'mimes:csv,txt'],
            'dateCol' => ['required', 'integer', 'min:0'],
            'descCol' => ['required', 'integer', 'min:0'],
            'dateFormat' => ['required', 'string'],
        ]);

        $mapping = [
            'transaction_date' => $this->dateCol,
            'description' => $this->descCol,
        ];

        if ($this->useSplitDebitCredit) {
            $this->validate([
                'debitCol' => ['required', 'integer', 'min:0'],
                'creditCol' => ['required', 'integer', 'min:0'],
            ]);
            $mapping['debit'] = $this->debitCol;
            $mapping['credit'] = $this->creditCol;
        } else {
            $this->validate(['amountCol' => ['required', 'integer', 'min:0']]);
            $mapping['amount'] = $this->amountCol;
        }

        if ($this->refCol !== null) {
            $mapping['reference'] = $this->refCol;
        }
        if ($this->balanceCol !== null) {
            $mapping['running_balance'] = $this->balanceCol;
        }

        try {
            $import = $importer->import(
                bankAccount: $this->bankAccount,
                csvPath: $this->csvFile->getRealPath(),
                columnMapping: $mapping,
                originalFilename: $this->csvFile->getClientOriginalName(),
                importedByUserId: auth()->id(),
                dateFormat: $this->dateFormat,
                hasHeaderRow: $this->hasHeaderRow,
            );
        } catch (\InvalidArgumentException $e) {
            $this->addError('csvFile', $e->getMessage());

            return;
        }

        session()->flash('status', "Imported {$import->imported_count} transaction(s), skipped {$import->skipped_count}.");

        $this->reset(['csvFile', 'previewHeaderRow', 'dateCol', 'descCol', 'amountCol', 'debitCol', 'creditCol', 'refCol', 'balanceCol']);
    }

    public function confirmMatch(int $statementLineId, int $journalEntryLineId, BankReconciliationMatcher $matcher): void
    {
        $this->authorize('update', $this->bankAccount);

        $statementLine = BankStatementLine::query()
            ->where('bank_account_id', $this->bankAccount->id)
            ->findOrFail($statementLineId);

        $journalEntryLine = JournalEntryLine::query()
            ->where('account_id', $this->bankAccount->account_id)
            ->findOrFail($journalEntryLineId);

        $matcher->confirmMatch($statementLine, $journalEntryLine, auth()->id());

        session()->flash('status', 'Match confirmed.');
        $this->manualMatchStatementLineId = null;
    }

    public function ignoreLine(int $statementLineId, BankReconciliationMatcher $matcher): void
    {
        $this->authorize('update', $this->bankAccount);

        $statementLine = BankStatementLine::query()
            ->where('bank_account_id', $this->bankAccount->id)
            ->findOrFail($statementLineId);

        $matcher->ignore($statementLine);

        session()->flash('status', 'Line marked as ignored.');
    }

    public function openManualMatch(int $statementLineId): void
    {
        $this->manualMatchStatementLineId = $statementLineId;
    }

    public function closeManualMatch(): void
    {
        $this->manualMatchStatementLineId = null;
    }

    public function render(BankReconciliationMatcher $matcher)
    {
        $suggestions = $matcher->suggestMatches($this->bankAccount);
        $suggestedStatementLineIds = $suggestions->pluck('statement_line.id');

        $unmatchedLines = BankStatementLine::query()
            ->where('bank_account_id', $this->bankAccount->id)
            ->where('match_status', 'unmatched')
            ->whereNotIn('id', $suggestedStatementLineIds)
            ->orderByDesc('transaction_date')
            ->get();

        $manualMatchCandidates = collect();
        if ($this->manualMatchStatementLineId !== null) {
            $alreadyMatchedIds = BankStatementLine::query()
                ->where('bank_account_id', $this->bankAccount->id)
                ->whereNotNull('matched_journal_entry_line_id')
                ->pluck('matched_journal_entry_line_id');

            $manualMatchCandidates = JournalEntryLine::query()
                ->where('account_id', $this->bankAccount->account_id)
                ->whereNotIn('id', $alreadyMatchedIds)
                ->with('journalEntry')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();
        }

        $recentlyMatched = BankStatementLine::query()
            ->where('bank_account_id', $this->bankAccount->id)
            ->where('match_status', 'matched')
            ->with('matchedJournalEntryLine.journalEntry')
            ->orderByDesc('matched_at')
            ->limit(20)
            ->get();

        return view('livewire.bank-reconciliation.workspace-page', [
            'suggestions' => $suggestions,
            'unmatchedLines' => $unmatchedLines,
            'manualMatchCandidates' => $manualMatchCandidates,
            'recentlyMatched' => $recentlyMatched,
        ]);
    }
}
