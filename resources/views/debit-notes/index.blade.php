@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">{{ __('Purchase Returns') }}</p>
                <h1 class="mt-3 text-4xl font-semibold text-ink">{{ __('Debit Notes') }}</h1>
                <p class="mt-3 max-w-2xl text-sm text-muted">{{ __('Purchase returns decrease warehouse stock and create fresh reversal entries against the original supplier bill economics.') }}</p>
            </div>

            <a href="{{ route('debit-notes.create') }}" class="btn-primary">{{ __('Create debit note') }}</a>
        </div>

        <div class="rounded-ui border border-line bg-surface p-6">
            <div class="overflow-hidden rounded-ui border border-line table-baseline">
                <table class="min-w-full text-start text-sm ui-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Debit note') }}</th>
                            <th class="px-4 py-3">{{ __('Bill') }}</th>
                            <th class="px-4 py-3">{{ __('Supplier') }}</th>
                            <th class="px-4 py-3">{{ __('Date') }}</th>
                            <th class="px-4 py-3">{{ __('Total') }}</th>
                            <th class="px-4 py-3">{{ __('Applied to AP') }}</th>
                            <th class="px-4 py-3">{{ __('Manual excess') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-ink">
                        @forelse ($debitNotes as $debitNote)
                            <tr>
                                <td class="px-4 py-3 figure-mono font-medium text-ink">{{ $debitNote->debit_note_number }}</td>
                                <td class="px-4 py-3">{{ $debitNote->supplierBill?->bill_number ?? (__('Bill #').$debitNote->supplier_bill_id) }}</td>
                                <td class="px-4 py-3">{{ $debitNote->supplierBill?->supplier?->name }}</td>
                                <td class="px-4 py-3 figure-mono">{{ $debitNote->note_date?->toDateString() }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $debitNote->total, 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $debitNote->applied_to_bill_balance, 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $debitNote->excess_amount, 2) }}</td>
                                <td class="px-4 py-3 text-end"><x-document-actions type="debit-note" :id="$debitNote->id" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-muted">{{ __('No debit notes posted yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">{{ $debitNotes->links() }}</div>
        </div>
    </section>
@endsection