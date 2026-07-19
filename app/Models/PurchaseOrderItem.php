<?php

namespace App\Models;

use App\Services\Inventory\UnitConversionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrderItem $item): void {
            $product = Product::query()->find($item->product_id);

            if ($product === null) {
                return;
            }

            $unitConversionService = app(UnitConversionService::class);
            $item->unit_id ??= $unitConversionService->baseUnitId($product);
            $item->base_quantity_ordered ??= $unitConversionService->toBaseQuantity($product, (float) $item->quantity_ordered, (int) $item->unit_id);
            $item->base_quantity_received ??= $unitConversionService->toBaseQuantity($product, (float) ($item->quantity_received ?? 0), (int) $item->unit_id);
        });
    }

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'unit_id',
        'quantity_ordered',
        'base_quantity_ordered',
        'quantity_received',
        'base_quantity_received',
        'unit_cost',
        'vat_rate',
    ];

    protected function casts(): array
    {
        return [
            'quantity_ordered' => 'decimal:3',
            'base_quantity_ordered' => 'decimal:3',
            'quantity_received' => 'decimal:3',
            'base_quantity_received' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'vat_rate' => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
