@php
    $themePreference = $cashier->theme_mode ?? 'system';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="{{ $themePreference }}" data-theme-preference="{{ $themePreference }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="user-interface-preferences-url" content="{{ route('user-interface-preferences.update') }}">
        <meta name="theme-color" content="#1daeff">
        <title>{{ __('POS') }} | {{ config('app.name', 'SyntekPro ERP') }}</title>
        <script>
            (() => {
                const preference = document.documentElement.dataset.themePreference || 'system';
                document.documentElement.dataset.theme = preference === 'dark' || preference === 'light'
                    ? preference
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            })();
        </script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="icon" type="image/png" href="{{ app(\App\Services\Settings\BusinessSettingsService::class)->faviconUrl() }}">
        <link rel="manifest" href="{{ route('manifest') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/icon-main-192.png') }}">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <link rel="stylesheet" href="{{ route('theme.css') }}">
    </head>
    <body class="min-h-screen bg-paper text-ink" data-pos-shell="true" data-persist-theme-default="{{ $cashier->theme_mode === null ? 'true' : 'false' }}">
        <script id="pos-bootstrap" type="application/json">@json($bootstrap)</script>

        <main class="mx-auto grid min-h-screen max-w-7xl gap-6 px-4 py-4 lg:grid-cols-[1.5fr_1fr] lg:px-6 lg:py-6">
            <section class="rounded-ui border border-line bg-surface p-6 backdrop-blur">
                <div class="flex flex-col gap-4 border-b border-line pb-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-ledger">{{ __('Offline cashier') }}</p>
                        <h1 class="mt-3 text-4xl font-semibold text-ink">{{ $shop?->name }}</h1>
                        <p class="mt-2 text-sm text-muted">{{ $cashier->name }} · {{ $cashier->email }}</p>
                    </div>

                    <div class="rounded-ui border border-ledger/20 bg-ledger/10 px-4 py-3 text-sm text-ledger">
                        <p class="font-medium">{{ __('Shop stock snapshot') }}</p>
                        <p class="mt-1 text-muted">{{ __('This screen stays usable offline after the first online load.') }}</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_auto]">
                    <input id="product-search" type="search" placeholder="{{ __('Search by name, SKU, or barcode') }}" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-sm text-ink outline-none placeholder:text-subtle" />
                    <button id="sync-sales" type="button" class="btn-secondary">{{ __('Sync queued sales') }}</button>
                </div>

                <div class="mt-6 overflow-hidden rounded-ui border border-line">
                    <div id="product-list" class="grid max-h-[34rem] gap-px overflow-auto bg-panel"></div>
                </div>
            </section>

            <aside class="rounded-ui border border-line bg-surface p-6 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-brass">{{ __('Cart') }}</p>
                        <h2 class="mt-2 text-2xl font-semibold text-ink">{{ __('Current sale') }}</h2>
                    </div>
                    <button type="button" data-theme-toggle class="rounded-full border border-line px-3 py-1 text-xs font-semibold text-muted"><span data-theme-toggle-label>{{ $themePreference }}</span></button>
                </div>

                <span id="queue-status" class="mt-4 inline-flex rounded-full bg-panel px-3 py-1 text-xs font-semibold text-muted">{{ __('Offline ready') }}</span>

                <div class="mt-6 space-y-3" id="cart-list"></div>

                <div class="mt-6 rounded-ui border border-line bg-panel p-5">
                    <div class="space-y-3">
                        <div>
                            <label for="payment-method" class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-muted">{{ __('Payment method') }}</label>
                            <select id="payment-method" class="w-full rounded-ui border border-line bg-surface px-4 py-3 text-sm text-ink outline-none">
                                <option value="cash">{{ __('Cash') }}</option>
                                <option value="card">{{ __('Card') }}</option>
                                <option value="credit_account">{{ __('Credit account') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="customer-select" class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-muted">{{ __('Customer (credit only)') }}</label>
                            <select id="customer-select" class="w-full rounded-ui border border-line bg-surface px-4 py-3 text-sm text-ink outline-none">
                                <option value="">{{ __('Select customer') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm text-muted">
                        <span>{{ __('Subtotal') }}</span>
                        <span id="subtotal-value" class="figure-mono ltr-content">0.00</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-muted">
                        <span>{{ __('VAT') }}</span>
                        <span id="vat-value" class="figure-mono ltr-content">0.00</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-muted">
                        <span>{{ __('Excise') }}</span>
                        <span id="excise-value" class="figure-mono ltr-content">0.00</span>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-line pt-4 text-lg font-semibold text-ink">
                        <span>{{ __('Total') }}</span>
                        <span id="total-value" class="ledger-total ltr-content">0.00</span>
                    </div>

                    <button id="complete-sale" type="button" class="btn-primary mt-5 w-full justify-center">{{ __('Queue sale offline') }}</button>
                </div>

                <div class="mt-5 rounded-ui border border-line bg-panel p-4 text-sm text-muted">
                    <p class="font-medium text-ink">{{ __('Sync policy') }}</p>
                    <p class="mt-2">{{ __('Queued sales are retried by idempotency key. If the server rejects a sale because shop stock changed before sync, the cashier must resolve it manually.') }}</p>
                </div>
            </aside>
        </main>
    </body>
</html>
