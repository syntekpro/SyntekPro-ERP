<?php

namespace App\Services\Updates;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateAgentClient
{
    public function isReachable(): bool
    {
        try {
            $response = $this->request()
                ->get($this->url('/health'));

            return $response->successful();
        } catch (ConnectionException) {
            return false;
        }
    }

    public function status(): ?array
    {
        return $this->send('GET', '/status');
    }

    public function requestUpdate(string $version): ?array
    {
        return $this->send('POST', '/update', [
            'version' => $version,
        ], timeoutSeconds: 15);
    }

    public function clearStatus(): ?array
    {
        return $this->send('DELETE', '/status');
    }

    protected function send(string $method, string $path, array $payload = null, int $timeoutSeconds = 10): ?array
    {
        try {
            $request = $this->request($timeoutSeconds);

            $response = $payload !== null
                ? $request->{$method}($this->url($path), $payload)
                : $request->{$method}($this->url($path));

            return [
                'status' => $response->status(),
                'data' => $response->json(),
            ];
        } catch (ConnectionException $exception) {
            Log::warning('Syntek update agent is unreachable.', [
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);

            return null;
        } catch (\Throwable $exception) {
            Log::warning('Syntek update agent request failed.', [
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    protected function request(int $timeoutSeconds = 10): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout($timeoutSeconds)
            ->connectTimeout(5)
            ->retry(2, 100)
            ->withToken($this->token());
    }

    protected function url(string $path): string
    {
        return rtrim($this->baseUrl(), '/') . '/' . ltrim($path, '/');
    }

    protected function baseUrl(): string
    {
        return (string) config('syntek.updater.url');
    }

    protected function token(): string
    {
        return (string) config('syntek.updater.token');
    }
}
