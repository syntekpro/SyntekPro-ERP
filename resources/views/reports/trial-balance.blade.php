@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">General Ledger</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Trial Balance</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Calendar-year ledger sanity check by shop and date range.</p>
        </div>

        <form method="GET" action="{{ route('reports.trial-balance') }}" class="rounded-ui border border-line bg-surface p-6">
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
                <a href="{{ route('reports.trial-balance') }}" class="btn-secondary">Reset</a>
            </div>
        </form>

        <div class="rounded-ui border border-line bg-surface p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-ink">Trial balance lines</h2>
                <x-status-badge :tone="$isBalanced ? 'success' : 'danger'">{{ $isBalanced ? 'Balanced' : 'Out of balance' }}</x-status-badge>
            </div>

            <div class="overflow-hidden rounded-ui border border-line table-baseline">
                <table class="min-w-full text-start text-sm ui-table">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Account</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Total debit</th>
                            <th class="px-4 py-3">Total credit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-ink">
                        @forelse ($rows as $row)
                            <tr>
                                <td class="px-4 py-3 figure-mono font-medium text-ink">{{ $row->code }}</td>
                                <td class="px-4 py-3">{{ $row->name }}</td>
                                <td class="px-4 py-3">{{ str($row->account_type instanceof \App\Enums\AccountType ? $row->account_type->value : $row->account_type)->title() }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row->total_debit, 2) }}</td>
                                <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $row->total_credit, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-muted">No accounts found.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-panel text-ink">
                        <tr>
                            <th colspan="3" class="px-4 py-3 text-end">Totals</th>
                            <th class="px-4 py-3 figure-mono ledger-total">SAR {{ number_format($totalDebits, 2) }}</th>
                            <th class="px-4 py-3 figure-mono ledger-total">SAR {{ number_format($totalCredits, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </section>
@endsection
