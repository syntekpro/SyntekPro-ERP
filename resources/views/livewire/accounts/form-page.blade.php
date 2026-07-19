<section class="max-w-3xl">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Chart of Accounts</p>
        <h1 class="mt-2 text-4xl font-semibold text-white">{{ $account ? 'Edit account' : 'Create account' }}</h1>
        <p class="mt-2 text-sm text-stone-300">Define the shared chart of accounts used for all shops.</p>
    </div>

    <form wire:submit="save" class="mt-6 rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Account code</label>
                <input wire:model.live="code" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('code') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Account name</label>
                <input wire:model.live="name" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('name') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Account type</label>
                <select wire:model="account_type" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none">
                    @foreach ($this->typeOptions as $type)
                        <option value="{{ $type->value }}">{{ str($type->value)->title() }}</option>
                    @endforeach
                </select>
                @error('account_type') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Parent account</label>
                <select wire:model="parent_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none">
                    <option value="">None</option>
                    @foreach ($this->parentOptions as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->name }}</option>
                    @endforeach
                </select>
                @error('parent_id') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
        </div>

        <label class="mt-5 flex items-center gap-3 text-sm text-stone-300">
            <input wire:model="is_active" type="checkbox" class="h-4 w-4 rounded border-white/10 bg-stone-900 text-amber-400" />
            <span>Account is active for postings</span>
        </label>

        <div class="mt-8 flex gap-3">
            <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Save account</button>
            <a href="{{ route('accounts.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Cancel</a>
        </div>
    </form>
</section>
