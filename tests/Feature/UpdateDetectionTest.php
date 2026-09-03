<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Updates\UpdateAgentClient;
use App\Services\Updates\UpdateManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class UpdateDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_update_manager_reports_no_update_when_running_latest_version(): void
    {
        config()->set('syntek.version', '1.0.0');

        $this->fakeGitHubRelease('1.0.0', 'v1.0.0', '2026-09-01T10:00:00Z');

        $manager = app(UpdateManager::class);
        $latest = $manager->latest();

        $this->assertSame('1.0.0', $latest->version);
        $this->assertFalse($manager->isUpdateAvailable($latest));
    }

    public function test_update_manager_reports_update_available_when_newer_release_exists(): void
    {
        config()->set('syntek.version', '1.0.0');

        $this->fakeGitHubRelease('1.1.0', 'v1.1.0', '2026-09-02T10:00:00Z');

        $manager = app(UpdateManager::class);
        $latest = $manager->latest();

        $this->assertSame('1.1.0', $latest->version);
        $this->assertTrue($manager->isUpdateAvailable($latest));
    }

    public function test_update_manager_handles_github_api_failure_gracefully(): void
    {
        config()->set('syntek.version', '1.0.0');

        Http::fake([
            'api.github.com/*' => Http::response(null, 500),
        ]);

        $manager = app(UpdateManager::class);

        $this->assertNull($manager->latest());
        $this->assertFalse($manager->isUpdateAvailable());
    }

    public function test_update_manager_uses_cached_response_and_avoids_repeated_api_calls(): void
    {
        config()->set('syntek.version', '1.0.0');

        $this->fakeGitHubRelease('1.1.0', 'v1.1.0', '2026-09-02T10:00:00Z');

        $manager = app(UpdateManager::class);
        $manager->latest();

        Http::fake([
            'api.github.com/*' => Http::response(null, 500),
        ]);

        $cached = $manager->latest();

        $this->assertSame('1.1.0', $cached?->version);
    }

    public function test_update_manager_force_refresh_bypasses_cached_release_response(): void
    {
        config()->set('syntek.version', '1.0.0');

        Http::fake([
            'api.github.com/repos/syntekpro/SyntekPro-ERP/releases/latest' => Http::sequence()
                ->push([
                    'tag_name' => 'v1.1.0',
                    'name' => 'SyntekPro ERP 1.1.0',
                    'body' => 'Release notes for 1.1.0',
                    'published_at' => '2026-09-02T10:00:00Z',
                ])
                ->push([
                    'tag_name' => 'v1.2.0',
                    'name' => 'SyntekPro ERP 1.2.0',
                    'body' => 'Release notes for 1.2.0',
                    'published_at' => '2026-09-03T10:00:00Z',
                ]),
        ]);

        $manager = app(UpdateManager::class);

        $this->assertSame('1.1.0', $manager->latest()?->version);
        $this->assertSame('1.2.0', $manager->latest(forceRefresh: true)?->version);
    }

    public function test_beta_release_channel_can_surface_prereleases(): void
    {
        config()->set('syntek.version', '1.0.0');
        config()->set('syntek.release_channel', 'beta');

        Http::fake([
            'api.github.com/repos/syntekpro/SyntekPro-ERP/releases*' => Http::response([
                [
                    'tag_name' => 'v1.2.0-beta.1',
                    'name' => 'SyntekPro ERP 1.2.0 Beta 1',
                    'body' => 'Beta release notes',
                    'published_at' => '2026-09-03T10:00:00Z',
                    'prerelease' => true,
                    'draft' => false,
                ],
                [
                    'tag_name' => 'v1.1.0',
                    'name' => 'SyntekPro ERP 1.1.0',
                    'body' => 'Stable release notes',
                    'published_at' => '2026-09-02T10:00:00Z',
                    'prerelease' => false,
                    'draft' => false,
                ],
            ]),
        ]);

        $manager = app(UpdateManager::class);
        $latest = $manager->latest();

        $this->assertSame('1.2.0-beta.1', $latest?->version);
        $this->assertTrue($latest?->is_prerelease);
        $this->assertTrue($manager->isUpdateAvailable($latest));
    }

    public function test_artisan_command_reports_update_available(): void
    {
        config()->set('syntek.version', '1.0.0');

        $this->fakeGitHubRelease('1.1.0', 'v1.1.0', '2026-09-02T10:00:00Z');

        $this->artisan('syntek:check-updates')
            ->assertSuccessful()
            ->expectsOutputToContain('1.1.0')
            ->expectsOutputToContain('update is available');
    }

    public function test_artisan_force_option_bypasses_cached_release_response(): void
    {
        config()->set('syntek.version', '1.0.0');

        Http::fake([
            'api.github.com/repos/syntekpro/SyntekPro-ERP/releases/latest' => Http::sequence()
                ->push([
                    'tag_name' => 'v1.1.0',
                    'name' => 'SyntekPro ERP 1.1.0',
                    'body' => 'Release notes for 1.1.0',
                    'published_at' => '2026-09-02T10:00:00Z',
                ])
                ->push([
                    'tag_name' => 'v1.2.0',
                    'name' => 'SyntekPro ERP 1.2.0',
                    'body' => 'Release notes for 1.2.0',
                    'published_at' => '2026-09-03T10:00:00Z',
                ]),
        ]);

        $this->artisan('syntek:check-updates')
            ->assertSuccessful()
            ->expectsOutputToContain('1.1.0');

        $this->artisan('syntek:check-updates --force')
            ->assertSuccessful()
            ->expectsOutputToContain('1.2.0');
    }

    public function test_settings_updates_tab_displays_version_information(): void
    {
        config()->set('syntek.version', '1.0.0');

        $this->fakeGitHubRelease('1.1.0', 'v1.1.0', '2026-09-02T10:00:00Z');

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Settings\SettingsPage::class)
            ->set('tab', 'updates')
            ->assertSee('1.0.0')
            ->assertSee('1.1.0')
            ->assertSee('Update available');
    }

    public function test_settings_updates_tab_restores_completed_background_job_status_after_reconnect(): void
    {
        config()->set('syntek.version', '1.1.0');

        $this->fakeGitHubRelease('1.1.0', 'v1.1.0', '2026-09-02T10:00:00Z');

        app()->instance(UpdateAgentClient::class, new class extends UpdateAgentClient
        {
            public function status(): ?array
            {
                return [
                    'status' => 200,
                    'data' => [
                        'ok' => true,
                        'job' => [
                            'id' => 'job-1',
                            'status' => 'success',
                            'requested_version' => '1.1.0',
                            'version' => '1.1.0',
                            'message' => 'SyntekPro ERP has been updated to 1.1.0.',
                        ],
                    ],
                ];
            }

            public function clearStatus(): ?array
            {
                return ['status' => 200, 'data' => ['ok' => true]];
            }

            public function isReachable(): bool
            {
                return true;
            }

            public function requestUpdate(string $version): ?array
            {
                return null;
            }
        });

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(\App\Livewire\Settings\SettingsPage::class)
            ->set('tab', 'updates')
            ->assertSee('Update complete')
            ->assertSee('1.1.0');
    }

    public function test_non_admin_cannot_access_settings_updates_tab(): void
    {
        $cashier = User::factory()->create(['role' => UserRole::Cashier]);

        Livewire::actingAs($cashier)
            ->test(\App\Livewire\Settings\SettingsPage::class)
            ->assertStatus(403);
    }

    public function test_header_shows_update_notification_for_authorized_user(): void
    {
        config()->set('syntek.version', '1.0.0');

        $this->fakeGitHubRelease('1.1.0', 'v1.1.0', '2026-09-02T10:00:00Z');

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('ERP update available')
            ->assertSee('1.1.0');
    }

    public function test_header_hides_update_notification_from_unauthorized_user(): void
    {
        config()->set('syntek.version', '1.0.0');

        $this->fakeGitHubRelease('1.1.0', 'v1.1.0', '2026-09-02T10:00:00Z');

        $cashier = User::factory()->create(['role' => UserRole::Cashier]);

        $this->actingAs($cashier)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('ERP update available');
    }

    protected function fakeGitHubRelease(string $version, string $tag, string $publishedAt): void
    {
        Http::fake([
            'api.github.com/repos/syntekpro/SyntekPro-ERP/releases/latest' => Http::response([
                'tag_name' => $tag,
                'name' => "SyntekPro ERP {$version}",
                'body' => "Release notes for {$version}",
                'published_at' => $publishedAt,
            ]),
        ]);
    }
}
