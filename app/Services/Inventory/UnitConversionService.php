<?php

namespace App\Services\Inventory;

use App\Models\Product;
use App\Models\ProductUnitConversion;

class UnitConversionService
{
    public function toBaseQuantity(Product $product, float $quantity, ?int $unitId = null): float
    {
        return round($quantity * $this->factorFor($product, $unitId), 3);
    }

    public function fromBaseQuantity(Product $product, float $baseQuantity, ?int $unitId = null): float
    {
        $factor = $this->factorFor($product, $unitId);

        return round($baseQuantity / $factor, 3);
    }

    public function baseUnitId(Product $product): int
    {
        return (int) ($product->base_unit_id ?: $product->baseUnit()->value('id'));
    }

    public function factorFor(Product $product, ?int $unitId = null): float
    {
        $baseUnitId = $this->baseUnitId($product);
        $selectedUnitId = $unitId ?: $baseUnitId;

        if ($selectedUnitId === $baseUnitId) {
            return 1.0;
        }

        $factor = ProductUnitConversion::query()
            ->where('product_id', $product->id)
            ->where('unit_id', $selectedUnitId)
            ->value('conversion_factor');

        if ($factor === null || (float) $factor <= 0) {
            throw new \RuntimeException('Selected unit is not configured for this product.');
        }

        return (float) $factor;
    }
}