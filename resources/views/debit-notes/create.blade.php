@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Purchase Returns</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Create debit note</h1>
            <p class="mt-3 max-w-3xl text-sm text-stone-300">Choose a supplier bill, then enter only the quantities being returned to the supplier. Warehouse stock is reduced under lock, but product average cost is intentionally left untouched.</p>
        </div>

        <form method="GET" action="{{ route('debit-notes.create') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <label for="supplier_bill_id" class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Supplier bill</label>
            <div class="flex flex-col gap-3 lg:flex-row">
                <select id="supplier_bill_id" name="supplier_bill_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none">
                    <option value="">Select a supplier bill</option>
                    @foreach ($supplierBills as $bill)
                        <option value="{{ $bill->id }}" @selected(($selectedBill?->id ?? old('supplier_bill_id')) == $bill->id)>
                            {{ $bill->bill_number }} · {{ $bill->supplier?->name }} · SAR {{ number_format((float) $bill->total, 2) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-2xl bg-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/15">Load items</button>
            </div>
        </form>

        @if ($selectedBill)
            <form method="POST" action="{{ route('debit-notes.store') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
                @csrf
                <input type="hidden" name="supplier_bill_id" value="{{ $selectedBill->id }}" />

                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-stone-950/40 p-4 text-sm text-stone-300">
                        <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Bill</p>
                        <p class="mt-2 text-white">{{ $selectedBill->bill_number }}</p>
                        <p class="mt-1">Outstanding: SAR {{ number_format((float) $selectedBill->outstanding_balance, 2) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-stone-950/40 p-4 text-sm text-stone-300">
                        <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Supplier</p>
                        <p class="mt-2 text-white">{{ $selectedBill->supplier?->name }}</p>
                        <p class="mt-1">Warehouse: {{ $selectedBill->warehouse?->name }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-stone-950/40 p-4 text-sm text-stone-300">
                        <label for="note_date" class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-500">Debit note date</label>
                        <input id="note_date" name="note_date" type="date" value="{{ old('note_date', now()->toDateString()) }}" class="mt-2 w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none" />
                    </div>
                </div>

                @error('items')
                    <div class="mt-4 rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-100">{{ $message }}</div>
                @enderror

                <div class="mt-4 rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                    If the total return exceeds this bill's current outstanding balance, the extra amount will be flagged for manual handling instead of pushing the bill negative.
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-white/10">
                    <table class="min-w-full divide-y divide-white/10 text-start text-sm">
                        <thead class="bg-stone-900/80 text-stone-400">
                            <tr>
                                <th class="px-4 py-3">Item</th>
                                <th class="px-4 py-3">Received</th>
                                <th class="px-4 py-3">Available to return</th>
                                <th class="px-4 py-3">Return qty</th>
                                <th class="px-4 py-3">Unit cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-stone-950/40 text-stone-200">
                            @foreach ($selectedBill->items as $index => $item)
                                @php($remainingQty = round((float) $item->quantity - (float) $item->debitNoteItems->sum('quantity'), 3))
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="text-white">{{ $item->description }}</p>
                                        <input type="hidden" name="items[{{ $index }}][supplier_bill_item_id]" value="{{ $item->id }}" />
                                    </td>
                                    <td class="px-4 py-3">{{ number_format((float) $item->quantity, 3) }}</td>
                                    <td class="px-4 py-3">{{ number_format($remainingQty, 3) }}</td>
                                    <td class="px-4 py-3">
                                        <input name="items[{{ $index }}][quantity]" type="number" min="0" step="0.001" value="{{ old('items.'.$index.'.quantity', '0') }}" class="w-28 rounded-xl border border-white/10 bg-stone-900 px-3 py-2 text-sm text-stone-100 outline-none" />
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
                    <button type="submit" class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-sky-300">Post debit note</button>
                </div>
            </form>
        @endif
    </section>
@endsection