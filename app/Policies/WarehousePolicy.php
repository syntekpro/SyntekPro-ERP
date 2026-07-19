<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;
use App\Policies\Concerns\AuthorizesShopResources;

class WarehousePolicy
{
    use AuthorizesShopResources;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('warehouses.view');
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('warehouses.create');
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermission('warehouses.update');
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermission('warehouses.delete');
    }
}