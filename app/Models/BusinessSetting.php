<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    protected static function booted(): void
    {
        static::saving(function (BusinessSetting $setting): void {
            $setting->singleton_key = 1;
        });

        static::deleting(function (): bool {
            return false;
        });
    }

    protected $fillable = [
        'singleton_key',
        'legal_name',
        'cr_number',
        'vat_number',
        'address',
        'phone',
        'email',
        'vat_enabled',
        'vat_rate',
        'currency_code',
        'currency_symbol',
        'quantity_decimal_places',
        'price_decimal_places',
        'date_format',
        'theme',
        'logo_path',
        'favicon_path',
        'invoice_footer_text',
        'mail_from_name',
        'mail_from_address',
    ];

    protected function casts(): array
    {
        return [
            'vat_enabled' => 'boolean',
            'vat_rate' => 'decimal:2',
            'quantity_decimal_places' => 'integer',
            'price_decimal_places' => 'integer',
        ];
    }
}