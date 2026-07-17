<?php

namespace App\Policies;

use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant() || $user->isShopManager();
    }

    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $user->isSuperAdmin()
            || $user->isAccountant()
            || ($user->isShopManager() && $user->shop_id === $journalEntry->shop_id);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }
}
