@props([
    'tone' => 'neutral',
])

@php
    $classes = match ($tone) {
        'success', 'paid', 'credit' => 'status-pill status-pill-success',
        'danger', 'overdue', 'debit', 'failed' => 'status-pill status-pill-danger',
        default => 'status-pill status-pill-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
