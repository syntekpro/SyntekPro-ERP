<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Hub module</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Products</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Manage the shared product catalog used across every shop in the chain.</p>
        </div>

        @can('create', \App\Models\Product::class)
            <a href="{{ route('products.create') }}" class="inline-flex rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Create product</a>
        @endcan
    </div>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white">Catalog</h2>
                <p class="mt-1 text-sm text-stone-400">Search by product name, SKU, or barcode.</p>
            </div>

            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by name, SKU, or barcode" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none placeholder:text-stone-500 lg:max-w-sm" />
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                <thead class="bg-stone-900/80 text-stone-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">SKU</th>
                        <th class="px-4 py-3 font-medium">Base unit</th>
                        <th class="px-4 py-3 font-medium">Price</th>
                        <th class="px-4 py-3 font-medium">Cost</th>
                        <th class="px-4 py-3 font-medium">VAT</th>
                        <th class="px-4 py-3 font-medium">Excise</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-stone-950/60 text-stone-200">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-4 py-4 font-medium text-white">{{ $product->name }}</td>
                            <td class="px-4 py-4">{{ $product->sku }}</td>
                            <td class="px-4 py-4">{{ $product->baseUnit?->code ?? 'PCS' }}</td>
                            <td class="px-4 py-4">SAR {{ number_format((float) $product->price, 2) }}</td>
                            <td class="px-4 py-4">SAR {{ number_format((float) $product->cost_price, 2) }}</td>
                            <td class="px-4 py-4">{{ number_format((float) $product->vat_rate, 2) }}%</td>
                            <td class="px-4 py-4">{{ $product->is_excise_applicable ? number_format((float) $product->excise_rate, 2).'%' : 'No' }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-emerald-500/15 text-emerald-200' : 'bg-rose-500/15 text-rose-200' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @can('update', $product)
                                        <a href="{{ route('products.edit', $product) }}" class="rounded-xl border border-white/10 px-3 py-2 text-xs font-semibold text-stone-100 transition hover:bg-white/10">Edit</a>
                                    @endcan
                                    @can('update', $product)
                                        <button wire:click="setActive({{ $product->id }}, {{ $product->is_active ? 'false' : 'true' }})" wire:confirm="{{ $product->is_active ? 'Deactivate' : 'Activate' }} this product?" class="rounded-xl border border-rose-400/20 px-3 py-2 text-xs font-semibold text-rose-200 transition hover:bg-rose-500/10">{{ $product->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-stone-400">No products match the current filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $products->links() }}
        </div>
    </div>
</section>