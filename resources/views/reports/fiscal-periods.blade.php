@extends('layouts.hub')

@section('title', __(''))

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">{{ __('Period Management') }}</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">{{ __('Fiscal Period Close') }}</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">{{ __('Monthly close and reopen controls for accounting periods.') }}</p>
        </div>

        <form method="GET" action="{{ route('fiscal-periods.index') }}" class="rounded-ui border border-line bg-surface p-6">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-ink">{{ __('Fiscal year') }}</label>
                    <input name="year" type="number" min="2000" max="2200" value="{{ $year }}" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                </div>
            </div>
            <div class="mt-5 flex gap-3">
                <button type="submit" class="btn-primary">{{ __('Load year') }}</button>
            </div>
        </form>

        @if (session('status'))
            <div class="rounded-ui border border-ledger/30 bg-ledger/10 px-4 py-3 text-sm text-ledger">{{ session('status') }}</div>
        @endif

        <div class="overflow-hidden rounded-ui border border-line table-baseline">
            <table class="min-w-full text-start text-sm ui-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3">{{ __('Month') }}</th>
                        <th class="px-4 py-3">{{ __('Start') }}</th>
                        <th class="px-4 py-3">{{ __('End') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Closed by') }}</th>
                        <th class="px-4 py-3">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @foreach ($periods as $period)
                        <tr>
                            <td class="px-4 py-3 figure-mono font-medium text-ink">{{ str_pad((string) $period->month, 2, '0', STR_PAD_LEFT) }} / {{ $period->year }}</td>
                            <td class="px-4 py-3 figure-mono">{{ $period->period_start?->toDateString() }}</td>
                            <td class="px-4 py-3 figure-mono">{{ $period->period_end?->toDateString() }}</td>
                            <td class="px-4 py-3"><x-status-badge :tone="$period->is_closed ? 'danger' : 'success'">{{ $period->is_closed ? __('Closed') : __('Open') }}</x-status-badge></td>
                            <td class="px-4 py-3">{{ $period->closer?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($canManage)
                                    @if ($period->is_closed)
                                        <form method="POST" action="{{ route('fiscal-periods.reopen', $period) }}">
                                            @csrf
                                            <button type="submit" class="btn-secondary btn-size-sm">{{ __('Reopen') }}</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('fiscal-periods.close', $period) }}">
                                            @csrf
                                            <button type="submit" class="btn-danger btn-size-sm">{{ __('Close') }}</button>
                                        </form>
                                    @endif
                                @else
                                    <span class="text-xs text-subtle">{{ __('Super admin only') }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
