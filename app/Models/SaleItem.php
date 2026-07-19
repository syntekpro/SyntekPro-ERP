<?php

namespace App\Models;

use App\Services\Inventory\UnitConversionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (SaleItem $item): void {
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
        'sale_id',
        'product_id',
        'unit_id',
        'product_name',
        'sku',
        'barcode',
        'quantity',
        'base_quantity',
        'unit_price',
        'unit_cost',
        'vat_rate',
        'vat_amount',
        'excise_rate',
        'excise_amount',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'base_quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'unit_cost' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'excise_rate' => 'decimal:2',
            'excise_amount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creditNoteItems(): HasMany
    {
        return $this->hasMany(CreditNoteItem::class);
    }
}
