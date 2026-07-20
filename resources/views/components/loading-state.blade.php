@props([
    'lines' => 4,
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'ui-loading-state rounded-ui border border-line bg-panel p-4']) }}>
    @if ($title)
        <p class="mb-3 text-sm font-semibold text-subtle">{{ $title }}</p>
    @endif

    <div class="space-y-2">
        @for ($i = 0; $i < $lines; $i++)
            <div class="skeleton-block" style="height: {{ $i === 0 ? '1.2rem' : '0.9rem' }}"></div>
        @endfor
    </div>
</div>
