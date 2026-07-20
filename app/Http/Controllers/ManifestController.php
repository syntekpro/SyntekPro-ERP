<?php

namespace App\Http\Controllers;

use App\Services\Settings\BusinessSettingsService;
use Illuminate\Http\JsonResponse;

class ManifestController extends Controller
{
    public function __invoke(BusinessSettingsService $settings): JsonResponse
    {
        $palette = $settings->brandPalette();

        $name = $settings->applicationName();
        $shortName = $settings->applicationShortName();
        $icon = $settings->faviconUrl();
        $touchIcon = $settings->touchIconUrl();
        $backgroundColor = $palette['background'] ?? '#0c0a09';
        $themeColor = $palette['primary'] ?? '#fbbf24';

        return response()->json([
            'name' => $name.' POS',
            'short_name' => $shortName,
            'description' => 'Offline-first POS shell for '.$name,
            'start_url' => '/pos/sales',
            'display' => 'standalone',
            'background_color' => $backgroundColor,
            'theme_color' => $themeColor,
            'icons' => [[
                'src' => $icon,
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ], [
                'src' => $touchIcon,
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ]],
        ]);
    }
}