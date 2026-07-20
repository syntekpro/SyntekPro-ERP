<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Purchasing</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Purchase orders</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Draft, submit, partially receive, and close warehouse-targeted purchase orders.</p>
        </div>

        <a href="{{ route('purchase-orders.create') }}" class="btn-primary">Create PO</a>
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by PO number or supplier" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none placeholder:text-subtle lg:max-w-sm" />

        <div class="mt-5 space-y-4">
            @forelse ($purchaseOrders as $purchaseOrder)
                <article class="rounded-ui border border-line bg-panel p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="figure-mono text-lg font-semibold text-ink">{{ $purchaseOrder->po_number }}</h3>
                                <x-status-badge tone="warning">{{ str($purchaseOrder->status->value)->replace('_', ' ')->title() }}</x-status-badge>
                            </div>
                            <p class="mt-2 text-sm text-muted">Supplier: {{ $purchaseOrder->supplier->name }} | Warehouse: {{ $purchaseOrder->warehouse->name }}</p>
                            <p class="mt-2 text-xs uppercase tracking-[0.28em] text-subtle">Created {{ $purchaseOrder->created_at->diffForHumans() }}</p>
                        </div>

                        <div class="flex flex-wrap justify-end gap-2">
                            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="btn-secondary btn-size-sm">Edit</a>

                            @can('submit', $purchaseOrder)
                                <button wire:click="submit({{ $purchaseOrder->id }})" wire:confirm="Submit this purchase order?" class="btn-secondary btn-size-sm">Submit</button>
                            @endcan

                            @can('receive', $purchaseOrder)
                                <button wire:click="receive({{ $purchaseOrder->id }})" wire:confirm="Receive remaining quantities for this PO?" class="btn-success btn-size-sm">Receive</button>
                            @endcan

                            @can('close', $purchaseOrder)
                                <button wire:click="close({{ $purchaseOrder->id }})" wire:confirm="Close this purchase order?" class="btn-warning btn-size-sm">Close</button>
                            @endcan
                            <x-document-actions type="purchase-order" :id="$purchaseOrder->id" />
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-ui border border-line table-baseline">
                        <table class="min-w-full text-start text-sm ui-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Product</th>
                                    <th class="px-4 py-3">Ordered</th>
                                    <th class="px-4 py-3">Received</th>
                                    <th class="px-4 py-3">Remaining</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line text-ink">
                                @foreach ($purchaseOrder->items as $item)
                                    @php
                                        $remaining = round((float) $item->quantity_ordered - (float) $item->quantity_received, 3);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3">{{ $item->product->name }}</td>
                                        <td class="px-4 py-3 figure-mono">{{ number_format((float) $item->quantity_ordered, 3) }} {{ $item->unit?->code ?? $item->product->baseUnit?->code ?? 'PCS' }}</td>
                                        <td class="px-4 py-3 figure-mono">{{ number_format((float) $item->quantity_received, 3) }} {{ $item->unit?->code ?? $item->product->baseUnit?->code ?? 'PCS' }}</td>
                                        <td class="px-4 py-3 figure-mono">{{ number_format($remaining, 3) }} {{ $item->unit?->code ?? $item->product->baseUnit?->code ?? 'PCS' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @empty
                <div class="rounded-ui border border-line bg-panel px-4 py-10 text-center text-muted">No purchase orders found.</div>
            @endforelse
        </div>

        <div class="mt-5">{{ $purchaseOrders->links() }}</div>
    </div>
</section>
