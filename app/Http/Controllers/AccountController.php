<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Account::class);

        return view('accounts.index');
    }

    public function create(): View
    {
        $this->authorize('create', Account::class);

        return view('accounts.create');
    }

    public function edit(Account $account): View
    {
        $this->authorize('update', $account);

        return view('accounts.edit', compact('account'));
    }
}
