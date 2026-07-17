<?php

namespace App\Policies;

use App\Models\SupplierBill;
use App\Models\User;

class SupplierBillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }

    public function view(User $user, SupplierBill $supplierBill): bool
    {
        return $this->viewAny($user);
    }

    public function recordPayment(User $user, SupplierBill $supplierBill): bool
    {
        return ($user->isSuperAdmin() || $user->isAccountant())
            && (float) $supplierBill->outstanding_balance > 0;
    }
}
