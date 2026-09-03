<?php

declare(strict_types=1);

final class BackupManager
{
    public function __construct(
        private readonly CommandRunner $commands,
        private readonly EnvEditor $env,
        private readonly Logger $logger,
        private readonly string $backupDir,
    ) {
        $this->ensureDirectory();
    }

    public function createDatabaseBackup(): string
    {
        $path = $this->backupDir . '/db-' . date('Ymd-His') . '.sql';

        $dbHost = $this->env->get('DB_HOST', 'db');
        $dbDatabase = $this->env->get('DB_DATABASE', 'syntekpro');
        $dbUser = $this->env->get('DB_USERNAME', 'syntekpro');
        $dbPassword = $this->env->get('DB_PASSWORD', 'syntekpro');

        $this->commands->mustRun(sprintf(
            'mysqldump -h %s -u %s -p%s %s > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPassword),
            escapeshellarg($dbDatabase),
            escapeshellarg($path)
        ));

        $this->logger->info('Database backup created', ['path' => $path]);

        return $path;
    }

    public function backupPublicDirectory(string $publicPath): string
    {
        $path = $this->backupDir . '/public-' . date('Ymd-His');

        if (is_dir($publicPath)) {
            $this->commands->mustRun(sprintf(
                'cp -a %s %s',
                escapeshellarg($publicPath),
                escapeshellarg($path)
            ));
        }

        $this->logger->info('Public directory backup created', ['path' => $path]);

        return $path;
    }

    public function restoreDatabaseBackup(string $path): void
    {
        if (! is_file($path)) {
            throw new \RuntimeException("Database backup {$path} does not exist.");
        }

        $dbHost = $this->env->get('DB_HOST', 'db');
        $dbDatabase = $this->env->get('DB_DATABASE', 'syntekpro');
        $dbUser = $this->env->get('DB_USERNAME', 'syntekpro');
        $dbPassword = $this->env->get('DB_PASSWORD', 'syntekpro');

        $this->commands->mustRun(sprintf(
            'mysql -h %s -u %s -p%s %s < %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPassword),
            escapeshellarg($dbDatabase),
            escapeshellarg($path)
        ));

        $this->logger->info('Database backup restored', ['path' => $path]);
    }

    private function ensureDirectory(): void
    {
        if (! is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0755, true);
        }
    }
}
