<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Inventory Transfers</p>
        <h1 class="mt-3 text-4xl font-semibold text-white">Create stock transfer</h1>
        <p class="mt-3 max-w-2xl text-sm text-stone-300">Draft a transfer from central warehouse stock to a destination shop. Stock is decremented only when the transfer is received.</p>
    </div>

    <form wire:submit="save" class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Source warehouse</label>
                <select wire:model.live="source_warehouse_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none">
                    <option value="">Select a warehouse</option>
                    @foreach ($this->warehouseOptions as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                @error('source_warehouse_id') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Destination shop</label>
                <select wire:model.live="destination_shop_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none">
                    <option value="">Select a shop</option>
                    @foreach ($this->shopOptions as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
                @error('destination_shop_id') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-5">
            <label class="mb-2 block text-sm font-medium text-stone-200">Notes</label>
            <textarea wire:model="notes" rows="3" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none"></textarea>
            @error('notes') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="mt-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Items</h2>
                <button type="button" wire:click="addItem" class="rounded-2xl border border-white/10 px-4 py-2 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Add item</button>
            </div>

            @foreach ($items as $index => $item)
                <div class="grid gap-4 rounded-2xl border border-white/10 bg-stone-950/50 p-4 md:grid-cols-[1fr_12rem_12rem_auto]">
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
                        @error('items.'.$index.'.product_id') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-stone-200">Quantity</label>
                        <input wire:model="items.{{ $index }}.quantity" type="number" step="0.001" min="0.001" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                        @error('items.'.$index.'.quantity') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
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
                        @error('items.'.$index.'.unit_id') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <button type="button" wire:click="removeItem({{ $index }})" class="w-full rounded-2xl border border-rose-400/20 px-4 py-3 text-sm font-semibold text-rose-200 transition hover:bg-rose-500/10">Remove</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex gap-3">
            <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Create transfer</button>
            <a href="{{ route('stock-transfers.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Cancel</a>
        </div>
    </form>
</section>