<?php

declare(strict_types=1);

final class EnvEditor
{
    public function __construct(
        private readonly string $path,
    ) {
    }

    public function get(string $key, string $default = ''): string
    {
        if (! is_file($this->path)) {
            return $default;
        }

        $content = file_get_contents($this->path);
        if ($content === false) {
            return $default;
        }

        if (preg_match('/^' . preg_quote($key, '/') . '=(.*)$/m', $content, $matches)) {
            return trim($matches[1], " \t\n\r\"\'");
        }

        return $default;
    }

    public function set(string $key, string $value): void
    {
        if (! is_file($this->path)) {
            file_put_contents($this->path, "{$key}={$value}\n", LOCK_EX);

            return;
        }

        $content = file_get_contents($this->path);
        if ($content === false) {
            $content = '';
        }

        $pattern = '/^' . preg_quote($key, '/') . '=.*/m';
        $line = "{$key}={$value}";

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $line, $content);
        } else {
            $content .= "\n{$line}\n";
        }

        file_put_contents($this->path, $content, LOCK_EX);
    }
}
