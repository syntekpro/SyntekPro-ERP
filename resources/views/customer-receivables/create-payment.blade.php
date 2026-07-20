@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6 max-w-2xl">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Receivables</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Record customer payment</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Invoice {{ $sale->invoice_number ?? ('Sale #'.$sale->id) }} | Outstanding SAR {{ number_format((float) $sale->outstanding_balance, 2) }}</p>
        </div>

        <form method="POST" action="{{ route('customer-receivables.payments.store', $sale) }}" class="rounded-ui border border-line bg-surface p-6">
            @csrf
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Amount</label>
                    <input name="amount" value="{{ old('amount', number_format((float) $sale->outstanding_balance, 2, '.', '')) }}" type="number" min="0.01" step="0.01" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                    @error('amount') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Paid at</label>
                    <input name="paid_at" value="{{ old('paid_at', now()->toDateString()) }}" type="date" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                    @error('paid_at') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Reference</label>
                    <input name="reference" value="{{ old('reference') }}" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Notes</label>
                    <input name="notes" value="{{ old('notes') }}" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="submit" class="btn-primary">Record payment</button>
                @can('create', \App\Models\Cheque::class)
                    <a href="{{ route('cheques.create', ['sale_id' => $sale->id]) }}" class="btn-warning">Record cheque instead</a>
                @endcan
                <a href="{{ route('customer-receivables.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
