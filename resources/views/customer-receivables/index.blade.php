@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Receivables</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Customer Receivables</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Open credit-account sales awaiting customer settlement.</p>
        </div>

        <div class="rounded-ui border border-line bg-surface p-6">
            <div class="overflow-hidden rounded-ui border border-line table-baseline">
                <table class="min-w-full text-start text-sm ui-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Invoice</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Shop</th>
                            <th class="px-4 py-3">Due</th>
                            <th class="px-4 py-3">Outstanding</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-ink">
                        @forelse ($sales as $sale)
                            <tr>
                                <td class="px-4 py-3 figure-mono font-medium text-ink">{{ $sale->invoice_number ?? ('Sale #'.$sale->id) }}</td>
                                <td class="px-4 py-3">{{ $sale->customer?->name }}</td>
                                <td class="px-4 py-3">{{ $sale->shop?->name }}</td>
                                <td class="px-4 py-3 figure-mono">{{ $sale->due_date?->toDateString() }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $sale->outstanding_balance, 2) }}</td>
                                <td class="px-4 py-3 text-end">
                                    <div class="mb-2 flex justify-end gap-2">
                                        <a href="{{ route('credit-notes.create', ['sale_id' => $sale->id]) }}" class="btn-secondary btn-size-sm">Create credit note</a>
                                        <a href="{{ route('customer-receivables.payments.create', $sale) }}" class="btn-success btn-size-sm">Record payment</a>
                                        @can('create', \App\Models\Cheque::class)
                                            <a href="{{ route('cheques.create', ['sale_id' => $sale->id]) }}" class="btn-warning btn-size-sm">Record cheque</a>
                                        @endcan
                                    </div>
                                    <x-document-actions type="sale" :id="$sale->id" :allow-receipt="true" />
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-muted">No open customer receivables.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">{{ $sales->links() }}</div>
        </div>
    </section>
@endsection
