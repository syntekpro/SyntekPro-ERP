<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Supplier Management</p>
        <h1 class="mt-3 text-4xl font-semibold text-white">{{ $supplier ? 'Edit supplier' : 'Create supplier' }}</h1>
        <p class="mt-3 max-w-2xl text-sm text-stone-300">Suppliers are Back Office-owned and shared across all purchasing workflows.</p>
    </div>

    <form wire:submit="save" class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Supplier name</label>
                <input wire:model.live="name" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('name') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Supplier code</label>
                <input wire:model.live="code" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('code') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Contact name</label>
                <input wire:model.live="contact_name" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Phone</label>
                <input wire:model.live="phone" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Email</label>
                <input wire:model.live="email" type="email" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">VAT registration number</label>
                <input wire:model.live="vat_registration_number" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Payment terms (days)</label>
                <input wire:model.live="payment_terms_days" type="number" min="0" max="365" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('payment_terms_days') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
        </div>

        <label class="mt-5 flex items-center gap-3 text-sm text-stone-300">
            <input wire:model="is_active" type="checkbox" class="h-4 w-4 rounded border-white/10 bg-stone-900 text-amber-400" />
            <span>Supplier is active for new purchase orders</span>
        </label>

        <div class="mt-8 flex gap-3">
            <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Save supplier</button>
            <a href="{{ route('suppliers.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Cancel</a>
        </div>
    </form>
</section>
