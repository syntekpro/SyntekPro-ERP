<section class="space-y-6">
    <div class="flex items-start gap-3">
        <x-icon-tile color="ledger" size="lg">
            <x-lucide-git-compare-arrows class="h-7 w-7" />
        </x-icon-tile>
        <div>
            <p class="text-xs font-medium text-ledger">Back office module</p>
            <h1 class="mt-1 text-3xl font-semibold text-ink">Bank Reconciliation</h1>
            <p class="mt-2 max-w-2xl text-sm text-muted">Pick a bank account to import a statement and match transactions against the ledger.</p>
        </div>
    </div>

    @if ($bankAccounts->isEmpty())
        <x-empty-state
            icon="landmark"
            title="No bank accounts yet"
            message="Add a bank account first, linked to a GL asset account, before you can reconcile statements."
            action-label="Add bank account"
            :action-href="route('bank-accounts.create')"
        />
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($bankAccounts as $bankAccount)
                <a href="{{ route('bank-reconciliation.show', $bankAccount) }}" class="block">
                    <x-card surface="surface" class="h-full transition hover:border-brass/50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-base font-semibold text-ink">{{ $bankAccount->bank_name }}</h2>
                                <p class="mt-1 text-sm text-muted">{{ $bankAccount->account->name ?? '—' }}</p>
                                @if ($bankAccount->iban)
                                    <p class="mt-1 font-mono text-xs text-subtle">{{ $bankAccount->iban }}</p>
                                @endif
                            </div>
                            @if ($bankAccount->unmatched_count > 0)
                                <x-badge tone="warning">{{ $bankAccount->unmatched_count }} unmatched</x-badge>
                            @else
                                <x-badge tone="success">All matched</x-badge>
                            @endif
                        </div>
                    </x-card>
                </a>
            @endforeach
        </div>
    @endif
</section>
