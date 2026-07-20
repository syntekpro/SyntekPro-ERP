@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Purchase Returns</p>
                <h1 class="mt-3 text-4xl font-semibold text-ink">Debit Notes</h1>
                <p class="mt-3 max-w-2xl text-sm text-muted">Purchase returns decrease warehouse stock and create fresh reversal entries against the original supplier bill economics.</p>
            </div>

            <a href="{{ route('debit-notes.create') }}" class="btn-primary">Create debit note</a>
        </div>

        <div class="rounded-ui border border-line bg-surface p-6">
            <div class="overflow-hidden rounded-ui border border-line table-baseline">
                <table class="min-w-full text-start text-sm ui-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Debit note</th>
                            <th class="px-4 py-3">Bill</th>
                            <th class="px-4 py-3">Supplier</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Applied to AP</th>
                            <th class="px-4 py-3">Manual excess</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-ink">
                        @forelse ($debitNotes as $debitNote)
                            <tr>
                                <td class="px-4 py-3 figure-mono font-medium text-ink">{{ $debitNote->debit_note_number }}</td>
                                <td class="px-4 py-3">{{ $debitNote->supplierBill?->bill_number ?? ('Bill #'.$debitNote->supplier_bill_id) }}</td>
                                <td class="px-4 py-3">{{ $debitNote->supplierBill?->supplier?->name }}</td>
                                <td class="px-4 py-3 figure-mono">{{ $debitNote->note_date?->toDateString() }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $debitNote->total, 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $debitNote->applied_to_bill_balance, 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $debitNote->excess_amount, 2) }}</td>
                                <td class="px-4 py-3 text-end"><x-document-actions type="debit-note" :id="$debitNote->id" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-muted">No debit notes posted yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">{{ $debitNotes->links() }}</div>
        </div>
    </section>
@endsection