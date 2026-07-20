@extends('layouts.hub')

@section('title', 'Customer Receivables')

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Receivables</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Customer Receivables</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Open credit-account sales awaiting customer settlement.</p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="overflow-hidden rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-stone-900/80 text-stone-400">
                        <tr>
                            <th class="px-4 py-3">Invoice</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Shop</th>
                            <th class="px-4 py-3">Due</th>
                            <th class="px-4 py-3">Outstanding</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 bg-stone-950/40 text-stone-200">
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="px-4 py-3 text-white">{{ $sale->invoice_number ?? ('Sale #'.$sale->id) }}</td>
                                <td class="px-4 py-3">{{ $sale->customer?->name }}</td>
                                <td class="px-4 py-3">{{ $sale->shop?->name }}</td>
                                <td class="px-4 py-3">{{ $sale->due_date?->toDateString() }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $sale->outstanding_balance, 2) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="mb-2 flex justify-end gap-2">
                                        <a href="{{ route('credit-notes.create', ['sale_id' => $sale->id]) }}" class="rounded-xl border border-sky-400/20 px-3 py-2 text-xs font-semibold text-sky-200 transition hover:bg-sky-500/10">Create credit note</a>
                                        <a href="{{ route('customer-receivables.payments.create', $sale) }}" class="rounded-xl border border-emerald-400/20 px-3 py-2 text-xs font-semibold text-emerald-200 transition hover:bg-emerald-500/10">Record payment</a>
                                        @can('create', \App\Models\Cheque::class)
                                            <a href="{{ route('cheques.create', ['sale_id' => $sale->id]) }}" class="rounded-xl border border-amber-400/20 px-3 py-2 text-xs font-semibold text-amber-200 transition hover:bg-amber-500/10">Record cheque</a>
                                        @endcan
                                    </div>
                                    <x-document-actions type="sale" :id="$sale->id" :allow-receipt="true" />
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-stone-400">No open customer receivables.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">{{ $sales->links() }}</div>
        </div>
    </section>
@endsection
