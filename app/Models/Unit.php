<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function productsUsingAsBase(): HasMany
    {
        return $this->hasMany(Product::class, 'base_unit_id');
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(ProductUnitConversion::class);
    }
}