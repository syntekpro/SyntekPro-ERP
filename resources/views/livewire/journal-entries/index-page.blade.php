<section class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Phase 6</p>
            <h1 class="mt-2 text-4xl font-semibold text-white">Journal Entries</h1>
            <p class="mt-2 text-sm text-stone-300">Shop-scoped double-entry ledger history.</p>
        </div>

        @can('create', \App\Models\JournalEntry::class)
            <a href="{{ route('journal-entries.create') }}" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Post adjustment</a>
        @endcan
    </div>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Shop</label>
                <select wire:model.live="shop_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" {{ $isShopScopedUser ? 'disabled' : '' }}>
                    <option value="">All shops</option>
                    @foreach ($this->shopOptions as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Start date</label>
                <input wire:model.live="start_date" type="date" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">End date</label>
                <input wire:model.live="end_date" type="date" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
            </div>
        </div>

        <div class="mt-5 overflow-hidden rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                <thead class="bg-stone-900/80 text-stone-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Shop</th>
                        <th class="px-4 py-3 font-medium">Source</th>
                        <th class="px-4 py-3 font-medium">Reference</th>
                        <th class="px-4 py-3 font-medium">Description</th>
                        <th class="px-4 py-3 font-medium">Lines</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-stone-950/60 text-stone-200">
                    @forelse ($entries as $entry)
                        <tr>
                            <td class="px-4 py-4 text-white">{{ $entry->entry_date?->toDateString() }}</td>
                            <td class="px-4 py-4">{{ $entry->shop?->name }}</td>
                            <td class="px-4 py-4">{{ str($entry->source)->replace('_', ' ')->title() }}</td>
                            <td class="px-4 py-4">{{ $entry->reference }}</td>
                            <td class="px-4 py-4">{{ $entry->description }}</td>
                            <td class="px-4 py-4">{{ $entry->lines()->count() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-stone-400">No journal entries found.</td>
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
