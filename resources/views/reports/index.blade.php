@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Reporting Center</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Reporting</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Back Office reporting for VAT, margin, and fast-moving SKUs with optional date and shop filters.</p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                <a href="{{ route('reports.trial-balance') }}" class="rounded-full border border-line bg-panel px-3 py-1 text-ink transition hover:border-brass/50 hover:bg-surface">Trial Balance</a>
                <a href="{{ route('reports.balance-sheet') }}" class="rounded-full border border-line bg-panel px-3 py-1 text-ink transition hover:border-brass/50 hover:bg-surface">Balance Sheet</a>
                <a href="{{ route('reports.income-statement') }}" class="rounded-full border border-line bg-panel px-3 py-1 text-ink transition hover:border-brass/50 hover:bg-surface">Income Statement</a>
                <a href="{{ route('reports.cash-flow') }}" class="rounded-full border border-line bg-panel px-3 py-1 text-ink transition hover:border-brass/50 hover:bg-surface">Cash Flow</a>
                <a href="{{ route('reports.ap-aging') }}" class="rounded-full border border-line bg-panel px-3 py-1 text-ink transition hover:border-brass/50 hover:bg-surface">AP Aging</a>
                <a href="{{ route('reports.ar-aging') }}" class="rounded-full border border-line bg-panel px-3 py-1 text-ink transition hover:border-brass/50 hover:bg-surface">AR Aging</a>
                <a href="{{ route('fiscal-periods.index') }}" class="rounded-full border border-line bg-panel px-3 py-1 text-ink transition hover:border-brass/50 hover:bg-surface">Fiscal Periods</a>
            </div>
        </div>

        <form method="GET" action="{{ route('reports.index') }}" class="rounded-ui border border-line bg-surface p-6">
            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-ink">Shop</label>
                    <select name="shop_id" class="ui-select w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" {{ $isShopScopedUser ? 'disabled' : '' }}>
                        <option value="">All shops</option>
                        @foreach ($shops as $shop)
                            <option value="{{ $shop->id }}" @selected((string) $filters['shop_id'] === (string) $shop->id)>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                    @if ($isShopScopedUser)
                        <input type="hidden" name="shop_id" value="{{ $filters['shop_id'] }}">
                    @endif
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-ink">Start date</label>
                    <input name="start_date" type="date" value="{{ $filters['start_date'] }}" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-ink">End date</label>
                    <input name="end_date" type="date" value="{{ $filters['end_date'] }}" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <button type="submit" class="btn-primary">Apply filters</button>
                <a href="{{ route('reports.index') }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        <div class="rounded-ui border border-line bg-surface p-6">
            <h2 class="text-xl font-semibold text-ink">VAT report</h2>
            <div class="mt-4 overflow-hidden rounded-ui border border-line table-baseline">
                <table class="min-w-full text-start text-sm ui-table">
                    <thead><tr><th class="px-4 py-3">Shop</th><th class="px-4 py-3">Sales</th><th class="px-4 py-3">VAT total</th><th class="px-4 py-3">Gross total</th></tr></thead>
                    <tbody class="divide-y divide-line text-ink">
                        @forelse ($vatRows as $row)
                            <tr><td class="px-4 py-3 font-medium text-ink">{{ $row->shop_name }}</td><td class="px-4 py-3 figure-mono">{{ $row->sale_count }}</td><td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row->vat_total, 2) }}</td><td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row->gross_total, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-muted">No VAT rows for the current filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-ui border border-line bg-surface p-6">
            <h2 class="text-xl font-semibold text-ink">Margin report</h2>
            <div class="mt-4 overflow-hidden rounded-ui border border-line table-baseline">
                <table class="min-w-full text-start text-sm ui-table">
                    <thead><tr><th class="px-4 py-3">Shop</th><th class="px-4 py-3">Product</th><th class="px-4 py-3">Revenue (ex VAT)</th><th class="px-4 py-3">COGS</th><th class="px-4 py-3">Margin</th></tr></thead>
                    <tbody class="divide-y divide-line text-ink">
                        @forelse ($marginRows as $row)
                            <tr><td class="px-4 py-3 font-medium text-ink">{{ $row->shop_name }}</td><td class="px-4 py-3">{{ $row->product_name }}</td><td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row->revenue_ex_vat, 2) }}</td><td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row->cogs_total, 2) }}</td><td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row->margin_total, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">No margin rows for the current filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-ui border border-line bg-surface p-6">
            <h2 class="text-xl font-semibold text-ink">Fast-moving SKUs</h2>
            <div class="mt-4 overflow-hidden rounded-ui border border-line table-baseline">
                <table class="min-w-full text-start text-sm ui-table">
                    <thead><tr><th class="px-4 py-3">Shop</th><th class="px-4 py-3">Product</th><th class="px-4 py-3">Quantity sold</th><th class="px-4 py-3">Line total</th></tr></thead>
                    <tbody class="divide-y divide-line text-ink">
                        @forelse ($fastMovingRows as $row)
                            <tr><td class="px-4 py-3 font-medium text-ink">{{ $row->shop_name }}</td><td class="px-4 py-3">{{ $row->product_name }}</td><td class="px-4 py-3 figure-mono">{{ number_format((float) $row->quantity_sold, 3) }}</td><td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row->line_total_sum, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-muted">No SKU movement rows for the current filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
