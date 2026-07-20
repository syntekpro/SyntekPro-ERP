<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">Supplier Billing</p>
        <h1 class="mt-3 text-4xl font-semibold text-white">Supplier bills</h1>
        <p class="mt-3 max-w-2xl text-sm text-stone-300">Bills are auto-generated from PO receiving and support partial payment tracking.</p>
    </div>

    <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by bill number or supplier" class="w-full rounded-2xl border border-white/10 bg-stone-900 px-4 py-3 text-sm text-stone-100 outline-none placeholder:text-stone-500 lg:max-w-sm" />

        <div class="mt-5 overflow-hidden rounded-2xl border border-white/10">
            <table class="min-w-full divide-y divide-white/10 text-start text-sm">
                <thead class="bg-stone-900/80 text-stone-400">
                    <tr>
                        <th class="px-4 py-3">Bill</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Due</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Outstanding</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10 bg-stone-950/40 text-stone-200">
                    @forelse ($bills as $bill)
                        <tr>
                            <td class="px-4 py-3 text-white">{{ $bill->bill_number }}</td>
                            <td class="px-4 py-3">{{ $bill->supplier->name }}</td>
                            <td class="px-4 py-3">{{ $bill->due_date?->toDateString() }}</td>
                            <td class="px-4 py-3">SAR {{ number_format((float) $bill->total, 2) }}</td>
                            <td class="px-4 py-3">SAR {{ number_format((float) $bill->outstanding_balance, 2) }}</td>
                            <td class="px-4 py-3">{{ str($bill->status->value)->replace('_', ' ')->title() }}</td>
                            <td class="px-4 py-3 text-end">
                                <div class="mb-2 flex justify-end gap-2">
                                    <a href="{{ route('debit-notes.create', ['supplier_bill_id' => $bill->id]) }}" class="rounded-xl border border-sky-400/20 px-3 py-2 text-xs font-semibold text-sky-200 transition hover:bg-sky-500/10">Create debit note</a>
                                    @can('recordPayment', $bill)
                                        <a href="{{ route('supplier-bills.payments.create', $bill) }}" class="rounded-xl border border-emerald-400/20 px-3 py-2 text-xs font-semibold text-emerald-200 transition hover:bg-emerald-500/10">Record payment</a>
                                    @endcan
                                    @can('create', \App\Models\Cheque::class)
                                        <a href="{{ route('cheques.create', ['supplier_bill_id' => $bill->id]) }}" class="rounded-xl border border-amber-400/20 px-3 py-2 text-xs font-semibold text-amber-200 transition hover:bg-amber-500/10">Record cheque</a>
                                    @endcan
                                </div>
                                <x-document-actions type="supplier-bill" :id="$bill->id" />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-stone-400">No supplier bills found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $bills->links() }}</div>
    </div>
</section>
