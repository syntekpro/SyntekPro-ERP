<?php

namespace App\Support;

use App\Models\Shop;

class ShopContext
{
    protected static ?int $shopId = null;

    public static function setShop(?Shop $shop): void
    {
        static::$shopId = $shop?->id;
    }

    public static function setShopId(?int $shopId): void
    {
        static::$shopId = $shopId;
    }

    public static function shopId(): ?int
    {
        return static::$shopId;
    }

    public static function hasShop(): bool
    {
        return static::$shopId !== null;
    }

    public static function clear(): void
    {
        static::$shopId = null;
    }
}
