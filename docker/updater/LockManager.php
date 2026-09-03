<?php

declare(strict_types=1);

final class LockManager
{
    private string $path;
    /** @var resource|null */
    private $handle = null;

    public function __construct(string $path = '/project/storage/framework/updater.lock')
    {
        $this->path = $path;
        $this->ensureDirectory();
    }

    public function acquire(): bool
    {
        $handle = fopen($this->path, 'c+');

        if ($handle === false) {
            return false;
        }

        if (! flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);

            return false;
        }

        $this->handle = $handle;
        ftruncate($handle, 0);
        fwrite($handle, (string) getmypid());
        fflush($handle);

        return true;
    }

    public function release(): void
    {
        if (! is_resource($this->handle)) {
            return;
        }

        $handle = $this->handle;
        $this->handle = null;

        @unlink($this->path);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    private function ensureDirectory(): void
    {
        $dir = dirname($this->path);

        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}
