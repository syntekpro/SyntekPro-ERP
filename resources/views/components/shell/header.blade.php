@props([
    'applicationName',
    'activeLocale',
    'themePreference' => 'system',
    'currentUser' => null,
    'headerBrandText' => null,
    'headerBrandSubtext' => null,
])

@php
    $workspaceLabel = $currentUser?->shop_id ? __('Shop Workspace') : __('Back Office Workspace');
@endphp

<header class="shell-header border-b border-line bg-surface/90 backdrop-blur">
    <div class="px-4 py-3 lg:px-6">
        <div class="shell-header-top flex flex-wrap items-center justify-between gap-2 border-b border-line/80 pb-3">
            <div class="flex min-w-0 items-center gap-2">
                <button
                    type="button"
                    data-shell-drawer-toggle
                    class="inline-flex h-9 w-9 items-center justify-center rounded-ui text-subtle transition hover:bg-panel hover:text-ink"
                    aria-label="{{ __('Toggle navigation drawer') }}"
                >
                    <x-lucide-panel-left class="h-[18px] w-[18px]" />
                </button>

                <div class="header-brand flex min-w-0 items-center gap-2.5 px-1">
                    <img src="{{ app(\App\Services\Settings\BusinessSettingsService::class)->logoUrl() }}" alt="{{ $applicationName }}" class="h-7 w-auto" />
                    <div class="min-w-0 hidden sm:block">
                        <p class="truncate text-sm font-semibold text-ink">{{ $applicationName }}</p>
                        <p class="truncate text-[0.65rem] text-subtle">{{ $headerBrandSubtext ?: __('Operations Hub') }}</p>
                    </div>
                </div>
            </div>

            <div class="hidden items-center gap-2 text-xs text-subtle lg:flex">
                <span data-header-date>{{ now()->translatedFormat('D, d M Y') }}</span>
                <span class="figure-mono" data-header-time>{{ now()->format('H:i:ss') }}</span>
            </div>
        </div>

        <div class="shell-header-row mt-3">
            <div class="flex min-w-0 items-center gap-1">
                <details class="header-menu relative hidden sm:block">
                    <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-ui text-subtle transition hover:bg-panel hover:text-ink" aria-label="{{ __('Workspace selector') }}">
                        <x-lucide-layout-dashboard class="h-[18px] w-[18px]" />
                    </summary>
                    <div class="header-menu-panel absolute start-0 z-40 mt-2 w-72 rounded-ui border border-line bg-surface p-3 shadow-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Workspace selector') }}</p>
                        <div class="mt-2 space-y-2 text-sm">
                            <a href="{{ route('dashboard') }}" class="flex items-center justify-between rounded-ui border border-line bg-panel px-3 py-2 text-ink transition hover:border-brass/60">
                                <span>{{ __('Back Office') }}</span>
                                <x-lucide-arrow-up-right class="h-3.5 w-3.5" />
                            </a>
                            @if ($currentUser?->shop_id)
                                <a href="{{ route('pos.sales') }}" class="flex items-center justify-between rounded-ui border border-line bg-panel px-3 py-2 text-ink transition hover:border-brass/60">
                                    <span>{{ __('POS Workspace') }}</span>
                                    <x-lucide-arrow-up-right class="h-3.5 w-3.5" />
                                </a>
                            @endif
                        </div>
                    </div>
                </details>

                <details class="header-menu relative">
                    <summary class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-ui text-subtle transition hover:bg-panel hover:text-ink" aria-label="{{ __('Quick actions') }}">
                        <x-lucide-zap class="h-[18px] w-[18px]" />
                    </summary>
                    <div class="header-menu-panel absolute start-0 z-40 mt-2 w-72 rounded-ui border border-line bg-surface p-3 shadow-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Quick actions') }}</p>
                        <div class="mt-2 space-y-2 text-sm">
                            @can('create', \App\Models\Shop::class)
                                <a href="{{ route('shops.create') }}" class="flex items-center justify-between rounded-ui border border-line bg-panel px-3 py-2 text-ink transition hover:border-brass/60"><span>{{ __('Add shop') }}</span><x-lucide-arrow-up-right class="h-3.5 w-3.5" /></a>
                            @endcan
                            @can('create', \App\Models\Warehouse::class)
                                <a href="{{ route('warehouses.create') }}" class="flex items-center justify-between rounded-ui border border-line bg-panel px-3 py-2 text-ink transition hover:border-brass/60"><span>{{ __('Add warehouse') }}</span><x-lucide-arrow-up-right class="h-3.5 w-3.5" /></a>
                            @endcan
                            @can('create', \App\Models\Product::class)
                                <a href="{{ route('products.create') }}" class="flex items-center justify-between rounded-ui border border-line bg-panel px-3 py-2 text-ink transition hover:border-brass/60"><span>{{ __('Add product') }}</span><x-lucide-arrow-up-right class="h-3.5 w-3.5" /></a>
                            @endcan
                        </div>
                    </div>
                </details>
            </div>

            <button
                type="button"
                data-command-open
                class="shell-search-trigger flex min-w-0 items-center gap-2 rounded-ui border border-line bg-surface px-3 py-2 text-start text-sm text-muted shadow-sm transition hover:border-brass/50 hover:text-ink"
                aria-label="{{ __('Open global search') }}"
            >
                <x-lucide-search class="h-4 w-4 shrink-0" />
                <span class="truncate">{{ __('Search screens, commands, and reports') }}</span>
                <kbd class="ms-auto hidden font-mono text-[0.65rem] text-subtle sm:inline">Ctrl K</kbd>
            </button>

            <div class="flex items-center justify-end gap-1">
                <details class="header-menu relative">
                    <summary class="relative flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-ui text-subtle transition hover:bg-panel hover:text-ink" aria-label="{{ __('Notifications') }}">
                        <x-lucide-bell class="h-[18px] w-[18px]" />
                        <span class="absolute -end-0.5 -top-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rust px-1 text-[0.6rem] font-semibold text-white">3</span>
                    </summary>
                    <div class="header-menu-panel absolute end-0 z-40 mt-2 w-80 rounded-ui border border-line bg-surface p-3 shadow-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Notifications') }}</p>
                        <div class="mt-2 space-y-2 text-sm">
                            <div class="rounded-ui border border-line bg-panel px-3 py-2 text-ink">{{ __('Receivables aging requires follow-up.') }}</div>
                            <div class="rounded-ui border border-line bg-panel px-3 py-2 text-ink">{{ __('Open purchase orders are pending closure.') }}</div>
                            <div class="rounded-ui border border-line bg-panel px-3 py-2 text-ink">{{ __('Theme and locale settings are synced.') }}</div>
                        </div>
                    </div>
                </details>

                <form method="POST" action="{{ route('locale.update') }}" class="hidden sm:block">
                    @csrf
                    <label for="header-locale-switch" class="sr-only">{{ __('Language') }}</label>
                    <select
                        id="header-locale-switch"
                        name="locale"
                        onchange="this.form.submit()"
                        class="h-9 rounded-ui border-0 bg-transparent px-2 text-sm font-medium text-subtle outline-none transition hover:bg-panel hover:text-ink"
                    >
                        <option value="en" @selected($activeLocale === 'en')>{{ __('EN') }}</option>
                        <option value="ar" @selected($activeLocale === 'ar')>{{ __('AR') }}</option>
                    </select>
                </form>

                <button
                    type="button"
                    data-theme-toggle
                    class="inline-flex h-9 w-9 items-center justify-center rounded-ui text-subtle transition hover:bg-panel hover:text-ink"
                    aria-label="{{ __('Toggle theme') }}"
                >
                    <x-lucide-sun-moon class="h-[18px] w-[18px]" />
                </button>

                <details class="profile-menu relative">
                    <summary class="flex h-9 cursor-pointer list-none items-center gap-2 rounded-ui px-1.5 text-sm text-ink transition hover:bg-panel">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-panel text-xs font-medium text-subtle">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentUser?->email ?? 'U', 0, 2)) }}
                        </span>
                        <span class="hidden lg:block max-w-[12rem] truncate">{{ $currentUser?->email }}</span>
                        <x-lucide-chevron-down class="hidden lg:block h-3.5 w-3.5 text-subtle" />
                    </summary>
                    <div class="profile-menu-panel absolute end-0 z-40 mt-2 w-64 rounded-ui border border-line bg-surface p-3 shadow-xl">
                        <p class="truncate text-sm font-semibold text-ink">{{ $currentUser?->email }}</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.2em] text-subtle">{{ $currentUser?->role?->value ?? __('user') }}</p>

                        <div class="mt-3 space-y-2">
                            <form method="POST" action="{{ route('locale.update') }}" class="sm:hidden">
                                @csrf
                                <label for="profile-locale-switch" class="mb-1 block text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Language') }}</label>
                                <select
                                    id="profile-locale-switch"
                                    name="locale"
                                    onchange="this.form.submit()"
                                    class="w-full rounded-ui border border-line bg-panel px-3 py-2 text-sm text-ink outline-none"
                                >
                                    <option value="en" @selected($activeLocale === 'en')>{{ __('English') }}</option>
                                    <option value="ar" @selected($activeLocale === 'ar')>{{ __('Arabic') }}</option>
                                </select>
                            </form>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn-secondary w-full justify-center">
                                    <x-lucide-log-out class="h-4 w-4" />
                                    {{ __('Sign out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </div>
</header>
