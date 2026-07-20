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
        'application_name',
        'application_short_name',
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
        'default_locale',
        'theme',
        'brand_primary_color',
        'brand_accent_color',
        'brand_background_color',
        'brand_surface_color',
        'logo_path',
        'favicon_path',
        'touch_icon_path',
        'login_title',
        'login_subtitle',
        'header_brand_text',
        'header_brand_subtext',
        'footer_show_powered_by',
        'footer_powered_by_text',
        'brand_website',
        'email_branding_header',
        'email_branding_footer',
        'pdf_branding_header',
        'pdf_branding_footer',
        'pdf_watermark_text',
        'invoice_footer_text',
        'legal_name_ar',
        'address_ar',
        'invoice_footer_text_ar',
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
            'footer_show_powered_by' => 'boolean',
        ];
    }
}