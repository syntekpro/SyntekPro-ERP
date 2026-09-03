<?php

declare(strict_types=1);

final class UpdaterServer
{
    private UpdateEngine $engine;
    private UpdateStatusStore $status;
    private string $envPath;

    public function __construct(
        private readonly string $token,
        private readonly string $currentVersion,
        private readonly string $projectDir = '/project',
        private readonly bool $dryRun = false,
    ) {
        $this->envPath = "{$this->projectDir}/.env";
        $logger = new Logger("{$this->projectDir}/storage/logs/updater.log");
        $commands = new CommandRunner($logger, $this->projectDir);
        $env = new EnvEditor($this->envPath);
        $compose = new ComposeManager($commands, $env, $logger);
        $backup = new BackupManager($commands, $env, $logger, "{$this->projectDir}/storage/backups");
        $this->status = new UpdateStatusStore("{$this->projectDir}/storage/framework/updater-status.json");

        $health = new HealthChecker($logger, "http://web:80/login");

        $this->engine = new UpdateEngine(
            $this->projectDir,
            $logger,
            new LockManager("{$this->projectDir}/storage/framework/updater.lock"),
            $compose,
            $backup,
            $health,
        );
    }

    public function handle(string $method, string $uri, ?string $authHeader, ?string $body): array
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        if ($path === '/health' && $method === 'GET') {
            return [200, ['status' => 'ok']];
        }

        if (! $this->isAuthorized($authHeader)) {
            return [401, ['error' => 'Unauthorized']];
        }

        if ($path === '/status' && $method === 'GET') {
            $env = new EnvEditor($this->envPath);

            return [200, [
                'ok' => true,
                'service' => 'syntek-updater',
                'version' => $env->get('SYNTEK_VERSION', $this->currentVersion),
                'image_tag' => $env->get('SYNTEK_IMAGE_TAG', 'latest'),
                'job' => $this->status->currentJob(),
            ]];
        }

        if ($path === '/status' && $method === 'DELETE') {
            if (! $this->status->clearCompletedJob()) {
                return [409, ['ok' => false, 'error' => 'An update is still in progress.']];
            }

            return [200, ['ok' => true]];
        }

        if ($path === '/update' && $method === 'POST') {
            return $this->handleUpdate($body);
        }

        return [404, ['error' => 'Not found']];
    }

    public function runUpdateJob(string $jobId, string $version): int
    {
        $job = $this->status->currentJob() ?? [
            'id' => $jobId,
            'requested_version' => $version,
            'dry_run' => $this->dryRun,
            'started_at' => gmdate(DATE_ATOM),
        ];

        $job['status'] = 'running';
        $job['message'] = $this->dryRun
            ? "Dry run update to {$version} is running in the background."
            : "Updating SyntekPro ERP to {$version} in the background.";
        $this->status->saveJob($job);

        try {
            $result = $this->dryRun
                ? [
                    'ok' => true,
                    'version' => $version,
                    'dry_run' => true,
                    'message' => "Dry run: would update SyntekPro ERP to {$version}.",
                ]
                : $this->engine->update($version);
        } catch (\Throwable $exception) {
            $result = [
                'ok' => false,
                'error' => $exception->getMessage(),
            ];
        }

        $job['status'] = ($result['ok'] ?? false) ? 'success' : 'failed';
        $job['message'] = $result['message'] ?? (($result['ok'] ?? false)
            ? "SyntekPro ERP has been updated to {$version}."
            : 'The update failed.');
        $job['version'] = $result['version'] ?? $version;
        $job['error'] = $result['error'] ?? null;
        $job['rollback_error'] = $result['rollback_error'] ?? null;
        $job['rolled_back_to'] = $result['rolled_back_to'] ?? null;
        $job['previous_version'] = $result['previous_version'] ?? null;
        $job['previous_image_tag'] = $result['previous_image_tag'] ?? null;
        $job['db_backup'] = $result['db_backup'] ?? null;
        $job['dry_run'] = (bool) ($result['dry_run'] ?? $this->dryRun);
        $job['finished_at'] = gmdate(DATE_ATOM);
        $this->status->saveJob($job);

        return ($result['ok'] ?? false) ? 0 : 1;
    }

    private function isAuthorized(?string $authHeader): bool
    {
        if ($this->token === '') {
            return false;
        }

        if ($authHeader === null || ! str_starts_with($authHeader, 'Bearer ')) {
            return false;
        }

        return hash_equals($this->token, substr($authHeader, 7));
    }

    private function handleUpdate(?string $body): array
    {
        $input = ($body !== null && $body !== '') ? json_decode($body, true) : [];
        $version = $input['version'] ?? null;

        if (! is_string($version) || $version === '') {
            return [422, ['error' => 'A valid version is required.']];
        }

        if (($activeJob = $this->status->activeJob()) !== null) {
            return [409, [
                'ok' => false,
                'error' => 'An update is already in progress.',
                'job' => $activeJob,
            ]];
        }

        $job = [
            'id' => bin2hex(random_bytes(16)),
            'requested_version' => $version,
            'status' => 'pending',
            'message' => $this->dryRun
                ? "Dry run update to {$version} has been queued."
                : "Update to {$version} has been queued.",
            'started_at' => gmdate(DATE_ATOM),
            'finished_at' => null,
            'dry_run' => $this->dryRun,
        ];
        $this->status->saveJob($job);

        try {
            $this->launchUpdateProcess($job['id'], $version);
        } catch (\Throwable $exception) {
            $job['status'] = 'failed';
            $job['message'] = 'The background update worker could not be started.';
            $job['error'] = $exception->getMessage();
            $job['finished_at'] = gmdate(DATE_ATOM);
            $this->status->saveJob($job);

            return [500, ['ok' => false, 'error' => $exception->getMessage(), 'job' => $job]];
        }

        return [202, [
            'ok' => true,
            'message' => $job['message'],
            'job' => $job,
        ]];
    }

    private function launchUpdateProcess(string $jobId, string $version): void
    {
        $php = escapeshellarg(PHP_BINARY);
        $script = escapeshellarg(__DIR__ . '/server.php');
        $jobArgument = escapeshellarg($jobId);
        $versionArgument = escapeshellarg($version);
        $command = DIRECTORY_SEPARATOR === '\\'
            ? sprintf('cmd /c start "" /B %s %s __run_job %s %s', $php, $script, $jobArgument, $versionArgument)
            : sprintf('%s %s __run_job %s %s > /dev/null 2>&1 &', $php, $script, $jobArgument, $versionArgument);

        $nullDevice = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
        $process = proc_open($command, [
            0 => ['file', $nullDevice, 'r'],
            1 => ['file', $nullDevice, 'a'],
            2 => ['file', $nullDevice, 'a'],
        ], $pipes, __DIR__);

        if (! is_resource($process)) {
            throw new \RuntimeException('Unable to start the background update worker.');
        }

        proc_close($process);
    }
}
