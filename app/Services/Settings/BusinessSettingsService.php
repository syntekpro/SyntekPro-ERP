<?php

namespace App\Services\Settings;

use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Storage;

class BusinessSettingsService
{
    public const DEFAULT_LOGO = '/images/logo-full.png';
    public const DEFAULT_FAVICON = '/images/icon-main.png';
    public const DEFAULT_TOUCH_ICON = '/images/icon-main-192.png';

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
        $theme = $this->resolvedTheme();
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
        $theme = $this->resolvedTheme();

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

    public function touchIconUrl(): string
    {
        return $this->publicUrlOrDefault($this->current()->touch_icon_path, self::DEFAULT_TOUCH_ICON);
    }

    public function applicationName(): string
    {
        $settings = $this->current();

        return $settings->application_name
            ?: $settings->legal_name
            ?: config('app.name', 'ERP');
    }

    public function applicationShortName(): string
    {
        $settings = $this->current();

        return $settings->application_short_name
            ?: str($this->applicationName())->limit(20, '')->toString();
    }

    public function brandWebsite(): string
    {
        $settings = $this->current();

        return $settings->brand_website ?: config('app.url', url('/'));
    }

    public function loginBranding(): array
    {
        $settings = $this->current();

        return [
            'title' => $settings->login_title ?: __('Back Office sign in'),
            'subtitle' => $settings->login_subtitle ?: __('Use the seeded super-admin account or your assigned shop credentials.'),
        ];
    }

    public function headerBranding(): array
    {
        $settings = $this->current();

        return [
            'text' => $settings->header_brand_text ?: __('Workspace'),
            'subtext' => $settings->header_brand_subtext ?: __('Operations Hub'),
        ];
    }

    public function footerBranding(): array
    {
        $settings = $this->current();
        $defaultPoweredBy = __('Powered by :name', ['name' => $this->applicationName()]);

        return [
            'show_powered_by' => $settings->footer_show_powered_by ?? true,
            'powered_by_text' => $settings->footer_powered_by_text ?: $defaultPoweredBy,
            'website' => $this->brandWebsite(),
        ];
    }

    public function emailBrandingPlaceholders(): array
    {
        $settings = $this->current();

        return [
            'header' => $settings->email_branding_header ?: $this->applicationName(),
            'footer' => $settings->email_branding_footer ?: __('Sent from :name', ['name' => $this->applicationName()]),
            'logo_url' => $this->logoUrl(),
        ];
    }

    public function pdfBrandingPlaceholders(): array
    {
        $settings = $this->current();

        return [
            'header' => $settings->pdf_branding_header ?: $this->applicationName(),
            'footer' => $settings->pdf_branding_footer ?: null,
            'watermark' => $settings->pdf_watermark_text ?: null,
        ];
    }

    public function current(): BusinessSetting
    {
        return BusinessSetting::query()->firstOrCreate([
            'singleton_key' => 1,
        ], [
            'application_name' => config('app.name', 'ERP'),
            'legal_name' => env('ZATCA_SELLER_LEGAL_NAME'),
            'vat_number' => env('ZATCA_SELLER_VAT_NUMBER'),
            'vat_enabled' => true,
            'vat_rate' => 15.00,
            'currency_code' => 'SAR',
            'currency_symbol' => 'SAR',
            'quantity_decimal_places' => 3,
            'price_decimal_places' => 2,
            'date_format' => 'Y-m-d',
            'default_locale' => 'en',
            'theme' => 'syntek-default',
            'footer_show_powered_by' => true,
            'mail_from_name' => config('mail.from.name'),
            'mail_from_address' => config('mail.from.address'),
        ]);
    }

    public function vatEnabled(): bool
    {
        return $this->current()->vat_enabled;
    }

    public function brandPalette(): array
    {
        return $this->resolvedTheme();
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

    protected function resolvedTheme(): array
    {
        $settings = $this->current();
        $theme = $this->themePreset($settings->theme);

        return [
            'primary' => $settings->brand_primary_color ?: $theme['primary'],
            'accent' => $settings->brand_accent_color ?: $theme['accent'],
            'background' => $settings->brand_background_color ?: $theme['background'],
            'surface' => $settings->brand_surface_color ?: $theme['surface'],
        ];
    }
}