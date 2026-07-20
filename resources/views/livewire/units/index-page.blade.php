<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Inventory Units</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Units</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Maintain units used for purchasing, transfers, POS sales, and returns.</p>
        </div>

        <a href="{{ route('units.create') }}" class="btn-primary">Create unit</a>
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by code or name" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none placeholder:text-subtle lg:max-w-sm" />

        <div class="mt-5 overflow-hidden rounded-ui border border-line table-baseline">
            <table class="min-w-full text-start text-sm ui-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">Code</th>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @forelse ($units as $unit)
                        <tr>
                            <td class="px-4 py-4 figure-mono font-medium text-ink">{{ $unit->code }}</td>
                            <td class="px-4 py-4">{{ $unit->name }}</td>
                            <td class="px-4 py-4"><x-status-badge :tone="$unit->is_active ? 'success' : 'danger'">{{ $unit->is_active ? 'Active' : 'Inactive' }}</x-status-badge></td>
                            <td class="px-4 py-4 text-end">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('units.edit', $unit) }}" class="btn-secondary btn-size-sm">Edit</a>
                                    <button wire:click="setActive({{ $unit->id }}, {{ $unit->is_active ? 'false' : 'true' }})" wire:confirm="{{ $unit->is_active ? 'Deactivate' : 'Activate' }} this unit?" class="btn-warning btn-size-sm">{{ $unit->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-muted">No units found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $units->links() }}</div>
    </div>
</section>