@php
    $canCreateTransfer = auth()->user()?->can('create', \App\Models\StockTransfer::class) ?? false;
@endphp

<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex items-start gap-3">
            <x-icon-tile color="ledger" size="lg">
                <x-lucide-arrow-left-right class="h-7 w-7" />
            </x-icon-tile>
            <div>
                <p class="text-xs font-medium text-ledger">Inventory transfers</p>
                <h1 class="mt-1 text-3xl font-semibold text-ink">Stock transfers</h1>
                <p class="mt-2 max-w-2xl text-sm text-muted">Create warehouse-to-shop transfers, dispatch them, and receive them into shop stock with audited status transitions.</p>
            </div>
        </div>

        @can('create', \App\Models\StockTransfer::class)
            <a href="{{ route('stock-transfers.create') }}" class="btn-primary">Create transfer</a>
        @endcan
    </div>

    <x-card surface="surface">
        <x-slot:header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-ink">Transfer queue</h2>
                    <p class="mt-1 text-sm text-muted">Track pending, in-transit, and received stock movements.</p>
                </div>
                <div class="relative w-full lg:max-w-sm">
                    <x-lucide-search class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-subtle" />
                    <x-input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by warehouse or shop" class="ps-9" />
                </div>
            </div>
        </x-slot:header>

        @if ($transfers->count())
            <div class="space-y-4">
                @foreach ($transfers as $transfer)
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
                                <p class="mt-3 text-xs text-subtle">Created {{ $transfer->created_at->diffForHumans() }}</p>
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

                        <x-table dense class="mt-4">
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
                        </x-table>
                    </article>
                @endforeach
            </div>

            <div class="mt-5">{{ $transfers->links() }}</div>
        @else
            <x-empty-state
                icon="arrow-left-right"
                :title="$search !== '' ? 'No transfers match this search' : 'No transfers yet'"
                :message="$search !== '' ? 'Try a different warehouse or shop name.' : 'Create your first stock transfer to move inventory into a shop.'"
                :actionLabel="$search === '' && $canCreateTransfer ? 'Create transfer' : null"
                :actionHref="$search === '' && $canCreateTransfer ? route('stock-transfers.create') : null"
            />
        @endif
    </x-card>
</section>