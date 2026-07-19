<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Hub module</p>
        <h1 class="mt-3 text-4xl font-semibold text-white">{{ $product ? 'Edit product' : 'Create product' }}</h1>
        <p class="mt-3 max-w-2xl text-sm text-stone-300">Manage hub-owned catalog entries with pricing and VAT defaults for the Saudi rollout.</p>
    </div>

    <form wire:submit="save" class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-medium text-stone-200">Product name</label>
                <input wire:model.live="name" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('name') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">SKU</label>
                <input wire:model.live="sku" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('sku') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Barcode</label>
                <input wire:model.live="barcode" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('barcode') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Base unit</label>
                <select wire:model="base_unit_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none">
                    @foreach ($this->unitOptions as $unitOption)
                        <option value="{{ $unitOption->id }}">{{ $unitOption->code }} - {{ $unitOption->name }}</option>
                    @endforeach
                </select>
                @error('base_unit_id') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Price (SAR)</label>
                <input wire:model.live="price" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('price') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Cost price (SAR)</label>
                <input wire:model.live="cost_price" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('cost_price') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">VAT rate (%)</label>
                <input wire:model.live="vat_rate" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('vat_rate') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Excise rate (%)</label>
                <input wire:model.live="excise_rate" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('excise_rate') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
        </div>

        <label class="mt-5 flex items-center gap-3 text-sm text-stone-300">
            <input wire:model="is_excise_applicable" type="checkbox" class="h-4 w-4 rounded border-white/10 bg-stone-900 text-amber-400" />
            <span>Excise tax applies to this product</span>
        </label>

        <label class="mt-5 flex items-center gap-3 text-sm text-stone-300">
            <input wire:model="is_active" type="checkbox" class="h-4 w-4 rounded border-white/10 bg-stone-900 text-amber-400" />
            <span>Product is active in the shared catalog</span>
        </label>

        <div class="mt-8 rounded-2xl border border-white/10 bg-stone-950/50 p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-white">Unit conversions</h2>
                    <p class="mt-1 text-sm text-stone-400">Each factor means one selected unit equals that many base units.</p>
                </div>
                <button type="button" wire:click="addUnitConversion" class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Add conversion</button>
            </div>

            <div class="mt-4 space-y-3">
                @foreach ($unit_conversions as $index => $conversion)
                    <div class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
                        <select wire:model="unit_conversions.{{ $index }}.unit_id" class="rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none">
                            <option value="">Select alternate unit</option>
                            @foreach ($this->unitOptions as $unitOption)
                                <option value="{{ $unitOption->id }}">{{ $unitOption->code }} - {{ $unitOption->name }}</option>
                            @endforeach
                        </select>
                        <input wire:model="unit_conversions.{{ $index }}.conversion_factor" type="number" step="0.000001" min="0.000001" class="rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                        <button type="button" wire:click="removeUnitConversion({{ $index }})" class="rounded-2xl border border-rose-400/20 px-4 py-3 text-sm font-semibold text-rose-200 transition hover:bg-rose-500/10">Remove</button>
                    </div>
                    @error('unit_conversions.'.$index.'.unit_id') <p class="text-sm text-rose-300">{{ $message }}</p> @enderror
                    @error('unit_conversions.'.$index.'.conversion_factor') <p class="text-sm text-rose-300">{{ $message }}</p> @enderror
                @endforeach
            </div>
        </div>

        <div class="mt-5 rounded-2xl border border-white/10 bg-stone-950/50 p-4">
            <h2 class="text-lg font-semibold text-white">Price overrides</h2>
            <p class="mt-1 text-sm text-stone-400">Leave a category blank to use the product base price fallback.</p>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach ($this->priceCategoryOptions as $category)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-stone-200">{{ $category->name }}</label>
                        <input wire:model="category_prices.{{ $category->id }}" type="number" step="0.01" min="0" placeholder="Fallback {{ number_format((float) $price, 2) }}" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                        @error('category_prices.'.$category->id) <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-8 flex gap-3">
            <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Save product</button>
            <a href="{{ route('products.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Cancel</a>
        </div>
    </form>
</section>