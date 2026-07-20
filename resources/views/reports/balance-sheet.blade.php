@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Financial Statements</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Balance Sheet</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Company-wide statement of financial position as of a selected date.</p>
        </div>

        <form method="GET" action="{{ route('reports.balance-sheet') }}" class="rounded-ui border border-line bg-surface p-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">As of date</label>
                    <input name="as_of_date" type="date" value="{{ $filters['as_of_date'] }}" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <button type="submit" class="btn-primary">Apply</button>
                <a href="{{ route('reports.balance-sheet') }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        <div class="grid gap-6 lg:grid-cols-3">
            <article class="rounded-ui border border-line bg-surface p-6">
                <h2 class="text-lg font-semibold text-ink">Assets</h2>
                <ul class="mt-4 space-y-2 text-sm text-muted">
                    @foreach ($statement['assets'] as $row)
                        <li class="flex items-center justify-between gap-3"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span class="figure-mono">SAR {{ number_format((float) $row['balance'], 2) }}</span></li>
                    @endforeach
                </ul>
                <p class="mt-4 border-t border-line pt-3 text-sm font-semibold text-ink">Total Assets: <span class="figure-mono">SAR {{ number_format((float) $statement['asset_total'], 2) }}</span></p>
            </article>

            <article class="rounded-ui border border-line bg-surface p-6">
                <h2 class="text-lg font-semibold text-ink">Liabilities</h2>
                <ul class="mt-4 space-y-2 text-sm text-muted">
                    @foreach ($statement['liabilities'] as $row)
                        <li class="flex items-center justify-between gap-3"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span class="figure-mono">SAR {{ number_format((float) $row['balance'], 2) }}</span></li>
                    @endforeach
                </ul>
                <p class="mt-4 border-t border-line pt-3 text-sm font-semibold text-ink">Total Liabilities: <span class="figure-mono">SAR {{ number_format((float) $statement['liability_total'], 2) }}</span></p>
            </article>

            <article class="rounded-ui border border-line bg-surface p-6">
                <h2 class="text-lg font-semibold text-ink">Equity</h2>
                <ul class="mt-4 space-y-2 text-sm text-muted">
                    @foreach ($statement['equity'] as $row)
                        <li class="flex items-center justify-between gap-3"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span class="figure-mono">SAR {{ number_format((float) $row['balance'], 2) }}</span></li>
                    @endforeach
                </ul>
                <p class="mt-4 border-t border-line pt-3 text-sm font-semibold text-ink">Total Equity: <span class="figure-mono">SAR {{ number_format((float) $statement['equity_total'], 2) }}</span></p>
            </article>
        </div>

        <div class="rounded-ui border border-line bg-surface p-6">
            <p class="text-sm text-muted">Liabilities + Equity: <span class="figure-mono font-semibold text-ink">SAR {{ number_format((float) ($statement['liability_total'] + $statement['equity_total']), 2) }}</span></p>
            <p class="mt-2 text-sm {{ $statement['is_balanced'] ? 'text-emerald-300' : 'text-rose-300' }}">{{ $statement['is_balanced'] ? 'Statement is balanced.' : 'Statement is out of balance.' }}</p>
        </div>
    </section>
@endsection
