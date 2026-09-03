<?php

declare(strict_types=1);

final class Logger
{
    private string $path;

    public function __construct(string $path = '/project/storage/logs/updater.log')
    {
        $this->path = $path;
        $this->ensureDirectory();
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    private function write(string $level, string $message, array $context): void
    {
        $line = sprintf(
            "[%s] %s: %s%s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context === [] ? '' : ' ' . json_encode($this->sanitize($context))
        );

        file_put_contents($this->path, $line, FILE_APPEND | LOCK_EX);
    }

    private function sanitize(array $context): array
    {
        $redacted = [];

        foreach ($context as $key => $value) {
            if (is_string($key) && stripos($key, 'token') !== false) {
                $redacted[$key] = '***';
                continue;
            }

            if (is_array($value)) {
                $redacted[$key] = $this->sanitize($value);
                continue;
            }

            $redacted[$key] = $value;
        }

        return $redacted;
    }

    private function ensureDirectory(): void
    {
        $dir = dirname($this->path);

        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}
