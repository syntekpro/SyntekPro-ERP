@extends('layouts.hub')

@section('title', 'Fiscal Periods')

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Period Management</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Fiscal Period Close</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Monthly close and reopen controls for accounting periods.</p>
        </div>

        <form method="GET" action="{{ route('fiscal-periods.index') }}" class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-stone-200">Fiscal year</label>
                    <input name="year" type="number" min="2000" max="2200" value="{{ $year }}" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                </div>
            </div>
            <div class="mt-5 flex gap-3">
                <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Load year</button>
            </div>
        </form>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-400/40 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                <thead class="bg-stone-900/80 text-stone-400">
                    <tr>
                        <th class="px-4 py-3">Month</th>
                        <th class="px-4 py-3">Start</th>
                        <th class="px-4 py-3">End</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Closed by</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-stone-950/60 text-stone-200">
                    @foreach ($periods as $period)
                        <tr>
                            <td class="px-4 py-3 text-white">{{ str_pad((string) $period->month, 2, '0', STR_PAD_LEFT) }} / {{ $period->year }}</td>
                            <td class="px-4 py-3">{{ $period->period_start?->toDateString() }}</td>
                            <td class="px-4 py-3">{{ $period->period_end?->toDateString() }}</td>
                            <td class="px-4 py-3 {{ $period->is_closed ? 'text-rose-300' : 'text-emerald-300' }}">{{ $period->is_closed ? 'Closed' : 'Open' }}</td>
                            <td class="px-4 py-3">{{ $period->closer?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($canManage)
                                    @if ($period->is_closed)
                                        <form method="POST" action="{{ route('fiscal-periods.reopen', $period) }}">
                                            @csrf
                                            <button type="submit" class="rounded-xl border border-white/10 px-3 py-2 text-xs font-semibold text-stone-100 hover:bg-white/10">Reopen</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('fiscal-periods.close', $period) }}">
                                            @csrf
                                            <button type="submit" class="rounded-xl bg-rose-500/80 px-3 py-2 text-xs font-semibold text-white hover:bg-rose-400/80">Close</button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-xs text-stone-500">Super admin only</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
