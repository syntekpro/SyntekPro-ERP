@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6 max-w-3xl">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Cheque Management</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Record post-dated cheque</h1>
            @if ($sale)
                <p class="mt-3 max-w-2xl text-sm text-muted">Incoming cheque against invoice {{ $sale->invoice_number ?? ('Sale #'.$sale->id) }}. Outstanding SAR {{ number_format((float) $sale->outstanding_balance, 2) }}.</p>
            @elseif ($supplierBill)
                <p class="mt-3 max-w-2xl text-sm text-muted">Outgoing cheque against supplier bill {{ $supplierBill->bill_number }}. Outstanding SAR {{ number_format((float) $supplierBill->outstanding_balance, 2) }}.</p>
            @endif
        </div>

        <form method="POST" action="{{ route('cheques.store') }}" class="rounded-ui border border-line bg-surface p-6">
            @csrf
            <input type="hidden" name="sale_id" value="{{ $sale?->id }}" />
            <input type="hidden" name="supplier_bill_id" value="{{ $supplierBill?->id }}" />

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Amount</label>
                    <input name="amount" value="{{ old('amount', number_format((float) ($sale?->outstanding_balance ?? $supplierBill?->outstanding_balance ?? 0), 2, '.', '')) }}" type="number" min="0.01" step="0.01" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                    @error('amount') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Cheque date</label>
                    <input name="cheque_date" value="{{ old('cheque_date', now()->toDateString()) }}" type="date" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                    @error('cheque_date') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Cheque number</label>
                    <input name="cheque_number" value="{{ old('cheque_number') }}" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                    @error('cheque_number') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Bank name</label>
                    <input name="bank_name" value="{{ old('bank_name') }}" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                    @error('bank_name') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="submit" class="btn-primary">Record cheque</button>
                <a href="{{ route('cheques.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
