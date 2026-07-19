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
        return $user->hasPermission('shop_stock.view');
    }

    public function view(User $user, ShopStock $shopStock): bool
    {
        return $user->hasPermission('shop_stock.view')
            && ($user->role?->value !== 'shop_manager' || $this->managesShopId($user, $shopStock->shop_id));
    }

    public function update(User $user, ShopStock $shopStock): bool
    {
        return $user->hasPermission('shop_stock.update')
            && ($user->role?->value !== 'shop_manager' || $this->managesShopId($user, $shopStock->shop_id));
    }
}