@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Financial Statements</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Balance Sheet</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Company-wide statement of financial position as of a selected date.</p>
        </div>

        <form method="GET" action="{{ route('reports.balance-sheet') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">As of date</label>
                    <input name="as_of_date" type="date" value="{{ $filters['as_of_date'] }}" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Apply</button>
                <a href="{{ route('reports.balance-sheet') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Reset</a>
            </div>
        </form>

        <div class="grid gap-6 lg:grid-cols-3">
            <article class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Assets</h2>
                <ul class="mt-4 space-y-2 text-sm text-stone-200">
                    @foreach ($statement['assets'] as $row)
                        <li class="flex items-center justify-between"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span>SAR {{ number_format((float) $row['balance'], 2) }}</span></li>
                    @endforeach
                </ul>
                <p class="mt-4 border-t border-white/10 pt-3 text-sm font-semibold text-white">Total Assets: SAR {{ number_format((float) $statement['asset_total'], 2) }}</p>
            </article>

            <article class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Liabilities</h2>
                <ul class="mt-4 space-y-2 text-sm text-stone-200">
                    @foreach ($statement['liabilities'] as $row)
                        <li class="flex items-center justify-between"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span>SAR {{ number_format((float) $row['balance'], 2) }}</span></li>
                    @endforeach
                </ul>
                <p class="mt-4 border-t border-white/10 pt-3 text-sm font-semibold text-white">Total Liabilities: SAR {{ number_format((float) $statement['liability_total'], 2) }}</p>
            </article>

            <article class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Equity</h2>
                <ul class="mt-4 space-y-2 text-sm text-stone-200">
                    @foreach ($statement['equity'] as $row)
                        <li class="flex items-center justify-between"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span>SAR {{ number_format((float) $row['balance'], 2) }}</span></li>
                    @endforeach
                </ul>
                <p class="mt-4 border-t border-white/10 pt-3 text-sm font-semibold text-white">Total Equity: SAR {{ number_format((float) $statement['equity_total'], 2) }}</p>
            </article>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <p class="text-sm text-stone-200">Liabilities + Equity: <span class="font-semibold text-white">SAR {{ number_format((float) ($statement['liability_total'] + $statement['equity_total']), 2) }}</span></p>
            <p class="mt-2 text-sm {{ $statement['is_balanced'] ? 'text-emerald-300' : 'text-rose-300' }}">{{ $statement['is_balanced'] ? 'Statement is balanced.' : 'Statement is out of balance.' }}</p>
        </div>
    </section>
@endsection
