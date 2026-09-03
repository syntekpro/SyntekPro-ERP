<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class ProductVersioningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workflowPath = base_path('.github/workflows/release.yml');
        $this->dockerfilePath = base_path('Dockerfile');
    }

    public function test_syntek_config_provides_default_version(): void
    {
        $this->assertSame('1.0.0', config('syntek.version'));
    }

    public function test_syntek_config_is_accessible_via_helper(): void
    {
        $this->assertSame('SyntekPro ERP', config('syntek.name'));
        $this->assertSame('stable', config('syntek.release_channel'));
        $this->assertSame('syntekpro', config('syntek.github.owner'));
        $this->assertSame('SyntekPro-ERP', config('syntek.github.repo'));
    }

    public function test_hub_layout_renders_product_version_in_footer(): void
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee(config('syntek.version'));
    }

    public function test_product_version_can_be_overridden_via_environment(): void
    {
        $this->withEnvironment(['SYNTEK_VERSION' => '1.2.3-beta']);

        $this->assertSame('1.2.3-beta', config('syntek.version'));
    }

    public function test_release_workflow_exists_and_triggers_on_semver_tags(): void
    {
        $this->assertFileExists($this->workflowPath);

        $workflow = Yaml::parseFile($this->workflowPath);

        $this->assertSame('Release Docker Image', $workflow['name']);
        $this->assertContains('v*.*.*', $workflow['on']['push']['tags']);
        $this->assertSame('write', $workflow['permissions']['contents']);
        $this->assertSame('write', $workflow['permissions']['packages']);
    }

    public function test_release_workflow_publishes_to_ghcr_with_version_and_latest_tags(): void
    {
        $workflow = Yaml::parseFile($this->workflowPath);
        $metadataStep = collect($workflow['jobs']['build-and-push']['steps'])
            ->first(fn (array $step): bool => ($step['id'] ?? '') === 'meta');

        $this->assertNotNull($metadataStep, 'Metadata step not found.');
        $this->assertStringContainsString('${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}', $metadataStep['with']['images']);

        $tags = $metadataStep['with']['tags'];
        $this->assertStringContainsString('type=raw,value=${{ steps.version.outputs.version }}', $tags);
        $this->assertStringContainsString('type=raw,value=latest', $tags);

        $buildStep = collect($workflow['jobs']['build-and-push']['steps'])
            ->first(fn (array $step): bool => ($step['id'] ?? '') === 'push');

        $this->assertNotNull($buildStep, 'Build/push step not found.');
        $this->assertTrue($buildStep['with']['push']);
        $this->assertStringContainsString('APP_VERSION=${{ steps.version.outputs.version }}', $buildStep['with']['build-args']);
    }

    public function test_release_workflow_creates_github_releases_with_prerelease_detection(): void
    {
        $workflow = Yaml::parseFile($this->workflowPath);
        $releaseStep = collect($workflow['jobs']['build-and-push']['steps'])
            ->first(fn (array $step): bool => ($step['id'] ?? '') === 'release');

        $this->assertNotNull($releaseStep, 'GitHub release step not found.');
        $this->assertSame('softprops/action-gh-release@v2', $releaseStep['uses']);
        $this->assertTrue($releaseStep['with']['generate_release_notes']);
        $this->assertSame('${{ contains(github.ref_name, \'-\') }}', $releaseStep['with']['prerelease']);
    }

    public function test_dockerfile_declares_version_build_argument_and_labels(): void
    {
        $dockerfile = file_get_contents($this->dockerfilePath);

        $this->assertStringContainsString('ARG APP_VERSION=unknown', $dockerfile);
        $this->assertStringContainsString('ENV SYNTEK_VERSION=${APP_VERSION}', $dockerfile);
        $this->assertStringContainsString('org.opencontainers.image.version="${APP_VERSION}"', $dockerfile);
        $this->assertStringContainsString('org.opencontainers.image.source="https://github.com/syntekpro/SyntekPro-ERP"', $dockerfile);
    }

    protected function withEnvironment(array $values): void
    {
        foreach ($values as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        $this->refreshApplication();
    }
}
