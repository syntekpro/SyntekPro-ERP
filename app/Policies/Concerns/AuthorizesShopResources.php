<?php

namespace App\Policies\Concerns;

use App\Enums\UserRole;
use App\Models\User;

trait AuthorizesShopResources
{
    protected function isSuperAdmin(User $user): bool
    {
        return $user->role === UserRole::SuperAdmin;
    }

    protected function isShopManager(User $user): bool
    {
        return $user->role === UserRole::ShopManager;
    }

    protected function managesShopId(User $user, ?int $shopId): bool
    {
        return $this->isShopManager($user) && $user->shop_id !== null && $user->shop_id === $shopId;
    }

    protected function deniesCashier(User $user): bool
    {
        return $user->role === UserRole::Cashier;
    }
}