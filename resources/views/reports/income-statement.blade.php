@extends('layouts.hub')

@section('title', 'Income Statement')

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Financial Statements</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Income Statement</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Revenue, COGS, and operating expenses for a selected date range with optional shop scope.</p>
        </div>

        <form method="GET" action="{{ route('reports.income-statement') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">Shop</label>
                    <select name="shop_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" {{ $isShopScopedUser ? 'disabled' : '' }}>
                        <option value="">Company-wide</option>
                        @foreach ($shops as $shop)
                            <option value="{{ $shop->id }}" @selected((string) $filters['shop_id'] === (string) $shop->id)>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                    @if ($isShopScopedUser)
                        <input type="hidden" name="shop_id" value="{{ $filters['shop_id'] }}">
                    @endif
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">Start date</label>
                    <input name="start_date" type="date" value="{{ $filters['start_date'] }}" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">End date</label>
                    <input name="end_date" type="date" value="{{ $filters['end_date'] }}" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Apply</button>
                <a href="{{ route('reports.income-statement') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Reset</a>
            </div>
        </form>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">Revenue</h2>
            <ul class="mt-4 space-y-2 text-sm text-stone-200">
                @forelse ($statement['revenue_rows'] as $row)
                    <li class="flex items-center justify-between"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span>SAR {{ number_format((float) $row['amount'], 2) }}</span></li>
                @empty
                    <li class="text-stone-400">No revenue rows in selected range.</li>
                @endforelse
            </ul>
            <p class="mt-4 border-t border-white/10 pt-3 text-sm font-semibold text-white">Total Revenue: SAR {{ number_format((float) $statement['revenue_total'], 2) }}</p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">Cost of Goods Sold</h2>
            <ul class="mt-4 space-y-2 text-sm text-stone-200">
                @forelse ($statement['cogs_rows'] as $row)
                    <li class="flex items-center justify-between"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span>SAR {{ number_format((float) $row['amount'], 2) }}</span></li>
                @empty
                    <li class="text-stone-400">No COGS rows in selected range.</li>
                @endforelse
            </ul>
            <p class="mt-4 border-t border-white/10 pt-3 text-sm font-semibold text-white">Total COGS: SAR {{ number_format((float) $statement['cogs_total'], 2) }}</p>
            <p class="mt-2 text-sm font-semibold text-emerald-300">Gross Profit: SAR {{ number_format((float) $statement['gross_profit'], 2) }}</p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">Operating Expenses</h2>
            <ul class="mt-4 space-y-2 text-sm text-stone-200">
                @forelse ($statement['operating_expense_rows'] as $row)
                    <li class="flex items-center justify-between"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span>SAR {{ number_format((float) $row['amount'], 2) }}</span></li>
                @empty
                    <li class="text-stone-400">No operating expense rows in selected range.</li>
                @endforelse
            </ul>
            <p class="mt-4 border-t border-white/10 pt-3 text-sm font-semibold text-white">Operating Expenses: SAR {{ number_format((float) $statement['operating_expense_total'], 2) }}</p>
            <p class="mt-2 text-sm font-semibold {{ $statement['net_income'] >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">Net Income: SAR {{ number_format((float) $statement['net_income'], 2) }}</p>
        </div>
    </section>
@endsection
