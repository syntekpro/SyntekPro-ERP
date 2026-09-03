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

    public function update(string $version): array
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
            $this->validateVersion($version);

            $rollbackTag = $this->compose->currentImageTag();
            $rollbackVersion = $this->currentVersion();
            $this->logger->info('Starting update', [
                'version' => $version,
                'rollback_tag' => $rollbackTag,
            ]);

            if (! $this->compose->imageExists($version)) {
                throw new \RuntimeException("Docker image for version {$version} was not found in GHCR.");
            }

            $dbBackup = $this->backup->createDatabaseBackup();
            $rollbackPublic = $this->backup->backupPublicDirectory("{$this->projectDir}/public");

            $this->down();
            $this->pullAndDeploy($version);
            $this->runPostDeploySteps();

            if (! $this->health->waitForHealthy()) {
                throw new \RuntimeException('Health check failed after update.');
            }

            $this->up();

            $this->logger->info('Update completed successfully', ['version' => $version]);

            return [
                'ok' => true,
                'version' => $version,
                'previous_version' => $rollbackVersion ?? $rollbackTag,
                'previous_image_tag' => $rollbackTag,
                'db_backup' => $dbBackup,
                'message' => "SyntekPro ERP has been updated to {$version}.",
            ];
        } catch (\Throwable $exception) {
            $this->logger->error('Update failed', [
                'version' => $version,
                'message' => $exception->getMessage(),
            ]);

            try {
                $this->rollback($rollbackTag, $rollbackVersion, $rollbackPublic, $dbBackup);
            } catch (\Throwable $rollbackException) {
                $this->logger->error('Rollback failed', [
                    'message' => $rollbackException->getMessage(),
                ]);

                return [
                    'ok' => false,
                    'error' => $exception->getMessage(),
                    'rollback_error' => $rollbackException->getMessage(),
                ];
            }

            return [
                'ok' => false,
                'error' => $exception->getMessage(),
                'rolled_back_to' => $rollbackVersion ?? $rollbackTag,
                'rolled_back_image_tag' => $rollbackTag,
            ];
        } finally {
            $this->lock->release();
        }
    }

    private function validateVersion(string $version): void
    {
        if (! preg_match(self::SEMVER_PATTERN, $version)) {
            throw new \InvalidArgumentException("{$version} is not a valid semantic version.");
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
