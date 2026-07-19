<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') | {{ config('app.name', 'SyntekPro ERP') }}</title>
        <link rel="icon" type="image/png" href="{{ app(\App\Services\Settings\BusinessSettingsService::class)->faviconUrl() }}">
        <link rel="manifest" href="{{ route('manifest') }}">
        <link rel="stylesheet" href="{{ route('theme.css') }}">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        @livewireStyles
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        @if (config('app.demo_mode'))
            <div class="border-b border-amber-400/40 bg-amber-500/20 px-4 py-2 text-center text-sm font-semibold uppercase tracking-[0.2em] text-amber-100">
                Demo Mode - Fictional data resets nightly
            </div>
        @endif

        <div class="min-h-screen lg:grid lg:grid-cols-[18rem_1fr]">
            <aside class="border-b border-white/10 bg-black/30 backdrop-blur lg:border-b-0 lg:border-r">
                <div class="flex h-full flex-col px-5 py-6">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-5">
                        <img src="{{ app(\App\Services\Settings\BusinessSettingsService::class)->logoUrl() }}" alt="SyntekPro ERP" class="h-auto w-full max-w-[15rem]" />
                        <p class="mt-3 text-xs font-semibold uppercase tracking-[0.35em] text-amber-300">SyntekPro ERP</p>
                        <h1 class="mt-2 text-2xl font-semibold text-white">Hub console</h1>
                        <p class="mt-2 text-sm text-stone-300">Central command for shops, warehouses, products, and chain operations.</p>
                    </div>

                    <nav class="mt-6 space-y-2 text-sm">
                        <a href="{{ route('dashboard') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('dashboard') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                            <span>Dashboard</span>
                            <span class="text-xs uppercase tracking-[0.28em]">Hub</span>
                        </a>

                        @can('viewAny', \App\Models\Shop::class)
                            <a href="{{ route('shops.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('shops.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Shops</span>
                                <span class="text-xs uppercase tracking-[0.28em]">CRUD</span>
                            </a>
                        @endcan

                        @can('viewAny', \App\Models\Warehouse::class)
                            <a href="{{ route('warehouses.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('warehouses.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Warehouses</span>
                                <span class="text-xs uppercase tracking-[0.28em]">Stock</span>
                            </a>
                        @endcan

                        @can('viewAny', \App\Models\Product::class)
                            <a href="{{ route('products.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('products.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Products</span>
                                <span class="text-xs uppercase tracking-[0.28em]">Catalog</span>
                            </a>
                        @endcan

                        @if (auth()->user()?->hasPermission('settings.manage'))
                            <a href="{{ route('units.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('units.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Units</span>
                                <span class="text-xs uppercase tracking-[0.28em]">UOM</span>
                            </a>
                            <a href="{{ route('price-categories.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('price-categories.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Price Categories</span>
                                <span class="text-xs uppercase tracking-[0.28em]">Price</span>
                            </a>
                        @endif

                        @can('viewAny', \App\Models\User::class)
                            <a href="{{ route('users.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('users.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Users</span>
                                <span class="text-xs uppercase tracking-[0.28em]">Access</span>
                            </a>
                        @endcan

                        @can('viewAny', \App\Models\Account::class)
                            <a href="{{ route('accounts.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('accounts.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Accounts</span>
                                <span class="text-xs uppercase tracking-[0.28em]">COA</span>
                            </a>
                        @endcan

                        @can('viewAny', \App\Models\JournalEntry::class)
                            <a href="{{ route('journal-entries.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('journal-entries.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Ledger</span>
                                <span class="text-xs uppercase tracking-[0.28em]">GL</span>
                            </a>
                        @endcan

                        @can('viewAny', \App\Models\Supplier::class)
                            <a href="{{ route('suppliers.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('suppliers.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Suppliers</span>
                                <span class="text-xs uppercase tracking-[0.28em]">AP</span>
                            </a>
                        @endcan

                        @can('viewAny', \App\Models\Customer::class)
                            <a href="{{ route('customers.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('customers.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Customers</span>
                                <span class="text-xs uppercase tracking-[0.28em]">AR</span>
                            </a>
                        @endcan

                        @can('viewAny', \App\Models\PurchaseOrder::class)
                            <a href="{{ route('purchase-orders.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('purchase-orders.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Purchase Orders</span>
                                <span class="text-xs uppercase tracking-[0.28em]">PO</span>
                            </a>
                        @endcan

                        @can('viewAny', \App\Models\SupplierBill::class)
                            <a href="{{ route('supplier-bills.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('supplier-bills.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Supplier Bills</span>
                                <span class="text-xs uppercase tracking-[0.28em]">Bills</span>
                            </a>
                        @endcan

                        @can('viewAny', \App\Models\CreditNote::class)
                            <a href="{{ route('credit-notes.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('credit-notes.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Credit Notes</span>
                                <span class="text-xs uppercase tracking-[0.28em]">Returns</span>
                            </a>
                        @endcan

                        @can('viewAny', \App\Models\DebitNote::class)
                            <a href="{{ route('debit-notes.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('debit-notes.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Debit Notes</span>
                                <span class="text-xs uppercase tracking-[0.28em]">Returns</span>
                            </a>
                        @endcan

                        @can('viewAny', \App\Models\StockTransfer::class)
                            <a href="{{ route('stock-transfers.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('stock-transfers.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Transfers</span>
                                <span class="text-xs uppercase tracking-[0.28em]">Flow</span>
                            </a>
                        @endcan

                        @if (auth()->user()?->isSuperAdmin() || auth()->user()?->isShopManager())
                            <a href="{{ route('reports.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('reports.index') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Reports</span>
                                <span class="text-xs uppercase tracking-[0.28em]">VAT</span>
                            </a>
                        @endif

                        @if (auth()->user()?->isSuperAdmin() || auth()->user()?->isShopManager() || auth()->user()?->isAccountant())
                            <a href="{{ route('reports.trial-balance') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('reports.trial-balance') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Trial Balance</span>
                                <span class="text-xs uppercase tracking-[0.28em]">GL</span>
                            </a>
                        @endif

                        @if (auth()->user()?->isSuperAdmin() || auth()->user()?->isAccountant())
                            <a href="{{ route('reports.balance-sheet') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('reports.balance-sheet') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Balance Sheet</span>
                                <span class="text-xs uppercase tracking-[0.28em]">FS</span>
                            </a>
                        @endif

                        @if (auth()->user()?->isSuperAdmin() || auth()->user()?->isShopManager() || auth()->user()?->isAccountant())
                            <a href="{{ route('reports.income-statement') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('reports.income-statement') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Income Statement (P&amp;L)</span>
                                <span class="text-xs uppercase tracking-[0.28em]">FS</span>
                            </a>
                        @endif

                        @if (auth()->user()?->isSuperAdmin() || auth()->user()?->isAccountant())
                            <a href="{{ route('reports.cash-flow') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('reports.cash-flow') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Cash Flow Statement</span>
                                <span class="text-xs uppercase tracking-[0.28em]">FS</span>
                            </a>
                        @endif

                        @if (auth()->user()?->isSuperAdmin() || auth()->user()?->isAccountant())
                            <a href="{{ route('reports.ap-aging') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('reports.ap-aging') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>AP Aging</span>
                                <span class="text-xs uppercase tracking-[0.28em]">AP</span>
                            </a>
                        @endif

                        @if (auth()->user()?->isSuperAdmin() || auth()->user()?->isAccountant())
                            <a href="{{ route('customer-receivables.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('customer-receivables.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Receivables</span>
                                <span class="text-xs uppercase tracking-[0.28em]">AR</span>
                            </a>
                        @endif

                        @if (auth()->user()?->isSuperAdmin() || auth()->user()?->isAccountant())
                            <a href="{{ route('reports.ar-aging') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('reports.ar-aging') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>AR Aging</span>
                                <span class="text-xs uppercase tracking-[0.28em]">AR</span>
                            </a>
                        @endif

                        @if (auth()->user()?->isSuperAdmin() || auth()->user()?->isAccountant())
                            <a href="{{ route('fiscal-periods.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('fiscal-periods.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Fiscal Periods</span>
                                <span class="text-xs uppercase tracking-[0.28em]">Close</span>
                            </a>
                        @endif

                        @if (auth()->user()?->shop_id !== null)
                            <a href="{{ route('pos.sales') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('pos.sales') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>POS</span>
                                <span class="text-xs uppercase tracking-[0.28em]">Offline</span>
                            </a>
                        @endif

                        @if (auth()->user()?->hasPermission('settings.manage'))
                            <a href="{{ route('settings.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('settings.*') ? 'bg-amber-400 text-stone-950' : 'text-stone-200 hover:bg-white/5' }}">
                                <span>Settings</span>
                                <span class="text-xs uppercase tracking-[0.28em]">Admin</span>
                            </a>
                        @endif
                    </nav>

                    <div class="mt-6 rounded-3xl border border-white/10 bg-stone-900/70 p-5 text-sm text-stone-300">
                        <p class="font-medium text-white">{{ auth()->user()?->email }}</p>
                        <p class="mt-1 uppercase tracking-[0.28em] text-stone-500">{{ auth()->user()?->role?->value ?? 'user' }}</p>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="mt-auto pt-6">
                        @csrf
                        <button type="submit" class="w-full rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">
                            Sign out
                        </button>
                    </form>

                    <a href="https://syntekpro.com" target="_blank" rel="noopener noreferrer" class="mt-4 block text-center text-xs font-semibold uppercase tracking-[0.24em] text-stone-500 transition hover:text-amber-300">Powered by SyntekPro ERP</a>
                </div>
            </aside>

            <main class="px-6 py-8 lg:px-10 lg:py-10">
                @if (session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                        {{ session('warning') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        @livewireScripts
    </body>
</html>