@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">{{ __('Sales Returns') }}</p>
                <h1 class="mt-3 text-4xl font-semibold text-ink">{{ __('Credit Notes') }}</h1>
                <p class="mt-3 max-w-2xl text-sm text-muted">{{ __('Sales returns create new reversal entries, never edits to the original sale posting.') }}</p>
            </div>

            <a href="{{ route('credit-notes.create') }}" class="btn-primary">{{ __('Create credit note') }}</a>
        </div>

        <div class="rounded-ui border border-line bg-surface p-6">
            <div class="overflow-hidden rounded-ui border border-line table-baseline">
                <table class="min-w-full text-start text-sm ui-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">{{ __('Credit note') }}</th>
                            <th class="px-4 py-3">{{ __('Sale') }}</th>
                            <th class="px-4 py-3">{{ __('Customer') }}</th>
                            <th class="px-4 py-3">{{ __('Date') }}</th>
                            <th class="px-4 py-3">{{ __('Total') }}</th>
                            <th class="px-4 py-3">{{ __('Refund') }}</th>
                            <th class="px-4 py-3">{{ __('Applied to AR') }}</th>
                            <th class="px-4 py-3 text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-ink">
                        @forelse ($creditNotes as $creditNote)
                            <tr>
                                <td class="px-4 py-3 figure-mono font-medium text-ink">{{ $creditNote->credit_note_number }}</td>
                                <td class="px-4 py-3">{{ $creditNote->sale?->invoice_number ?? (__('Sale #').$creditNote->sale_id) }}</td>
                                <td class="px-4 py-3">{{ $creditNote->sale?->customer?->name ?? __('Walk-in customer') }}</td>
                                <td class="px-4 py-3 figure-mono">{{ $creditNote->note_date?->toDateString() }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $creditNote->total, 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $creditNote->refund_amount, 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $creditNote->applied_to_sale_balance, 2) }}</td>
                                <td class="px-4 py-3 text-end"><x-document-actions type="credit-note" :id="$creditNote->id" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-muted">{{ __('No credit notes posted yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5">{{ $creditNotes->links() }}</div>
        </div>
    </section>
@endsection