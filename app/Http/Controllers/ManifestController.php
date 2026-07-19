<?php

namespace App\Http\Controllers;

use App\Services\Settings\BusinessSettingsService;
use Illuminate\Http\JsonResponse;

class ManifestController extends Controller
{
    public function __invoke(BusinessSettingsService $settings): JsonResponse
    {
        $businessSettings = $settings->current();
        $theme = $settings->themePreset($businessSettings->theme);
        $name = $businessSettings->legal_name ?: config('app.name', 'SyntekPro ERP');
        $icon = $settings->faviconUrl();

        return response()->json([
            'name' => $name.' POS',
            'short_name' => $name,
            'description' => 'Offline-first POS shell for '.$name,
            'start_url' => '/pos/sales',
            'display' => 'standalone',
            'background_color' => $theme['background'],
            'theme_color' => $theme['primary'],
            'icons' => [[
                'src' => $icon,
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ], [
                'src' => $icon,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ]],
        ]);
    }
}