<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#1daeff">
        <title>POS | {{ config('app.name', 'SyntekPro ERP') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/icon-main.png') }}">
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/icon-main-192.png') }}">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100" data-pos-shell="true">
        <script id="pos-bootstrap" type="application/json">@json($bootstrap)</script>

        <main class="mx-auto grid min-h-screen max-w-7xl gap-6 px-4 py-4 lg:grid-cols-[1.5fr_1fr] lg:px-6 lg:py-6">
            <section class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/30 backdrop-blur">
                <div class="flex flex-col gap-4 border-b border-white/10 pb-5 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300">Offline cashier</p>
                        <h1 class="mt-3 text-4xl font-semibold text-white">{{ $shop?->name }}</h1>
                        <p class="mt-2 text-sm text-slate-300">{{ $cashier->name }} · {{ $cashier->email }}</p>
                    </div>

                    <div class="rounded-2xl border border-cyan-400/20 bg-cyan-500/10 px-4 py-3 text-sm text-cyan-100">
                        <p class="font-medium">Shop stock snapshot</p>
                        <p class="mt-1 text-cyan-200/80">This screen stays usable offline after the first online load.</p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-[1fr_auto]">
                    <input id="product-search" type="search" placeholder="Search by name, SKU, or barcode" class="w-full rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-slate-100 outline-none placeholder:text-slate-500" />
                    <button id="sync-sales" type="button" class="rounded-2xl bg-emerald-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-300">Sync queued sales</button>
                </div>

                <div class="mt-6 overflow-hidden rounded-3xl border border-white/10">
                    <div id="product-list" class="grid max-h-[34rem] gap-px overflow-auto bg-white/5"></div>
                </div>
            </section>

            <aside class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 shadow-2xl shadow-black/30 backdrop-blur">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-amber-300">Cart</p>
                        <h2 class="mt-2 text-2xl font-semibold text-white">Current sale</h2>
                    </div>
                    <span id="queue-status" class="rounded-full bg-white/5 px-3 py-1 text-xs font-semibold text-slate-300">Offline ready</span>
                </div>

                <div class="mt-6 space-y-3" id="cart-list"></div>

                <div class="mt-6 rounded-3xl border border-white/10 bg-white/5 p-5">
                    <div class="space-y-3">
                        <div>
                            <label for="payment-method" class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Payment method</label>
                            <select id="payment-method" class="w-full rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-slate-100 outline-none">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="credit_account">Credit account</option>
                            </select>
                        </div>
                        <div>
                            <label for="customer-select" class="mb-2 block text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Customer (credit only)</label>
                            <select id="customer-select" class="w-full rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 text-sm text-slate-100 outline-none">
                                <option value="">Select customer</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm text-slate-300">
                        <span>Subtotal</span>
                        <span id="subtotal-value">0.00</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between text-sm text-slate-300">
                        <span>VAT</span>
                        <span id="vat-value">0.00</span>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-4 text-lg font-semibold text-white">
                        <span>Total</span>
                        <span id="total-value">0.00</span>
                    </div>

                    <button id="complete-sale" type="button" class="mt-5 w-full rounded-2xl bg-amber-400 px-4 py-3 font-semibold text-slate-950 transition hover:bg-amber-300">Queue sale offline</button>
                </div>

                <div class="mt-5 rounded-3xl border border-white/10 bg-black/20 p-4 text-sm text-slate-300">
                    <p class="font-medium text-white">Sync policy</p>
                    <p class="mt-2">Queued sales are retried by idempotency key. If the server rejects a sale because shop stock changed before sync, the cashier must resolve it manually.</p>
                </div>
            </aside>
        </main>
    </body>
</html>
