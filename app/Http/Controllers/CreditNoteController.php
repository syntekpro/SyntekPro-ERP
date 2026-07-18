<?php

namespace App\Http\Controllers;

use App\Exceptions\UnbalancedJournalEntryException;
use App\Models\CreditNote;
use App\Models\Sale;
use App\Services\Returns\CreditNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreditNoteController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', CreditNote::class);

        $creditNotes = CreditNote::query()
            ->with(['sale.customer', 'sale.shop'])
            ->latest('note_date')
            ->latest('id')
            ->paginate(15);

        return view('credit-notes.index', compact('creditNotes'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', CreditNote::class);

        $selectedSaleId = $request->integer('sale_id') ?: null;
        $selectedSale = $selectedSaleId === null
            ? null
            : Sale::query()
                ->with(['items.creditNoteItems', 'customer', 'shop'])
                ->findOrFail($selectedSaleId);

        $sales = Sale::query()
            ->with(['customer', 'shop'])
            ->latest('sold_at')
            ->latest('id')
            ->limit(50)
            ->get();

        return view('credit-notes.create', compact('sales', 'selectedSale'));
    }

    public function store(Request $request, CreditNoteService $creditNoteService): RedirectResponse
    {
        $this->authorize('create', CreditNote::class);

        $validated = $request->validate([
            'sale_id' => ['required', 'integer', 'exists:sales,id'],
            'note_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array'],
            'items.*.sale_item_id' => ['required', 'integer', 'exists:sale_items,id'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.condition' => ['nullable', 'in:sellable,damaged'],
        ]);

        $returnLines = collect($validated['items'])
            ->filter(fn (array $line) => (float) ($line['quantity'] ?? 0) > 0)
            ->values()
            ->all();

        try {
            $creditNote = $creditNoteService->record(
                (int) $validated['sale_id'],
                $validated['note_date'],
                $returnLines,
                $validated['notes'] ?? null,
                $request->user()?->id,
            );
        } catch (\RuntimeException|UnbalancedJournalEntryException $exception) {
            return back()->withErrors(['items' => $exception->getMessage()])->withInput();
        }

        $status = $creditNote->refund_amount > 0
            ? 'Credit note posted. Customer refund recorded in the reversal entry.'
            : 'Credit note posted. Return applied fully against the original sale balance.';

        return redirect()->route('credit-notes.index')->with('status', $status);
    }
}