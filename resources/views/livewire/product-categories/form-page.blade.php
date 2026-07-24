<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Catalog</p>
        <h1 class="mt-3 text-4xl font-semibold text-ink">{{ $productCategory ? 'Edit product category' : 'Create product category' }}</h1>
    </div>

    <form wire:submit="save" class="max-w-3xl rounded-ui border border-line bg-surface p-6">
        <div>
            <label class="mb-2 block text-sm font-medium text-muted">Name</label>
            <input wire:model.live="name" type="text" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-ink outline-none" />
            @error('name') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
        </div>
        <label class="mt-5 flex items-center gap-3 text-sm text-muted">
            <input wire:model="is_active" type="checkbox" class="h-4 w-4 rounded border-line bg-panel text-brass" />
            <span>Product category is active</span>
        </label>
        <div class="mt-8 flex gap-3">
            <button type="submit" class="btn-primary">Save category</button>
            <a href="{{ route('product-categories.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</section>
