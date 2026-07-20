<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Supplier Management</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Suppliers</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Back Office supplier master shared across all warehouse purchasing flows.</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="btn-primary">Create supplier</a>
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by supplier name or code" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none placeholder:text-subtle lg:max-w-sm" />

        <div class="mt-5 overflow-hidden rounded-ui border border-line table-baseline">
            <table class="min-w-full text-start text-sm ui-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Terms</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="px-4 py-3 font-medium text-ink">{{ $supplier->name }}</td>
                            <td class="px-4 py-3 figure-mono">{{ $supplier->code }}</td>
                            <td class="px-4 py-3 figure-mono">{{ $supplier->payment_terms_days }} days</td>
                            <td class="px-4 py-3">
                                <x-status-badge :tone="$supplier->is_active ? 'success' : 'danger'">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</x-status-badge>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary btn-size-sm">Edit</a>
                                    <button wire:click="setActive({{ $supplier->id }}, {{ $supplier->is_active ? 'false' : 'true' }})" wire:confirm="{{ $supplier->is_active ? 'Deactivate' : 'Activate' }} this supplier?" class="btn-warning btn-size-sm">{{ $supplier->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-muted">No suppliers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $suppliers->links() }}</div>
    </div>
</section>
