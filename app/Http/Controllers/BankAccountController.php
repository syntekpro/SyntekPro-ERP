<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', BankAccount::class);

        return view('bank-accounts.index');
    }

    public function create(): View
    {
        $this->authorize('create', BankAccount::class);

        return view('bank-accounts.create');
    }

    public function edit(BankAccount $bankAccount): View
    {
        $this->authorize('update', $bankAccount);

        return view('bank-accounts.edit', compact('bankAccount'));
    }
}
