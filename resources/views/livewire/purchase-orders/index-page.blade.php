<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Phase 7</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Purchase orders</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Draft, submit, partially receive, and close warehouse-targeted purchase orders.</p>
        </div>

        <a href="{{ route('purchase-orders.create') }}" class="inline-flex rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Create PO</a>
    </div>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by PO number or supplier" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none placeholder:text-stone-500 lg:max-w-sm" />

        <div class="mt-5 space-y-4">
            @forelse ($purchaseOrders as $purchaseOrder)
                <article class="rounded-2xl border border-white/10 bg-stone-950/60 p-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-semibold text-white">{{ $purchaseOrder->po_number }}</h3>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold bg-sky-500/15 text-sky-200">{{ str($purchaseOrder->status->value)->replace('_', ' ')->title() }}</span>
                            </div>
                            <p class="mt-2 text-sm text-stone-300">Supplier: {{ $purchaseOrder->supplier->name }} | Warehouse: {{ $purchaseOrder->warehouse->name }}</p>
                            <p class="mt-2 text-xs uppercase tracking-[0.28em] text-stone-500">Created {{ $purchaseOrder->created_at->diffForHumans() }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}" class="rounded-xl border border-white/10 px-3 py-2 text-xs font-semibold text-stone-100 transition hover:bg-white/10">Edit</a>

                            @can('submit', $purchaseOrder)
                                <button wire:click="submit({{ $purchaseOrder->id }})" wire:confirm="Submit this purchase order?" class="rounded-xl border border-sky-400/20 px-3 py-2 text-xs font-semibold text-sky-200 transition hover:bg-sky-500/10">Submit</button>
                            @endcan

                            @can('receive', $purchaseOrder)
                                <button wire:click="receive({{ $purchaseOrder->id }})" wire:confirm="Receive remaining quantities for this PO?" class="rounded-xl border border-emerald-400/20 px-3 py-2 text-xs font-semibold text-emerald-200 transition hover:bg-emerald-500/10">Receive</button>
                            @endcan

                            @can('close', $purchaseOrder)
                                <button wire:click="close({{ $purchaseOrder->id }})" wire:confirm="Close this purchase order?" class="rounded-xl border border-amber-400/20 px-3 py-2 text-xs font-semibold text-amber-200 transition hover:bg-amber-500/10">Close</button>
                            @endcan
                        </div>
                    </div>

                    <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
                        <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                            <thead class="bg-stone-900/80 text-stone-400">
                                <tr>
                                    <th class="px-4 py-3">Product</th>
                                    <th class="px-4 py-3">Ordered</th>
                                    <th class="px-4 py-3">Received</th>
                                    <th class="px-4 py-3">Remaining</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10 bg-stone-950/40 text-stone-200">
                                @foreach ($purchaseOrder->items as $item)
                                    @php
                                        $remaining = round((float) $item->quantity_ordered - (float) $item->quantity_received, 3);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3">{{ $item->product->name }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $item->quantity_ordered, 3) }} {{ $item->unit?->code ?? $item->product->baseUnit?->code ?? 'PCS' }}</td>
                                        <td class="px-4 py-3">{{ number_format((float) $item->quantity_received, 3) }} {{ $item->unit?->code ?? $item->product->baseUnit?->code ?? 'PCS' }}</td>
                                        <td class="px-4 py-3">{{ number_format($remaining, 3) }} {{ $item->unit?->code ?? $item->product->baseUnit?->code ?? 'PCS' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-white/10 bg-stone-950/60 px-4 py-10 text-center text-stone-400">No purchase orders found.</div>
            @endforelse
        </div>

        <div class="mt-5">{{ $purchaseOrders->links() }}</div>
    </div>
</section>
