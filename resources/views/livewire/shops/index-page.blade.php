<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Back Office module</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Shops</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Manage branch identities and path-based shop contexts for the POS side of the platform.</p>
        </div>

        @can('create', \App\Models\Shop::class)
            <a href="{{ route('shops.create') }}" class="inline-flex rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Create shop</a>
        @endcan
    </div>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white">Directory</h2>
                <p class="mt-1 text-sm text-stone-400">Search and maintain the list of active shops.</p>
            </div>

            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name or slug" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none placeholder:text-stone-500 lg:max-w-sm" />
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-start text-sm">
                <thead class="bg-stone-900/80 text-stone-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Slug</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-stone-950/60 text-stone-200">
                    @forelse ($shops as $shop)
                        <tr>
                            <td class="px-4 py-4 font-medium text-white">{{ $shop->name }}</td>
                            <td class="px-4 py-4">/{{ $shop->slug }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $shop->is_active ? 'bg-emerald-500/15 text-emerald-200' : 'bg-rose-500/15 text-rose-200' }}">{{ $shop->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-4 text-end">
                                <div class="flex justify-end gap-2">
                                    @can('update', $shop)
                                        <a href="{{ route('shops.edit', $shop) }}" class="rounded-xl border border-white/10 px-3 py-2 text-xs font-semibold text-stone-100 transition hover:bg-white/10">Edit</a>
                                    @endcan
                                    @can('update', $shop)
                                        <button wire:click="setActive({{ $shop->id }}, {{ $shop->is_active ? 'false' : 'true' }})" wire:confirm="{{ $shop->is_active ? 'Deactivate' : 'Activate' }} this shop?" class="rounded-xl border border-rose-400/20 px-3 py-2 text-xs font-semibold text-rose-200 transition hover:bg-rose-500/10">{{ $shop->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-stone-400">No shops match the current filter.</td>
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