@php
    $currentUser = auth()->user();
    $themePreference = $currentUser?->theme_mode ?? 'system';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $themePreference }}" data-theme-preference="{{ $themePreference }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-interface-preferences-url" content="{{ route('user-interface-preferences.update') }}">
        <title>@yield('title') | {{ config('app.name', 'SyntekPro ERP') }}</title>
        <script>
            (() => {
                const root = document.documentElement;
                const preference = root.dataset.themePreference || 'system';
                const resolved = preference === 'dark' || preference === 'light'
                    ? preference
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

                root.dataset.theme = resolved;
            })();
        </script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="icon" type="image/png" href="{{ app(\App\Services\Settings\BusinessSettingsService::class)->faviconUrl() }}">
        <link rel="manifest" href="{{ route('manifest') }}">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        @livewireStyles
        <link rel="stylesheet" href="{{ route('theme.css') }}">
    </head>
    <body class="min-h-screen bg-paper text-ink transition-colors" data-persist-theme-default="{{ $currentUser?->theme_mode === null ? 'true' : 'false' }}">
        @php
            $canSettings = (bool) $currentUser?->hasPermission('settings.manage');
            $canReports = $currentUser?->isSuperAdmin() || $currentUser?->isShopManager() || $currentUser?->isAccountant();
            $canFinancialReports = $currentUser?->isSuperAdmin() || $currentUser?->isAccountant();
            $collapsedSections = $currentUser?->navigation_state['collapsed_sections'] ?? [];
            $isActive = function (array|string $patterns): bool {
                foreach ((array) $patterns as $pattern) {
                    if (request()->routeIs($pattern)) {
                        return true;
                    }
                }

                return false;
            };

            $navSections = [
                ['key' => 'operations', 'label' => 'Operations', 'icon' => 'warehouse', 'items' => [
                    ['label' => 'Shops', 'route' => 'shops.index', 'patterns' => 'shops.*', 'icon' => 'store', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Shop::class)],
                    ['label' => 'Warehouses', 'route' => 'warehouses.index', 'patterns' => 'warehouses.*', 'icon' => 'warehouse', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Warehouse::class)],
                    ['label' => 'Products', 'route' => 'products.index', 'patterns' => 'products.*', 'icon' => 'package-search', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Product::class)],
                    ['label' => 'Stock Transfers', 'route' => 'stock-transfers.index', 'patterns' => 'stock-transfers.*', 'icon' => 'arrow-left-right', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\StockTransfer::class)],
                ]],
                ['key' => 'purchasing', 'label' => 'Purchasing', 'icon' => 'shopping-bag', 'items' => [
                    ['label' => 'Suppliers', 'route' => 'suppliers.index', 'patterns' => 'suppliers.*', 'icon' => 'truck', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Supplier::class)],
                    ['label' => 'Purchase Orders', 'route' => 'purchase-orders.index', 'patterns' => 'purchase-orders.*', 'icon' => 'clipboard-list', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\PurchaseOrder::class)],
                    ['label' => 'Supplier Bills', 'route' => 'supplier-bills.index', 'patterns' => 'supplier-bills.*', 'icon' => 'receipt-text', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\SupplierBill::class)],
                    ['label' => 'Debit Notes', 'route' => 'debit-notes.index', 'patterns' => 'debit-notes.*', 'icon' => 'undo-2', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\DebitNote::class)],
                ]],
                ['key' => 'sales', 'label' => 'Sales', 'icon' => 'badge-dollar-sign', 'items' => [
                    ['label' => 'Customers', 'route' => 'customers.index', 'patterns' => 'customers.*', 'icon' => 'users', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Customer::class)],
                    ['label' => 'Customer Receivables', 'route' => 'customer-receivables.index', 'patterns' => 'customer-receivables.*', 'icon' => 'wallet-cards', 'visible' => $canFinancialReports],
                    ['label' => 'Credit Notes', 'route' => 'credit-notes.index', 'patterns' => 'credit-notes.*', 'icon' => 'rotate-ccw', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\CreditNote::class)],
                    ['label' => 'POS', 'route' => 'pos.sales', 'patterns' => 'pos.sales', 'icon' => 'monitor-smartphone', 'visible' => $currentUser?->shop_id !== null],
                ]],
                ['key' => 'accounting', 'label' => 'Accounting', 'icon' => 'landmark', 'items' => [
                    ['label' => 'Accounts', 'route' => 'accounts.index', 'patterns' => 'accounts.*', 'icon' => 'book-open', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Account::class)],
                    ['label' => 'Journal Entries', 'route' => 'journal-entries.index', 'patterns' => 'journal-entries.*', 'icon' => 'notebook-tabs', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\JournalEntry::class)],
                    ['label' => 'Fiscal Periods', 'route' => 'fiscal-periods.index', 'patterns' => 'fiscal-periods.*', 'icon' => 'calendar-days', 'visible' => $canFinancialReports],
                ]],
                ['key' => 'reports', 'label' => 'Reports', 'icon' => 'chart-column', 'items' => [
                    ['label' => 'Reports Overview', 'route' => 'reports.index', 'patterns' => 'reports.index', 'icon' => 'chart-no-axes-combined', 'visible' => $currentUser?->isSuperAdmin() || $currentUser?->isShopManager()],
                    ['label' => 'Trial Balance', 'route' => 'reports.trial-balance', 'patterns' => 'reports.trial-balance', 'icon' => 'scale', 'visible' => $canReports],
                    ['label' => 'Balance Sheet', 'route' => 'reports.balance-sheet', 'patterns' => 'reports.balance-sheet', 'icon' => 'columns-3', 'visible' => $canFinancialReports],
                    ['label' => 'Income Statement', 'route' => 'reports.income-statement', 'patterns' => 'reports.income-statement', 'icon' => 'chart-line', 'visible' => $canReports],
                    ['label' => 'Cash Flow Statement', 'route' => 'reports.cash-flow', 'patterns' => 'reports.cash-flow', 'icon' => 'waves', 'visible' => $canFinancialReports],
                    ['label' => 'AP Aging', 'route' => 'reports.ap-aging', 'patterns' => 'reports.ap-aging', 'icon' => 'hourglass', 'visible' => $canFinancialReports],
                    ['label' => 'AR Aging', 'route' => 'reports.ar-aging', 'patterns' => 'reports.ar-aging', 'icon' => 'timer-reset', 'visible' => $canFinancialReports],
                ]],
                ['key' => 'administration', 'label' => 'Administration', 'icon' => 'settings-2', 'items' => [
                    ['label' => 'Users', 'route' => 'users.index', 'patterns' => 'users.*', 'icon' => 'user-cog', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\User::class)],
                    ['label' => 'Units', 'route' => 'units.index', 'patterns' => 'units.*', 'icon' => 'ruler', 'visible' => $canSettings],
                    ['label' => 'Price Categories', 'route' => 'price-categories.index', 'patterns' => 'price-categories.*', 'icon' => 'tags', 'visible' => $canSettings],
                    ['label' => 'Settings / Roles / Branding', 'route' => 'settings.index', 'patterns' => 'settings.*', 'icon' => 'sliders-horizontal', 'visible' => $canSettings],
                ]],
            ];

            $visibleSections = collect($navSections)->map(function (array $section): array {
                $section['items'] = collect($section['items'])->filter(fn (array $item): bool => (bool) $item['visible'])->values()->all();

                return $section;
            })->filter(fn (array $section): bool => count($section['items']) > 0)->values()->all();

            $commandItems = collect($visibleSections)->flatMap(fn (array $section) => collect($section['items'])->map(fn (array $item) => [
                'label' => $item['label'],
                'section' => $section['label'],
                'url' => route($item['route']),
            ]))->prepend(['label' => 'Dashboard', 'section' => 'Home', 'url' => route('dashboard')])->values()->all();
        @endphp

        @if (config('app.demo_mode'))
            <div class="border-b border-brass/40 bg-brass/15 px-4 py-2 text-center text-sm font-semibold uppercase tracking-[0.2em] text-brass-contrast">
                Demo Mode - Fictional data resets nightly
            </div>
        @endif

        <div class="min-h-screen lg:grid lg:grid-cols-[19rem_1fr]">
            <aside class="border-b border-line bg-surface/90 backdrop-blur lg:border-b-0 lg:border-r">
                <div class="flex h-full flex-col px-4 py-5">
                    <div class="rounded-ui border border-line bg-panel p-4">
                        <img src="{{ app(\App\Services\Settings\BusinessSettingsService::class)->logoUrl() }}" alt="SyntekPro ERP" class="h-auto w-full max-w-[14rem]" />
                        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.28em] text-brass">SyntekPro ERP</p>
                        <h1 class="mt-2 text-xl font-semibold text-ink">Back Office</h1>
                        <p class="mt-2 text-sm text-muted">Chain operations, finance, and administration.</p>
                    </div>

                    <div class="mt-4 grid gap-2">
                        <button type="button" data-command-open class="flex items-center justify-between rounded-ui border border-line bg-panel px-3 py-2 text-left text-sm text-muted transition hover:border-brass/60 hover:text-ink">
                            <span class="flex items-center gap-2"><x-lucide-search class="h-4 w-4" /> Search screens</span>
                            <kbd class="font-mono text-[0.65rem] text-subtle">Ctrl K</kbd>
                        </button>
                        <button type="button" data-theme-toggle class="flex items-center justify-between rounded-ui border border-line px-3 py-2 text-sm font-semibold text-ink transition hover:border-brass/60">
                            <span class="flex items-center gap-2"><x-lucide-sun-moon class="h-4 w-4" /> Appearance</span>
                            <span data-theme-toggle-label class="text-xs uppercase tracking-[0.2em] text-subtle">{{ $themePreference }}</span>
                        </button>
                    </div>

                    <nav class="mt-5 space-y-2 text-sm" aria-label="Primary navigation" data-nav-root data-initial-collapsed-sections='@json($collapsedSections)'>
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
                            <x-lucide-layout-dashboard class="h-4 w-4" />
                            <span>Dashboard</span>
                        </a>

                        @foreach ($visibleSections as $section)
                            @php
                                $sectionActive = collect($section['items'])->contains(fn (array $item): bool => $isActive($item['patterns']));
                                $sectionCollapsed = in_array($section['key'], $collapsedSections, true) && ! $sectionActive;
                            @endphp
                            <section class="nav-section" data-nav-section="{{ $section['key'] }}">
                                <button type="button" class="nav-section-button" data-nav-section-toggle aria-expanded="{{ $sectionCollapsed ? 'false' : 'true' }}" aria-controls="nav-section-{{ $section['key'] }}">
                                    <span class="flex items-center gap-2">
                                        <x-dynamic-component :component="'lucide-'.$section['icon']" class="h-4 w-4" />
                                        {{ $section['label'] }}
                                    </span>
                                    <x-lucide-chevron-down class="h-4 w-4 transition" data-nav-chevron />
                                </button>
                                <div id="nav-section-{{ $section['key'] }}" class="mt-1 space-y-1 pl-2 {{ $sectionCollapsed ? 'hidden' : '' }}" data-nav-section-panel>
                                    @foreach ($section['items'] as $item)
                                        <a href="{{ route($item['route']) }}" class="nav-link nav-link-nested {{ $isActive($item['patterns']) ? 'nav-link-active' : '' }}">
                                            <x-dynamic-component :component="'lucide-'.$item['icon']" class="h-4 w-4" />
                                            <span>{{ $item['label'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </nav>

                    <div class="mt-6 rounded-ui border border-line bg-panel p-4 text-sm text-muted">
                        <p class="font-medium text-ink">{{ $currentUser?->email }}</p>
                        <p class="mt-1 uppercase tracking-[0.24em] text-subtle">{{ $currentUser?->role?->value ?? 'user' }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="mt-auto pt-6">
                        @csrf
                        <button type="submit" class="btn-secondary w-full justify-center">
                            <x-lucide-log-out class="h-4 w-4" />
                            Sign out
                        </button>
                    </form>

                    <a href="https://syntekpro.com" target="_blank" rel="noopener noreferrer" class="mt-4 block text-center text-xs font-semibold uppercase tracking-[0.24em] text-subtle transition hover:text-brass">Powered by SyntekPro ERP</a>
                </div>
            </aside>

            <main class="px-6 py-8 lg:px-10 lg:py-10">
                @if (session('status'))
                    <div class="mb-6 rounded-ui border border-ledger/30 bg-ledger/10 px-4 py-3 text-sm text-ledger">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 rounded-ui border border-brass/30 bg-brass/10 px-4 py-3 text-sm text-brass-contrast">
                        {{ session('warning') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        <div class="command-palette fixed inset-0 z-50 hidden bg-ink/60 p-4 backdrop-blur" data-command-palette aria-hidden="true">
            <div class="mx-auto mt-20 max-w-2xl overflow-hidden rounded-ui border border-line bg-surface shadow-xl">
                <div class="flex items-center gap-3 border-b border-line px-4 py-3">
                    <x-lucide-search class="h-5 w-5 text-muted" />
                    <input data-command-input type="search" placeholder="Jump to a screen" class="w-full bg-transparent py-2 text-base text-ink outline-none placeholder:text-subtle" />
                    <kbd class="rounded border border-line px-2 py-1 font-mono text-xs text-subtle">Esc</kbd>
                </div>
                <div class="max-h-96 overflow-auto p-2" data-command-results></div>
            </div>
        </div>

        <script type="application/json" id="navigation-commands">@json($commandItems)</script>
        @livewireScripts
    </body>
</html>
