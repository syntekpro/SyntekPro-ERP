<?php

namespace App\Policies;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('purchase_orders.view');
    }

    public function view(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('purchase_orders.create');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasPermission('purchase_orders.update');
    }

    public function submit(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasPermission('purchase_orders.submit')
            && $purchaseOrder->status === PurchaseOrderStatus::Draft;
    }

    public function receive(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasPermission('purchase_orders.receive')
            && in_array($purchaseOrder->status, [PurchaseOrderStatus::Submitted, PurchaseOrderStatus::PartiallyReceived], true);
    }

    public function close(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasPermission('purchase_orders.close')
            && $purchaseOrder->status === PurchaseOrderStatus::Received;
    }
}
