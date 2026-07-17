<?php

namespace App\Policies;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->isSuperAdmin() || $user->isAccountant();
    }

    public function submit(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return ($user->isSuperAdmin() || $user->isAccountant())
            && $purchaseOrder->status === PurchaseOrderStatus::Draft;
    }

    public function receive(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return ($user->isSuperAdmin() || $user->isAccountant())
            && in_array($purchaseOrder->status, [PurchaseOrderStatus::Submitted, PurchaseOrderStatus::PartiallyReceived], true);
    }

    public function close(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return ($user->isSuperAdmin() || $user->isAccountant())
            && $purchaseOrder->status === PurchaseOrderStatus::Received;
    }
}
