<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }

    public function view(User $user, Account $account): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Account $account): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Account $account): bool
    {
        return $user->isSuperAdmin() && ! $account->journalEntryLines()->exists();
    }
}
