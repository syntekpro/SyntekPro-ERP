<?php

namespace App\Models\Scopes;

use App\Support\ShopContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ShopScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! ShopContext::hasShop()) {
            return;
        }

        $builder->where($model->getTable().'.shop_id', ShopContext::shopId());
    }
}
