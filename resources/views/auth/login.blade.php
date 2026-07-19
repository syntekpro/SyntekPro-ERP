<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sign In | {{ config('app.name', 'SyntekPro ERP') }}</title>
        <link rel="icon" type="image/png" href="{{ app(\App\Services\Settings\BusinessSettingsService::class)->faviconUrl() }}">
        <link rel="manifest" href="{{ route('manifest') }}">
        <link rel="stylesheet" href="{{ route('theme.css') }}">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-slate-950 text-slate-100">
        <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-6 py-12">
            <section class="w-full rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl shadow-black/30 backdrop-blur">
                <img src="{{ app(\App\Services\Settings\BusinessSettingsService::class)->logoUrl() }}" alt="SyntekPro ERP" class="h-auto w-full max-w-[16rem]" />
                <p class="mt-3 text-sm uppercase tracking-[0.3em] text-amber-300">SyntekPro ERP</p>
                <h1 class="mt-4 text-3xl font-semibold">Hub sign in</h1>
                <p class="mt-2 text-sm text-slate-300">Use the seeded super-admin account or your assigned shop credentials.</p>

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-slate-200">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 text-slate-100 outline-none ring-0 placeholder:text-slate-500" />
                        @error('email')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-slate-200">Password</label>
                        <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-white/10 bg-slate-900 px-4 py-3 text-slate-100 outline-none ring-0 placeholder:text-slate-500" />
                    </div>

                    <label class="flex items-center gap-3 text-sm text-slate-300">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-white/10 bg-slate-900 text-amber-400" />
                        <span>Remember this device</span>
                    </label>

                    <button type="submit" class="w-full rounded-2xl bg-amber-400 px-4 py-3 font-semibold text-slate-950 transition hover:bg-amber-300">
                        Sign in
                    </button>
                </form>
            </section>
            <a href="https://syntekpro.com" target="_blank" rel="noopener noreferrer" class="fixed bottom-5 left-0 right-0 text-center text-xs font-semibold uppercase tracking-[0.24em] text-slate-500 transition hover:text-amber-300">Powered by SyntekPro ERP</a>
        </main>
    </body>
</html>