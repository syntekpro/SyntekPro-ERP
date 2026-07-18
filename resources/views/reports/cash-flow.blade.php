@extends('layouts.hub')

@section('title', 'Cash Flow')

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Phase 9</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Cash Flow (Indirect)</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Company-wide cash flow using indirect operating method from accounting movements.</p>
        </div>

        <form method="GET" action="{{ route('reports.cash-flow') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="grid gap-5 md:grid-cols-2">
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
                <a href="{{ route('reports.cash-flow') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Reset</a>
            </div>
        </form>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 text-sm text-stone-200">
            <div class="flex items-center justify-between"><span>Net income</span><span>SAR {{ number_format((float) $statement['net_income'], 2) }}</span></div>
            <div class="mt-3 flex items-center justify-between"><span>Working capital adjustment: Accounts receivable</span><span>SAR {{ number_format((float) $statement['working_capital_adjustments']['ar'], 2) }}</span></div>
            <div class="mt-2 flex items-center justify-between"><span>Working capital adjustment: Inventory</span><span>SAR {{ number_format((float) $statement['working_capital_adjustments']['inventory'], 2) }}</span></div>
            <div class="mt-2 flex items-center justify-between"><span>Working capital adjustment: VAT receivable</span><span>SAR {{ number_format((float) $statement['working_capital_adjustments']['vat_receivable'], 2) }}</span></div>
            <div class="mt-2 flex items-center justify-between"><span>Working capital adjustment: Accounts payable</span><span>SAR {{ number_format((float) $statement['working_capital_adjustments']['ap'], 2) }}</span></div>
            <div class="mt-2 flex items-center justify-between"><span>Working capital adjustment: VAT payable</span><span>SAR {{ number_format((float) $statement['working_capital_adjustments']['vat_payable'], 2) }}</span></div>

            <div class="mt-5 border-t border-white/10 pt-4">
                <div class="flex items-center justify-between font-semibold text-white"><span>Operating cash flow</span><span>SAR {{ number_format((float) $statement['operating_cash_flow'], 2) }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Investing cash flow</span><span>SAR {{ number_format((float) $statement['investing_cash_flow'], 2) }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Financing cash flow</span><span>SAR {{ number_format((float) $statement['financing_cash_flow'], 2) }}</span></div>
                <div class="mt-3 flex items-center justify-between font-semibold text-amber-200"><span>Net cash change (reported)</span><span>SAR {{ number_format((float) $statement['net_cash_change'], 2) }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Cash opening</span><span>SAR {{ number_format((float) $statement['cash_opening'], 2) }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Cash closing</span><span>SAR {{ number_format((float) $statement['cash_closing'], 2) }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Cash change (actual)</span><span>SAR {{ number_format((float) $statement['cash_change_actual'], 2) }}</span></div>
            </div>
        </div>
    </section>
@endsection
