<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex items-start gap-3">
            <x-icon-tile color="brass" size="lg">
                <x-lucide-tag class="h-7 w-7" />
            </x-icon-tile>
            <div>
                <p class="text-xs font-medium text-brass">{{ __('Back office module') }}</p>
                <h1 class="mt-1 text-3xl font-semibold text-ink">{{ __('Products') }}</h1>
                <p class="mt-2 max-w-2xl text-sm text-muted">{{ __('Manage the shared catalog, unit setup, and pricing defaults used across every shop.') }}</p>
            </div>
        </div>

        @can('create', \App\Models\Product::class)
            <x-button :href="route('products.create')">
                <x-lucide-plus class="h-4 w-4" />
                {{ __('Create New') }}
            </x-button>
        @endcan
    </div>

    <x-card surface="surface">
        @if (session('status'))
            <div class="mb-5 rounded-ui border border-line bg-panel px-4 py-3 text-sm text-ink">{{ session('status') }}</div>
        @endif

        <div class="grid gap-4 xl:grid-cols-[1fr_auto] xl:items-end">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-7">
                <div class="md:col-span-2 xl:col-span-2">
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-subtle">{{ __('Search') }}</label>
                    <div class="relative">
                        <x-lucide-search class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-subtle" />
                        <x-input wire:model.live.debounce.300ms="search" type="search" placeholder="{{ __('Name, SKU, or barcode') }}" class="ps-9" />
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-subtle">{{ __('Status') }}</label>
                    <select wire:model.live="statusFilter" class="w-full rounded-ui border border-line bg-panel px-3 py-2.5 text-sm text-ink outline-none">
                        <option value="all">{{ __('All') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-subtle">{{ __('Base unit') }}</label>
                    <select wire:model.live="unitFilter" class="w-full rounded-ui border border-line bg-panel px-3 py-2.5 text-sm text-ink outline-none">
                        <option value="">{{ __('All units') }}</option>
                        @foreach ($unitOptions as $unitOption)
                            <option value="{{ $unitOption->id }}">{{ $unitOption->code }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-subtle">{{ __('Category') }}</label>
                    <select wire:model.live="categoryFilter" class="w-full rounded-ui border border-line bg-panel px-3 py-2.5 text-sm text-ink outline-none">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categoryOptions as $categoryOption)
                            <option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-subtle">{{ __('Brand') }}</label>
                    <select wire:model.live="brandFilter" class="w-full rounded-ui border border-line bg-panel px-3 py-2.5 text-sm text-ink outline-none">
                        <option value="">{{ __('All brands') }}</option>
                        @foreach ($brandOptions as $brandOption)
                            <option value="{{ $brandOption->id }}">{{ $brandOption->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-subtle">{{ __('VAT') }}</label>
                    <select wire:model.live="vatRateFilter" class="w-full rounded-ui border border-line bg-panel px-3 py-2.5 text-sm text-ink outline-none">
                        <option value="">{{ __('All VAT rates') }}</option>
                        @foreach ($vatRateOptions as $vatRate)
                            <option value="{{ $vatRate }}">{{ number_format((float) $vatRate, 2) }}%</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-subtle">{{ __('Price category') }}</label>
                    <select wire:model.live="priceCategoryFilter" class="w-full rounded-ui border border-line bg-panel px-3 py-2.5 text-sm text-ink outline-none">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($priceCategoryOptions as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 xl:justify-end">
                <x-button variant="secondary" wire:click="clearFilters">
                    <x-lucide-filter-x class="h-4 w-4" />
                    {{ __('Clear') }}
                </x-button>
                <x-button variant="secondary" :href="route('products.export', ['format' => 'xlsx'])">
                    <x-lucide-download class="h-4 w-4" />
                    {{ __('Export') }}
                </x-button>
                <x-button variant="secondary" :href="route('products.import')">
                    <x-lucide-upload class="h-4 w-4" />
                    {{ __('Import') }}
                </x-button>
            </div>
        </div>

        @if (count($selectedProductIds) > 0)
            <div class="mt-5 flex flex-col gap-3 rounded-ui border border-line bg-panel px-4 py-3 text-sm text-ink md:flex-row md:items-center md:justify-between">
                <span class="font-semibold">{{ count($selectedProductIds) }} {{ __('selected') }}</span>
                <div class="flex flex-wrap gap-2">
                    <x-button variant="secondary" wire:click="bulkSetActive(true)">
                        <x-lucide-circle-check class="h-4 w-4" />
                        {{ __('Activate') }}
                    </x-button>
                    <x-button variant="danger" wire:click="bulkSetActive(false)" wire:confirm="{{ __('Deactivate selected products?') }}">
                        <x-lucide-circle-off class="h-4 w-4" />
                        {{ __('Deactivate') }}
                    </x-button>
                </div>
            </div>
        @endif

        @if ($products->isEmpty())
            <x-empty-state class="mt-6" icon="package-search" :title="__('No products found')" :message="__('Adjust the filters or create the first catalog item.')" :action-label="auth()->user()->can('create', \App\Models\Product::class) ? __('Create New') : null" :action-href="auth()->user()->can('create', \App\Models\Product::class) ? route('products.create') : null" />
        @else
            <x-table class="mt-6">
                <thead class="text-muted">
                        <tr>
                            <th class="w-12 px-4 py-3 font-medium">
                                <span class="sr-only">{{ __('Select') }}</span>
                            </th>
                            @foreach ([
                                'sku' => __('Code'),
                                'name' => __('Name'),
                                'price' => __('Sales price'),
                                'cost_price' => __('Purchase price'),
                                'base_unit_id' => __('Base unit'),
                            ] as $field => $label)
                                <th class="px-4 py-3 font-medium">
                                    <button type="button" wire:click="sortBy('{{ $field }}')" class="inline-flex items-center gap-1 text-start font-semibold text-muted hover:text-ink">
                                        {{ $label }}
                                        @if ($sortField === $field)
                                            @if ($sortDirection === 'asc')
                                                <x-lucide-arrow-up class="h-3.5 w-3.5" />
                                            @else
                                                <x-lucide-arrow-down class="h-3.5 w-3.5" />
                                            @endif
                                        @else
                                            <x-lucide-arrow-up-down class="h-3.5 w-3.5 text-subtle" />
                                        @endif
                                    </button>
                                </th>
                            @endforeach
                            <th class="px-4 py-3 font-medium">{{ __('Category') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Brand') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Reorder point') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('VAT') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('Status') }}</th>
                            <th class="px-4 py-3 font-medium text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-ink">
                        @foreach ($products as $product)
                            <tr class="group bg-surface transition hover:bg-panel">
                                <td class="px-4 py-4">
                                    <input wire:model.live="selectedProductIds" value="{{ $product->id }}" type="checkbox" class="h-4 w-4 rounded border-line bg-panel text-brass" />
                                </td>
                                <td class="px-4 py-4 figure-mono text-muted">{{ $product->sku }}</td>
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-ink">{{ $product->name }}</div>
                                    <div class="mt-1 text-xs text-subtle">{{ $product->barcode ?: __('No barcode') }}</div>
                                </td>
                                <td class="px-4 py-4 figure-mono">SAR {{ number_format((float) $product->price, 2) }}</td>
                                <td class="px-4 py-4 figure-mono">SAR {{ number_format((float) $product->cost_price, 2) }}</td>
                                <td class="px-4 py-4">{{ $product->baseUnit?->code ?? 'PCS' }}</td>
                                <td class="px-4 py-4">{{ $product->category?->name ?? '-' }}</td>
                                <td class="px-4 py-4">{{ $product->brand?->name ?? '-' }}</td>
                                <td class="px-4 py-4 figure-mono">{{ $product->stock_reorder_point !== null ? number_format((float) $product->stock_reorder_point, 3) : '-' }}</td>
                                <td class="px-4 py-4 figure-mono">{{ number_format((float) $product->vat_rate, 2) }}%</td>
                                <td class="px-4 py-4">
                                    <x-status-badge :tone="$product->is_active ? 'success' : 'danger'">{{ __($product->is_active ? 'Active' : 'Inactive') }}</x-status-badge>
                                </td>
                                <td class="px-4 py-4 text-end">
                                    <details class="relative inline-block text-start">
                                        <summary class="inline-flex cursor-pointer items-center rounded-ui border border-line p-2 text-muted transition hover:bg-panel hover:text-ink">
                                            <x-lucide-more-vertical class="h-4 w-4" />
                                            <span class="sr-only">{{ __('Open row actions') }}</span>
                                        </summary>
                                        <div class="absolute end-0 z-20 mt-2 w-56 rounded-ui border border-line bg-surface p-2 text-sm shadow-xl">
                                            @can('update', $product)
                                                <a href="{{ route('products.edit', $product) }}" class="flex items-center gap-2 rounded-ui px-3 py-2 text-ink hover:bg-panel">
                                                    <x-lucide-pencil class="h-4 w-4" />
                                                    {{ __('Edit') }}
                                                </a>
                                                <button type="button" wire:click="setActive({{ $product->id }}, {{ $product->is_active ? 'false' : 'true' }})" wire:confirm="{{ __($product->is_active ? 'Deactivate' : 'Activate') }} {{ __('this product?') }}" class="flex w-full items-center gap-2 rounded-ui px-3 py-2 text-start text-ink hover:bg-panel">
                                                    <x-lucide-power class="h-4 w-4" />
                                                    {{ __($product->is_active ? 'Deactivate' : 'Activate') }}
                                                </button>
                                            @endcan
                                            <a href="{{ route('products.edit', $product) }}#units" class="flex items-center gap-2 rounded-ui px-3 py-2 text-ink hover:bg-panel">
                                                <x-lucide-ruler class="h-4 w-4" />
                                                {{ __('View unit conversions') }}
                                            </a>
                                            <a href="{{ route('products.edit', $product) }}#pricing" class="flex items-center gap-2 rounded-ui px-3 py-2 text-ink hover:bg-panel">
                                                <x-lucide-tags class="h-4 w-4" />
                                                {{ __('View price overrides') }}
                                            </a>
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table>

            <div class="mt-5">
                {{ $products->links() }}
            </div>
        @endif
    </x-card>
</section>
