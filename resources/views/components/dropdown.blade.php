@props([
    'align' => 'end',
    'width' => 'w-64',
])

@php
    $panelAlign = $align === 'start' ? 'start-0' : 'end-0';
@endphp

<details {{ $attributes->merge(['class' => 'ui-dropdown relative']) }}>
    <summary class="ui-dropdown-trigger cursor-pointer list-none">{{ $trigger ?? '' }}</summary>
    <div class="ui-dropdown-panel absolute {{ $panelAlign }} z-40 mt-2 {{ $width }} rounded-ui border border-line bg-surface p-3 shadow-xl">
        {{ $slot }}
    </div>
</details>
