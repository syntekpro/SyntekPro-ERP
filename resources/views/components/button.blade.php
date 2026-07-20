@props([
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
    'disabled' => false,
    'href' => null,
    'type' => 'button',
])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'btn-secondary',
        'ghost' => 'btn-ghost',
        'warning' => 'btn-warning',
        'success' => 'btn-success',
        'danger', 'destructive' => 'btn-danger',
        default => 'btn-primary',
    };

    $sizeClasses = match ($size) {
        'sm' => 'btn-size-sm',
        'lg' => 'btn-size-lg',
        default => 'btn-size-md',
    };

    $disabledState = $disabled || $loading;
    $classes = "{$variantClasses} {$sizeClasses}";
@endphp

@if ($href)
    <a href="{{ $href }}" aria-disabled="{{ $disabledState ? 'true' : 'false' }}" {{ $attributes->merge(['class' => $classes.($disabledState ? ' btn-disabled pointer-events-none opacity-60' : '')]) }}>
        @if ($loading)
            <x-lucide-loader-circle class="h-4 w-4 animate-spin" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @disabled($disabledState) aria-busy="{{ $loading ? 'true' : 'false' }}" {{ $attributes->merge(['class' => $classes.($disabledState ? ' btn-disabled opacity-60' : '')]) }}>
        @if ($loading)
            <x-lucide-loader-circle class="h-4 w-4 animate-spin" />
        @endif
        {{ $slot }}
    </button>
@endif
