<?php

declare(strict_types=1);

final class HealthChecker
{
    public function __construct(
        private readonly Logger $logger,
        private readonly string $webUrl,
    ) {
    }

    public function waitForHealthy(int $timeoutSeconds = 120, int $intervalSeconds = 5): bool
    {
        $start = time();

        while (time() - $start < $timeoutSeconds) {
            if ($this->isHealthy()) {
                return true;
            }

            sleep($intervalSeconds);
        }

        return $this->isHealthy();
    }

    public function isHealthy(): bool
    {
        $ch = curl_init($this->webUrl);

        if ($ch === false) {
            return false;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        $this->logger->info('Health check', ['url' => $this->webUrl, 'code' => $code, 'error' => $error]);

        return $code >= 200 && $code < 400;
    }
}
