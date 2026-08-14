<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\View\View;

class BankReconciliationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', BankAccount::class);

        return view('bank-reconciliation.index');
    }

    public function show(BankAccount $bankAccount): View
    {
        $this->authorize('view', $bankAccount);

        return view('bank-reconciliation.show', compact('bankAccount'));
    }
}
