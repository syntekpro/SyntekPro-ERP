<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Phase 6</p>
        <h1 class="mt-2 text-4xl font-semibold text-white">Post journal adjustment</h1>
        <p class="mt-2 text-sm text-stone-300">Manual accounting adjustments must remain balanced before posting.</p>
    </div>

    <form wire:submit="save" class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Shop</label>
                <select wire:model="shop_id" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" {{ auth()->user()?->isShopManager() ? 'disabled' : '' }}>
                    <option value="">Select a shop</option>
                    @foreach ($this->shopOptions as $shop)
                        <option value="{{ $shop->id }}">{{ $shop->name }}</option>
                    @endforeach
                </select>
                @error('shop_id') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Entry date</label>
                <input wire:model="entry_date" type="date" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('entry_date') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Reference</label>
                <input wire:model="reference" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('reference') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Description</label>
                <input wire:model="description" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('description') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Lines</h2>
                <button type="button" wire:click="addLine" class="rounded-xl border border-white/10 px-3 py-2 text-xs font-semibold text-stone-100 transition hover:bg-white/10">Add line</button>
            </div>

            <div class="space-y-3">
                @foreach ($lines as $index => $line)
                    <div class="grid gap-3 rounded-2xl border border-white/10 bg-stone-950/60 p-4 md:grid-cols-[2fr_1fr_1fr_2fr_auto]">
                        <div>
                            <label class="mb-2 block text-xs font-medium uppercase tracking-[0.18em] text-stone-400">Account</label>
                            <select wire:model="lines.{{ $index }}.account_id" class="w-full rounded-xl border border-white/10 bg-stone-900 px-3 py-2 text-stone-100 outline-none">
                                <option value="">Select account</option>
                                @foreach ($this->accountOptions as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-medium uppercase tracking-[0.18em] text-stone-400">Debit</label>
                            <input wire:model="lines.{{ $index }}.debit" type="number" step="0.01" min="0" class="w-full rounded-xl border border-white/10 bg-stone-900 px-3 py-2 text-stone-100 outline-none" />
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-medium uppercase tracking-[0.18em] text-stone-400">Credit</label>
                            <input wire:model="lines.{{ $index }}.credit" type="number" step="0.01" min="0" class="w-full rounded-xl border border-white/10 bg-stone-900 px-3 py-2 text-stone-100 outline-none" />
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-medium uppercase tracking-[0.18em] text-stone-400">Line description</label>
                            <input wire:model="lines.{{ $index }}.description" type="text" class="w-full rounded-xl border border-white/10 bg-stone-900 px-3 py-2 text-stone-100 outline-none" />
                        </div>
                        <div class="flex items-end">
                            <button type="button" wire:click="removeLine({{ $index }})" class="rounded-xl border border-rose-400/20 px-3 py-2 text-xs font-semibold text-rose-200 transition hover:bg-rose-500/10">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>

            @error('lines') <p class="mt-3 text-sm text-rose-300">{{ $message }}</p> @enderror
            @error('lines.*.account_id') <p class="mt-3 text-sm text-rose-300">{{ $message }}</p> @enderror
            @error('lines.*.debit') <p class="mt-3 text-sm text-rose-300">{{ $message }}</p> @enderror
            @error('lines.*.credit') <p class="mt-3 text-sm text-rose-300">{{ $message }}</p> @enderror
        </div>

        <div class="mt-8 flex gap-3">
            <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Post journal entry</button>
            <a href="{{ route('journal-entries.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Cancel</a>
        </div>
    </form>
</section>
