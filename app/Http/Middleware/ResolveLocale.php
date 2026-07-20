<?php

namespace App\Http\Middleware;

use App\Models\BusinessSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ResolveLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['en', 'ar'];

        $defaultLocale = 'en';
        try {
            $defaultLocale = (string) (BusinessSetting::query()->value('default_locale') ?: 'en');
        } catch (\Throwable) {
            $defaultLocale = 'en';
        }

        if (! in_array($defaultLocale, $supported, true)) {
            $defaultLocale = 'en';
        }

        $userLocale = (string) ($request->user()?->locale ?? '');
        $sessionLocale = $request->hasSession()
            ? (string) $request->session()->get('locale', '')
            : '';

        $resolved = in_array($userLocale, $supported, true)
            ? $userLocale
            : (in_array($sessionLocale, $supported, true) ? $sessionLocale : $defaultLocale);

        App::setLocale($resolved);
        if ($request->hasSession()) {
            $request->session()->put('locale', $resolved);
        }

        return $next($request);
    }
}
