<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Phase 12</p>
        <h1 class="mt-3 text-4xl font-semibold text-white">{{ $priceCategory ? 'Edit price category' : 'Create price category' }}</h1>
    </div>

    <form wire:submit="save" class="max-w-3xl rounded-3xl border border-white/10 bg-white/5 p-6">
        <div>
            <label class="mb-2 block text-sm font-medium text-stone-200">Name</label>
            <input wire:model.live="name" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
            @error('name') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
        </div>
        <label class="mt-5 flex items-center gap-3 text-sm text-stone-300">
            <input wire:model="is_active" type="checkbox" class="h-4 w-4 rounded border-white/10 bg-stone-900 text-amber-400" />
            <span>Price category is active</span>
        </label>
        <div class="mt-8 flex gap-3">
            <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Save category</button>
            <a href="{{ route('price-categories.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Cancel</a>
        </div>
    </form>
</section>