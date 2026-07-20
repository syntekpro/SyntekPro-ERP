<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Inventory Transfers</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Stock transfers</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Create warehouse-to-shop transfers, dispatch them, and receive them into shop stock with audited status transitions.</p>
        </div>

        @can('create', \App\Models\StockTransfer::class)
            <a href="{{ route('stock-transfers.create') }}" class="btn-primary">Create transfer</a>
        @endcan
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-ink">Transfer queue</h2>
                <p class="mt-1 text-sm text-muted">Track pending, in-transit, and received stock movements.</p>
            </div>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by warehouse or shop" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none placeholder:text-subtle lg:max-w-sm" />
        </div>

        <div class="space-y-4">
            @forelse ($transfers as $transfer)
                <article class="rounded-ui border border-line bg-panel p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-lg font-semibold text-ink">{{ $transfer->warehouse->name }} to {{ $transfer->destinationShop->name }}</h3>
                                @php
                                    $transferTone = $transfer->status->value === 'pending'
                                        ? 'warning'
                                        : ($transfer->status->value === 'in_transit' ? 'info' : 'success');
                                @endphp
                                <x-status-badge :tone="$transferTone">{{ str($transfer->status->value)->replace('_', ' ')->title() }}</x-status-badge>
                            </div>
                            <p class="mt-2 text-sm text-muted">{{ $transfer->notes ?: 'No notes attached.' }}</p>
                            <p class="mt-3 text-xs uppercase tracking-[0.28em] text-subtle">Created {{ $transfer->created_at->diffForHumans() }}</p>
                        </div>

                        <div class="flex gap-2">
                            @can('markInTransit', $transfer)
                                <button wire:click="markInTransit({{ $transfer->id }})" wire:confirm="Mark this transfer in transit?" class="btn-secondary btn-size-sm">Dispatch</button>
                            @endcan
                            @can('receive', $transfer)
                                <button wire:click="receive({{ $transfer->id }})" wire:confirm="Receive this transfer and move stock?" class="btn-success btn-size-sm">Receive</button>
                            @endcan
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-ui border border-line table-baseline">
                        <table class="min-w-full text-start text-sm ui-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 font-medium">Product</th>
                                    <th class="px-4 py-3 font-medium">Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line text-ink">
                                @foreach ($transfer->items as $item)
                                    <tr>
                                        <td class="px-4 py-3">{{ $item->product->name }}</td>
                                        <td class="px-4 py-3 figure-mono">{{ number_format((float) $item->quantity, 3) }} {{ $item->unit?->code ?? $item->product->baseUnit?->code ?? 'PCS' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @empty
                <div class="rounded-ui border border-line bg-panel px-4 py-10 text-center text-muted">No transfers match the current filter.</div>
            @endforelse
        </div>

        <div class="mt-5">{{ $transfers->links() }}</div>
    </div>
</section>