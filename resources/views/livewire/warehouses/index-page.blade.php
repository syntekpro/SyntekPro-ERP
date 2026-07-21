@php
    $canCreateWarehouse = auth()->user()?->can('create', \App\Models\Warehouse::class) ?? false;
@endphp

<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex items-start gap-3">
            <x-icon-tile color="ledger" size="lg">
                <x-lucide-warehouse class="h-7 w-7" />
            </x-icon-tile>
            <div>
                <p class="text-xs font-medium text-ledger">Back office module</p>
                <h1 class="mt-1 text-3xl font-semibold text-ink">Warehouses</h1>
                <p class="mt-2 max-w-2xl text-sm text-muted">Maintain central stock locations that will dispatch inventory into shop-owned stock.</p>
            </div>
        </div>

        @can('create', \App\Models\Warehouse::class)
            <a href="{{ route('warehouses.create') }}" class="btn-primary">Create warehouse</a>
        @endcan
    </div>

    <x-card surface="surface">
        <x-slot:header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-ink">Central locations</h2>
                    <p class="mt-1 text-sm text-muted">Search warehouse codes and active fulfillment points.</p>
                </div>

                <div class="relative w-full lg:max-w-sm">
                    <x-lucide-search class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-subtle" />
                    <x-input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by name or code" class="ps-9" />
                </div>
            </div>
        </x-slot:header>

        @if ($warehouses->count())
            <x-table>
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Code</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @foreach ($warehouses as $warehouse)
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
                    @endforeach
                </tbody>
            </x-table>

            <div class="mt-5">
                {{ $warehouses->links() }}
            </div>
        @else
            <x-empty-state
                icon="warehouse"
                :title="$search !== '' ? 'No warehouses match this search' : 'No warehouses yet'"
                :message="$search !== '' ? 'Try a different name or code.' : 'Create your first warehouse to start dispatching stock.'"
                :actionLabel="$search === '' && $canCreateWarehouse ? 'Create warehouse' : null"
                :actionHref="$search === '' && $canCreateWarehouse ? route('warehouses.create') : null"
            />
        @endif
    </x-card>
</section>