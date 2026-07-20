<?php

namespace App\Policies;

use App\Enums\ChequeStatus;
use App\Models\Cheque;
use App\Models\User;

class ChequePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('cheques.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('cheques.record');
    }

    public function clear(User $user, Cheque $cheque): bool
    {
        return $user->hasPermission('cheques.clear')
            && $cheque->status === ChequeStatus::Pending;
    }

    public function bounce(User $user, Cheque $cheque): bool
    {
        return $user->hasPermission('cheques.bounce')
            && $cheque->status === ChequeStatus::Pending;
    }
}
