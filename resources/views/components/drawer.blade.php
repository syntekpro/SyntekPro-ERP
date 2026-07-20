@props([
    'id',
    'side' => 'end',
    'title' => null,
    'description' => null,
    'width' => 'md',
])

@php
    $sideClasses = $side === 'start'
        ? 'start-0 ui-drawer-panel-start'
        : 'end-0 ui-drawer-panel-end';

    $widthClasses = match ($width) {
        'sm' => 'max-w-sm',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        default => 'max-w-xl',
    };

    $titleId = $title ? $id.'-title' : null;
    $descriptionId = $description ? $id.'-description' : null;
@endphp

<div id="{{ $id }}" data-ui-drawer class="ui-drawer fixed inset-0 z-50 hidden" aria-hidden="true">
    <div class="ui-drawer-backdrop absolute inset-0 bg-ink/45 backdrop-blur-sm" data-ui-drawer-close="{{ $id }}"></div>
    <aside
        class="ui-drawer-panel absolute inset-y-0 {{ $sideClasses }} w-full {{ $widthClasses }} border-line bg-surface shadow-2xl"
        role="dialog"
        aria-modal="true"
        @if($titleId) aria-labelledby="{{ $titleId }}" @endif
        @if($descriptionId) aria-describedby="{{ $descriptionId }}" @endif
    >
        <div class="flex h-full flex-col">
            <header class="flex items-start justify-between gap-3 border-b border-line px-5 py-4">
                <div>
                    @if ($title)
                        <h2 id="{{ $titleId }}" class="text-lg font-semibold text-ink">{{ $title }}</h2>
                    @endif
                    @if ($description)
                        <p id="{{ $descriptionId }}" class="mt-1 text-sm text-muted">{{ $description }}</p>
                    @endif
                </div>
                <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-ui border border-line text-subtle transition hover:border-brass/60 hover:text-ink" data-ui-drawer-close="{{ $id }}" aria-label="{{ __('Close drawer') }}">
                    <x-lucide-x class="h-4 w-4" />
                </button>
            </header>
            <div class="flex-1 overflow-auto px-5 py-4">
                {{ $slot }}
            </div>
            @isset($footer)
                <footer class="border-t border-line px-5 py-4">
                    {{ $footer }}
                </footer>
            @endisset
        </div>
    </aside>
</div>
