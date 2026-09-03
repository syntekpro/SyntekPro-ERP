@props([
    'applicationName',
    'activeLocale',
    'themePreference' => 'system',
    'currentUser' => null,
    'headerBrandText' => null,
    'headerBrandSubtext' => null,
    'quickMenuItems' => [],
    'quickMenuSections' => [],
    'updateNotification' => null,
])

@php
    $workspaceLabel = $currentUser?->shop_id ? __('Shop Workspace') : __('Back Office Workspace');
    $hiddenQuickMenuItems = collect($currentUser?->navigation_state['quick_menu_hidden_items'] ?? [])
        ->filter(fn ($key): bool => is_string($key) && $key !== '')
        ->values()
        ->all();
    $dismissedHeaderNotifications = collect($currentUser?->navigation_state['header_notifications_dismissed'] ?? [])
        ->filter(fn ($notificationId): bool => is_string($notificationId) && $notificationId !== '')
        ->values()
        ->all();
    $headerNotifications = [
        [
            'id' => 'receivables-follow-up',
            'title' => __('Receivables follow-up'),
            'message' => __('Some customer balances require collections follow-up.'),
            'tone' => 'warning',
        ],
        [
            'id' => 'purchase-orders-pending',
            'title' => __('Purchase orders pending closure'),
            'message' => __('Review open purchase orders and close completed records.'),
            'tone' => 'danger',
        ],
        [
            'id' => 'preferences-synced',
            'title' => __('Preferences synced'),
            'message' => __('Theme and language preferences are saved for your account.'),
            'tone' => 'info',
        ],
    ];

    if ($updateNotification !== null) {
        $headerNotifications[] = $updateNotification;
    }
    $notificationToneClasses = [
        'warning' => 'border-brass/35 bg-brass/10 text-brass-contrast',
        'danger' => 'border-rust/30 bg-rust/10 text-ink',
        'info' => 'border-ledger/30 bg-ledger/10 text-ink',
    ];
    $visibleHeaderNotifications = collect($headerNotifications)
        ->reject(fn (array $notification): bool => in_array($notification['id'], $dismissedHeaderNotifications, true))
        ->values()
        ->all();
    $visibleHeaderNotificationCount = count($visibleHeaderNotifications);
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
                    <div class="header-menu-panel start-0 w-72 rounded-ui border border-line bg-surface p-3 shadow-xl">
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

                <details class="header-menu quick-menu relative" data-quick-menu data-quick-menu-hidden-items='@json($hiddenQuickMenuItems)'>
                    <summary class="quick-menu-trigger flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-ui text-[var(--color-link)] transition hover:bg-panel hover:text-ink" aria-label="{{ __('Quick Menu') }}">
                        <x-lucide-grid-2x2 class="h-[18px] w-[18px]" />
                    </summary>
                    <div class="quick-menu-panel header-menu-panel start-0 rounded-2xl p-3 shadow-xl">
                        <div class="quick-menu-toolbar mb-2 flex items-center justify-between gap-2">
                            <p class="text-[0.68rem] font-semibold uppercase tracking-[0.28em] text-subtle">{{ __('Quick Menu') }}</p>
                            <button type="button" class="quick-menu-customize-btn inline-flex h-8 w-8 items-center justify-center rounded-full transition" data-quick-menu-customize-toggle aria-expanded="false" aria-controls="quick-menu-customize-panel" aria-label="{{ __('Customize shortcuts') }}">
                                <x-lucide-pencil-line class="h-3.5 w-3.5" />
                            </button>
                            <div id="quick-menu-customize-panel" class="quick-menu-customize-popover hidden" data-quick-menu-customize-panel>
                                <p class="quick-menu-customize-title">{{ __('Customize shortcuts') }}</p>
                                <p class="quick-menu-customize-copy">{{ __('Choose which shortcuts appear in the Quick Menu.') }}</p>
                                <div class="quick-menu-customize-list">
                                    @foreach ($quickMenuSections as $quickMenuSection)
                                        <div class="quick-menu-customize-section">
                                            <p class="quick-menu-customize-section-label">{{ $quickMenuSection['label'] }}</p>
                                            @foreach ($quickMenuSection['items'] as $quickMenuItem)
                                                @php
                                                    $quickMenuToggleId = 'quick-menu-toggle-' . str_replace(['.', '/', ':'], '-', $quickMenuItem['key']);
                                                @endphp
                                                <label for="{{ $quickMenuToggleId }}" class="quick-menu-customize-option">
                                                    <input
                                                        id="{{ $quickMenuToggleId }}"
                                                        type="checkbox"
                                                        class="quick-menu-customize-checkbox"
                                                        data-quick-menu-item-toggle
                                                        value="{{ $quickMenuItem['key'] }}"
                                                        @checked(!in_array($quickMenuItem['key'], $hiddenQuickMenuItems, true))
                                                    />
                                                    <span>{{ $quickMenuItem['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                                <div class="quick-menu-customize-actions">
                                    <button type="button" class="quick-menu-customize-reset" data-quick-menu-reset>{{ __('Reset') }}</button>
                                    <button type="button" class="quick-menu-customize-close" data-quick-menu-customize-close>{{ __('Done') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="quick-menu-columns">
                            @foreach ($quickMenuSections as $quickMenuSection)
                                <div class="quick-menu-column" data-quick-menu-section data-quick-menu-section-key="{{ $quickMenuSection['key'] }}">
                                    <p class="quick-menu-column-title">{{ $quickMenuSection['label'] }}</p>
                                    <div class="quick-menu-column-items">
                                        @foreach ($quickMenuSection['items'] as $quickMenuItem)
                                            <a
                                                href="{{ $quickMenuItem['url'] }}"
                                                class="quick-menu-item"
                                                data-quick-menu-item
                                                data-quick-menu-item-key="{{ $quickMenuItem['key'] }}"
                                                data-quick-menu-section-key="{{ $quickMenuSection['key'] }}"
                                            >
                                                <x-icon-tile color="{{ $quickMenuItem['color'] ?? 'brass' }}" size="xs" class="quick-menu-item-icon">
                                                    <span class="material-symbols-quick-menu" aria-hidden="true">{{ $quickMenuItem['icon'] }}</span>
                                                </x-icon-tile>
                                                <span class="quick-menu-item-label">{{ $quickMenuItem['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
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
                <details
                    class="header-menu relative"
                    data-header-notifications
                    data-header-notification-dismissed="{{ implode(',', $dismissedHeaderNotifications) }}"
                >
                    <summary class="relative flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-ui text-subtle transition hover:bg-panel hover:text-ink" aria-label="{{ __('Notifications') }}">
                        <x-lucide-bell class="h-[18px] w-[18px]" />
                        @if ($visibleHeaderNotificationCount > 0)
                            <span class="absolute -end-0.5 -top-0.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-rust px-1 text-[0.6rem] font-semibold text-white" data-header-notification-badge>
                                {{ $visibleHeaderNotificationCount }}
                            </span>
                        @endif
                    </summary>
                    <div class="header-menu-panel end-0 w-80 rounded-ui border border-line bg-surface p-3 shadow-xl">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-subtle">{{ __('Notifications') }}</p>
                            <button
                                type="button"
                                data-header-notification-mark-all
                                class="text-[0.68rem] font-semibold uppercase tracking-[0.12em] text-link transition hover:opacity-80"
                                @disabled($visibleHeaderNotificationCount === 0)
                            >
                                {{ __('Mark all read') }}
                            </button>
                        </div>
                        <div class="mt-2 space-y-2 text-sm" data-header-notification-list>
                            @foreach ($headerNotifications as $notification)
                                @php
                                    $isDismissed = in_array($notification['id'], $dismissedHeaderNotifications, true);
                                    $toneClass = $notificationToneClasses[$notification['tone']] ?? 'border-line bg-panel text-ink';
                                @endphp
                                <div
                                    class="rounded-ui border px-3 py-2 {{ $toneClass }} {{ $isDismissed ? 'hidden' : '' }}"
                                    data-header-notification-item
                                    data-header-notification-id="{{ $notification['id'] }}"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-semibold uppercase tracking-[0.08em]">{{ $notification['title'] }}</p>
                                            <p class="mt-1 text-sm">{{ $notification['message'] }}</p>
                                        </div>
                                        <button
                                            type="button"
                                            class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-line/80 bg-surface/70 text-subtle transition hover:border-brass/50 hover:text-ink"
                                            data-header-notification-dismiss
                                            aria-label="{{ __('Dismiss notification') }}"
                                        >
                                            <x-lucide-x class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                            <p
                                class="rounded-ui border border-line bg-panel/70 px-3 py-3 text-center text-sm text-muted {{ $visibleHeaderNotificationCount > 0 ? 'hidden' : '' }}"
                                data-header-notification-empty
                            >
                                {{ __('You are all caught up.') }}
                            </p>
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
