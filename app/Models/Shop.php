<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_name',
        'vat_registration_number',
        'default_price_category_id',
        'slug',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_price_category_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function defaultPriceCategory(): BelongsTo
    {
        return $this->belongsTo(PriceCategory::class, 'default_price_category_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'destination_shop_id');
    }
}
