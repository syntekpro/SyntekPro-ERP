<?php

namespace App\Http\Controllers;

use App\Enums\SalePaymentMethod;
use App\Models\Sale;
use App\Services\Receivables\CustomerPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerReceivableController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null && ($user->isSuperAdmin() || $user->isAccountant()), 403);

        $sales = Sale::query()
            ->with(['customer', 'shop'])
            ->where('payment_method', SalePaymentMethod::CreditAccount->value)
            ->where('outstanding_balance', '>', 0)
            ->orderBy('due_date')
            ->paginate(20);

        return view('customer-receivables.index', compact('sales'));
    }

    public function createPayment(Request $request, Sale $sale): View
    {
        $user = $request->user();

        abort_unless($user !== null && ($user->isSuperAdmin() || $user->isAccountant()), 403);
        abort_unless($sale->payment_method === SalePaymentMethod::CreditAccount && (float) $sale->outstanding_balance > 0, 403);

        return view('customer-receivables.create-payment', compact('sale'));
    }

    public function storePayment(Request $request, Sale $sale, CustomerPaymentService $customerPaymentService): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user !== null && ($user->isSuperAdmin() || $user->isAccountant()), 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'paid_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $customerPaymentService->record(
                $sale->id,
                (float) $validated['amount'],
                $validated['paid_at'],
                $validated['reference'] ?? null,
                $validated['notes'] ?? null,
                $user->id,
            );
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['amount' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('customer-receivables.index')->with('status', 'Customer payment recorded and posted to ledger.');
    }
}
