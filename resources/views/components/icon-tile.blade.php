@props([
    'color' => 'brass',
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-8 w-8',
        'md' => 'h-11 w-11',
        'lg' => 'h-14 w-14',
    ];

    $sizeClasses = $sizes[$size] ?? $sizes['md'];
@endphp

<div
    {{ $attributes->merge(['class' => "icon-tile {$sizeClasses} shrink-0 rounded-xl flex items-center justify-center"]) }}
    style="
        background: linear-gradient(155deg, color-mix(in srgb, var(--color-{{ $color }}) 16%, white), color-mix(in srgb, var(--color-{{ $color }}) 8%, white));
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.6),
            inset 0 -1px 2px color-mix(in srgb, var(--color-{{ $color }}) 25%, transparent),
            0 1px 2px color-mix(in srgb, var(--color-ink-navy) 8%, transparent);
        color: color-mix(in srgb, var(--color-{{ $color }}) 70%, black);
    "
>
    {{ $slot }}
</div>
