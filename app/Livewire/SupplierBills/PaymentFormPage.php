<?php

namespace App\Livewire\SupplierBills;

use App\Models\SupplierBill;
use App\Services\Purchasing\SupplierPaymentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PaymentFormPage extends Component
{
    use AuthorizesRequests;

    public SupplierBill $supplierBill;

    public string $amount = '0.00';

    public string $paid_at = '';

    public string $reference = '';

    public string $notes = '';

    public function mount(SupplierBill $supplierBill): void
    {
        $this->supplierBill = $supplierBill;

        $this->authorize('recordPayment', $this->supplierBill);

        $this->paid_at = now()->toDateString();
        $this->amount = number_format((float) $supplierBill->outstanding_balance, 2, '.', '');
    }

    public function save(SupplierPaymentService $supplierPaymentService)
    {
        $validated = $this->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'paid_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $supplierPaymentService->record(
                $this->supplierBill->id,
                (float) $validated['amount'],
                $validated['paid_at'],
                $validated['reference'] === '' ? null : $validated['reference'],
                $validated['notes'] === '' ? null : $validated['notes'],
                Auth::id(),
            );
        } catch (\RuntimeException $exception) {
            $this->addError('amount', $exception->getMessage());

            return null;
        }

        session()->flash('status', 'Supplier payment recorded and posted to ledger.');

        return redirect()->route('supplier-bills.index');
    }

    public function render()
    {
        return view('livewire.supplier-bills.payment-form-page');
    }
}
