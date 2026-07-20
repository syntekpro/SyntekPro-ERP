@props([
    'icon' => 'inbox',
    'title' => 'No records yet',
    'message' => 'Create the first record to start using this screen.',
    'actionLabel' => null,
    'actionHref' => null,
])

<x-card padding="lg" {{ $attributes->merge(['class' => 'ui-empty-state border-dashed text-center']) }}>
    <x-dynamic-component :component="'lucide-'.$icon" class="mx-auto h-10 w-10 text-brass" />
    <h2 class="mt-4 text-base font-semibold text-ink">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm text-muted">{{ $message }}</p>

    @isset($actions)
        <div class="mt-5 flex justify-center gap-2">{{ $actions }}</div>
    @else
        @if ($actionLabel && $actionHref)
            <x-button :href="$actionHref" class="mt-5 justify-center">{{ $actionLabel }}</x-button>
        @endif
    @endisset
</x-card>
