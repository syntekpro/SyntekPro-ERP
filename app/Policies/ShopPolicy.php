<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;
use App\Policies\Concerns\AuthorizesShopResources;

class ShopPolicy
{
    use AuthorizesShopResources;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('shops.view_any');
    }

    public function view(User $user, Shop $shop): bool
    {
        return $user->hasPermission('shops.view_any')
            || ($user->hasPermission('shops.view') && $this->managesShopId($user, $shop->id));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('shops.create');
    }

    public function update(User $user, Shop $shop): bool
    {
        return $user->hasPermission('shops.update');
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $user->hasPermission('shops.delete');
    }
}