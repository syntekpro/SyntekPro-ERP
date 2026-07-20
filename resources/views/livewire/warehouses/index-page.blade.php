<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Back Office module</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Warehouses</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Maintain central stock locations that will dispatch inventory into shop-owned stock.</p>
        </div>

        @can('create', \App\Models\Warehouse::class)
            <a href="{{ route('warehouses.create') }}" class="btn-primary">Create warehouse</a>
        @endcan
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-ink">Central locations</h2>
                <p class="mt-1 text-sm text-muted">Search warehouse codes and active fulfillment points.</p>
            </div>

            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by name or code" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none placeholder:text-subtle lg:max-w-sm" />
        </div>

        <div class="overflow-hidden rounded-ui border border-line table-baseline">
            <table class="min-w-full text-start text-sm ui-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Code</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @forelse ($warehouses as $warehouse)
                        <tr>
                            <td class="px-4 py-4 font-medium text-ink">{{ $warehouse->name }}</td>
                            <td class="px-4 py-4 figure-mono">{{ $warehouse->code }}</td>
                            <td class="px-4 py-4">
                                <x-status-badge :tone="$warehouse->is_active ? 'success' : 'danger'">{{ $warehouse->is_active ? 'Active' : 'Inactive' }}</x-status-badge>
                            </td>
                            <td class="px-4 py-4 text-end">
                                <div class="flex justify-end gap-2">
                                    @can('update', $warehouse)
                                        <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn-secondary btn-size-sm">Edit</a>
                                    @endcan
                                    @can('update', $warehouse)
                                        <button wire:click="setActive({{ $warehouse->id }}, {{ $warehouse->is_active ? 'false' : 'true' }})" wire:confirm="{{ $warehouse->is_active ? 'Deactivate' : 'Activate' }} this warehouse?" class="btn-warning btn-size-sm">{{ $warehouse->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-muted">No warehouses match the current filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $warehouses->links() }}
        </div>
    </div>
</section>