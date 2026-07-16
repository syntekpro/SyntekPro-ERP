<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Dashboard | {{ config('app.name', 'SyntekPro ERP') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        <main class="mx-auto flex min-h-screen max-w-5xl flex-col px-6 py-10">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-8 shadow-2xl shadow-black/30 backdrop-blur">
                <p class="text-sm uppercase tracking-[0.3em] text-amber-300">SyntekPro ERP</p>
                <h1 class="mt-4 text-4xl font-semibold">Dashboard</h1>
                <p class="mt-3 text-stone-300">Phase 0 foundation is active. This screen is the current hub landing surface.</p>

                <dl class="mt-8 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-stone-900/70 p-5">
                        <dt class="text-sm text-stone-400">Signed-in user</dt>
                        <dd class="mt-2 text-lg font-medium">{{ $user?->email ?? 'Guest' }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-stone-900/70 p-5">
                        <dt class="text-sm text-stone-400">Resolved shop context</dt>
                        <dd class="mt-2 text-lg font-medium">{{ $currentShopId ?? 'Hub context' }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('logout') }}" class="mt-8">
                    @csrf
                    <button type="submit" class="rounded-2xl border border-white/10 px-4 py-3 text-sm font-semibold text-stone-100 transition hover:bg-white/10">
                        Sign out
                    </button>
                </form>
            </div>
        </main>
    </body>
</html>