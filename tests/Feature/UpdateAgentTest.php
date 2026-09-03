<?php

namespace Tests\Feature;

use Symfony\Component\Process\Process;
use Tests\TestCase;

class UpdateAgentTest extends TestCase
{
    private ?Process $process = null;
    private string $token;
    private string $statusPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = 'test-token-' . random_int(1000, 9999);
        $this->statusPath = base_path('storage/framework/updater-status.json');

        if (is_file($this->statusPath)) {
            @unlink($this->statusPath);
        }
    }

    protected function tearDown(): void
    {
        if ($this->process !== null) {
            $this->process->stop();
        }

        if (is_file($this->statusPath)) {
            @unlink($this->statusPath);
        }

        parent::tearDown();
    }

    private function startUpdaterServer(): void
    {
        $phpBinary = PHP_BINARY;

        $this->process = new Process([
            $phpBinary,
            '-S',
            '127.0.0.1:19888',
            base_path('docker/updater/server.php'),
        ], base_path('docker/updater'), [
            'UPDATER_API_TOKEN' => $this->token,
            'UPDATER_DRY_RUN' => 'true',
            'UPDATER_PROJECT_DIR' => base_path(),
            'SYNTEK_VERSION' => '1.0.0',
            'SYNTEK_IMAGE_TAG' => 'latest',
        ]);
        $this->process->start();

        $start = microtime(true);
        while (microtime(true) - $start < 5) {
            $sock = @fsockopen('127.0.0.1', 19888);
            if ($sock) {
                fclose($sock);
                break;
            }
            usleep(50000);
        }
    }

    private function updaterRequest(string $method, string $path, ?string $token = null): array
    {
        $ch = curl_init("http://127.0.0.1:19888{$path}");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = [];
        if ($token) {
            $headers[] = "Authorization: Bearer {$token}";
        }

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['version' => '1.0.1']));
            $headers[] = 'Content-Type: application/json';
        }

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'code' => $code,
            'body' => $body,
            'json' => json_decode($body, true),
        ];
    }

    private function waitForJobStatus(string $status, int $timeoutSeconds = 5): array
    {
        $start = microtime(true);

        do {
            $response = $this->updaterRequest('GET', '/status', $this->token);
            $jobStatus = $response['json']['job']['status'] ?? null;

            if ($jobStatus === $status) {
                return $response;
            }

            usleep(100000);
        } while (microtime(true) - $start < $timeoutSeconds);

        return $response ?? $this->updaterRequest('GET', '/status', $this->token);
    }

    public function test_health_endpoint_is_public_and_reports_ok(): void
    {
        $this->startUpdaterServer();

        $response = $this->updaterRequest('GET', '/health');

        $this->assertSame(200, $response['code']);
        $this->assertSame('ok', $response['json']['status'] ?? null);
    }

    public function test_status_endpoint_requires_valid_token(): void
    {
        $this->startUpdaterServer();

        $without = $this->updaterRequest('GET', '/status');
        $this->assertSame(401, $without['code']);

        $bad = $this->updaterRequest('GET', '/status', 'wrong-token');
        $this->assertSame(401, $bad['code']);

        $valid = $this->updaterRequest('GET', '/status', $this->token);
        $this->assertSame(200, $valid['code']);
        $this->assertSame('1.0.0', $valid['json']['version'] ?? null);
    }

    public function test_update_endpoint_queues_background_dry_run_job_and_exposes_status(): void
    {
        $this->startUpdaterServer();

        $without = $this->updaterRequest('POST', '/update');
        $this->assertSame(401, $without['code']);

        $valid = $this->updaterRequest('POST', '/update', $this->token);
        $this->assertSame(202, $valid['code']);
        $this->assertTrue($valid['json']['ok'] ?? false);
        $this->assertTrue($valid['json']['job']['dry_run'] ?? false);
        $this->assertSame('pending', $valid['json']['job']['status'] ?? null);
        $this->assertSame('1.0.1', $valid['json']['job']['requested_version'] ?? null);

        $status = $this->waitForJobStatus('success');

        $this->assertSame(200, $status['code']);
        $this->assertTrue($status['json']['job']['dry_run'] ?? false);
        $this->assertSame('success', $status['json']['job']['status'] ?? null);
        $this->assertSame('1.0.1', $status['json']['job']['version'] ?? null);
        $this->assertStringContainsString('would update', $status['json']['job']['message'] ?? '');
    }

    public function test_completed_job_status_can_be_cleared(): void
    {
        $this->startUpdaterServer();

        $this->updaterRequest('POST', '/update', $this->token);
        $this->waitForJobStatus('success');

        $cleared = $this->updaterRequest('DELETE', '/status', $this->token);

        $this->assertSame(200, $cleared['code']);
        $this->assertTrue($cleared['json']['ok'] ?? false);

        $status = $this->updaterRequest('GET', '/status', $this->token);

        $this->assertSame(200, $status['code']);
        $this->assertNull($status['json']['job'] ?? null);
    }
}
