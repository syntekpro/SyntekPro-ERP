<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Back Office module</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Users</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Manage Back Office administrators, shop managers, and cashiers with explicit role and shop assignment.</p>
        </div>

        <a href="{{ route('users.create') }}" class="btn-primary">Create user</a>
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-ink">Team directory</h2>
                <p class="mt-1 text-sm text-muted">Search staff by name or email.</p>
            </div>

            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name or email" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none placeholder:text-subtle lg:max-w-sm" />
        </div>

        <div class="overflow-hidden rounded-ui border border-line table-baseline">
            <table class="min-w-full text-start text-sm ui-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Shop</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @forelse ($users as $managedUser)
                        <tr>
                            <td class="px-4 py-4 font-medium text-ink">{{ $managedUser->name }}</td>
                            <td class="px-4 py-4">{{ $managedUser->email }}</td>
                            <td class="px-4 py-4">{{ str($managedUser->role->value)->replace('_', ' ')->title() }}</td>
                            <td class="px-4 py-4">{{ $managedUser->shop?->name ?? 'Back Office' }}</td>
                            <td class="px-4 py-4">
                                <x-status-badge :tone="$managedUser->is_active ? 'success' : 'danger'">{{ $managedUser->is_active ? 'Active' : 'Inactive' }}</x-status-badge>
                            </td>
                            <td class="px-4 py-4 text-end">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('users.edit', $managedUser) }}" class="btn-secondary btn-size-sm">Edit</a>
                                    @if (auth()->id() !== $managedUser->id)
                                        <button wire:click="setActive({{ $managedUser->id }}, {{ $managedUser->is_active ? 'false' : 'true' }})" wire:confirm="{{ $managedUser->is_active ? 'Deactivate' : 'Activate' }} this user?" class="btn-warning btn-size-sm">{{ $managedUser->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-muted">No users match the current filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $users->links() }}</div>
    </div>
</section>