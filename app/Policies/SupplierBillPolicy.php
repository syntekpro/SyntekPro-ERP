<?php

namespace App\Policies;

use App\Models\SupplierBill;
use App\Models\User;

class SupplierBillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('supplier_bills.view');
    }

    public function view(User $user, SupplierBill $supplierBill): bool
    {
        return $this->viewAny($user);
    }

    public function recordPayment(User $user, SupplierBill $supplierBill): bool
    {
        return $user->hasPermission('supplier_bills.record_payment')
            && (float) $supplierBill->outstanding_balance > 0;
    }
}
