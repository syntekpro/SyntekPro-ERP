<section class="space-y-6 max-w-2xl">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Phase 7</p>
        <h1 class="mt-3 text-4xl font-semibold text-white">Record supplier payment</h1>
        <p class="mt-3 max-w-2xl text-sm text-stone-300">Bill {{ $supplierBill->bill_number }} | Outstanding SAR {{ number_format((float) $supplierBill->outstanding_balance, 2) }}</p>
    </div>

    <form wire:submit="save" class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Amount</label>
                <input wire:model.live="amount" type="number" min="0.01" step="0.01" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('amount') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Paid at</label>
                <input wire:model.live="paid_at" type="date" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
                @error('paid_at') <p class="mt-2 text-sm text-rose-300">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Reference</label>
                <input wire:model.live="reference" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
            </div>
            <div>
                <label class="mb-2 block text-sm font-medium text-stone-200">Notes</label>
                <input wire:model.live="notes" type="text" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-stone-100 outline-none" />
            </div>
        </div>

        <div class="mt-8 flex gap-3">
            <button type="submit" class="rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">Record payment</button>
            <a href="{{ route('supplier-bills.index') }}" class="rounded-2xl border border-white/10 px-5 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">Cancel</a>
        </div>
    </form>
</section>
