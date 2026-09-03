<?php

namespace App\Console\Commands;

use App\Services\Updates\UpdateManager;
use Illuminate\Console\Command;

class CheckForUpdates extends Command
{
    protected $signature = 'syntek:check-updates
                            {--force : Bypass the cache and check GitHub immediately}';

    protected $description = 'Check GitHub for the latest SyntekPro ERP release.';

    public function handle(UpdateManager $manager): int
    {
        $this->info('Checking for SyntekPro ERP updates...');

        $update = $manager->latest(forceRefresh: (bool) $this->option('force'));

        if ($update === null) {
            $this->warn('Could not reach GitHub or no release information was returned.');

            return self::FAILURE;
        }

        $this->info("Latest release: {$update->version}");

        if ($manager->isUpdateAvailable($update)) {
            $this->warn('An update is available.');

            return self::SUCCESS;
        }

        $this->info('You are running the latest version.');

        return self::SUCCESS;
    }
}
