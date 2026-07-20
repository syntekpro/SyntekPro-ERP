@props([
    'tone' => 'neutral',
])

@php
    $resolvedTone = match ($tone) {
        'success', 'paid', 'credit', 'active', 'balanced' => 'success',
        'danger', 'overdue', 'debit', 'failed', 'inactive', 'void', 'cancelled' => 'danger',
        'warning', 'pending', 'draft', 'submitted', 'open', 'in_transit' => 'warning',
        default => 'neutral',
    };
@endphp

<x-badge :tone="$resolvedTone" {{ $attributes }}>{{ $slot }}</x-badge>
