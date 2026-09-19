<?php

namespace App\Services\Updates;

/**
 * Persists the outcome of the latest update check to a small JSON file so
 * that non-web consumers (e.g. the scheduler, monitoring, support scripts)
 * can read the current update status without touching the database.
 *
 * Mirrors the style of docker/updater/UpdateStatusStore.php.
 */
class UpdateStatusStore
{
    public function __construct(private readonly string $path)
    {
        $this->ensureDirectory();
    }

    public function record(string $latestVersion, string $currentVersion): array
    {
        $status = [
            'latest_version' => $latestVersion,
            'current_version' => $currentVersion,
            'update_available' => version_compare($latestVersion, $currentVersion, '>'),
            'checked_at' => now()->toIso8601String(),
        ];

        $this->write($status);

        return $status;
    }

    public function current(): ?array
    {
        if (! is_file($this->path)) {
            return null;
        }

        $contents = file_get_contents($this->path);

        if ($contents === false || trim($contents) === '') {
            return null;
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function write(array $status): void
    {
        file_put_contents(
            $this->path,
            json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
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
