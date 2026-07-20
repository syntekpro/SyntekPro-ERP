<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Back Office module</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Warehouses</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Maintain central stock locations that will dispatch inventory into shop-owned stock.</p>
        </div>

        @can('create', \App\Models\Warehouse::class)
            <a href="{{ route('warehouses.create') }}" class="inline-flex rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Create warehouse</a>
        @endcan
    </div>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white">Central locations</h2>
                <p class="mt-1 text-sm text-stone-400">Search warehouse codes and active fulfillment points.</p>
            </div>

            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Search by name or code" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none placeholder:text-stone-500 lg:max-w-sm" />
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-left text-sm">
                <thead class="bg-stone-900/80 text-stone-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Code</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-stone-950/60 text-stone-200">
                    @forelse ($warehouses as $warehouse)
                        <tr>
                            <td class="px-4 py-4 font-medium text-white">{{ $warehouse->name }}</td>
                            <td class="px-4 py-4">{{ $warehouse->code }}</td>
                            <td class="px-4 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $warehouse->is_active ? 'bg-emerald-500/15 text-emerald-200' : 'bg-rose-500/15 text-rose-200' }}">{{ $warehouse->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @can('update', $warehouse)
                                        <a href="{{ route('warehouses.edit', $warehouse) }}" class="rounded-xl border border-white/10 px-3 py-2 text-xs font-semibold text-stone-100 transition hover:bg-white/10">Edit</a>
                                    @endcan
                                    @can('update', $warehouse)
                                        <button wire:click="setActive({{ $warehouse->id }}, {{ $warehouse->is_active ? 'false' : 'true' }})" wire:confirm="{{ $warehouse->is_active ? 'Deactivate' : 'Activate' }} this warehouse?" class="rounded-xl border border-rose-400/20 px-3 py-2 text-xs font-semibold text-rose-200 transition hover:bg-rose-500/10">{{ $warehouse->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-stone-400">No warehouses match the current filter.</td>
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