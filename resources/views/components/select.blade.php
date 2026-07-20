<select {{ $attributes->merge(['class' => 'ui-select w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none disabled:cursor-not-allowed disabled:opacity-70']) }}>
    {{ $slot }}
</select>
