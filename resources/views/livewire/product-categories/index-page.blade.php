<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Catalog</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Product Categories</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Manage category assignments used to segment products in the shared catalog.</p>
        </div>

        <a href="{{ route('product-categories.create') }}" class="btn-primary">Create category</a>
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by name" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none placeholder:text-subtle lg:max-w-sm" />

        <div class="mt-5 overflow-hidden rounded-ui border border-line table-baseline">
            <table class="min-w-full text-start text-sm ui-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @forelse ($productCategories as $productCategory)
                        <tr>
                            <td class="px-4 py-4 font-medium text-ink">{{ $productCategory->name }}</td>
                            <td class="px-4 py-4"><x-status-badge :tone="$productCategory->is_active ? 'success' : 'danger'">{{ $productCategory->is_active ? 'Active' : 'Inactive' }}</x-status-badge></td>
                            <td class="px-4 py-4 text-end">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('product-categories.edit', $productCategory) }}" class="btn-secondary btn-size-sm">Edit</a>
                                    <button wire:click="setActive({{ $productCategory->id }}, {{ $productCategory->is_active ? 'false' : 'true' }})" wire:confirm="{{ $productCategory->is_active ? 'Deactivate' : 'Activate' }} this category?" class="btn-warning btn-size-sm">{{ $productCategory->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-10 text-center text-muted">No product categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $productCategories->links() }}</div>
    </div>
</section>
