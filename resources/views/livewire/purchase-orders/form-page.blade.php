<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Purchasing</p>
        <h1 class="mt-3 text-4xl font-semibold text-white">{{ $purchaseOrder ? 'Edit purchase order' : 'Create purchase order' }}</h1>
        <p class="mt-3 max-w-2xl text-sm text-stone-300">Purchase orders target central warehouse stock and do not write to shop stock directly.</p>
    </div>

    <form wire:submit="save" class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Supplier</label>
                <select wire:model.live="supplier_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none">
                    <option value="">Select supplier</option>
                    @foreach ($this->supplierOptions as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->code }})</option>
                    @endforeach
                </select>
                @error('supplier_id') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Warehouse</label>
                <select wire:model.live="warehouse_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none">
                    <option value="">Select warehouse</option>
                    @foreach ($this->warehouseOptions as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                @error('warehouse_id') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-medium text-stone-200">Notes</label>
            <textarea wire:model="notes" rows="3" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none"></textarea>
        </div>

        <div class="mt-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Items</h2>
                <button type="button" wire:click="addItem" class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Add item</button>
            </div>

            @foreach ($items as $index => $item)
                <div class="grid gap-4 rounded-2xl border border-white/10 bg-stone-950/50 p-4 md:grid-cols-[2fr_1fr_1fr_1fr_1fr_auto]">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-stone-200">Product</label>
                        <div x-data="{ open: false }" class="relative">
                            <input wire:model.live.debounce.300ms="productSearch.{{ $index }}" type="text" placeholder="Search by name, SKU, or barcode" x-on:focus="open = true" x-on:input="open = true" x-on:click.outside="open = false" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                            <input wire:model="items.{{ $index }}.product_id" type="hidden" />
                            @if (trim((string) ($productSearch[$index] ?? '')) !== '')
                                @php($results = $this->productResults($index))
                                <div x-show="open" x-transition class="absolute z-20 mt-2 max-h-64 w-full overflow-y-auto rounded-2xl border border-white/10 bg-stone-900 shadow-xl">
                                    @if ($results->isEmpty())
                                        <p class="px-4 py-3 text-sm text-stone-300">No products found.</p>
                                    @else
                                        @foreach ($results as $product)
                                            <button type="button" wire:click="selectProduct({{ $index }}, {{ $product->id }})" x-on:click="open = false" class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm text-stone-100 transition hover:bg-white/10">
                                                <span>{{ $product->name }}</span>
                                                <span class="text-xs text-stone-400">{{ $product->sku ?: ($product->barcode ?: 'No code') }}</span>
                                            </button>
                                        @endforeach
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-stone-200">Qty</label>
                        <input wire:model="items.{{ $index }}.quantity_ordered" type="number" step="0.001" min="0.001" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-stone-200">Unit</label>
                        @php($selectedProduct = $this->selectedProduct($index))
                        <select wire:model="items.{{ $index }}.unit_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none">
                            <option value="">Base unit</option>
                            @foreach ($selectedProduct?->unitConversions ?? [] as $conversion)
                                <option value="{{ $conversion->unit_id }}">{{ $conversion->unit?->code }} - {{ $conversion->unit?->name }}</option>
                            @endforeach
                            @if ($selectedProduct)
                                <option value="{{ $selectedProduct->base_unit_id }}">{{ $selectedProduct->baseUnit?->code ?? 'PCS' }} - {{ $selectedProduct->baseUnit?->name ?? 'Piece' }}</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-stone-200">Unit cost</label>
                        <input wire:model="items.{{ $index }}.unit_cost" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-stone-200">VAT %</label>
                        <input wire:model="items.{{ $index }}.vat_rate" type="number" step="0.01" min="0" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                    </div>
                    <div class="flex items-end">
                        <button type="button" wire:click="removeItem({{ $index }})" class="w-full rounded-2xl border border-rose-400/20 px-4 py-3 text-sm font-semibold text-rose-200 transition hover:bg-rose-500/10">Remove</button>
                    </div>
                </div>
                @error('items.'.$index.'.product_id') <p class="text-sm text-rose-300">{{ $message }}</p> @enderror
                @error('items.'.$index.'.unit_id') <p class="text-sm text-rose-300">{{ $message }}</p> @enderror
                @error('items.'.$index.'.quantity_ordered') <p class="text-sm text-rose-300">{{ $message }}</p> @enderror
                @error('items.'.$index.'.unit_cost') <p class="text-sm text-rose-300">{{ $message }}</p> @enderror
                @error('items.'.$index.'.vat_rate') <p class="text-sm text-rose-300">{{ $message }}</p> @enderror
            @endforeach
        </div>

        <div class="mt-8 flex gap-3">
            <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Save purchase order</button>
            <a href="{{ route('purchase-orders.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Cancel</a>
        </div>
    </form>
</section>
