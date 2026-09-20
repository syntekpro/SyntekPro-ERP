<section class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">{{ __('Chart of Accounts') }}</p>
            <h1 class="mt-2 text-4xl font-semibold text-ink">{{ __('Chart of Accounts') }}</h1>
            <p class="mt-2 text-sm text-muted">{{ __('Shared Back Office chart used by all shops for ledger postings.') }}</p>
        </div>
        @can('create', \App\Models\Account::class)
            <a href="{{ route('accounts.create') }}" class="btn-primary">{{ __('Add account') }}</a>
        @endcan
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <input
            wire:model.live.debounce.300ms="search"
            type="text"
            placeholder="{{ __('Search by account code or name') }}"
            class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none"
        />

        <div class="mt-5 overflow-hidden rounded-ui border border-line table-baseline">
            <table class="min-w-full text-start text-sm ui-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('Code') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Name') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Type') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Parent') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                        <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @forelse ($accounts as $account)
                        <tr>
                            <td class="px-4 py-4 figure-mono font-medium text-ink">{{ $account->code }}</td>
                            <td class="px-4 py-4">{{ $account->name }}</td>
                            <td class="px-4 py-4">{{ str($account->account_type->value)->title() }}</td>
                            <td class="px-4 py-4">{{ $account->parent?->code }} {{ $account->parent?->name }}</td>
                            <td class="px-4 py-4">
                                <x-status-badge :tone="$account->is_active ? 'success' : 'danger'">{{ $account->is_active ? __('Active') : __('Inactive') }}</x-status-badge>
                            </td>
                            <td class="px-4 py-4 text-end">
                                <div class="flex justify-end gap-2">
                                    @can('update', $account)
                                        <a href="{{ route('accounts.edit', $account) }}" class="btn-secondary btn-size-sm">{{ __('Edit') }}</a>
                                        <button wire:click="setActive({{ $account->id }}, {{ $account->is_active ? 'false' : 'true' }})" wire:confirm="{{ $account->is_active ? __('Deactivate this account?') : __('Activate this account?') }}" class="btn-warning btn-size-sm">{{ $account->is_active ? __('Deactivate') : __('Activate') }}</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-muted">{{ __('No accounts found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $accounts->links() }}
        </div>
    </div>
</section>
