<?php

namespace App\Providers;

use App\Console\Commands\ResetDemoEnvironment;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->commands([
            ResetDemoEnvironment::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! config('app.demo_mode')) {
            return;
        }

        $databaseName = strtolower((string) config('database.connections.'.config('database.default').'.database'));

        if ($databaseName === '' || ! str_contains($databaseName, 'demo')) {
            throw new \RuntimeException('APP_DEMO_MODE is enabled, but the active database name does not contain "demo".');
        }
    }
}
