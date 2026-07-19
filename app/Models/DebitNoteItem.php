<?php

namespace App\Models;

use App\Services\Inventory\UnitConversionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebitNoteItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (DebitNoteItem $item): void {
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
        'debit_note_id',
        'supplier_bill_item_id',
        'product_id',
        'unit_id',
        'description',
        'quantity',
        'base_quantity',
        'unit_cost',
        'vat_rate',
        'net_amount',
        'vat_amount',
        'gross_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'base_quantity' => 'decimal:3',
            'unit_cost' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'gross_amount' => 'decimal:2',
        ];
    }

    public function debitNote(): BelongsTo
    {
        return $this->belongsTo(DebitNote::class);
    }

    public function supplierBillItem(): BelongsTo
    {
        return $this->belongsTo(SupplierBillItem::class);
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