<?php

namespace App\Services\Updates;

use App\Models\SystemUpdate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateManager
{
    public const CACHE_KEY = 'syntek:latest-release';

    public function __construct(protected UpdateAgentClient $agent)
    {
    }

    public const CACHE_TTL_SECONDS = 3600;

    public function installedVersion(): string
    {
        return (string) config('syntek.version');
    }

    public function latest(?bool $forceRefresh = null): ?SystemUpdate
    {
        $shouldRefresh = $forceRefresh === true || ! $this->hasRecentCheck();

        if ($shouldRefresh) {
            $latest = $this->check(forceRefresh: $forceRefresh === true);

            if ($latest !== null) {
                return $latest;
            }
        }

        return $this->latestPersistedRelease();
    }

    public function check(bool $forceRefresh = false): ?SystemUpdate
    {
        $release = $this->fetchLatestRelease($forceRefresh);

        if ($release === null) {
            return null;
        }

        return $this->persistRelease($release);
    }

    public function isUpdateAvailable(?SystemUpdate $latest = null): bool
    {
        $latest ??= $this->latest(false);

        if ($latest === null) {
            return false;
        }

        return version_compare($latest->version, $this->installedVersion(), '>');
    }

    public function lastCheckAt(): ?\DateTimeInterface
    {
        return $this->latestPersistedRelease()?->checked_at;
    }

    public function agentStatus(): ?array
    {
        return $this->agent->status();
    }

    public function agentIsReachable(): bool
    {
        return $this->agent->isReachable();
    }

    public function requestUpdate(string $version): ?array
    {
        return $this->agent->requestUpdate($version);
    }

    public function clearUpdateStatus(): ?array
    {
        return $this->agent->clearStatus();
    }

    protected function hasRecentCheck(): bool
    {
        return Cache::has($this->cacheKey());
    }

    protected function fetchLatestRelease(bool $forceRefresh = false): ?array
    {
        $cacheKey = $this->cacheKey();
        $cached = $forceRefresh ? null : Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $release = $this->releaseChannel() === 'beta'
                ? $this->fetchLatestBetaRelease()
                : $this->fetchLatestStableRelease();

            if ($release === null) {
                return null;
            }

            Cache::put($cacheKey, $release, self::CACHE_TTL_SECONDS);

            return $release;
        } catch (\Throwable $exception) {
            Log::warning('Syntek update check failed.', [
                'message' => $exception->getMessage(),
                'channel' => $this->releaseChannel(),
            ]);

            return null;
        }
    }

    protected function persistRelease(array $release): SystemUpdate
    {
        $version = ltrim((string) $release['tag_name'], 'v');
        $name = $release['name'] ?? null;
        $notes = $release['body'] ?? null;
        $releasedAt = $release['published_at'] ?? null;
        $isPrerelease = (bool) ($release['prerelease'] ?? false);

        $update = SystemUpdate::query()->updateOrCreate(
            ['version' => $version],
            [
                'name' => is_string($name) ? $name : null,
                'notes' => is_string($notes) ? $notes : null,
                'released_at' => $releasedAt,
                'checked_at' => now(),
                'is_prerelease' => $isPrerelease,
                'status' => 'available',
            ]
        );

        return $update;
    }

    protected function latestPersistedRelease(): ?SystemUpdate
    {
        return SystemUpdate::query()
            ->where('status', 'available')
            ->when(
                $this->releaseChannel() === 'stable',
                fn ($query) => $query->where('is_prerelease', false)
            )
            ->orderByDesc('checked_at')
            ->orderByDesc('released_at')
            ->orderByDesc('id')
            ->first();
    }

    protected function fetchLatestStableRelease(): ?array
    {
        $response = $this->githubRequest()->get($this->githubApiUrl('/releases/latest'));

        if (! $response->successful()) {
            Log::warning('Syntek update check returned a non-successful response.', [
                'status' => $response->status(),
                'channel' => 'stable',
            ]);

            return null;
        }

        return $this->normalizeReleasePayload($response->json(), allowPrerelease: false);
    }

    protected function fetchLatestBetaRelease(): ?array
    {
        $response = $this->githubRequest()->get($this->githubApiUrl('/releases'), [
            'per_page' => 20,
        ]);

        if (! $response->successful()) {
            Log::warning('Syntek beta update check returned a non-successful response.', [
                'status' => $response->status(),
                'channel' => 'beta',
            ]);

            return null;
        }

        $releases = $response->json();

        if (! is_array($releases)) {
            return null;
        }

        foreach ($releases as $release) {
            $candidate = $this->normalizeReleasePayload($release, allowPrerelease: true);

            if ($candidate !== null) {
                return $candidate;
            }
        }

        return null;
    }

    protected function normalizeReleasePayload(mixed $payload, bool $allowPrerelease): ?array
    {
        if (! is_array($payload) || empty($payload['tag_name']) || ! is_string($payload['tag_name'])) {
            return null;
        }

        if ((bool) ($payload['draft'] ?? false)) {
            return null;
        }

        if (! $allowPrerelease && (bool) ($payload['prerelease'] ?? false)) {
            return null;
        }

        return $payload;
    }

    protected function githubRequest(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::timeout(10)
            ->withHeaders([
                'Accept' => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => '2022-11-28',
            ]);
    }

    protected function githubApiUrl(string $path): string
    {
        $owner = config('syntek.github.owner');
        $repo = config('syntek.github.repo');

        return "https://api.github.com/repos/{$owner}/{$repo}{$path}";
    }

    protected function cacheKey(): string
    {
        return self::CACHE_KEY . ':' . $this->releaseChannel();
    }

    protected function releaseChannel(): string
    {
        return config('syntek.release_channel') === 'beta' ? 'beta' : 'stable';
    }
}
