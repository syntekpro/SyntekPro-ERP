<section class="space-y-6">
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.32em] text-brass">Supplier Billing</p>
        <h1 class="mt-3 text-4xl font-semibold text-ink">Supplier bills</h1>
        <p class="mt-3 max-w-2xl text-sm text-muted">Bills are auto-generated from PO receiving and support partial payment tracking.</p>
    </div>

    <div class="rounded-ui border border-line bg-surface p-6">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search by bill number or supplier" class="ui-input w-full rounded-ui border border-line bg-panel px-4 py-2.5 text-sm text-ink outline-none placeholder:text-subtle lg:max-w-sm" />

        <div class="mt-5 overflow-hidden rounded-ui border border-line table-baseline">
            <table class="min-w-full text-start text-sm ui-table">
                <thead>
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
                <tbody class="divide-y divide-line text-ink">
                    @forelse ($bills as $bill)
                        <tr>
                            <td class="px-4 py-3 figure-mono font-medium text-ink">{{ $bill->bill_number }}</td>
                            <td class="px-4 py-3">{{ $bill->supplier->name }}</td>
                            <td class="px-4 py-3 figure-mono">{{ $bill->due_date?->toDateString() }}</td>
                            <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $bill->total, 2) }}</td>
                            <td class="px-4 py-3 figure-mono">SAR {{ number_format((float) $bill->outstanding_balance, 2) }}</td>
                            <td class="px-4 py-3"><x-status-badge tone="warning">{{ str($bill->status->value)->replace('_', ' ')->title() }}</x-status-badge></td>
                            <td class="px-4 py-3 text-end">
                                <div class="mb-2 flex justify-end gap-2">
                                    <a href="{{ route('debit-notes.create', ['supplier_bill_id' => $bill->id]) }}" class="btn-secondary btn-size-sm">Create debit note</a>
                                    @can('recordPayment', $bill)
                                        <a href="{{ route('supplier-bills.payments.create', $bill) }}" class="btn-success btn-size-sm">Record payment</a>
                                    @endcan
                                    @can('create', \App\Models\Cheque::class)
                                        <a href="{{ route('cheques.create', ['supplier_bill_id' => $bill->id]) }}" class="btn-warning btn-size-sm">Record cheque</a>
                                    @endcan
                                </div>
                                <x-document-actions type="supplier-bill" :id="$bill->id" />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-muted">No supplier bills found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">{{ $bills->links() }}</div>
    </div>
</section>
