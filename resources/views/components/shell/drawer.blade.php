@props([
    'applicationName',
    'brandWebsite',
    'poweredByLabel',
    'showPoweredBy' => true,
    'visibleSections' => [],
    'collapsedSections' => [],
    'isActive',
])

<aside class="shell-drawer border-e border-line bg-surface/95 backdrop-blur" data-shell-drawer>
    <div class="flex h-full flex-col px-4 py-5">
        <div class="flex items-start justify-between rounded-ui border border-line bg-panel p-4">
            <div>
                <div class="drawer-copy">
                    <img src="{{ app(\App\Services\Settings\BusinessSettingsService::class)->logoUrl() }}" alt="{{ $applicationName }}" class="h-auto w-full max-w-[14rem]" />
                </div>
                <div class="drawer-icon-only hidden h-10 w-10 items-center justify-center rounded-ui border border-line bg-surface text-sm font-semibold text-ink" aria-hidden="true">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($applicationName, 0, 1)) }}
                </div>
                <p class="drawer-copy mt-3 text-xs font-semibold uppercase tracking-[0.28em] text-brass">{{ $applicationName }}</p>
                <h1 class="drawer-copy mt-2 text-xl font-semibold text-ink">{{ __('Back Office') }}</h1>
                <p class="drawer-copy mt-2 text-sm text-muted">{{ __('Chain operations, finance, and administration.') }}</p>
            </div>
            <button type="button" data-shell-drawer-close class="inline-flex h-9 w-9 items-center justify-center rounded-ui border border-line text-subtle transition hover:border-brass/60 hover:text-ink lg:hidden" aria-label="{{ __('Close navigation drawer') }}">
                <x-lucide-x class="h-4 w-4" />
            </button>
        </div>

        <nav class="mt-5 space-y-2 text-sm" aria-label="{{ __('Primary navigation') }}" data-nav-root data-initial-collapsed-sections='@json($collapsedSections)'>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" aria-label="{{ __('Dashboard') }}">
                <x-lucide-layout-dashboard class="h-4 w-4 shrink-0" />
                <span class="drawer-copy">{{ __('Dashboard') }}</span>
            </a>

            @foreach ($visibleSections as $section)
                @php
                    $sectionActive = collect($section['items'])->contains(fn (array $item): bool => $isActive($item['patterns']));
                    $sectionCollapsed = in_array($section['key'], $collapsedSections, true) && ! $sectionActive;
                @endphp
                <section class="nav-section" data-nav-section="{{ $section['key'] }}">
                    <button type="button" class="nav-section-button" data-nav-section-toggle aria-expanded="{{ $sectionCollapsed ? 'false' : 'true' }}" aria-controls="nav-section-{{ $section['key'] }}" aria-label="{{ $section['label'] }}">
                        <span class="flex items-center gap-2">
                            <x-dynamic-component :component="'lucide-'.$section['icon']" class="h-4 w-4 shrink-0" />
                            <span class="drawer-copy">{{ $section['label'] }}</span>
                        </span>
                        <x-lucide-chevron-down class="drawer-copy h-4 w-4 transition" data-nav-chevron />
                    </button>
                    <div id="nav-section-{{ $section['key'] }}" class="mt-1 space-y-1 ps-2 {{ $sectionCollapsed ? 'hidden' : '' }}" data-nav-section-panel>
                        @foreach ($section['items'] as $item)
                            <a href="{{ route($item['route']) }}" class="nav-link nav-link-nested {{ $isActive($item['patterns']) ? 'nav-link-active' : '' }}" aria-label="{{ $item['label'] }}">
                                <x-dynamic-component :component="'lucide-'.$item['icon']" class="h-4 w-4 shrink-0" />
                                <span class="drawer-copy">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </nav>

        @if ($showPoweredBy)
            <a href="{{ $brandWebsite }}" target="_blank" rel="noopener noreferrer" class="drawer-copy mt-auto pt-6 text-center text-xs font-semibold uppercase tracking-[0.24em] text-subtle transition hover:text-brass">{{ $poweredByLabel }}</a>
        @endif
    </div>
</aside>
