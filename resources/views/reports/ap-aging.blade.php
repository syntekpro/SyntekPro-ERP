@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Payables</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Accounts Payable Aging</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Outstanding supplier balances bucketed by days overdue as of {{ $today->toDateString() }}.</p>
        </div>

        <div class="rounded-ui border border-line bg-surface p-6">
            <div class="overflow-hidden rounded-ui border border-line table-baseline">
                <table class="min-w-full text-start text-sm ui-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Supplier</th>
                            <th class="px-4 py-3">Current</th>
                            <th class="px-4 py-3">1-30</th>
                            <th class="px-4 py-3">31-60</th>
                            <th class="px-4 py-3">61-90</th>
                            <th class="px-4 py-3">90+</th>
                            <th class="px-4 py-3">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-ink">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-ink">{{ $row['supplier_name'] }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row['current'], 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row['1_30'], 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row['31_60'], 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row['61_90'], 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row['90_plus'], 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-muted">No outstanding supplier balances.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
