@props([
    'padding' => 'md',
    'surface' => 'panel',
])

@php
    $paddingClasses = match ($padding) {
        'none' => '',
        'sm' => 'p-4',
        'lg' => 'p-8',
        default => 'p-6',
    };

    $surfaceClasses = match ($surface) {
        'surface' => 'bg-surface',
        'transparent' => 'bg-transparent',
        default => 'bg-panel',
    };
@endphp

<section {{ $attributes->merge(['class' => "ui-card rounded-ui border border-line {$surfaceClasses} {$paddingClasses}"]) }}>
    @isset($header)
        <div class="ui-card-header mb-4">{{ $header }}</div>
    @endisset

    <div class="ui-card-body">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="ui-card-footer mt-4">{{ $footer }}</div>
    @endisset
</section>
