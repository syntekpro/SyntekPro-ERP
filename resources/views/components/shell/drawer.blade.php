@props([
    'applicationName',
    'brandWebsite',
    'poweredByLabel',
    'poweredByLogo' => null,
    'showPoweredBy' => true,
    'visibleSections' => [],
    'collapsedSections' => [],
    'isActive',
    'version' => null,
])

<aside class="shell-drawer border-e border-black/10 bg-surface/95 backdrop-blur" data-shell-drawer>
    <div class="flex h-full flex-col px-2 py-3">
        <div class="flex items-start justify-between rounded-ui border border-line bg-panel p-3">
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

        <nav class="mt-4 min-h-0 flex-1 space-y-1 overflow-y-auto overflow-x-hidden text-sm" aria-label="{{ __('Primary navigation') }}" data-nav-root data-initial-collapsed-sections='@json($collapsedSections)'>
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
                        <x-lucide-chevron-down class="drawer-copy h-4 w-4 transition {{ $sectionCollapsed ? '-rotate-90' : '' }}" data-nav-chevron />
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
        <script>
            (() => {
                try {
                    const nav = document.currentScript ? document.currentScript.previousElementSibling : document.querySelector('[data-nav-root]');
                    if (nav && nav.hasAttribute('data-nav-root')) {
                        const savedPos = sessionStorage.getItem('shell:sidebar-scroll-position');
                        if (savedPos !== null) {
                            nav.scrollTop = parseInt(savedPos, 10);
                        }
                    }
                } catch (e) {}
            })();
        </script>

        @if ($showPoweredBy)
            <a href="{{ $brandWebsite }}" target="_blank" rel="noopener noreferrer" class="mt-auto pt-6 flex flex-col items-center gap-2 text-subtle transition hover:text-brass">
                @if ($poweredByLogo)
                    <img src="{{ asset($poweredByLogo) }}" alt="SyntekPro" class="h-6 w-auto opacity-80 transition hover:opacity-100" />
                @endif
                <span class="drawer-copy text-center text-xs font-semibold uppercase tracking-[0.24em]">{{ $poweredByLabel }}</span>
            </a>
        @endif

        @if ($version)
            <div class="mt-3 text-center text-[0.65rem] text-subtle/70 figure-mono" data-product-version>
                {{ $applicationName }} {{ $version }}
            </div>
        @endif
    </div>
</aside>
