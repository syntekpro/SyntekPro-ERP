<?php

namespace App\Http\Controllers;

use App\Enums\ChequeDirection;
use App\Enums\ChequeStatus;
use App\Enums\SalePaymentMethod;
use App\Models\Cheque;
use App\Models\Sale;
use App\Models\SupplierBill;
use App\Services\Cheques\ChequeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChequeController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Cheque::class);

        $direction = (string) $request->query('direction', '');
        $status = (string) $request->query('status', '');
        $fromDate = (string) $request->query('from_date', '');
        $toDate = (string) $request->query('to_date', '');
        $sort = (string) $request->query('sort', 'asc');

        $query = Cheque::query()
            ->with(['sale.customer', 'supplierBill.supplier'])
            ->when(in_array($direction, [ChequeDirection::Incoming->value, ChequeDirection::Outgoing->value], true), function ($q) use ($direction): void {
                $q->where('direction', $direction);
            })
            ->when(in_array($status, [
                ChequeStatus::Pending->value,
                ChequeStatus::Cleared->value,
                ChequeStatus::Bounced->value,
                ChequeStatus::Cancelled->value,
            ], true), function ($q) use ($status): void {
                $q->where('status', $status);
            })
            ->when($fromDate !== '', function ($q) use ($fromDate): void {
                $q->whereDate('cheque_date', '>=', $fromDate);
            })
            ->when($toDate !== '', function ($q) use ($toDate): void {
                $q->whereDate('cheque_date', '<=', $toDate);
            });

        $directionOrder = $sort === 'desc' ? 'desc' : 'asc';

        $cheques = $query
            ->orderBy('cheque_date', $directionOrder)
            ->orderBy('id', $directionOrder)
            ->paginate(20)
            ->withQueryString();

        return view('cheques.index', [
            'cheques' => $cheques,
            'filters' => [
                'direction' => $direction,
                'status' => $status,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'sort' => $directionOrder,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Cheque::class);

        $saleId = $request->integer('sale_id');
        $supplierBillId = $request->integer('supplier_bill_id');

        if ($saleId > 0 && $supplierBillId > 0) {
            abort(422, 'Select either a sale or a supplier bill, not both.');
        }

        if ($saleId <= 0 && $supplierBillId <= 0) {
            abort(422, 'A sale or supplier bill must be provided to record a cheque.');
        }

        $sale = null;
        $supplierBill = null;

        if ($saleId > 0) {
            $sale = Sale::query()->with(['customer', 'shop'])->findOrFail($saleId);

            abort_unless(
                $sale->payment_method === SalePaymentMethod::CreditAccount
                && (float) $sale->outstanding_balance > 0,
                403
            );
        }

        if ($supplierBillId > 0) {
            $supplierBill = SupplierBill::query()->with('supplier')->findOrFail($supplierBillId);

            abort_unless((float) $supplierBill->outstanding_balance > 0, 403);
        }

        return view('cheques.create', compact('sale', 'supplierBill'));
    }

    public function store(Request $request, ChequeService $chequeService): RedirectResponse
    {
        $this->authorize('create', Cheque::class);

        $validated = $request->validate([
            'sale_id' => ['nullable', 'integer', 'exists:sales,id'],
            'supplier_bill_id' => ['nullable', 'integer', 'exists:supplier_bills,id'],
            'cheque_number' => ['required', 'string', 'max:80'],
            'bank_name' => ['required', 'string', 'max:120'],
            'cheque_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $saleId = $validated['sale_id'] ?? null;
        $supplierBillId = $validated['supplier_bill_id'] ?? null;

        if (($saleId === null && $supplierBillId === null) || ($saleId !== null && $supplierBillId !== null)) {
            return back()->withErrors([
                'amount' => 'Select exactly one settlement target: sale or supplier bill.',
            ])->withInput();
        }

        try {
            if ($saleId !== null) {
                $chequeService->recordIncomingForSale(
                    (int) $saleId,
                    (float) $validated['amount'],
                    (string) $validated['cheque_number'],
                    (string) $validated['bank_name'],
                    (string) $validated['cheque_date'],
                    $request->user()?->id,
                );
            } else {
                $chequeService->recordOutgoingForSupplierBill(
                    (int) $supplierBillId,
                    (float) $validated['amount'],
                    (string) $validated['cheque_number'],
                    (string) $validated['bank_name'],
                    (string) $validated['cheque_date'],
                    $request->user()?->id,
                );
            }
        } catch (\RuntimeException $exception) {
            return back()->withErrors([
                'amount' => $exception->getMessage(),
            ])->withInput();
        }

        return redirect()
            ->route('cheques.index')
            ->with('status', 'Post-dated cheque recorded and posted to ledger.');
    }

    public function clear(Request $request, Cheque $cheque, ChequeService $chequeService): RedirectResponse
    {
        $this->authorize('clear', $cheque);

        $validated = $request->validate([
            'entry_date' => ['nullable', 'date'],
        ]);

        try {
            $chequeService->markCleared(
                $cheque->id,
                $request->user()?->id,
                $validated['entry_date'] ?? now()->toDateString(),
            );
        } catch (\Throwable $throwable) {
            return back()->with('warning', $throwable->getMessage());
        }

        return back()->with('status', 'Cheque marked as cleared.');
    }

    public function bounce(Request $request, Cheque $cheque, ChequeService $chequeService): RedirectResponse
    {
        $this->authorize('bounce', $cheque);

        $validated = $request->validate([
            'entry_date' => ['nullable', 'date'],
        ]);

        try {
            $chequeService->markBounced(
                $cheque->id,
                $request->user()?->id,
                $validated['entry_date'] ?? now()->toDateString(),
            );
        } catch (\Throwable $throwable) {
            return back()->with('warning', $throwable->getMessage());
        }

        return back()->with('status', 'Cheque marked as bounced and balances restored.');
    }
}
