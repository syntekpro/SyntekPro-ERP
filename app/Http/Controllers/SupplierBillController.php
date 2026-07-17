<?php

namespace App\Http\Controllers;

use App\Models\SupplierBill;
use Illuminate\View\View;

class SupplierBillController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', SupplierBill::class);

        return view('supplier-bills.index');
    }

    public function createPayment(SupplierBill $supplierBill): View
    {
        $this->authorize('recordPayment', $supplierBill);

        return view('supplier-bills.create-payment', compact('supplierBill'));
    }
}
