<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Back Office module</p>
        <h1 class="mt-3 text-4xl font-semibold text-ink">{{ $product ? 'Edit product' : 'Create product' }}</h1>
        <p class="mt-3 max-w-2xl text-sm text-muted">Manage the catalog record, pricing, stock thresholds, unit conversions, and category overrides in one flow.</p>
    </div>

    <form wire:submit="save" class="rounded-ui border border-line bg-surface shadow-sm">
        <div class="border-b border-line px-6 pt-6">
            <div class="flex flex-wrap gap-2">
                @foreach ([
                    'details' => ['Details', 'package'],
                    'pricing' => ['Pricing & Inventory', 'badge-dollar-sign'],
                    'units' => ['Units', 'ruler'],
                ] as $tabKey => [$label, $icon])
                    <button type="button" wire:click="$set('tab', '{{ $tabKey }}')" class="inline-flex items-center gap-2 rounded-t-ui border border-b-0 px-4 py-2 text-sm font-semibold {{ $tab === $tabKey ? 'border-line bg-panel text-ink' : 'border-transparent text-muted hover:text-ink' }}">
                        <x-dynamic-component :component="'lucide-'.$icon" class="h-4 w-4" />
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="space-y-8 p-6 pb-28">
            @if ($tab === 'details')
                <section class="space-y-5" id="details">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">Identity</h2>
                        <p class="mt-1 text-sm text-muted">Core catalog information used by POS, purchasing, and reporting.</p>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-[16rem_1fr]">
                        <label class="block cursor-pointer rounded-ui border border-dashed border-line bg-panel p-4 text-center">
                            <input wire:model="imageUpload" type="file" accept="image/*" class="sr-only" />
                            @if ($imageUpload)
                                <img src="{{ $imageUpload->temporaryUrl() }}" alt="Product preview" class="mx-auto h-40 w-40 rounded-ui object-cover" />
                            @elseif ($image_path)
                                <img src="{{ asset('storage/'.$image_path) }}" alt="Product image" class="mx-auto h-40 w-40 rounded-ui object-cover" />
                            @else
                                <div class="mx-auto flex h-40 w-40 items-center justify-center rounded-ui border border-line bg-surface text-subtle">
                                    <x-lucide-image-plus class="h-10 w-10" />
                                </div>
                            @endif
                            <span class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-ink"><x-lucide-upload class="h-4 w-4" /> Upload image</span>
                            @error('imageUpload') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                        </label>

                        <div class="grid gap-5 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-ink">Product name</label>
                                <input wire:model.live="name" type="text" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none" />
                                @error('name') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-ink">SKU</label>
                                <input wire:model.live="sku" type="text" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none" />
                                @error('sku') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-ink">Barcode</label>
                                <input wire:model.live="barcode" type="text" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none" />
                                @error('barcode') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-medium text-ink">Description</label>
                                <textarea wire:model="description" rows="4" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none"></textarea>
                                @error('description') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="space-y-4 border-t border-line pt-8">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">Availability</h2>
                        <p class="mt-1 text-sm text-muted">Activation controls catalog visibility; sale and purchase flags prepare the workflow for later channel-specific rules.</p>
                    </div>
                    <div class="grid gap-3 md:grid-cols-3">
                        <label class="flex items-center gap-3 rounded-ui border border-line bg-panel p-4 text-sm text-ink">
                            <input wire:model="is_active" type="checkbox" class="h-4 w-4 rounded border-line bg-surface text-brass" />
                            Active in catalog
                        </label>
                        <label class="flex items-center gap-3 rounded-ui border border-line bg-panel p-4 text-sm text-ink">
                            <input wire:model="is_sellable" type="checkbox" class="h-4 w-4 rounded border-line bg-surface text-brass" />
                            Available for sales
                        </label>
                        <label class="flex items-center gap-3 rounded-ui border border-line bg-panel p-4 text-sm text-ink">
                            <input wire:model="is_purchasable" type="checkbox" class="h-4 w-4 rounded border-line bg-surface text-brass" />
                            Available for purchasing
                        </label>
                    </div>
                </section>
            @endif

            @if ($tab === 'pricing')
                <section class="space-y-5" id="pricing">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">Pricing</h2>
                        <p class="mt-1 text-sm text-muted">Base prices are stored per base unit; category overrides fall back to sales price when blank.</p>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-ink">Sales price (SAR)</label>
                            <input wire:model.live="price" type="number" step="0.01" min="0" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none" />
                            @error('price') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-ink">Purchase price (SAR)</label>
                            <input wire:model.live="cost_price" type="number" step="0.01" min="0" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none" />
                            @error('cost_price') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-ink">VAT rate (%)</label>
                            <input wire:model.live="vat_rate" type="number" step="0.01" min="0" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none" />
                            @error('vat_rate') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-ink">Excise rate (%)</label>
                            <input wire:model.live="excise_rate" type="number" step="0.01" min="0" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none" />
                            @error('excise_rate') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <label class="flex items-center gap-3 text-sm text-ink">
                        <input wire:model="is_excise_applicable" type="checkbox" class="h-4 w-4 rounded border-line bg-panel text-brass" />
                        Excise tax applies to this product
                    </label>
                </section>

                <section class="space-y-5 border-t border-line pt-8">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">Stock Thresholds</h2>
                        <p class="mt-1 text-sm text-muted">Thresholds are stored in the product base unit.</p>
                    </div>
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-ink">Minimum stock</label>
                            <input wire:model.live="stock_min" type="number" step="0.001" min="0" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none" />
                            @error('stock_min') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-ink">Maximum stock</label>
                            <input wire:model.live="stock_max" type="number" step="0.001" min="0" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none" />
                            @error('stock_max') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section class="space-y-5 border-t border-line pt-8">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">Price Overrides</h2>
                        <p class="mt-1 text-sm text-muted">One row per active price category.</p>
                    </div>
                    <div class="overflow-hidden rounded-ui border border-line table-baseline">
                        <table class="min-w-full text-start text-sm">
                            <thead class="text-muted"><tr><th class="px-4 py-3">Category</th><th class="px-4 py-3">Price</th></tr></thead>
                            <tbody class="divide-y divide-line text-ink">
                                @forelse ($this->priceCategoryOptions as $category)
                                    <tr>
                                        <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                                        <td class="px-4 py-3">
                                            <input wire:model="category_prices.{{ $category->id }}" type="number" step="0.01" min="0" placeholder="Fallback {{ number_format((float) $price, 2) }}" class="w-full max-w-xs rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
                                            @error('category_prices.'.$category->id) <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="2" class="px-4 py-8 text-center text-muted">No active price categories.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @if ($tab === 'units')
                <section class="space-y-5" id="units">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">Base Unit</h2>
                        <p class="mt-1 text-sm text-muted">All stock at rest and category prices are stored against this unit.</p>
                    </div>
                    <div class="max-w-xl">
                        <label class="mb-2 block text-sm font-medium text-ink">Base unit</label>
                        <select wire:model="base_unit_id" class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none">
                            @foreach ($this->unitOptions as $unitOption)
                                <option value="{{ $unitOption->id }}">{{ $unitOption->code }} - {{ $unitOption->name }}</option>
                            @endforeach
                        </select>
                        @error('base_unit_id') <p class="mt-2 text-sm text-rust">{{ $message }}</p> @enderror
                    </div>
                </section>

                <section class="space-y-5 border-t border-line pt-8">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-ink">Alternate Unit Conversions</h2>
                            <p class="mt-1 text-sm text-muted">Each factor means one selected unit equals that many base units.</p>
                        </div>
                        <x-button type="button" variant="secondary" wire:click="addUnitConversion"><x-lucide-plus class="h-4 w-4" /> Add conversion</x-button>
                    </div>

                    <div class="space-y-3">
                        @forelse ($unit_conversions as $index => $conversion)
                            <div class="grid gap-3 rounded-ui border border-line bg-panel p-4 md:grid-cols-[1fr_1fr_auto]">
                                <select wire:model="unit_conversions.{{ $index }}.unit_id" class="rounded-ui border border-line bg-surface px-4 py-3 text-ink outline-none">
                                    <option value="">Select alternate unit</option>
                                    @foreach ($this->unitOptions as $unitOption)
                                        <option value="{{ $unitOption->id }}">{{ $unitOption->code }} - {{ $unitOption->name }}</option>
                                    @endforeach
                                </select>
                                <input wire:model="unit_conversions.{{ $index }}.conversion_factor" type="number" step="0.000001" min="0.000001" class="rounded-ui border border-line bg-surface px-4 py-3 text-ink outline-none" />
                                <x-button type="button" variant="danger" wire:click="removeUnitConversion({{ $index }})"><x-lucide-trash-2 class="h-4 w-4" /> Remove</x-button>
                            </div>
                            @error('unit_conversions.'.$index.'.unit_id') <p class="text-sm text-rust">{{ $message }}</p> @enderror
                            @error('unit_conversions.'.$index.'.conversion_factor') <p class="text-sm text-rust">{{ $message }}</p> @enderror
                        @empty
                            <x-empty-state icon="ruler" title="No alternate units" message="Add a conversion when this product is bought or sold in packs, boxes, cartons, or other units." />
                        @endforelse
                    </div>
                </section>
            @endif
        </div>

        <div class="sticky bottom-0 flex flex-col gap-3 border-t border-line bg-surface/95 p-4 backdrop-blur md:flex-row md:items-center md:justify-end">
            <x-button type="button" variant="secondary" wire:click="saveAndAddAnother"><x-lucide-save class="h-4 w-4" /> Save & Add Another</x-button>
            <x-button type="submit"><x-lucide-save class="h-4 w-4" /> Save product</x-button>
            <x-button variant="secondary" :href="route('products.index')">Cancel</x-button>
        </div>
    </form>
</section>
