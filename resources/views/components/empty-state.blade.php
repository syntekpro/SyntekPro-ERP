@props([
    'icon' => 'inbox',
    'title' => 'No records yet',
    'message' => 'Create the first record to start using this screen.',
    'actionLabel' => null,
    'actionHref' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-ui border border-dashed border-line bg-panel px-6 py-10 text-center']) }}>
    <x-dynamic-component :component="'lucide-'.$icon" class="mx-auto h-10 w-10 text-brass" />
    <h2 class="mt-4 text-base font-semibold text-ink">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-sm text-muted">{{ $message }}</p>
    @if ($actionLabel && $actionHref)
        <x-button :href="$actionHref" class="mt-5 justify-center">{{ $actionLabel }}</x-button>
    @endif
</div>
