@php
    $currentUser = auth()->user();
    $themePreference = $currentUser?->theme_mode ?? 'system';
    $activeLocale = app()->getLocale();
    $isRtl = $activeLocale === 'ar';
    $brandingService = app(\App\Services\Settings\BusinessSettingsService::class);
    $brandingSettings = $brandingService->current();
    $applicationName = $brandingService->applicationName();
    $footerBranding = $brandingService->footerBranding();
    $headerBranding = $brandingService->headerBranding();
    $poweredByLabel = $footerBranding['powered_by_text'];
    $brandWebsite = $footerBranding['website'];
    $showPoweredBy = $footerBranding['show_powered_by'];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $activeLocale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" data-theme="{{ $themePreference }}" data-theme-preference="{{ $themePreference }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-interface-preferences-url" content="{{ route('user-interface-preferences.update') }}">
        <title>@yield('title') | {{ $applicationName }}</title>
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
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="icon" type="image/png" href="{{ $brandingService->faviconUrl() }}">
        <link rel="apple-touch-icon" href="{{ $brandingService->touchIconUrl() }}">
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
            $canCheques = (bool) $currentUser?->hasPermission('cheques.view');
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
                ['key' => 'operations', 'label' => __('Operations'), 'icon' => 'warehouse', 'items' => [
                    ['label' => __('Shops'), 'route' => 'shops.index', 'patterns' => 'shops.*', 'icon' => 'store', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Shop::class)],
                    ['label' => __('Warehouses'), 'route' => 'warehouses.index', 'patterns' => 'warehouses.*', 'icon' => 'warehouse', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Warehouse::class)],
                    ['label' => __('Products'), 'route' => 'products.index', 'patterns' => 'products.*', 'icon' => 'package-search', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Product::class)],
                    ['label' => __('Stock Transfers'), 'route' => 'stock-transfers.index', 'patterns' => 'stock-transfers.*', 'icon' => 'arrow-left-right', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\StockTransfer::class)],
                ]],
                ['key' => 'purchasing', 'label' => __('Purchasing'), 'icon' => 'shopping-bag', 'items' => [
                    ['label' => __('Suppliers'), 'route' => 'suppliers.index', 'patterns' => 'suppliers.*', 'icon' => 'truck', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Supplier::class)],
                    ['label' => __('Purchase Orders'), 'route' => 'purchase-orders.index', 'patterns' => 'purchase-orders.*', 'icon' => 'clipboard-list', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\PurchaseOrder::class)],
                    ['label' => __('Supplier Bills'), 'route' => 'supplier-bills.index', 'patterns' => 'supplier-bills.*', 'icon' => 'receipt-text', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\SupplierBill::class)],
                    ['label' => __('Debit Notes'), 'route' => 'debit-notes.index', 'patterns' => 'debit-notes.*', 'icon' => 'undo-2', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\DebitNote::class)],
                ]],
                ['key' => 'sales', 'label' => __('Sales'), 'icon' => 'badge-dollar-sign', 'items' => [
                    ['label' => __('Customers'), 'route' => 'customers.index', 'patterns' => 'customers.*', 'icon' => 'users', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Customer::class)],
                    ['label' => __('Customer Receivables'), 'route' => 'customer-receivables.index', 'patterns' => 'customer-receivables.*', 'icon' => 'wallet-cards', 'visible' => $canFinancialReports],
                    ['label' => __('Credit Notes'), 'route' => 'credit-notes.index', 'patterns' => 'credit-notes.*', 'icon' => 'rotate-ccw', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\CreditNote::class)],
                    ['label' => 'POS', 'route' => 'pos.sales', 'patterns' => 'pos.sales', 'icon' => 'monitor-smartphone', 'visible' => $currentUser?->shop_id !== null],
                ]],
                ['key' => 'accounting', 'label' => __('Accounting'), 'icon' => 'landmark', 'items' => [
                    ['label' => __('Accounts'), 'route' => 'accounts.index', 'patterns' => 'accounts.*', 'icon' => 'book-open', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\Account::class)],
                    ['label' => __('Journal Entries'), 'route' => 'journal-entries.index', 'patterns' => 'journal-entries.*', 'icon' => 'notebook-tabs', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\JournalEntry::class)],
                    ['label' => __('Cheques Register'), 'route' => 'cheques.index', 'patterns' => 'cheques.*', 'icon' => 'scroll-text', 'visible' => $canCheques],
                    ['label' => __('Fiscal Periods'), 'route' => 'fiscal-periods.index', 'patterns' => 'fiscal-periods.*', 'icon' => 'calendar-days', 'visible' => $canFinancialReports],
                ]],
                ['key' => 'reports', 'label' => __('Reports'), 'icon' => 'chart-column', 'items' => [
                    ['label' => __('Reports Overview'), 'route' => 'reports.index', 'patterns' => 'reports.index', 'icon' => 'chart-no-axes-combined', 'visible' => $currentUser?->isSuperAdmin() || $currentUser?->isShopManager()],
                    ['label' => __('Trial Balance'), 'route' => 'reports.trial-balance', 'patterns' => 'reports.trial-balance', 'icon' => 'scale', 'visible' => $canReports],
                    ['label' => __('Balance Sheet'), 'route' => 'reports.balance-sheet', 'patterns' => 'reports.balance-sheet', 'icon' => 'columns-3', 'visible' => $canFinancialReports],
                    ['label' => __('Income Statement'), 'route' => 'reports.income-statement', 'patterns' => 'reports.income-statement', 'icon' => 'chart-line', 'visible' => $canReports],
                    ['label' => __('Cash Flow Statement'), 'route' => 'reports.cash-flow', 'patterns' => 'reports.cash-flow', 'icon' => 'waves', 'visible' => $canFinancialReports],
                    ['label' => __('AP Aging'), 'route' => 'reports.ap-aging', 'patterns' => 'reports.ap-aging', 'icon' => 'hourglass', 'visible' => $canFinancialReports],
                    ['label' => __('AR Aging'), 'route' => 'reports.ar-aging', 'patterns' => 'reports.ar-aging', 'icon' => 'timer-reset', 'visible' => $canFinancialReports],
                ]],
                ['key' => 'administration', 'label' => __('Administration'), 'icon' => 'settings-2', 'items' => [
                    ['label' => __('Users'), 'route' => 'users.index', 'patterns' => 'users.*', 'icon' => 'user-cog', 'visible' => \Illuminate\Support\Facades\Gate::allows('viewAny', \App\Models\User::class)],
                    ['label' => __('Units'), 'route' => 'units.index', 'patterns' => 'units.*', 'icon' => 'ruler', 'visible' => $canSettings],
                    ['label' => __('Price Categories'), 'route' => 'price-categories.index', 'patterns' => 'price-categories.*', 'icon' => 'tags', 'visible' => $canSettings],
                    ['label' => __('Product Categories'), 'route' => 'product-categories.index', 'patterns' => 'product-categories.*', 'icon' => 'folder-tree', 'visible' => $canSettings],
                    ['label' => __('Brands'), 'route' => 'brands.index', 'patterns' => 'brands.*', 'icon' => 'award', 'visible' => $canSettings],
                    ['label' => __('Settings / Roles / Branding'), 'route' => 'settings.index', 'patterns' => 'settings.*', 'icon' => 'sliders-horizontal', 'visible' => $canSettings],
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
            ]))->prepend(['label' => __('Dashboard'), 'section' => __('Home'), 'url' => route('dashboard')])->values()->all();
        @endphp

        @if (config('app.demo_mode'))
            <div class="border-b border-brass/40 bg-brass/15 px-4 py-2 text-center text-sm font-semibold uppercase tracking-[0.2em] text-brass-contrast">
                {{ __('Demo Mode - Fictional data resets nightly') }}
            </div>
        @endif

        <div class="shell-overlay hidden bg-ink/40 backdrop-blur-sm lg:hidden" data-shell-overlay></div>

        <div class="shell-layout">
            <x-shell.drawer
                :application-name="$applicationName"
                :brand-website="$brandWebsite"
                :powered-by-label="$poweredByLabel"
                :show-powered-by="$showPoweredBy"
                :visible-sections="$visibleSections"
                :collapsed-sections="$collapsedSections"
                :is-active="$isActive"
            />

            <section class="shell-content">
                <x-shell.header
                    :application-name="$applicationName"
                    :active-locale="$activeLocale"
                    :theme-preference="$themePreference"
                    :current-user="$currentUser"
                    :header-brand-text="$headerBranding['text']"
                    :header-brand-subtext="$headerBranding['subtext']"
                />

                <main class="shell-main px-4 py-6 lg:px-8 lg:py-8">
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
            </section>
        </div>

        <div class="command-palette fixed inset-0 z-50 hidden bg-ink/60 p-4 backdrop-blur" data-command-palette aria-hidden="true">
            <div class="mx-auto mt-20 max-w-2xl overflow-hidden rounded-ui border border-line bg-surface shadow-xl">
                <div class="flex items-center gap-3 border-b border-line px-4 py-3">
                    <x-lucide-search class="h-5 w-5 text-muted" />
                    <input data-command-input type="search" placeholder="{{ __('Jump to a screen') }}" class="w-full bg-transparent py-2 text-base text-ink outline-none placeholder:text-subtle" />
                    <kbd class="rounded border border-line px-2 py-1 font-mono text-xs text-subtle">Esc</kbd>
                </div>
                <div class="max-h-96 overflow-auto p-2" data-command-results></div>
            </div>
        </div>

        <script type="application/json" id="navigation-commands">@json($commandItems)</script>
        @livewireScripts
    </body>
</html>
