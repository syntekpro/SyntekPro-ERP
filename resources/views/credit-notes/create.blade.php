@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Sales Returns</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Create credit note</h1>
            <p class="mt-3 max-w-3xl text-sm text-stone-300">Choose a sale, then enter only the quantities being returned. Sellable lines restock the original shop. Damaged lines stay out of stock and flow to the write-off account.</p>
        </div>

        <form method="GET" action="{{ route('credit-notes.create') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <label for="sale_id" class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Original sale</label>
            <div class="flex flex-col gap-3 lg:flex-row">
                <select id="sale_id" name="sale_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none">
                    <option value="">Select a sale</option>
                    @foreach ($sales as $sale)
                        <option value="{{ $sale->id }}" @selected(($selectedSale?->id ?? old('sale_id')) == $sale->id)>
                            {{ $sale->invoice_number ?? ('Sale #'.$sale->id) }} · {{ $sale->shop?->name }} · SAR {{ number_format((float) $sale->total, 2) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/15">Load items</button>
            </div>
        </form>

        @if ($selectedSale)
            <form method="POST" action="{{ route('credit-notes.store') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
                @csrf
                <input type="hidden" name="sale_id" value="{{ $selectedSale->id }}" />

                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-stone-950/40 p-4 text-sm text-stone-300">
                        <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Sale</p>
                        <p class="mt-2 text-white">{{ $selectedSale->invoice_number ?? ('Sale #'.$selectedSale->id) }}</p>
                        <p class="mt-1">{{ $selectedSale->shop?->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-stone-950/40 p-4 text-sm text-stone-300">
                        <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Customer</p>
                        <p class="mt-2 text-white">{{ $selectedSale->customer?->name ?? 'Walk-in customer' }}</p>
                        <p class="mt-1">Payment method: {{ str($selectedSale->payment_method->value)->replace('_', ' ')->title() }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-stone-950/40 p-4 text-sm text-stone-300">
                        <label for="note_date" class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Credit note date</label>
                        <input id="note_date" name="note_date" type="date" value="{{ old('note_date', now()->toDateString()) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none" />
                    </div>
                </div>

                @error('items')
                    <div class="mt-4 rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">{{ $message }}</div>
                @enderror

                <div class="mt-6 overflow-hidden rounded-2xl border border-white/10">
                    <table class="min-w-full divide-y divide-white/10 text-start text-sm">
                        <thead class="bg-stone-900/80 text-stone-400">
                            <tr>
                                <th class="px-4 py-3">Item</th>
                                <th class="px-4 py-3">Sold</th>
                                <th class="px-4 py-3">Available to return</th>
                                <th class="px-4 py-3">Return qty</th>
                                <th class="px-4 py-3">Condition</th>
                                <th class="px-4 py-3">Frozen unit cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-stone-950/40 text-stone-200">
                            @foreach ($selectedSale->items as $index => $item)
                                @php($remainingQty = round((float) $item->quantity - (float) $item->creditNoteItems->sum('quantity'), 3))
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="text-white">{{ $item->product_name }}</p>
                                        <p class="text-xs text-stone-500">{{ $item->sku }}</p>
                                        <input type="hidden" name="items[{{ $index }}][sale_item_id]" value="{{ $item->id }}" />
                                    </td>
                                    <td class="px-4 py-3">{{ number_format((float) $item->quantity, 3) }}</td>
                                    <td class="px-4 py-3">{{ number_format($remainingQty, 3) }}</td>
                                    <td class="px-4 py-3">
                                        <input name="items[{{ $index }}][quantity]" type="number" min="0" step="0.001" value="{{ old('items.'.$index.'.quantity', '0') }}" class="w-28 rounded-xl border border-white/10 bg-stone-900 px-3 py-2 text-sm text-stone-100 outline-none" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <select name="items[{{ $index }}][condition]" class="w-40 rounded-xl border border-white/10 bg-stone-900 px-3 py-2 text-sm text-stone-100 outline-none">
                                            <option value="sellable" @selected(old('items.'.$index.'.condition', 'sellable') === 'sellable')>Sellable</option>
                                            <option value="damaged" @selected(old('items.'.$index.'.condition') === 'damaged')>Damaged</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">SAR {{ number_format((float) $item->unit_cost, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    <label for="notes" class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Notes</label>
                    <textarea id="notes" name="notes" rows="4" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none">{{ old('notes') }}</textarea>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-sky-300">Post credit note</button>
                </div>
            </form>
        @endif
    </section>
@endsection