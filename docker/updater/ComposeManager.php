<?php

declare(strict_types=1);

final class ComposeManager
{
    public function __construct(
        public readonly CommandRunner $runner,
        private readonly EnvEditor $env,
        private readonly Logger $logger,
    ) {
    }

    public function currentImageTag(): string
    {
        return $this->env->get('SYNTEK_IMAGE_TAG', 'latest');
    }

    public function setImageTag(string $tag): void
    {
        $this->env->set('SYNTEK_IMAGE_TAG', $tag);
    }

    public function setVersion(string $version): void
    {
        $this->env->set('SYNTEK_VERSION', $version);
    }

    public function pullImage(string $version): void
    {
        $image = $this->imageName($version);
        $this->runner->mustRun("docker pull {$image}");
    }

    public function imageExists(string $version): bool
    {
        $image = $this->imageName($version);

        try {
            $this->runner->mustRun("docker manifest inspect {$image}");

            return true;
        } catch (\Throwable $exception) {
            $this->logger->warning('Image manifest check failed', [
                'image' => $image,
                'message' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function recreateApp(): void
    {
        $this->runner->mustRun('docker compose -f docker-compose.prod.yml up -d app');
    }

    public function runInApp(string $command): void
    {
        $this->runner->mustRun("docker compose -f docker-compose.prod.yml exec -T app {$command}");
    }

    public function copyFromApp(string $containerPath, string $hostPath): void
    {
        $this->runner->mustRun("docker compose -f docker-compose.prod.yml cp app:{$containerPath} {$hostPath}");
    }

    public function imageName(string $version): string
    {
        return "ghcr.io/syntekpro/syntekpro-erp:{$version}";
    }
}
