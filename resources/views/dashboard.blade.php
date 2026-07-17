@extends('layouts.hub')

@section('title', 'Dashboard')

@section('content')
    <section class="space-y-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Hub overview</p>
                <h1 class="mt-3 text-4xl font-semibold text-white">SyntekPro ERP dashboard</h1>
                <p class="mt-3 max-w-3xl text-sm text-stone-300">
                    Central visibility for the current rollout. Shops, warehouses, and the shared product catalog are managed here from the hub.
                </p>
            </div>

            <div class="rounded-3xl border border-white/10 bg-stone-900/70 px-5 py-4 text-sm text-stone-300">
                <p class="font-medium text-white">Signed in as {{ $user?->email ?? 'Guest' }}</p>
                <p class="mt-1">Shop context: {{ $currentShopId ?? 'Hub context' }}</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <article class="rounded-3xl border border-white/10 bg-gradient-to-br from-stone-900 via-stone-950 to-black p-6 shadow-2xl shadow-black/25">
                <p class="text-xs uppercase tracking-[0.3em] text-stone-400">Shops</p>
                <p class="mt-4 text-5xl font-semibold text-white">{{ $counts['shops'] }}</p>
                <p class="mt-2 text-sm text-stone-300">Registered branch locations and POS contexts.</p>
            </article>

            <article class="rounded-3xl border border-white/10 bg-gradient-to-br from-amber-500/15 via-stone-950 to-black p-6 shadow-2xl shadow-black/25">
                <p class="text-xs uppercase tracking-[0.3em] text-stone-400">Warehouses</p>
                <p class="mt-4 text-5xl font-semibold text-white">{{ $counts['warehouses'] }}</p>
                <p class="mt-2 text-sm text-stone-300">Central stock locations feeding shop transfers.</p>
            </article>

            <article class="rounded-3xl border border-white/10 bg-gradient-to-br from-teal-500/15 via-stone-950 to-black p-6 shadow-2xl shadow-black/25">
                <p class="text-xs uppercase tracking-[0.3em] text-stone-400">Products</p>
                <p class="mt-4 text-5xl font-semibold text-white">{{ $counts['products'] }}</p>
                <p class="mt-2 text-sm text-stone-300">Shared catalog entries available across the chain.</p>
            </article>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Quick actions</h2>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @can('create', \App\Models\Shop::class)
                        <a href="{{ route('shops.create') }}" class="rounded-2xl border border-white/10 bg-stone-900/80 px-4 py-4 text-sm font-medium text-stone-100 transition hover:border-amber-300/40 hover:bg-stone-900">
                            Add a new shop
                        </a>
                    @endcan
                    @can('create', \App\Models\Warehouse::class)
                        <a href="{{ route('warehouses.create') }}" class="rounded-2xl border border-white/10 bg-stone-900/80 px-4 py-4 text-sm font-medium text-stone-100 transition hover:border-amber-300/40 hover:bg-stone-900">
                            Add a warehouse
                        </a>
                    @endcan
                    @can('create', \App\Models\Product::class)
                        <a href="{{ route('products.create') }}" class="rounded-2xl border border-white/10 bg-stone-900/80 px-4 py-4 text-sm font-medium text-stone-100 transition hover:border-amber-300/40 hover:bg-stone-900">
                            Add a product
                        </a>
                    @endcan
                    <a href="{{ route('products.index') }}" class="rounded-2xl border border-white/10 bg-stone-900/80 px-4 py-4 text-sm font-medium text-stone-100 transition hover:border-teal-300/40 hover:bg-stone-900">
                        Browse product catalog
                    </a>
                </div>
            </section>

            <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
                <h2 class="text-lg font-semibold text-white">Rollout status</h2>
                <ul class="mt-5 space-y-3 text-sm text-stone-300">
                    <li class="rounded-2xl border border-white/10 bg-stone-900/70 px-4 py-3">Phase 0 tenancy and auth foundation is verified by feature tests.</li>
                    <li class="rounded-2xl border border-white/10 bg-stone-900/70 px-4 py-3">Phase 1 hub CRUD surfaces are now wired for shops, warehouses, and products.</li>
                    <li class="rounded-2xl border border-white/10 bg-stone-900/70 px-4 py-3">Early Phase 2 stock tables exist, with transfer status schema ready for workflow logic.</li>
                </ul>
            </section>
        </div>

        <section class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <h2 class="text-lg font-semibold text-white">SyntekPro capabilities</h2>
            <p class="mt-1 text-sm text-stone-400">Core ERP modules represented by the official branding icon set.</p>
            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <article class="rounded-2xl border border-white/10 bg-stone-900/70 p-4 text-center">
                    <img src="{{ asset('images/icon-accounting.png') }}" alt="Accounting" class="mx-auto h-12 w-12" />
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-300">Accounting</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-stone-900/70 p-4 text-center">
                    <img src="{{ asset('images/icon-analytics.png') }}" alt="Analytics" class="mx-auto h-12 w-12" />
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-300">Analytics</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-stone-900/70 p-4 text-center">
                    <img src="{{ asset('images/icon-cloud-ready.png') }}" alt="Cloud ready" class="mx-auto h-12 w-12" />
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-300">Cloud Ready</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-stone-900/70 p-4 text-center">
                    <img src="{{ asset('images/icon-secure.png') }}" alt="Secure" class="mx-auto h-12 w-12" />
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-300">Secure</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-stone-900/70 p-4 text-center">
                    <img src="{{ asset('images/icon-hrm.png') }}" alt="HRM" class="mx-auto h-12 w-12" />
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-300">HRM</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-stone-900/70 p-4 text-center">
                    <img src="{{ asset('images/icon-sales.png') }}" alt="Sales" class="mx-auto h-12 w-12" />
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-300">Sales</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-stone-900/70 p-4 text-center">
                    <img src="{{ asset('images/icon-anytime-anywhere.png') }}" alt="Anytime anywhere" class="mx-auto h-12 w-12" />
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-300">Anytime Anywhere</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-stone-900/70 p-4 text-center">
                    <img src="{{ asset('images/icon-inventory.png') }}" alt="Inventory" class="mx-auto h-12 w-12" />
                    <p class="mt-3 text-xs font-semibold uppercase tracking-[0.2em] text-stone-300">Inventory</p>
                </article>
            </div>
        </section>
    </section>
@endsection