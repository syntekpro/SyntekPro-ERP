@props([
    'name' => null,
    'id' => null,
    'label' => null,
    'hint' => null,
    'required' => false,
    'error' => null,
])

@php
    $fieldId = $id ?? $name;
    $resolvedError = $error;

    if ($resolvedError === null && $name !== null && isset($errors) && $errors->has($name)) {
        $resolvedError = $errors->first($name);
    }
@endphp

<div {{ $attributes->merge(['class' => 'ui-form-field']) }}>
    @if ($label)
        <label @if($fieldId) for="{{ $fieldId }}" @endif class="ui-form-label block text-sm font-medium text-ink">
            {{ $label }}@if($required)<span class="ms-1 text-rust">*</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="text-xs text-subtle">{{ $hint }}</p>
    @endif

    @if ($resolvedError)
        <p class="text-xs text-rust" role="alert">{{ $resolvedError }}</p>
    @endif
</div>
