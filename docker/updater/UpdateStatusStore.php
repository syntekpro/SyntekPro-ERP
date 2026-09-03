<?php

declare(strict_types=1);

final class UpdateStatusStore
{
    public function __construct(
        private readonly string $path,
    ) {
        $this->ensureDirectory();
    }

    public function currentJob(): ?array
    {
        $state = $this->read();
        $job = $state['job'] ?? null;

        return is_array($job) ? $job : null;
    }

    public function activeJob(): ?array
    {
        $job = $this->currentJob();

        if (! is_array($job)) {
            return null;
        }

        return in_array($job['status'] ?? null, ['pending', 'running'], true) ? $job : null;
    }

    public function saveJob(array $job): array
    {
        $this->write([
            'job' => $job,
            'updated_at' => gmdate(DATE_ATOM),
        ]);

        return $job;
    }

    public function clearCompletedJob(): bool
    {
        $job = $this->currentJob();

        if (! is_array($job)) {
            return true;
        }

        if (in_array($job['status'] ?? null, ['pending', 'running'], true)) {
            return false;
        }

        if (is_file($this->path)) {
            @unlink($this->path);
        }

        return true;
    }

    private function read(): array
    {
        if (! is_file($this->path)) {
            return [];
        }

        $contents = file_get_contents($this->path);

        if ($contents === false || trim($contents) === '') {
            return [];
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function write(array $state): void
    {
        file_put_contents(
            $this->path,
            json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }

    private function ensureDirectory(): void
    {
        $dir = dirname($this->path);

        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}
