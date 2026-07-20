<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Back Office module</p>
            <h1 class="mt-3 text-4xl font-semibold text-ink">Shops</h1>
            <p class="mt-3 max-w-2xl text-sm text-muted">Manage branch identities and path-based shop contexts for the POS side of the platform.</p>
        </div>

        @can('create', \App\Models\Shop::class)
            <a href="{{ route('shops.create') }}" class="btn-primary">Create shop</a>
        @endcan
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-ink">Directory</h2>
                <p class="mt-1 text-sm text-muted">Search and maintain the list of active shops.</p>
            </div>

            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name or slug" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none placeholder:text-subtle lg:max-w-sm" />
        </div>

        <div class="overflow-hidden rounded-ui border border-line table-baseline">
            <table class="min-w-full text-start text-sm ui-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @forelse ($shops as $shop)
                        <tr>
                            <td class="px-4 py-4 font-medium text-ink">{{ $shop->name }}</td>
                            <td class="px-4 py-4 figure-mono">/{{ $shop->slug }}</td>
                            <td class="px-4 py-4">
                                <x-status-badge :tone="$shop->is_active ? 'success' : 'danger'">{{ $shop->is_active ? 'Active' : 'Inactive' }}</x-status-badge>
                            </td>
                            <td class="px-4 py-4 text-end">
                                <div class="flex justify-end gap-2">
                                    @can('update', $shop)
                                        <a href="{{ route('shops.edit', $shop) }}" class="btn-secondary btn-size-sm">Edit</a>
                                    @endcan
                                    @can('update', $shop)
                                        <button wire:click="setActive({{ $shop->id }}, {{ $shop->is_active ? 'false' : 'true' }})" wire:confirm="{{ $shop->is_active ? 'Deactivate' : 'Activate' }} this shop?" class="btn-warning btn-size-sm">{{ $shop->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-muted">No shops match the current filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $shops->links() }}
        </div>
    </div>
</section>