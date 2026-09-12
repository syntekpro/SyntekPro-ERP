<?php

declare(strict_types=1);

final class UpdateEngine
{
    private const SEMVER_PATTERN = '/^(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>[a-zA-Z\d\-]+(?:\.[a-zA-Z\d\-]+)*))?(?:\+(?P<build>[a-zA-Z\d\-]+(?:\.[a-zA-Z\d\-]+)*))?$/';

    public function __construct(
        private readonly string $projectDir,
        private readonly Logger $logger,
        private readonly LockManager $lock,
        private readonly ComposeManager $compose,
        private readonly BackupManager $backup,
        private readonly HealthChecker $health,
    ) {
    }

    public function update(string $version, ?callable $onStep = null): array
    {
        if (! $this->lock->acquire()) {
            return [
                'ok' => false,
                'error' => 'An update is already in progress.',
            ];
        }

        $rollbackTag = null;
        $rollbackVersion = null;
        $rollbackPublic = null;
        $dbBackup = null;

        try {
            $this->reportStep($onStep, 'validating_version');
            $this->validateVersion($version);

            $this->reportStep($onStep, 'capturing_previous_version');
            $rollbackTag = $this->compose->currentImageTag();
            $rollbackVersion = $this->currentVersion();
            $this->logger->info('Starting update', [
                'version' => $version,
                'rollback_tag' => $rollbackTag,
            ]);

            $this->preflight($rollbackTag);

            $this->reportStep($onStep, 'checking_image');
            if (! $this->compose->imageExists($version)) {
                throw new \RuntimeException("Docker image for version {$version} was not found in GHCR.");
            }

            $this->reportStep($onStep, 'creating_database_backup');
            $dbBackup = $this->backup->createDatabaseBackup();
            $this->reportStep($onStep, 'creating_public_backup');
            $rollbackPublic = $this->backup->backupPublicDirectory("{$this->projectDir}/public");

            $this->reportStep($onStep, 'enabling_maintenance_mode');
            $this->down();
            $this->reportStep($onStep, 'deploying_update');
            $this->pullAndDeploy($version);
            $this->reportStep($onStep, 'running_post_deploy_steps');
            $this->runPostDeploySteps();

            $this->reportStep($onStep, 'checking_health');
            if (! $this->health->waitForHealthy()) {
                throw new \RuntimeException('Health check failed after update.');
            }

            $this->reportStep($onStep, 'disabling_maintenance_mode');
            $this->up();

            $this->logger->info('Update completed successfully', ['version' => $version]);

            return [
                'ok' => true,
                'version' => $version,
                'previous_version' => $rollbackVersion ?? $rollbackTag,
                'previous_image_tag' => $rollbackTag,
                'db_backup' => $dbBackup,
                'rollback_occurred' => false,
                'message' => "SyntekPro ERP has been updated to {$version}.",
            ];
        } catch (\Throwable $exception) {
            $this->logger->error('Update failed', [
                'version' => $version,
                'message' => $exception->getMessage(),
            ]);

            try {
                $this->reportStep($onStep, 'rolling_back');
                $this->rollback($rollbackTag, $rollbackVersion, $rollbackPublic, $dbBackup);
            } catch (\Throwable $rollbackException) {
                $this->logger->error('Rollback failed', [
                    'message' => $rollbackException->getMessage(),
                ]);

                return [
                    'ok' => false,
                    'error' => $exception->getMessage(),
                    'rollback_error' => $rollbackException->getMessage(),
                    'previous_version' => $rollbackVersion,
                    'previous_image_tag' => $rollbackTag,
                    'rollback_occurred' => false,
                ];
            }

            return [
                'ok' => false,
                'error' => $exception->getMessage(),
                'rolled_back_to' => $rollbackVersion ?? $rollbackTag,
                'rolled_back_image_tag' => $rollbackTag,
                'previous_version' => $rollbackVersion,
                'previous_image_tag' => $rollbackTag,
                'database_restored' => $dbBackup !== null,
                'rollback_occurred' => true,
                'message' => 'Update failed; the previous ERP version and database were restored.',
            ];
        } finally {
            $this->lock->release();
        }
    }

    /**
     * Refuse to start an update unless we know we can get back to a
     * working state if it fails. Two things are required:
     *  - the app is actually serving traffic right now (don't layer an
     *    update attempt on top of a system that's already broken —
     *    fix that first, separately, before updating);
     *  - the image we'd roll back to is present in the LOCAL docker
     *    cache, not just recorded in .env. If it were ever pruned or
     *    never pulled, "rollback" would otherwise try to recreate a
     *    container from an image that isn't there and fail destructively.
     */
    private function preflight(string $rollbackTag): void
    {
        if (! $this->health->isHealthy()) {
            throw new \RuntimeException(
                'Refusing to start update: the currently running site is not healthy. '
                . 'Fix the current outage first — an update cannot safely roll back to a broken baseline.'
            );
        }

        if (! $this->compose->localImageExists($rollbackTag)) {
            throw new \RuntimeException(
                "Refusing to start update: the current image ({$rollbackTag}) is not present in the local "
                . 'Docker cache, so a rollback would not be possible if this update fails.'
            );
        }

        $this->logger->info('Preflight checks passed', ['rollback_tag' => $rollbackTag]);
    }

    private function validateVersion(string $version): void
    {
        if (! preg_match(self::SEMVER_PATTERN, $version)) {
            throw new \InvalidArgumentException("{$version} is not a valid semantic version.");
        }
    }

    private function reportStep(?callable $onStep, string $step): void
    {
        if ($onStep !== null) {
            $onStep($step);
        }
    }

    private function down(): void
    {
        $this->logger->info('Enabling maintenance mode');
        $this->compose->runInApp('php artisan down');
    }

    private function up(): void
    {
        $this->logger->info('Disabling maintenance mode');
        $this->compose->runInApp('php artisan up');
    }

    private function pullAndDeploy(string $version): void
    {
        $this->logger->info('Pulling new image', ['version' => $version]);
        $this->compose->pullImage($version);

        $this->logger->info('Updating environment');
        $this->compose->setImageTag($version);
        $this->compose->setVersion($version);

        $this->logger->info('Recreating application container');
        $this->compose->recreateApp();

        $this->logger->info('Syncing public assets');
        $this->compose->copyFromApp('/var/www/html/public', "{$this->projectDir}/public");
    }

    private function runPostDeploySteps(): void
    {
        $this->logger->info('Running database migrations');
        $this->compose->runInApp('php artisan migrate --force');

        $this->logger->info('Rebuilding caches');
        $this->compose->runInApp('php artisan optimize');
    }

    private function rollback(?string $tag, ?string $version, ?string $publicBackup, ?string $dbBackup): void
    {
        $this->logger->warning('Starting rollback', ['tag' => $tag]);

        if ($tag === null) {
            throw new \RuntimeException('Cannot rollback: no previous image tag was recorded.');
        }

        if ($publicBackup !== null && is_dir($publicBackup)) {
            $this->logger->info('Restoring public directory backup', ['path' => $publicBackup]);
            $this->compose->runner->mustRun(sprintf(
                'rm -rf %s && cp -a %s %s',
                escapeshellarg("{$this->projectDir}/public"),
                escapeshellarg($publicBackup),
                escapeshellarg("{$this->projectDir}/public")
            ));
        }

        $this->compose->setImageTag($tag);
        if ($version !== null) {
            $this->compose->setVersion($version);
        }
        $this->compose->recreateApp();

        if ($dbBackup !== null) {
            $this->logger->info('Restoring database backup', ['path' => $dbBackup]);
            $this->backup->restoreDatabaseBackup($dbBackup);
        }

        $this->compose->runInApp('php artisan optimize');
        $this->up();

        $this->logger->info('Rollback completed', ['tag' => $tag]);
    }

    private function currentVersion(): ?string
    {
        $content = file_get_contents("{$this->projectDir}/.env");

        if ($content === false) {
            return null;
        }

        if (! preg_match('/^SYNTEK_VERSION=(.*)$/m', $content, $matches)) {
            return null;
        }

        $version = trim($matches[1], " \t\n\r\"'");

        return $version === '' ? null : $version;
    }
}
