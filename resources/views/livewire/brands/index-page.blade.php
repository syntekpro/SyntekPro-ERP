<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Catalog</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Brands</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Manage product brands for catalog organization and filtering.</p>
        </div>

        <a href="{{ route('brands.create') }}" class="btn-primary">Create brand</a>
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
                    @forelse ($brands as $brand)
                        <tr>
                            <td class="px-4 py-4 font-medium text-ink">{{ $brand->name }}</td>
                            <td class="px-4 py-4"><x-status-badge :tone="$brand->is_active ? 'success' : 'danger'">{{ $brand->is_active ? 'Active' : 'Inactive' }}</x-status-badge></td>
                            <td class="px-4 py-4 text-end">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('brands.edit', $brand) }}" class="btn-secondary btn-size-sm">Edit</a>
                                    <button wire:click="setActive({{ $brand->id }}, {{ $brand->is_active ? 'false' : 'true' }})" wire:confirm="{{ $brand->is_active ? 'Deactivate' : 'Activate' }} this brand?" class="btn-warning btn-size-sm">{{ $brand->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-10 text-center text-muted">No brands found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $brands->links() }}</div>
    </div>
</section>
