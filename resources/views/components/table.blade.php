@props([
    'dense' => false,
    'responsive' => true,
])

@php
    $tableClasses = $dense ? 'ui-table ui-table-dense' : 'ui-table';
@endphp

<div {{ $attributes->merge(['class' => $responsive ? 'ui-table-wrap overflow-x-auto rounded-ui border border-line bg-surface table-baseline' : 'ui-table-wrap rounded-ui border border-line bg-surface table-baseline']) }}>
    <table class="min-w-full text-start text-sm {{ $tableClasses }}">
        {{ $slot }}
    </table>
</div>
