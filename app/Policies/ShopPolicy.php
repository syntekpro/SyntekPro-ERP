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
        return $this->isSuperAdmin($user);
    }

    public function view(User $user, Shop $shop): bool
    {
        return $this->isSuperAdmin($user) || $this->managesShopId($user, $shop->id);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user, Shop $shop): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $this->isSuperAdmin($user);
    }
}