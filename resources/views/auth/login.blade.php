<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="system" data-theme-preference="system">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sign In | {{ config('app.name', 'SyntekPro ERP') }}</title>
        <script>
            document.documentElement.dataset.theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        </script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="icon" type="image/png" href="{{ app(\App\Services\Settings\BusinessSettingsService::class)->faviconUrl() }}">
        <link rel="manifest" href="{{ route('manifest') }}">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
        <link rel="stylesheet" href="{{ route('theme.css') }}">
    </head>
    <body class="min-h-screen bg-paper text-ink">
        <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-6 py-12">
            <section class="w-full rounded-ui border border-line bg-surface p-8 backdrop-blur">
                <img src="{{ app(\App\Services\Settings\BusinessSettingsService::class)->logoUrl() }}" alt="SyntekPro ERP" class="h-auto w-full max-w-[16rem]" />
                <p class="mt-3 text-sm uppercase tracking-[0.3em] text-brass">SyntekPro ERP</p>
                <h1 class="mt-4 text-3xl font-semibold">Back Office sign in</h1>
                <p class="mt-2 text-sm text-muted">Use the seeded super-admin account or your assigned shop credentials.</p>

                <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-medium text-ink">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none ring-0 placeholder:text-subtle" />
                        @error('email')
                            <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-medium text-ink">Password</label>
                        <input id="password" name="password" type="password" required class="w-full rounded-ui border border-line bg-panel px-4 py-3 text-ink outline-none ring-0 placeholder:text-subtle" />
                    </div>

                    <label class="flex items-center gap-3 text-sm text-muted">
                        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-line bg-panel text-brass" />
                        <span>Remember this device</span>
                    </label>

                    <button type="submit" class="btn-primary w-full justify-center">
                        Sign in
                    </button>
                </form>
            </section>
            <a href="https://syntekpro.com" target="_blank" rel="noopener noreferrer" class="fixed bottom-5 left-0 right-0 text-center text-xs font-semibold uppercase tracking-[0.24em] text-subtle transition hover:text-brass">Powered by SyntekPro ERP</a>
        </main>
    </body>
</html>