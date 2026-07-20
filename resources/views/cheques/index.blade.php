@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Accounting</p>
                <h1 class="mt-3 text-4xl font-semibold text-white">Cheques Register</h1>
                <p class="mt-3 max-w-2xl text-sm text-stone-300">Track incoming and outgoing post-dated cheques, then mark them cleared or bounced when bank outcomes are known.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('cheques.index') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="grid gap-4 md:grid-cols-5">
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">Direction</label>
                    <select name="direction" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none">
                        <option value="">All</option>
                        <option value="incoming" @selected($filters['direction'] === 'incoming')>Incoming</option>
                        <option value="outgoing" @selected($filters['direction'] === 'outgoing')>Outgoing</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">Status</label>
                    <select name="status" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none">
                        <option value="">All</option>
                        <option value="pending" @selected($filters['status'] === 'pending')>Pending</option>
                        <option value="cleared" @selected($filters['status'] === 'cleared')>Cleared</option>
                        <option value="bounced" @selected($filters['status'] === 'bounced')>Bounced</option>
                        <option value="cancelled" @selected($filters['status'] === 'cancelled')>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">From date</label>
                    <input type="date" name="from_date" value="{{ $filters['from_date'] }}" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">To date</label>
                    <input type="date" name="to_date" value="{{ $filters['to_date'] }}" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">Cheque date sort</label>
                    <select name="sort" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none">
                        <option value="asc" @selected($filters['sort'] === 'asc')>Earliest first</option>
                        <option value="desc" @selected($filters['sort'] === 'desc')>Latest first</option>
                    </select>
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Apply filters</button>
                <a href="{{ route('cheques.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-start text-sm">
                <thead class="bg-stone-900/80 text-stone-400">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Direction</th>
                        <th class="px-4 py-3">Cheque #</th>
                        <th class="px-4 py-3">Bank</th>
                        <th class="px-4 py-3">Settlement</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-stone-950/40 text-stone-200">
                    @forelse ($cheques as $cheque)
                        @php
                            $statusTone = match ($cheque->status->value) {
                                'cleared' => 'success',
                                'bounced' => 'danger',
                                default => 'neutral',
                            };
                            $settlementLabel = $cheque->direction->value === 'incoming'
                                ? ($cheque->sale?->invoice_number ?? ('Sale #'.$cheque->sale_id))
                                : ($cheque->supplierBill?->bill_number ?? ('Bill #'.$cheque->supplier_bill_id));
                        @endphp
                        <tr>
                            <td class="px-4 py-3">{{ $cheque->cheque_date?->toDateString() }}</td>
                            <td class="px-4 py-3">{{ str($cheque->direction->value)->title() }}</td>
                            <td class="px-4 py-3 text-white">{{ $cheque->cheque_number }}</td>
                            <td class="px-4 py-3">{{ $cheque->bank_name }}</td>
                            <td class="px-4 py-3">{{ $settlementLabel }}</td>
                            <td class="px-4 py-3">SAR {{ number_format((float) $cheque->amount, 2) }}</td>
                            <td class="px-4 py-3"><x-status-badge :tone="$statusTone">{{ str($cheque->status->value)->title() }}</x-status-badge></td>
                            <td class="px-4 py-3 text-end">
                                @if ($cheque->status->value === 'pending')
                                    <div class="flex justify-end gap-2">
                                        @can('clear', $cheque)
                                            <form method="POST" action="{{ route('cheques.clear', $cheque) }}">
                                                @csrf
                                                <button type="submit" class="rounded-xl border border-emerald-400/20 px-3 py-2 text-xs font-semibold text-emerald-200 transition hover:bg-emerald-500/10">Clear</button>
                                            </form>
                                        @endcan
                                        @can('bounce', $cheque)
                                            <form method="POST" action="{{ route('cheques.bounce', $cheque) }}">
                                                @csrf
                                                <button type="submit" class="rounded-xl border border-rose-400/20 px-3 py-2 text-xs font-semibold text-rose-200 transition hover:bg-rose-500/10">Bounce</button>
                                            </form>
                                        @endcan
                                    </div>
                                @else
                                    <span class="text-xs uppercase tracking-[0.2em] text-stone-500">Finalized</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-stone-400">No cheques found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $cheques->links() }}</div>
    </section>
@endsection
