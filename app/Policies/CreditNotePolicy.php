<?php

namespace App\Policies;

use App\Models\CreditNote;
use App\Models\User;

class CreditNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }

    public function view(User $user, CreditNote $creditNote): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }
}