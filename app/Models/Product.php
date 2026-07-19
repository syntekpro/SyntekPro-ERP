<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if ($product->average_cost === null) {
                $product->average_cost = $product->cost_price ?? 0;
            }

            if ($product->base_unit_id === null) {
                $product->base_unit_id = Unit::query()->where('code', 'PCS')->value('id');
            }
        });
    }

    protected $fillable = [
        'name',
        'description',
        'sku',
        'barcode',
        'image_path',
        'base_unit_id',
        'price',
        'cost_price',
        'average_cost',
        'vat_rate',
        'is_excise_applicable',
        'excise_rate',
        'stock_min',
        'stock_max',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'average_cost' => 'decimal:4',
            'base_unit_id' => 'integer',
            'vat_rate' => 'decimal:2',
            'is_excise_applicable' => 'boolean',
            'excise_rate' => 'decimal:2',
            'stock_min' => 'decimal:3',
            'stock_max' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(ProductUnitConversion::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function transferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }
}