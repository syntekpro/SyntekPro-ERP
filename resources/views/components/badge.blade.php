@props([
    'tone' => 'neutral',
])

@php
    $classes = match ($tone) {
        'success' => 'ui-badge ui-badge-success',
        'warning' => 'ui-badge ui-badge-warning',
        'danger' => 'ui-badge ui-badge-danger',
        'info' => 'ui-badge ui-badge-info',
        default => 'ui-badge ui-badge-neutral',
    };
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
