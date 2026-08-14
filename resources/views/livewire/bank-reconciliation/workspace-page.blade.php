<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex items-start gap-3">
            <x-icon-tile color="ledger" size="lg">
                <x-lucide-git-compare-arrows class="h-7 w-7" />
            </x-icon-tile>
            <div>
                <p class="text-xs font-medium text-ledger">Back office module</p>
                <h1 class="mt-1 text-3xl font-semibold text-ink">{{ __('Reconcile') }} — {{ $bankAccount->bank_name }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-muted">{{ $bankAccount->account->name ?? '' }} · {{ $bankAccount->currency_code }}</p>
            </div>
        </div>
        <x-button variant="secondary" :href="route('bank-reconciliation.index')">{{ __('Back to accounts') }}</x-button>
    </div>

    @if (session('status'))
        <div class="rounded-ui border border-line bg-panel px-4 py-3 text-sm text-ink">{{ session('status') }}</div>
    @endif

    {{-- Import CSV --}}
    <x-card surface="surface">
        <x-slot:header>
            <h2 class="text-lg font-semibold text-ink">{{ __('Import statement') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('Upload a CSV export from your bank, then tell us which column is which.') }}</p>
        </x-slot:header>

        <form wire:submit="import" class="space-y-5">
            <div>
                <label class="text-sm font-medium text-ink">{{ __('CSV file') }}</label>
                <input type="file" wire:model="csvFile" accept=".csv,.txt" class="mt-1 block w-full text-sm text-muted file:me-3 file:rounded-ui file:border file:border-line file:bg-panel file:px-3 file:py-2 file:text-sm file:font-medium file:text-ink" />
                @error('csvFile') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                <div wire:loading wire:target="csvFile" class="mt-1 text-xs text-muted">{{ __('Reading file…') }}</div>
            </div>

            @if (!empty($previewHeaderRow))
                <div class="rounded-ui border border-line bg-panel p-3">
                    <p class="text-xs font-medium uppercase tracking-wide text-subtle">{{ __('First row of your file (use these numbers below)') }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($previewHeaderRow as $index => $value)
                            <span class="inline-flex items-center gap-1 rounded-ui border border-line bg-surface px-2 py-1 text-xs">
                                <span class="font-mono font-semibold text-brass">{{ $index }}</span>
                                <span class="text-muted">{{ Str::limit($value, 24) }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="text-sm font-medium text-ink">{{ __('Date column #') }}</label>
                        <x-input type="number" min="0" wire:model="dateCol" />
                        @error('dateCol') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink">{{ __('Date format') }}</label>
                        <x-input type="text" wire:model="dateFormat" placeholder="d/m/Y" />
                        <p class="mt-1 text-xs text-subtle">{{ __('e.g. d/m/Y for 31/07/2026, Y-m-d for 2026-07-31') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink">{{ __('Description column #') }}</label>
                        <x-input type="number" min="0" wire:model="descCol" />
                        @error('descCol') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink">{{ __('Reference column #') }} <span class="text-subtle">({{ __('optional') }})</span></label>
                        <x-input type="number" min="0" wire:model="refCol" />
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink">{{ __('Running balance column #') }} <span class="text-subtle">({{ __('optional') }})</span></label>
                        <x-input type="number" min="0" wire:model="balanceCol" />
                    </div>
                    <div class="flex items-end gap-2 pb-2">
                        <input type="checkbox" wire:model="hasHeaderRow" id="hasHeaderRow" class="rounded border-line" />
                        <label for="hasHeaderRow" class="text-sm text-ink">{{ __('First row is a header (skip it)') }}</label>
                    </div>
                </div>

                <div class="rounded-ui border border-line bg-panel p-4">
                    <label class="flex items-center gap-2 text-sm font-medium text-ink">
                        <input type="checkbox" wire:model.live="useSplitDebitCredit" class="rounded border-line" />
                        {{ __('My bank uses separate debit/credit columns (uncheck if it has one signed amount column)') }}
                    </label>

                    @if ($useSplitDebitCredit)
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-ink">{{ __('Debit (money out) column #') }}</label>
                                <x-input type="number" min="0" wire:model="debitCol" />
                                @error('debitCol') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-ink">{{ __('Credit (money in) column #') }}</label>
                                <x-input type="number" min="0" wire:model="creditCol" />
                                @error('creditCol') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @else
                        <div class="mt-3">
                            <label class="text-sm font-medium text-ink">{{ __('Amount column # (positive = in, negative = out)') }}</label>
                            <x-input type="number" min="0" wire:model="amountCol" />
                            @error('amountCol') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                <x-button type="submit" wire:loading.attr="disabled" wire:target="import">
                    <span wire:loading.remove wire:target="import">{{ __('Import statement') }}</span>
                    <span wire:loading wire:target="import">{{ __('Importing…') }}</span>
                </x-button>
            @endif
        </form>
    </x-card>

    {{-- Suggested matches --}}
    <x-card surface="surface">
        <x-slot:header>
            <h2 class="text-lg font-semibold text-ink">{{ __('Suggested matches') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('Same amount, close dates - review and confirm.') }}</p>
        </x-slot:header>

        @if ($suggestions->isEmpty())
            <p class="text-sm text-muted">{{ __('No suggested matches right now.') }}</p>
        @else
            <x-table>
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-start">{{ __('Statement line') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('Amount') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('Matched journal entry') }}</th>
                        <th class="px-4 py-2 text-start">{{ __('Day diff') }}</th>
                        <th class="px-4 py-2 text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suggestions as $suggestion)
                        <tr class="border-t border-line">
                            <td class="px-4 py-2">
                                <p class="font-medium text-ink">{{ $suggestion['statement_line']->description }}</p>
                                <p class="text-xs text-subtle">{{ $suggestion['statement_line']->transaction_date->format('Y-m-d') }}</p>
                            </td>
                            <td class="px-4 py-2 font-mono">{{ number_format($suggestion['statement_line']->amount, 2) }}</td>
                            <td class="px-4 py-2">{{ $suggestion['journal_entry_line']->description ?: 'JE #'.$suggestion['journal_entry_line']->journal_entry_id }}</td>
                            <td class="px-4 py-2">{{ $suggestion['date_diff_days'] }}d</td>
                            <td class="px-4 py-2 text-end">
                                <x-button size="sm" variant="success" wire:click="confirmMatch({{ $suggestion['statement_line']->id }}, {{ $suggestion['journal_entry_line']->id }})">
                                    {{ __('Confirm') }}
                                </x-button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>
        @endif
    </x-card>

    {{-- Unmatched lines needing manual review --}}
    <x-card surface="surface">
        <x-slot:header>
            <h2 class="text-lg font-semibold text-ink">{{ __('Needs manual review') }}</h2>
            <p class="mt-1 text-sm text-muted">{{ __('No automatic match found - match by hand or ignore (e.g. bank fees not yet journaled).') }}</p>
        </x-slot:header>

        @if ($unmatchedLines->isEmpty())
            <p class="text-sm text-muted">{{ __('Nothing left to review.') }}</p>
        @else
            <div class="space-y-3">
                @foreach ($unmatchedLines as $line)
                    <div class="rounded-ui border border-line bg-panel p-4">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="font-medium text-ink">{{ $line->description }}</p>
                                <p class="text-xs text-subtle">{{ $line->transaction_date->format('Y-m-d') }} · <span class="font-mono">{{ number_format($line->amount, 2) }}</span></p>
                            </div>
                            <div class="flex gap-2">
                                <x-button size="sm" variant="secondary" wire:click="openManualMatch({{ $line->id }})">{{ __('Match manually') }}</x-button>
                                <x-button size="sm" variant="ghost" wire:click="ignoreLine({{ $line->id }})" wire:confirm="{{ __('Ignore this line? It will be excluded from reconciliation.') }}">{{ __('Ignore') }}</x-button>
                            </div>
                        </div>

                        @if ($manualMatchStatementLineId === $line->id)
                            <div class="mt-3 rounded-ui border border-brass/40 bg-surface p-3">
                                <div class="mb-2 flex items-center justify-between">
                                    <p class="text-xs font-medium uppercase tracking-wide text-subtle">{{ __('Pick a journal entry line to match') }}</p>
                                    <button type="button" wire:click="closeManualMatch" class="text-xs text-subtle hover:text-ink">{{ __('Cancel') }}</button>
                                </div>

                                @if ($manualMatchCandidates->isEmpty())
                                    <p class="text-sm text-muted">{{ __('No unmatched journal entry lines on this account.') }}</p>
                                @else
                                    <ul class="space-y-1">
                                        @foreach ($manualMatchCandidates as $candidate)
                                            <li class="flex items-center justify-between gap-2 rounded-ui px-2 py-1 text-sm hover:bg-panel">
                                                <span>
                                                    {{ $candidate->description ?: 'JE #'.$candidate->journal_entry_id }}
                                                    <span class="text-xs text-subtle">({{ $candidate->journalEntry->entry_date }})</span>
                                                </span>
                                                <span class="flex items-center gap-2">
                                                    <span class="font-mono text-xs">{{ number_format((float) $candidate->debit - (float) $candidate->credit, 2) }}</span>
                                                    <x-button size="sm" wire:click="confirmMatch({{ $line->id }}, {{ $candidate->id }})">{{ __('Select') }}</x-button>
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    {{-- Recently matched --}}
    @if ($recentlyMatched->isNotEmpty())
        <x-card surface="surface">
            <x-slot:header>
                <h2 class="text-lg font-semibold text-ink">{{ __('Recently matched') }}</h2>
            </x-slot:header>
            <ul class="space-y-2 text-sm">
                @foreach ($recentlyMatched as $line)
                    <li class="flex items-center justify-between border-t border-line pt-2 first:border-t-0 first:pt-0">
                        <span class="text-ink">{{ $line->description }}</span>
                        <span class="flex items-center gap-2 text-subtle">
                            <span class="font-mono">{{ number_format($line->amount, 2) }}</span>
                            <x-badge tone="success">{{ __('Matched') }}</x-badge>
                        </span>
                    </li>
                @endforeach
            </ul>
        </x-card>
    @endif
</section>
