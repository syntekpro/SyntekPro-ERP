<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Policies\Concerns\AuthorizesShopResources;

class ProductPolicy
{
    use AuthorizesShopResources;

    public function viewAny(User $user): bool
    {
        return ! $this->deniesCashier($user);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->isSuperAdmin($user);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->isSuperAdmin($user);
    }
}