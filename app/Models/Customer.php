<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'contact_name',
        'phone',
        'email',
        'vat_registration_number',
        'payment_terms_days',
        'credit_limit',
        'default_price_category_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'payment_terms_days' => 'integer',
            'credit_limit' => 'decimal:2',
            'default_price_category_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function defaultPriceCategory(): BelongsTo
    {
        return $this->belongsTo(PriceCategory::class, 'default_price_category_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
