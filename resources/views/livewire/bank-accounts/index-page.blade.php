@php
    $canCreate = auth()->user()?->can('create', \App\Models\BankAccount::class) ?? false;
@endphp

<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex items-start gap-3">
            <x-icon-tile color="ledger" size="lg">
                <x-lucide-landmark class="h-7 w-7" />
            </x-icon-tile>
            <div>
                <p class="text-xs font-medium text-ledger">Back office module</p>
                <h1 class="mt-1 text-3xl font-semibold text-ink">Bank Accounts</h1>
                <p class="mt-2 max-w-2xl text-sm text-muted">Link your bank accounts to the chart of accounts and manage statement reconciliation.</p>
            </div>
        </div>

        @if ($canCreate)
            <a href="{{ route('bank-accounts.create') }}" class="btn-primary">Add bank account</a>
        @endif
    </div>

    @if (session('status'))
        <div class="rounded-ui border border-line bg-panel px-4 py-3 text-sm text-ink">{{ session('status') }}</div>
    @endif

    <x-card surface="surface">
        <x-slot:header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-ink">Directory</h2>
                    <p class="mt-1 text-sm text-muted">Search by bank name or IBAN.</p>
                </div>

                <div class="relative w-full lg:max-w-sm">
                    <x-lucide-search class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-subtle" />
                    <x-input type="search" wire:model.live.debounce.300ms="search" placeholder="Search" class="ps-9" />
                </div>
            </div>
        </x-slot:header>

        @if ($bankAccounts->count())
            <x-table>
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">Bank</th>
                        <th class="px-4 py-3 font-medium">GL Account</th>
                        <th class="px-4 py-3 font-medium">IBAN</th>
                        <th class="px-4 py-3 font-medium">Unmatched lines</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @foreach ($bankAccounts as $bankAccount)
                        <tr>
                            <td class="px-4 py-4 font-medium text-ink">{{ $bankAccount->bank_name }}</td>
                            <td class="px-4 py-4 text-muted">{{ $bankAccount->account?->code }} &mdash; {{ $bankAccount->account?->name }}</td>
                            <td class="px-4 py-4 figure-mono text-muted">{{ $bankAccount->iban ?: '—' }}</td>
                            <td class="px-4 py-4">
                                @if ($bankAccount->unmatched_count > 0)
                                    <x-status-badge tone="warning">{{ $bankAccount->unmatched_count }} unmatched</x-status-badge>
                                @else
                                    <x-status-badge tone="success">All matched</x-status-badge>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <x-status-badge :tone="$bankAccount->is_active ? 'success' : 'danger'">{{ $bankAccount->is_active ? 'Active' : 'Inactive' }}</x-status-badge>
                            </td>
                            <td class="px-4 py-4 text-end">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('bank-reconciliation.show', $bankAccount) }}" class="btn-secondary btn-size-sm">Reconcile</a>
                                    @can('update', $bankAccount)
                                        <a href="{{ route('bank-accounts.edit', $bankAccount) }}" class="btn-secondary btn-size-sm">Edit</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>

            <div class="mt-4">{{ $bankAccounts->links() }}</div>
        @else
            <x-empty-state
                icon="landmark"
                title="No bank accounts yet"
                message="Add a bank account and link it to a GL asset account to start reconciling statements."
                :action-label="$canCreate ? 'Add bank account' : null"
                :action-href="$canCreate ? route('bank-accounts.create') : null"
            />
        @endif
    </x-card>
</section>
