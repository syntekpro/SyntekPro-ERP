<?php

namespace App\Services\Pricing;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Shop;

class PriceResolutionService
{
    public function effectiveUnitPrice(Product $product, ?Customer $customer = null, ?Shop $shop = null): float
    {
        $customerPrice = $this->priceForCategory($product, $customer?->default_price_category_id);

        if ($customerPrice !== null) {
            return $customerPrice;
        }

        $shopPrice = $this->priceForCategory($product, $shop?->default_price_category_id);

        if ($shopPrice !== null) {
            return $shopPrice;
        }

        return (float) $product->price;
    }

    protected function priceForCategory(Product $product, ?int $priceCategoryId): ?float
    {
        if ($priceCategoryId === null) {
            return null;
        }

        $price = ProductPrice::query()
            ->where('product_id', $product->id)
            ->where('price_category_id', $priceCategoryId)
            ->value('price');

        return $price === null ? null : (float) $price;
    }
}