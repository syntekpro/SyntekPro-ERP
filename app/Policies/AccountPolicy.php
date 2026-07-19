<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('accounts.view');
    }

    public function view(User $user, Account $account): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('accounts.create');
    }

    public function update(User $user, Account $account): bool
    {
        return $user->hasPermission('accounts.update');
    }

    public function delete(User $user, Account $account): bool
    {
        return $user->hasPermission('accounts.delete') && ! $account->journalEntryLines()->exists();
    }
}
