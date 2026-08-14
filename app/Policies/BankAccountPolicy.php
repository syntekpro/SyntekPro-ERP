<?php

namespace App\Policies;

use App\Models\BankAccount;
use App\Models\User;

class BankAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }

    public function view(User $user, BankAccount $bankAccount): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }

    public function update(User $user, BankAccount $bankAccount): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, BankAccount $bankAccount): bool
    {
        return $user->isSuperAdmin();
    }
}
