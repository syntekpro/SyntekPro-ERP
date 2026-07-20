@props([
    'id',
    'title' => null,
    'description' => null,
    'size' => 'md',
    'open' => false,
])

@php
    $sizeClasses = match ($size) {
        'sm' => 'max-w-md',
        'lg' => 'max-w-3xl',
        'xl' => 'max-w-5xl',
        default => 'max-w-xl',
    };

    $titleId = $title ? $id.'-title' : null;
    $descriptionId = $description ? $id.'-description' : null;
@endphp

<div
    id="{{ $id }}"
    data-ui-modal
    class="ui-modal fixed inset-0 z-50 {{ $open ? '' : 'hidden' }}"
    aria-hidden="{{ $open ? 'false' : 'true' }}"
    role="dialog"
    aria-modal="true"
    @if($titleId) aria-labelledby="{{ $titleId }}" @endif
    @if($descriptionId) aria-describedby="{{ $descriptionId }}" @endif
>
    <div class="ui-modal-backdrop absolute inset-0 bg-ink/50 backdrop-blur-sm" data-ui-modal-close="{{ $id }}"></div>
    <div class="ui-modal-frame relative mx-auto mt-12 w-[calc(100%-2rem)] {{ $sizeClasses }} max-h-[calc(100vh-4rem)] overflow-auto rounded-ui border border-line bg-surface p-5 shadow-2xl lg:mt-20">
        <div class="flex items-start justify-between gap-3">
            <div>
                @if ($title)
                    <h2 id="{{ $titleId }}" class="text-lg font-semibold text-ink">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p id="{{ $descriptionId }}" class="mt-1 text-sm text-muted">{{ $description }}</p>
                @endif
            </div>
            <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-ui border border-line text-subtle transition hover:border-brass/60 hover:text-ink" data-ui-modal-close="{{ $id }}" aria-label="{{ __('Close modal') }}">
                <x-lucide-x class="h-4 w-4" />
            </button>
        </div>

        <div class="mt-4">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="mt-5 flex flex-wrap justify-end gap-2">{{ $footer }}</div>
        @endisset
    </div>
</div>
