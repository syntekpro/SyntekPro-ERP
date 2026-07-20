@props([
    'type' => 'text',
])

<input type="{{ $type }}" {{ $attributes->merge(['class' => 'ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none disabled:cursor-not-allowed disabled:opacity-70']) }} />
