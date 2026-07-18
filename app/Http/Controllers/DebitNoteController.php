<?php

namespace App\Http\Controllers;

use App\Exceptions\UnbalancedJournalEntryException;
use App\Models\DebitNote;
use App\Models\SupplierBill;
use App\Services\Returns\DebitNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DebitNoteController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', DebitNote::class);

        $debitNotes = DebitNote::query()
            ->with(['supplierBill.supplier', 'supplierBill.warehouse'])
            ->latest('note_date')
            ->latest('id')
            ->paginate(15);

        return view('debit-notes.index', compact('debitNotes'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', DebitNote::class);

        $selectedBillId = $request->integer('supplier_bill_id') ?: null;
        $selectedBill = $selectedBillId === null
            ? null
            : SupplierBill::query()
                ->with(['items.debitNoteItems', 'supplier', 'warehouse'])
                ->findOrFail($selectedBillId);

        $supplierBills = SupplierBill::query()
            ->with(['supplier', 'warehouse'])
            ->latest('bill_date')
            ->latest('id')
            ->limit(50)
            ->get();

        return view('debit-notes.create', compact('supplierBills', 'selectedBill'));
    }

    public function store(Request $request, DebitNoteService $debitNoteService): RedirectResponse
    {
        $this->authorize('create', DebitNote::class);

        $validated = $request->validate([
            'supplier_bill_id' => ['required', 'integer', 'exists:supplier_bills,id'],
            'note_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.supplier_bill_item_id' => ['required', 'integer', 'exists:supplier_bill_items,id'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $returnLines = collect($validated['items'])
            ->filter(fn (array $line) => (float) ($line['quantity'] ?? 0) > 0)
            ->values()
            ->all();

        try {
            $debitNote = $debitNoteService->record(
                (int) $validated['supplier_bill_id'],
                $validated['note_date'],
                $returnLines,
                $validated['notes'] ?? null,
                $request->user()?->id,
            );
        } catch (\RuntimeException|UnbalancedJournalEntryException $exception) {
            return back()->withErrors(['items' => $exception->getMessage()])->withInput();
        }

        $redirect = redirect()->route('debit-notes.index')->with('status', 'Debit note posted and inventory reduced from warehouse stock.');

        if ((float) $debitNote->excess_amount > 0) {
            $redirect->with('warning', 'Debit note amount exceeded the bill\'s open balance. The excess is flagged for manual supplier handling.');
        }

        return $redirect;
    }
}