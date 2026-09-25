@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">{{ __('Financial Statements') }}</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">{{ __('Income Statement') }}</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">{{ __('Revenue, COGS, and operating expenses for a selected date range with optional shop scope.') }}</p>
        </div>

        <form method="GET" action="{{ route('reports.income-statement') }}" class="rounded-ui border border-line bg-surface p-6">
            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">{{ __('Shop') }}</label>
                    <select name="shop_id" class="ui-select w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" {{ $isShopScopedUser ? 'disabled' : '' }}>
                        <option value="">{{ __('Company-wide') }}</option>
                        @foreach ($shops as $shop)
                            <option value="{{ $shop->id }}" @selected((string) $filters['shop_id'] === (string) $shop->id)>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                    @if ($isShopScopedUser)
                        <input type="hidden" name="shop_id" value="{{ $filters['shop_id'] }}">
                    @endif
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">{{ __('Start date') }}</label>
                    <input name="start_date" type="date" value="{{ $filters['start_date'] }}" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">{{ __('End date') }}</label>
                    <input name="end_date" type="date" value="{{ $filters['end_date'] }}" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <button type="submit" class="btn-primary">{{ __('Apply') }}</button>
                <a href="{{ route('reports.income-statement') }}" class="btn-secondary">{{ __('Reset') }}</a>
            </div>
        </form>

        <div class="rounded-ui border border-line bg-surface p-6">
            <h2 class="text-lg font-semibold text-ink">{{ __('Revenue') }}</h2>
            <ul class="mt-4 space-y-2 text-sm text-muted">
                @forelse ($statement['revenue_rows'] as $row)
                    <li class="flex items-center justify-between gap-3"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span class="figure-mono">SAR {{ number_format((float) $row['amount'], 2) }}</span></li>
                @empty
                    <li class="text-muted">{{ __('No revenue rows in selected range.') }}</li>
                @endforelse
            </ul>
            <p class="mt-4 border-t border-line pt-3 text-sm font-semibold text-ink">{{ __('Total Revenue') }}: <span class="figure-mono">SAR {{ number_format((float) $statement['revenue_total'], 2) }}</span></p>
        </div>

        <div class="rounded-ui border border-line bg-surface p-6">
            <h2 class="text-lg font-semibold text-ink">{{ __('Cost of Goods Sold') }}</h2>
            <ul class="mt-4 space-y-2 text-sm text-muted">
                @forelse ($statement['cogs_rows'] as $row)
                    <li class="flex items-center justify-between gap-3"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span class="figure-mono">SAR {{ number_format((float) $row['amount'], 2) }}</span></li>
                @empty
                    <li class="text-muted">{{ __('No COGS rows in selected range.') }}</li>
                @endforelse
            </ul>
            <p class="mt-4 border-t border-line pt-3 text-sm font-semibold text-ink">{{ __('Total COGS') }}: <span class="figure-mono">SAR {{ number_format((float) $statement['cogs_total'], 2) }}</span></p>
            <p class="mt-2 text-sm font-semibold text-emerald-300">{{ __('Gross Profit') }}: <span class="figure-mono">SAR {{ number_format((float) $statement['gross_profit'], 2) }}</span></p>
        </div>

        <div class="rounded-ui border border-line bg-surface p-6">
            <h2 class="text-lg font-semibold text-ink">{{ __('Operating Expenses') }}</h2>
            <ul class="mt-4 space-y-2 text-sm text-muted">
                @forelse ($statement['operating_expense_rows'] as $row)
                    <li class="flex items-center justify-between gap-3"><span>{{ $row['code'] }} · {{ $row['name'] }}</span><span class="figure-mono">SAR {{ number_format((float) $row['amount'], 2) }}</span></li>
                @empty
                    <li class="text-muted">{{ __('No operating expense rows in selected range.') }}</li>
                @endforelse
            </ul>
            <p class="mt-4 border-t border-line pt-3 text-sm font-semibold text-ink">{{ __('Operating Expenses') }}: <span class="figure-mono">SAR {{ number_format((float) $statement['operating_expense_total'], 2) }}</span></p>
            <p class="mt-2 text-sm font-semibold {{ $statement['net_income'] >= 0 ? 'text-emerald-300' : 'text-rose-300' }}>{{ __('Net Income') }}: <span class="figure-mono">SAR {{ number_format((float) $statement['net_income'], 2) }}</span></p>
        </div>
    </section>
@endsection
