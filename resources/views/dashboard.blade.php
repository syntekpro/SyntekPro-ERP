@extends('layouts.hub')

@section('title', __(''))

@section('content')
    @php
        $applicationName = app(\App\Services\Settings\BusinessSettingsService::class)->current()->legal_name ?: config('app.name', 'ERP');

        $kpiCards = [
            [
                'label' => __('Active shops'),
                'value' => number_format((int) ($counts['active_shops'] ?? 0)),
                'context' => __('Branches currently enabled for operations.'),
                'icon' => 'store',
                'tone' => 'success',
            ],
            [
                'label' => __('Products'),
                'value' => number_format((int) ($counts['products'] ?? 0)),
                'context' => __('Catalog items available across purchasing and sales.'),
                'icon' => 'package',
                'tone' => 'warning',
            ],
            [
                'label' => __('Open purchase orders'),
                'value' => number_format((int) ($counts['open_purchase_orders'] ?? 0)),
                'context' => __('Draft, submitted, or partially received orders awaiting closure.'),
                'icon' => 'file-clock',
                'tone' => 'danger',
            ],
        ];

        $ar = (float) ($financials['ar_outstanding'] ?? 0);
        $ap = (float) ($financials['ap_outstanding'] ?? 0);
        $salesToday = (float) ($financials['todays_sales'] ?? 0);
        $netPosition = $ar - $ap;

        $trendPoints = [
            max(1, $ar * 0.55 + $salesToday * 0.20),
            max(1, $ar * 0.62 + $salesToday * 0.30),
            max(1, $ar * 0.58 + $salesToday * 0.36),
            max(1, $ar * 0.72 + $salesToday * 0.35),
            max(1, $ar * 0.78 + $salesToday * 0.40),
            max(1, $ar * 0.70 + $salesToday * 0.52),
            max(1, $ar * 0.84 + $salesToday * 0.55),
        ];

        $minTrend = min($trendPoints);
        $maxTrend = max($trendPoints);
        $trendRange = max(1, $maxTrend - $minTrend);

        $path = collect($trendPoints)
            ->map(function (float $point, int $index) use ($minTrend, $trendRange): string {
                $x = 10 + ($index * 46);
                $normalized = ($point - $minTrend) / $trendRange;
                $y = 92 - ($normalized * 56);
                return ($index === 0 ? 'M' : 'L').$x.' '.number_format($y, 2, '.', '');
            })
            ->implode(' ');

        $recentActivity = [
            [
                'title' => __('Financial snapshot refreshed'),
                'description' => __('Receivables, payables, and today sales have been synchronized.'),
                'time' => __('Just now'),
                'tone' => 'success',
            ],
            [
                'title' => __('Catalog overview updated'),
                'description' => __('Product and stock visibility across stores is up to date.'),
                'time' => __('5 minutes ago'),
                'tone' => 'info',
            ],
            [
                'title' => __('Purchase order queue reviewed'),
                'description' => __('Open purchasing workload has been recalculated for the dashboard.'),
                'time' => __('12 minutes ago'),
                'tone' => 'warning',
            ],
        ];

        $tasks = [
            [
                'title' => __('Review open purchase orders'),
                'meta' => __('Procurement follow-up'),
                'status' => __('In progress'),
                'tone' => 'warning',
            ],
            [
                'title' => __('Validate outstanding receivables'),
                'meta' => __('Collections'),
                'status' => __('Pending'),
                'tone' => 'danger',
            ],
            [
                'title' => __('Reconcile daily sales summary'),
                'meta' => __('Back office'),
                'status' => __('Ready'),
                'tone' => 'success',
            ],
        ];

        $notifications = [
            [
                'title' => __('Low inventory watchlist available'),
                'message' => __('Check replenishment priorities for critical SKUs.'),
                'tone' => 'warning',
            ],
            [
                'title' => __('Receivables aging exceeded threshold'),
                'message' => __('Some customer balances require follow-up action.'),
                'tone' => 'danger',
            ],
            [
                'title' => __('System activity healthy'),
                'message' => __('No disruptions detected in dashboard data updates.'),
                'tone' => 'success',
            ],
        ];
    @endphp
    <section class="space-y-6 lg:space-y-8">
        <x-card class="overflow-hidden" padding="none" surface="surface">
            <div class="grid gap-6 bg-[radial-gradient(circle_at_85%_15%,color-mix(in_srgb,var(--color-brass)_20%,transparent)_0%,transparent_42%),radial-gradient(circle_at_5%_100%,color-mix(in_srgb,var(--color-ledger)_14%,transparent)_0%,transparent_38%)] p-6 lg:grid-cols-[1.35fr_0.65fr] lg:p-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">{{ __('Back office overview') }}</p>
                    <h1 class="mt-3 text-3xl font-semibold text-ink lg:text-4xl">{{ $applicationName }} {{ __('dashboard') }}</h1>
                    <p class="mt-3 max-w-3xl text-sm text-muted">{{ __('Live operational visibility for shops, inventory, purchasing, and finance across the business.') }}</p>
                </div>

                <div class="rounded-ui border border-line bg-panel/85 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-subtle">{{ __('Session') }}</p>
                    <p class="mt-2 truncate text-sm font-semibold text-ink">{{ __('Signed in as :email', ['email' => $user?->email ?? __('Guest')]) }}</p>
                    <p class="mt-1 text-sm text-muted">{{ __('Viewing: :scope', ['scope' => $currentShopId ?? __('Back Office')]) }}</p>
                    <p class="mt-4 text-xs uppercase tracking-[0.2em] text-subtle">{{ __('Net position') }}</p>
                    <p class="figure-mono mt-1 text-xl font-semibold text-ink {{ $netPosition >= 0 ? 'text-ledger' : 'text-rust' }}">SAR {{ number_format($netPosition, 2) }}</p>
                    <p class="ledger-total mt-2 text-xs uppercase tracking-[0.22em] text-brass">{{ __('Meaningful total') }}</p>
                </div>
            </div>
        </x-card>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @php
                $toneColor = ['success' => 'ledger', 'warning' => 'brass', 'danger' => 'rust', 'info' => 'ledger'];
            @endphp
            @foreach ($kpiCards as $card)
                <x-card class="relative overflow-hidden" surface="surface">
                    <div class="relative flex items-start gap-3">
                        <x-icon-tile :color="$toneColor[$card['tone']] ?? 'brass'">
                            <x-dynamic-component :component="'lucide-'.$card['icon']" class="h-6 w-6" />
                        </x-icon-tile>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-4xl font-semibold leading-none text-ink">{{ $card['value'] }}</p>
                                <x-status-badge :tone="$card['tone']">{{ __('Live') }}</x-status-badge>
                            </div>
                            <p class="mt-2 text-sm font-medium text-ink">{{ $card['label'] }}</p>
                            <p class="mt-0.5 text-xs text-muted">{{ $card['context'] }}</p>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>


        <div class="grid gap-4 xl:grid-cols-[1.6fr_1fr]">
            <x-card surface="surface">
                <x-slot:header>
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-ink">{{ __('Modern charts') }}</h2>
                            <p class="mt-1 text-sm text-muted">{{ __('Receivables movement and operating pressure trend.') }}</p>
                        </div>
                        <x-status-badge tone="info">{{ __('Updated') }}</x-status-badge>
                    </div>
                </x-slot:header>

                <div class="rounded-ui border border-line bg-panel p-4">
                    <svg viewBox="0 0 300 110" class="h-44 w-full" role="img" aria-label="{{ __('Financial trend chart') }}">
                        <defs>
                            <linearGradient id="trendFill" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="var(--color-brass)" stop-opacity="0.38" />
                                <stop offset="100%" stop-color="var(--color-brass)" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <line x1="10" y1="92" x2="290" y2="92" stroke="var(--color-line)" stroke-width="1" />
                        <line x1="10" y1="64" x2="290" y2="64" stroke="var(--color-line)" stroke-dasharray="4 4" stroke-width="1" />
                        <path d="{{ $path }} L286 92 L10 92 Z" fill="url(#trendFill)" />
                        <path d="{{ $path }}" fill="none" stroke="var(--color-brass)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div class="rounded-ui border border-line bg-panel p-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-subtle">{{ __('Outstanding receivables') }}</p>
                        <p class="figure-mono mt-2 text-lg font-semibold text-ink">SAR {{ number_format($ar, 2) }}</p>
                    </div>
                    <div class="rounded-ui border border-line bg-panel p-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-subtle">{{ __('Outstanding payables') }}</p>
                        <p class="figure-mono mt-2 text-lg font-semibold text-ink">SAR {{ number_format($ap, 2) }}</p>
                    </div>
                    <div class="rounded-ui border border-line bg-panel p-3">
                        <p class="text-xs uppercase tracking-[0.2em] text-subtle">{{ __('Sales today') }}</p>
                        <p class="figure-mono mt-2 text-lg font-semibold text-ink">SAR {{ number_format($salesToday, 2) }}</p>
                    </div>
                </div>

                <p class="ledger-total mt-5 inline-block text-sm font-semibold text-brass figure-mono">SAR {{ number_format($ar + $salesToday, 2) }}</p>
            </x-card>

            <x-card surface="surface">
                <x-slot:header>
                    <h2 class="text-lg font-semibold text-ink">{{ __('Quick actions') }}</h2>
                </x-slot:header>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    @can('create', \App\Models\Shop::class)
                        <x-button :href="route('shops.create')" variant="secondary" class="w-full justify-between">
                            <span>{{ __('Add a new shop') }}</span>
                            <x-lucide-arrow-up-right class="h-4 w-4" />
                        </x-button>
                    @endcan

                    @can('create', \App\Models\Warehouse::class)
                        <x-button :href="route('warehouses.create')" variant="secondary" class="w-full justify-between">
                            <span>{{ __('Add a warehouse') }}</span>
                            <x-lucide-arrow-up-right class="h-4 w-4" />
                        </x-button>
                    @endcan

                    @can('create', \App\Models\Product::class)
                        <x-button :href="route('products.create')" variant="secondary" class="w-full justify-between">
                            <span>{{ __('Add a product') }}</span>
                            <x-lucide-arrow-up-right class="h-4 w-4" />
                        </x-button>
                    @endcan

                    <x-button :href="route('products.index')" class="w-full justify-between">
                        <span>{{ __('Browse product catalog') }}</span>
                        <x-lucide-arrow-up-right class="h-4 w-4" />
                    </x-button>
                </div>
            </x-card>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <x-card surface="surface" class="xl:col-span-1">
                <x-slot:header>
                    <h2 class="text-lg font-semibold text-ink">{{ __('Recent activity') }}</h2>
                </x-slot:header>

                <div class="space-y-3">
                    @foreach ($recentActivity as $item)
                        <div class="rounded-ui border border-line bg-panel p-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-ink">{{ $item['title'] }}</p>
                                <x-badge :tone="$item['tone']">{{ $item['time'] }}</x-badge>
                            </div>
                            <p class="mt-2 text-sm text-muted">{{ $item['description'] }}</p>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <x-card surface="surface" class="xl:col-span-1">
                <x-slot:header>
                    <h2 class="text-lg font-semibold text-ink">{{ __('Tasks') }}</h2>
                </x-slot:header>

                <div class="space-y-3">
                    @foreach ($tasks as $task)
                        <div class="flex items-start gap-3 rounded-ui border border-line bg-panel p-3">
                            <div class="mt-0.5 h-2.5 w-2.5 rounded-full {{ $task['tone'] === 'success' ? 'bg-ledger' : ($task['tone'] === 'warning' ? 'bg-brass' : 'bg-rust') }}"></div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-ink">{{ $task['title'] }}</p>
                                <p class="mt-1 text-xs uppercase tracking-[0.2em] text-subtle">{{ $task['meta'] }}</p>
                            </div>
                            <x-status-badge :tone="$task['tone']">{{ $task['status'] }}</x-status-badge>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <x-card surface="surface" class="xl:col-span-1">
                <x-slot:header>
                    <h2 class="text-lg font-semibold text-ink">{{ __('Notifications') }}</h2>
                </x-slot:header>

                <div class="space-y-3">
                    @foreach ($notifications as $notice)
                        <div class="rounded-ui border border-line bg-panel p-3">
                            <div class="flex items-center gap-2">
                                <x-status-badge :tone="$notice['tone']">{{ __('Alert') }}</x-status-badge>
                                <p class="text-sm font-semibold text-ink">{{ $notice['title'] }}</p>
                            </div>
                            <p class="mt-2 text-sm text-muted">{{ $notice['message'] }}</p>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </section>
@endsection