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

        $update = $this->persistRelease($release);

        $this->writeStatusFile($update);

        return $update;
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
        $checkedAt = SystemUpdate::query()->max('checked_at');

        return $checkedAt === null ? null : \Illuminate\Support\Carbon::parse($checkedAt);
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

    /**
     * Mirror the check result to storage/framework/update-check-status.json
     * so tooling outside the web/DB context (e.g. the scheduler) can read it.
     */
    protected function writeStatusFile(SystemUpdate $update): void
    {
        try {
            (new UpdateStatusStore(storage_path('framework/update-check-status.json')))
                ->record($update->version, $this->installedVersion());
        } catch (\Throwable $exception) {
            Log::warning('Failed to write the Syntek update status file.', [
                'message' => $exception->getMessage(),
            ]);
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

        if ($response->status() === 404) {
            return $this->fetchLatestReleaseFromTags();
        }

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

        if ($response->status() === 404) {
            return $this->fetchLatestReleaseFromTags();
        }

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

    /**
     * The releases API 404s for an unauthenticated caller when the repository
     * is private. Fall back to the tags API using UPDATE_CHECK_GITHUB_TOKEN.
     * If no token is configured, skip the check gracefully instead of failing.
     */
    protected function fetchLatestReleaseFromTags(): ?array
    {
        $token = (string) config('syntek.update_check_token');

        if ($token === '') {
            Log::warning('Syntek update check skipped: the releases API is inaccessible (private repository) and no UPDATE_CHECK_GITHUB_TOKEN is configured.');

            return null;
        }

        $response = Http::timeout(10)
            ->withToken($token)
            ->withHeaders([
                'Accept' => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => '2022-11-28',
            ])
            ->get($this->githubApiUrl('/tags'), ['per_page' => 1]);

        if (! $response->successful()) {
            Log::warning('Syntek update check via the tags API returned a non-successful response.', [
                'status' => $response->status(),
            ]);

            return null;
        }

        $tags = $response->json();

        if (! is_array($tags) || empty($tags[0]['name']) || ! is_string($tags[0]['name'])) {
            return null;
        }

        return [
            'tag_name' => $tags[0]['name'],
            'name' => null,
            'body' => null,
            'published_at' => null,
            'prerelease' => false,
            'draft' => false,
        ];
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
