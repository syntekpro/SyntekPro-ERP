<?php

namespace App\Policies;

use App\Models\ShopStock;
use App\Models\User;
use App\Policies\Concerns\AuthorizesShopResources;

class ShopStockPolicy
{
    use AuthorizesShopResources;

    public function viewAny(User $user): bool
    {
        return $this->isSuperAdmin($user) || $this->isShopManager($user);
    }

    public function view(User $user, ShopStock $shopStock): bool
    {
        return $this->isSuperAdmin($user) || $this->managesShopId($user, $shopStock->shop_id);
    }

    public function update(User $user, ShopStock $shopStock): bool
    {
        return $this->isSuperAdmin($user) || $this->managesShopId($user, $shopStock->shop_id);
    }
}