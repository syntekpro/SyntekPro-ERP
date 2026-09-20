<section class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">{{ __('General Ledger') }}</p>
            <h1 class="mt-2 text-4xl font-semibold text-ink">{{ __('Journal Entries') }}</h1>
            <p class="mt-2 text-sm text-muted">{{ __('Shop-scoped double-entry ledger history.') }}</p>
        </div>

        @can('create', \App\Models\JournalEntry::class)
            <a href="{{ route('journal-entries.create') }}" class="btn-primary">{{ __('Post adjustment') }}</a>
        @endcan
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-medium text-ink">{{ __('Shop') }}</label>
                <select wire:model.live="shop_id" class="ui-select w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" {{ $isShopScopedUser ? 'disabled' : '' }}>
                    <option value="">{{ __('All shops') }}</option>
                    @foreach ($this->shopOptions as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-ink">{{ __('Start date') }}</label>
                <input wire:model.live="start_date" type="date" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-ink">{{ __('End date') }}</label>
                <input wire:model.live="end_date" type="date" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded-ui border border-line table-baseline">
            <table class="min-w-full text-start text-sm ui-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ __('Date') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Shop') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Source') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Reference') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Description') }}</th>
                        <th class="px-4 py-3 font-medium">{{ __('Lines') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @forelse ($entries as $entry)
                        <tr>
                            <td class="px-4 py-4 figure-mono font-medium text-ink">{{ $entry->entry_date?->toDateString() }}</td>
                            <td class="px-4 py-4">{{ $entry->shop?->name }}</td>
                            <td class="px-4 py-4">{{ str($entry->source)->replace('_', ' ')->title() }}</td>
                            <td class="px-4 py-4">{{ $entry->reference }}</td>
                            <td class="px-4 py-4">{{ $entry->description }}</td>
                            <td class="px-4 py-4 figure-mono">{{ $entry->lines()->count() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-muted">{{ __('No journal entries found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $entries->links() }}
        </div>
    </div>
</section>
