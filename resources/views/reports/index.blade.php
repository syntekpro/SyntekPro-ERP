@extends('layouts.hub')

@section('title', 'Reports')

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Reporting Center</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Reporting</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Back Office reporting for VAT, margin, and fast-moving SKUs with optional date and shop filters.</p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                <a href="{{ route('reports.trial-balance') }}" class="rounded-full border border-white/10 px-3 py-1 text-stone-200 hover:bg-white/10">Trial Balance</a>
                <a href="{{ route('reports.balance-sheet') }}" class="rounded-full border border-white/10 px-3 py-1 text-stone-200 hover:bg-white/10">Balance Sheet</a>
                <a href="{{ route('reports.income-statement') }}" class="rounded-full border border-white/10 px-3 py-1 text-stone-200 hover:bg-white/10">Income Statement</a>
                <a href="{{ route('reports.cash-flow') }}" class="rounded-full border border-white/10 px-3 py-1 text-stone-200 hover:bg-white/10">Cash Flow</a>
                <a href="{{ route('reports.ap-aging') }}" class="rounded-full border border-white/10 px-3 py-1 text-stone-200 hover:bg-white/10">AP Aging</a>
                <a href="{{ route('reports.ar-aging') }}" class="rounded-full border border-white/10 px-3 py-1 text-stone-200 hover:bg-white/10">AR Aging</a>
                <a href="{{ route('fiscal-periods.index') }}" class="rounded-full border border-white/10 px-3 py-1 text-stone-200 hover:bg-white/10">Fiscal Periods</a>
            </div>
        </div>

        <form method="GET" action="{{ route('reports.index') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">Shop</label>
                    <select name="shop_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" {{ $isShopScopedUser ? 'disabled' : '' }}>
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
                    <label class="mb-2 block text-sm font-medium text-stone-200">Start date</label>
                    <input name="start_date" type="date" value="{{ $filters['start_date'] }}" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">End date</label>
                    <input name="end_date" type="date" value="{{ $filters['end_date'] }}" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Apply filters</button>
                <a href="{{ route('reports.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Reset</a>
            </div>
        </form>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-xl font-semibold text-white">VAT report</h2>
            <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-stone-900/80 text-stone-400"><tr><th class="px-4 py-3">Shop</th><th class="px-4 py-3">Sales</th><th class="px-4 py-3">VAT total</th><th class="px-4 py-3">Gross total</th></tr></thead>
                    <tbody class="divide-y divide-white/10 bg-stone-950/60 text-stone-200">
                        @forelse ($vatRows as $row)
                            <tr><td class="px-4 py-3 text-white">{{ $row->shop_name }}</td><td class="px-4 py-3">{{ $row->sale_count }}</td><td class="px-4 py-3">SAR {{ number_format((float) $row->vat_total, 2) }}</td><td class="px-4 py-3">SAR {{ number_format((float) $row->gross_total, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-stone-400">No VAT rows for the current filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-xl font-semibold text-white">Margin report</h2>
            <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-stone-900/80 text-stone-400"><tr><th class="px-4 py-3">Shop</th><th class="px-4 py-3">Product</th><th class="px-4 py-3">Revenue (ex VAT)</th><th class="px-4 py-3">COGS</th><th class="px-4 py-3">Margin</th></tr></thead>
                    <tbody class="divide-y divide-white/10 bg-stone-950/60 text-stone-200">
                        @forelse ($marginRows as $row)
                            <tr><td class="px-4 py-3 text-white">{{ $row->shop_name }}</td><td class="px-4 py-3">{{ $row->product_name }}</td><td class="px-4 py-3">SAR {{ number_format((float) $row->revenue_ex_vat, 2) }}</td><td class="px-4 py-3">SAR {{ number_format((float) $row->cogs_total, 2) }}</td><td class="px-4 py-3">SAR {{ number_format((float) $row->margin_total, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-stone-400">No margin rows for the current filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-xl font-semibold text-white">Fast-moving SKUs</h2>
            <div class="mt-4 overflow-hidden rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-stone-900/80 text-stone-400"><tr><th class="px-4 py-3">Shop</th><th class="px-4 py-3">Product</th><th class="px-4 py-3">Quantity sold</th><th class="px-4 py-3">Line total</th></tr></thead>
                    <tbody class="divide-y divide-white/10 bg-stone-950/60 text-stone-200">
                        @forelse ($fastMovingRows as $row)
                            <tr><td class="px-4 py-3 text-white">{{ $row->shop_name }}</td><td class="px-4 py-3">{{ $row->product_name }}</td><td class="px-4 py-3">{{ number_format((float) $row->quantity_sold, 3) }}</td><td class="px-4 py-3">SAR {{ number_format((float) $row->line_total_sum, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-stone-400">No SKU movement rows for the current filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
