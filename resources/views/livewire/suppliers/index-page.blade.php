<section class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Supplier Management</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">Suppliers</h1>
            <p class="mt-3 max-w-2xl text-sm text-stone-300">Back Office supplier master shared across all warehouse purchasing flows.</p>
        </div>
        <a href="{{ route('suppliers.create') }}" class="inline-flex rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Create supplier</a>
    </div>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by supplier name or code" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none placeholder:text-stone-500 lg:max-w-sm" />

        <div class="mt-5 overflow-hidden rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-start text-sm">
                <thead class="bg-stone-900/80 text-stone-400">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Terms</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-stone-950/40 text-stone-200">
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="px-4 py-3 text-white">{{ $supplier->name }}</td>
                            <td class="px-4 py-3">{{ $supplier->code }}</td>
                            <td class="px-4 py-3">{{ $supplier->payment_terms_days }} days</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $supplier->is_active ? 'bg-emerald-500/15 text-emerald-200' : 'bg-rose-500/15 text-rose-200' }}">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="rounded-xl border border-white/10 px-3 py-2 text-xs font-semibold text-stone-100 transition hover:bg-white/10">Edit</a>
                                    <button wire:click="setActive({{ $supplier->id }}, {{ $supplier->is_active ? 'false' : 'true' }})" wire:confirm="{{ $supplier->is_active ? 'Deactivate' : 'Activate' }} this supplier?" class="rounded-xl border border-rose-400/20 px-3 py-2 text-xs font-semibold text-rose-200 transition hover:bg-rose-500/10">{{ $supplier->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-stone-400">No suppliers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $suppliers->links() }}</div>
    </div>
</section>
