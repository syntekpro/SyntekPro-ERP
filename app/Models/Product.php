<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if ($product->average_cost === null) {
                $product->average_cost = $product->cost_price;
            }
        });
    }

    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'price',
        'cost_price',
        'average_cost',
        'vat_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'average_cost' => 'decimal:4',
            'vat_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function transferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }
}