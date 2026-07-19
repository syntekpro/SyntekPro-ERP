<?php

namespace App\Models;

use App\Services\Inventory\UnitConversionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (StockTransferItem $item): void {
            $product = Product::query()->find($item->product_id);

            if ($product === null) {
                return;
            }

            $unitConversionService = app(UnitConversionService::class);
            $item->unit_id ??= $unitConversionService->baseUnitId($product);
            $item->base_quantity ??= $unitConversionService->toBaseQuantity($product, (float) $item->quantity, (int) $item->unit_id);
        });
    }

    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'unit_id',
        'quantity',
        'base_quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'base_quantity' => 'decimal:3',
        ];
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function stockTransfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }
}