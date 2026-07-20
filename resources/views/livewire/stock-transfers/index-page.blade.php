<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Inventory Transfers</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Stock transfers</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Create warehouse-to-shop transfers, dispatch them, and receive them into shop stock with audited status transitions.</p>
        </div>

        @can('create', \App\Models\StockTransfer::class)
            <a href="{{ route('stock-transfers.create') }}" class="inline-flex rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Create transfer</a>
        @endcan
    </div>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white">Transfer queue</h2>
                <p class="mt-1 text-sm text-stone-400">Track pending, in-transit, and received stock movements.</p>
            </div>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by warehouse or shop" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none placeholder:text-stone-500 lg:max-w-sm" />
        </div>

        <div class="space-y-4">
            @forelse ($transfers as $transfer)
                <article class="rounded-2xl border border-white/10 bg-stone-950/60 p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-lg font-semibold text-white">{{ $transfer->warehouse->name }} to {{ $transfer->destinationShop->name }}</h3>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $transfer->status->value === 'pending' ? 'bg-amber-500/15 text-amber-200' : ($transfer->status->value === 'in_transit' ? 'bg-sky-500/15 text-sky-200' : 'bg-emerald-500/15 text-emerald-200') }}">{{ str($transfer->status->value)->replace('_', ' ')->title() }}</span>
                            </div>
                            <p class="mt-2 text-sm text-stone-300">{{ $transfer->notes ?: 'No notes attached.' }}</p>
                            <p class="mt-3 text-xs uppercase tracking-[0.28em] text-stone-500">Created {{ $transfer->created_at->diffForHumans() }}</p>
                        </div>

                        <div class="flex gap-2">
                            @can('markInTransit', $transfer)
                                <button wire:click="markInTransit({{ $transfer->id }})" wire:confirm="Mark this transfer in transit?" class="rounded-xl border border-sky-400/20 px-3 py-2 text-xs font-semibold text-sky-200 transition hover:bg-sky-500/10">Dispatch</button>
                            @endcan
                            @can('receive', $transfer)
                                <button wire:click="receive({{ $transfer->id }})" wire:confirm="Receive this transfer and move stock?" class="rounded-xl border border-emerald-400/20 px-3 py-2 text-xs font-semibold text-emerald-200 transition hover:bg-emerald-500/10">Receive</button>
                            @endcan
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
                        <table class="min-w-full divide-y divide-white/10 text-start text-sm">
                            <thead class="bg-stone-900/80 text-stone-400">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Product</th>
                                    <th class="px-4 py-3 font-medium">Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-stone-950/40 text-stone-200">
                                @foreach ($transfer->items as $item)
                                    <tr>
                                        <td class="px-4 py-3">{{ $item->product->name }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $item->quantity, 3) }} {{ $item->unit?->code ?? $item->product->baseUnit?->code ?? 'PCS' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-white/10 bg-stone-950/60 px-4 py-10 text-center text-stone-400">No transfers match the current filter.</div>
            @endforelse
        </div>

        <div class="mt-5">{{ $transfers->links() }}</div>
    </div>
</section>