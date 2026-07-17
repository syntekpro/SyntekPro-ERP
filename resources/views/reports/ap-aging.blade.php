@extends('layouts.hub')

@section('title', 'AP Aging')

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Phase 7</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Accounts Payable Aging</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Outstanding supplier balances bucketed by days overdue as of {{ $today->toDateString() }}.</p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="overflow-hidden rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-stone-900/80 text-stone-400">
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
                    <tbody class="divide-y divide-white/10 bg-stone-950/40 text-stone-200">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-4 py-3 text-white">{{ $row['supplier_name'] }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $row['current'], 2) }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $row['1_30'], 2) }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $row['31_60'], 2) }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $row['61_90'], 2) }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $row['90_plus'], 2) }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $row['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-stone-400">No outstanding supplier balances.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
