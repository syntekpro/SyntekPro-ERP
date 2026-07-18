<?php

namespace App\Policies;

use App\Models\DebitNote;
use App\Models\User;

class DebitNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }

    public function view(User $user, DebitNote $debitNote): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }
}