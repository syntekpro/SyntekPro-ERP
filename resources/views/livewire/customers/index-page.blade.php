<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Customer Management</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Customers</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Back Office customer master shared across all shops and AR workflows.</p>
        </div>
        <a href="{{ route('customers.create') }}" class="btn-primary">Create customer</a>
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by customer name or code" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none placeholder:text-subtle lg:max-w-sm" />

        <div class="mt-5 overflow-hidden rounded-ui border border-line table-baseline">
            <table class="min-w-full text-start text-sm ui-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Terms</th>
                        <th class="px-4 py-3">Credit Limit</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="px-4 py-3 font-medium text-ink">{{ $customer->name }}</td>
                            <td class="px-4 py-3 figure-mono">{{ $customer->code }}</td>
                            <td class="px-4 py-3 figure-mono">{{ $customer->payment_terms_days }} days</td>
                            <td class="px-4 py-3 figure-mono">{{ $customer->credit_limit !== null ? 'SAR '.number_format((float) $customer->credit_limit, 2) : 'No limit' }}</td>
                            <td class="px-4 py-3">
                                <x-status-badge :tone="$customer->is_active ? 'success' : 'danger'">{{ $customer->is_active ? 'Active' : 'Inactive' }}</x-status-badge>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn-secondary btn-size-sm">Edit</a>
                                    <button wire:click="setActive({{ $customer->id }}, {{ $customer->is_active ? 'false' : 'true' }})" wire:confirm="{{ $customer->is_active ? 'Deactivate' : 'Activate' }} this customer?" class="btn-warning btn-size-sm">{{ $customer->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-muted">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $customers->links() }}</div>
    </div>
</section>
