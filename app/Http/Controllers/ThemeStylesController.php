<?php

namespace App\Http\Controllers;

use App\Services\Settings\BusinessSettingsService;
use Illuminate\Http\Response;

class ThemeStylesController extends Controller
{
    public function __invoke(BusinessSettingsService $settings): Response
    {
        return response($settings->themeCss(), 200, [
            'Content-Type' => 'text/css; charset=UTF-8',
        ]);
    }
}