@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Financial Statements</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Cash Flow (Indirect)</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Company-wide cash flow using indirect operating method from accounting movements.</p>
        </div>

        <form method="GET" action="{{ route('reports.cash-flow') }}" class="rounded-ui border border-line bg-surface p-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Start date</label>
                    <input name="start_date" type="date" value="{{ $filters['start_date'] }}" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">End date</label>
                    <input name="end_date" type="date" value="{{ $filters['end_date'] }}" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <button type="submit" class="btn-primary">Apply</button>
                <a href="{{ route('reports.cash-flow') }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        <div class="rounded-ui border border-line bg-surface p-6 text-sm text-muted">
            <div class="flex items-center justify-between"><span>Net income</span><span class="figure-mono">SAR {{ number_format((float) $statement['net_income'], 2) }}</span></div>
            <div class="mt-3 flex items-center justify-between"><span>Working capital adjustment: Accounts receivable</span><span class="figure-mono">SAR {{ number_format((float) $statement['working_capital_adjustments']['ar'], 2) }}</span></div>
            <div class="mt-2 flex items-center justify-between"><span>Working capital adjustment: Inventory</span><span class="figure-mono">SAR {{ number_format((float) $statement['working_capital_adjustments']['inventory'], 2) }}</span></div>
            <div class="mt-2 flex items-center justify-between"><span>Working capital adjustment: VAT receivable</span><span class="figure-mono">SAR {{ number_format((float) $statement['working_capital_adjustments']['vat_receivable'], 2) }}</span></div>
            <div class="mt-2 flex items-center justify-between"><span>Working capital adjustment: Accounts payable</span><span class="figure-mono">SAR {{ number_format((float) $statement['working_capital_adjustments']['ap'], 2) }}</span></div>
            <div class="mt-2 flex items-center justify-between"><span>Working capital adjustment: VAT payable</span><span class="figure-mono">SAR {{ number_format((float) $statement['working_capital_adjustments']['vat_payable'], 2) }}</span></div>

            <div class="mt-5 border-t border-line pt-4">
                <div class="flex items-center justify-between font-semibold text-ink"><span>Operating cash flow</span><span class="figure-mono">SAR {{ number_format((float) $statement['operating_cash_flow'], 2) }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Investing cash flow</span><span class="figure-mono">SAR {{ number_format((float) $statement['investing_cash_flow'], 2) }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Financing cash flow</span><span class="figure-mono">SAR {{ number_format((float) $statement['financing_cash_flow'], 2) }}</span></div>
                <div class="mt-3 flex items-center justify-between font-semibold text-brass"><span>Net cash change (reported)</span><span class="figure-mono">SAR {{ number_format((float) $statement['net_cash_change'], 2) }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Cash opening</span><span class="figure-mono">SAR {{ number_format((float) $statement['cash_opening'], 2) }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Cash closing</span><span class="figure-mono">SAR {{ number_format((float) $statement['cash_closing'], 2) }}</span></div>
                <div class="mt-2 flex items-center justify-between"><span>Cash change (actual)</span><span class="figure-mono">SAR {{ number_format((float) $statement['cash_change_actual'], 2) }}</span></div>
            </div>
        </div>
    </section>
@endsection
