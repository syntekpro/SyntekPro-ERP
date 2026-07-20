@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Sales Returns</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Create credit note</h1>
            <p class="mt-3 max-w-3xl text-sm text-muted">Choose a sale, then enter only the quantities being returned. Sellable lines restock the original shop. Damaged lines stay out of stock and flow to the write-off account.</p>
        </div>

        <form method="GET" action="{{ route('credit-notes.create') }}" class="rounded-ui border border-line bg-surface p-6">
            <label for="sale_id" class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-subtle">Original sale</label>
            <div class="flex flex-col gap-3 lg:flex-row">
                <select id="sale_id" name="sale_id" class="ui-select w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none">
                    <option value="">Select a sale</option>
                    @foreach ($sales as $sale)
                        <option value="{{ $sale->id }}" @selected(($selectedSale?->id ?? old('sale_id')) == $sale->id)>
                            {{ $sale->invoice_number ?? ('Sale #'.$sale->id) }} · {{ $sale->shop?->name }} · SAR {{ number_format((float) $sale->total, 2) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-secondary">Load items</button>
            </div>
        </form>

        @if ($selectedSale)
            <form method="POST" action="{{ route('credit-notes.store') }}" class="rounded-ui border border-line bg-surface p-6">
                @csrf
                <input type="hidden" name="sale_id" value="{{ $selectedSale->id }}" />

                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-ui border border-line bg-panel p-4 text-sm text-muted">
                        <p class="text-xs uppercase tracking-[0.24em] text-subtle">Sale</p>
                        <p class="mt-2 figure-mono font-semibold text-ink">{{ $selectedSale->invoice_number ?? ('Sale #'.$selectedSale->id) }}</p>
                        <p class="mt-1">{{ $selectedSale->shop?->name }}</p>
                    </div>
                    <div class="rounded-ui border border-line bg-panel p-4 text-sm text-muted">
                        <p class="text-xs uppercase tracking-[0.24em] text-subtle">Customer</p>
                        <p class="mt-2 font-semibold text-ink">{{ $selectedSale->customer?->name ?? 'Walk-in customer' }}</p>
                        <p class="mt-1">Payment method: {{ str($selectedSale->payment_method->value)->replace('_', ' ')->title() }}</p>
                    </div>
                    <div class="rounded-ui border border-line bg-panel p-4 text-sm text-muted">
                        <label for="note_date" class="text-xs font-semibold uppercase tracking-[0.24em] text-subtle">Credit note date</label>
                        <input id="note_date" name="note_date" type="date" value="{{ old('note_date', now()->toDateString()) }}" class="ui-input mt-2 w-full rounded-ui border border-line bg-surface px-4 py-2.5 text-sm text-ink outline-none" />
                    </div>
                </div>

                @error('items')
                    <div class="mt-4 rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">{{ $message }}</div>
                @enderror

                <div class="mt-6 overflow-hidden rounded-ui border border-line table-baseline">
                    <table class="min-w-full text-start text-sm ui-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3">Item</th>
                                <th class="px-4 py-3">Sold</th>
                                <th class="px-4 py-3">Available to return</th>
                                <th class="px-4 py-3">Return qty</th>
                                <th class="px-4 py-3">Condition</th>
                                <th class="px-4 py-3">Frozen unit cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line text-ink">
                            @foreach ($selectedSale->items as $index => $item)
                                @php($remainingQty = round((float) $item->quantity - (float) $item->creditNoteItems->sum('quantity'), 3))
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-ink">{{ $item->product_name }}</p>
                                        <p class="figure-mono text-xs text-subtle">{{ $item->sku }}</p>
                                        <input type="hidden" name="items[{{ $index }}][sale_item_id]" value="{{ $item->id }}" />
                                    </td>
                                    <td class="px-4 py-3 figure-mono">{{ number_format((float) $item->quantity, 3) }}</td>
                                    <td class="px-4 py-3 figure-mono">{{ number_format($remainingQty, 3) }}</td>
                                    <td class="px-4 py-3">
                                        <input name="items[{{ $index }}][quantity]" type="number" min="0" step="0.001" value="{{ old('items.'.$index.'.quantity', '0') }}" class="ui-input w-28 rounded-ui border border-line bg-panel px-3 py-2 text-sm text-ink outline-none" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <select name="items[{{ $index }}][condition]" class="ui-select w-40 rounded-ui border border-line bg-panel px-3 py-2 text-sm text-ink outline-none">
                                            <option value="sellable" @selected(old('items.'.$index.'.condition', 'sellable') === 'sellable')>Sellable</option>
                                            <option value="damaged" @selected(old('items.'.$index.'.condition') === 'damaged')>Damaged</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $item->unit_cost, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    <label for="notes" class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-subtle">Notes</label>
                    <textarea id="notes" name="notes" rows="4" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none">{{ old('notes') }}</textarea>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-primary">Post credit note</button>
                </div>
            </form>
        @endif
    </section>
@endsection