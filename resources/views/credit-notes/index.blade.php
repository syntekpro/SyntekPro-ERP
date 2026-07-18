@extends('layouts.hub')

@section('title', 'Credit Notes')

@section('content')
    <section class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Phase 10</p>
                <h1 class="mt-3 text-4xl font-semibold text-white">Credit Notes</h1>
                <p class="mt-3 max-w-2xl text-sm text-stone-300">Sales returns create new reversal entries, never edits to the original sale posting.</p>
            </div>

            <a href="{{ route('credit-notes.create') }}" class="rounded-2xl bg-sky-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-sky-300">Create credit note</a>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="overflow-hidden rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-stone-900/80 text-stone-400">
                        <tr>
                            <th class="px-4 py-3">Credit note</th>
                            <th class="px-4 py-3">Sale</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Refund</th>
                            <th class="px-4 py-3">Applied to AR</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 bg-stone-950/40 text-stone-200">
                        @forelse ($creditNotes as $creditNote)
                            <tr>
                                <td class="px-4 py-3 text-white">{{ $creditNote->credit_note_number }}</td>
                                <td class="px-4 py-3">{{ $creditNote->sale?->invoice_number ?? ('Sale #'.$creditNote->sale_id) }}</td>
                                <td class="px-4 py-3">{{ $creditNote->sale?->customer?->name ?? 'Walk-in customer' }}</td>
                                <td class="px-4 py-3">{{ $creditNote->note_date?->toDateString() }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $creditNote->total, 2) }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $creditNote->refund_amount, 2) }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $creditNote->applied_to_sale_balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-stone-400">No credit notes posted yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">{{ $creditNotes->links() }}</div>
        </div>
    </section>
@endsection