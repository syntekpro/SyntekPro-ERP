@php
    $canCreateShop = auth()->user()?->can('create', \App\Models\Shop::class) ?? false;
@endphp

<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex items-start gap-3">
            <x-icon-tile color="ledger" size="lg">
                <x-lucide-store class="h-7 w-7" />
            </x-icon-tile>
            <div>
                <p class="text-xs font-medium text-ledger">Back office module</p>
                <h1 class="mt-1 text-3xl font-semibold text-ink">Shops</h1>
                <p class="mt-2 max-w-2xl text-sm text-muted">Manage branch identities and path-based shop contexts for the POS side of the platform.</p>
            </div>
        </div>

        @can('create', \App\Models\Shop::class)
            <a href="{{ route('shops.create') }}" class="btn-primary">Create shop</a>
        @endcan
    </div>

    <x-card surface="surface">
        <x-slot:header>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-ink">Directory</h2>
                    <p class="mt-1 text-sm text-muted">Search and maintain the list of active shops.</p>
                </div>

                <div class="relative w-full lg:max-w-sm">
                    <x-lucide-search class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-subtle" />
                    <x-input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name or slug" class="ps-9" />
                </div>
            </div>
        </x-slot:header>

        @if ($shops->count())
            <x-table>
                <thead>
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line text-ink">
                    @foreach ($shops as $shop)
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
                    @endforeach
                </tbody>
            </x-table>

            <div class="mt-5">
                {{ $shops->links() }}
            </div>
        @else
            <x-empty-state
                icon="store"
                :title="$search !== '' ? 'No shops match this search' : 'No shops yet'"
                :message="$search !== '' ? 'Try a different name or slug.' : 'Create your first shop to start operating from it.'"
                :actionLabel="$search === '' && $canCreateShop ? 'Create shop' : null"
                :actionHref="$search === '' && $canCreateShop ? route('shops.create') : null"
            />
        @endif
    </x-card>
</section>