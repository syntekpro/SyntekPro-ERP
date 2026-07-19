@extends('layouts.hub')

@section('title', 'Trial Balance')

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">General Ledger</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Trial Balance</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Calendar-year ledger sanity check by shop and date range.</p>
        </div>

        <form method="GET" action="{{ route('reports.trial-balance') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
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
                <a href="{{ route('reports.trial-balance') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Reset</a>
            </div>
        </form>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white">Trial balance lines</h2>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $isBalanced ? 'bg-emerald-500/15 text-emerald-200' : 'bg-rose-500/15 text-rose-200' }}">{{ $isBalanced ? 'Balanced' : 'Out of balance' }}</span>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/10">
                <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                    <thead class="bg-stone-900/80 text-stone-400">
                        <tr>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Account</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Total debit</th>
                            <th class="px-4 py-3">Total credit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 bg-stone-950/60 text-stone-200">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-4 py-3 text-white">{{ $row->code }}</td>
                                <td class="px-4 py-3">{{ $row->name }}</td>
                                <td class="px-4 py-3">{{ str($row->account_type instanceof \App\Enums\AccountType ? $row->account_type->value : $row->account_type)->title() }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $row->total_debit, 2) }}</td>
                                <td class="px-4 py-3">SAR {{ number_format((float) $row->total_credit, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-stone-400">No accounts found.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-stone-900/80 text-stone-200">
                        <tr>
                            <th colspan="3" class="px-4 py-3 text-right">Totals</th>
                            <th class="px-4 py-3">SAR {{ number_format($totalDebits, 2) }}</th>
                            <th class="px-4 py-3">SAR {{ number_format($totalCredits, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>
@endsection
