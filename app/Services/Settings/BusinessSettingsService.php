<?php

namespace App\Services\Settings;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Storage;

class BusinessSettingsService
{
    public const DEFAULT_LOGO = '/images/logo-full.png';
    public const DEFAULT_FAVICON = '/images/icon-main.png';

    public function themePresets(): array
    {
        return [
            'syntek-default' => ['name' => 'Syntek Default', 'primary' => '#fbbf24', 'accent' => '#38bdf8', 'background' => '#0c0a09', 'surface' => '#1c1917'],
            'riyadh-graphite' => ['name' => 'Riyadh Graphite', 'primary' => '#22c55e', 'accent' => '#eab308', 'background' => '#111827', 'surface' => '#1f2937'],
            'red-sea' => ['name' => 'Red Sea', 'primary' => '#06b6d4', 'accent' => '#f97316', 'background' => '#082f49', 'surface' => '#0f3f5f'],
            'date-palm' => ['name' => 'Date Palm', 'primary' => '#84cc16', 'accent' => '#14b8a6', 'background' => '#1a2e05', 'surface' => '#2b3f12'],
        ];
    }

    public function themePreset(?string $theme): array
    {
        return $this->themePresets()[$theme ?? 'syntek-default'] ?? $this->themePresets()['syntek-default'];
    }

    public function themeCss(): string
    {
        $theme = $this->themePreset($this->current()->theme);
        $primary = $theme['primary'];
        $accent = $theme['accent'];
        $background = $theme['background'];
        $surface = $theme['surface'];

        return sprintf(
            ':root{--brand-primary:%1$s;--brand-accent:%2$s;--brand-background:%3$s;--brand-surface:%4$s;--color-brass:%1$s;--color-brass-contrast:#2b1b05;--color-ledger:%2$s;--color-amber-100:color-mix(in srgb,%1$s 25%%,white);--color-amber-200:color-mix(in srgb,%1$s 45%%,white);--color-amber-300:color-mix(in srgb,%1$s 75%%,white);--color-amber-400:%1$s;--color-amber-500:color-mix(in srgb,%1$s 85%%,black);--color-cyan-100:color-mix(in srgb,%2$s 25%%,white);--color-cyan-200:color-mix(in srgb,%2$s 45%%,white);--color-cyan-300:color-mix(in srgb,%2$s 75%%,white);--color-cyan-400:%2$s;--color-cyan-500:color-mix(in srgb,%2$s 85%%,black);--color-stone-900:%4$s;--color-stone-950:%3$s;--color-slate-900:%4$s;--color-slate-950:%3$s;}',
            $primary,
            $accent,
            $background,
            $surface,
        );
    }

    public function themeStyleAttribute(): string
    {
        $theme = $this->themePreset($this->current()->theme);

        return sprintf(
            '--brand-primary:%s;--brand-accent:%s;--brand-background:%s;--brand-surface:%s;--color-brass:%s;--color-ledger:%s;',
            $theme['primary'],
            $theme['accent'],
            $theme['background'],
            $theme['surface'],
            $theme['primary'],
            $theme['accent'],
        );
    }

    public function logoUrl(): string
    {
        return $this->publicUrlOrDefault($this->current()->logo_path, self::DEFAULT_LOGO);
    }

    public function faviconUrl(): string
    {
        return $this->publicUrlOrDefault($this->current()->favicon_path, self::DEFAULT_FAVICON);
    }

    public function current(): BusinessSetting
    {
        return BusinessSetting::query()->firstOrCreate([
            'singleton_key' => 1,
        ], [
            'legal_name' => env('ZATCA_SELLER_LEGAL_NAME'),
            'vat_number' => env('ZATCA_SELLER_VAT_NUMBER'),
            'vat_enabled' => true,
            'vat_rate' => 15.00,
            'currency_code' => 'SAR',
            'currency_symbol' => 'SAR',
            'quantity_decimal_places' => 3,
            'price_decimal_places' => 2,
            'date_format' => 'Y-m-d',
            'theme' => 'syntek-default',
            'mail_from_name' => config('mail.from.name'),
            'mail_from_address' => config('mail.from.address'),
        ]);
    }

    public function vatEnabled(): bool
    {
        return $this->current()->vat_enabled;
    }

    public function vatRate(): float
    {
        return $this->vatEnabled() ? (float) $this->current()->vat_rate : 0.0;
    }

    protected function publicUrlOrDefault(?string $path, string $default): string
    {
        if ($path !== null && Storage::disk('public')->exists($path)) {
            return Storage::url($path);
        }

        return $default;
    }
}