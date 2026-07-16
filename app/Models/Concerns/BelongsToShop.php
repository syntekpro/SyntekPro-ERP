<?php

namespace App\Models\Concerns;

use App\Models\Scopes\ShopScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToShop
{
    protected static function bootBelongsToShop(): void
    {
        static::addGlobalScope(new ShopScope());
    }

    public function scopeForAllShops(Builder $query): Builder
    {
        return $query->withoutGlobalScope(ShopScope::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Shop::class);
    }
}
