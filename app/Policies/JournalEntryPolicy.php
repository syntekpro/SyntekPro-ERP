<?php

namespace App\Policies;

use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('journal_entries.view');
    }

    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $user->hasPermission('journal_entries.view')
            && ($user->role?->value !== 'shop_manager' || $user->shop_id === $journalEntry->shop_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('journal_entries.create');
    }
}
