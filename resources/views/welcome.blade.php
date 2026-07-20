<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="refresh" content="0;url={{ route('login') }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'ERP') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="flex min-h-screen items-center justify-center bg-stone-950 px-6 text-stone-100">
        <div class="max-w-md rounded-3xl border border-white/10 bg-white/5 p-8 text-center shadow-2xl shadow-black/25 backdrop-blur">
            <p class="text-xs font-semibold uppercase tracking-[0.32em] text-amber-300">{{ config('app.name', 'ERP') }}</p>
            <h1 class="mt-4 text-3xl font-semibold">{{ __('Redirecting to sign in') }}</h1>
            <p class="mt-3 text-sm text-stone-300">{{ __('The Back Office landing page is handled through the authenticated dashboard. If you are not redirected automatically, continue below.') }}</p>
            <a href="{{ route('login') }}" class="mt-6 inline-flex rounded-2xl bg-amber-400 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-300">{{ __('Go to login') }}</a>
        </div>
    </body>
</html>
