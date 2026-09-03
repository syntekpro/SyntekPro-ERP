<?php

declare(strict_types=1);

final class CommandRunner
{
    public function __construct(
        private readonly Logger $logger,
        private readonly string $projectDir,
    ) {
    }

    public function run(string $command): array
    {
        $this->logger->info('Running command', ['cmd' => $this->maskSecrets($command)]);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes, $this->projectDir);

        if (! is_resource($process)) {
            throw new \RuntimeException('Failed to start command: ' . $command);
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        $stdout = $stdout === false ? '' : $stdout;
        $stderr = $stderr === false ? '' : $stderr;

        $this->logger->info('Command finished', [
            'cmd' => $this->maskSecrets($command),
            'exit_code' => $exitCode,
        ]);

        return [
            'exit_code' => $exitCode,
            'stdout' => trim($stdout),
            'stderr' => trim($stderr),
        ];
    }

    public function mustRun(string $command): void
    {
        $result = $this->run($command);

        if ($result['exit_code'] !== 0) {
            $this->logger->error('Command failed', [
                'cmd' => $this->maskSecrets($command),
                'exit_code' => $result['exit_code'],
                'stderr' => $result['stderr'],
            ]);

            throw new \RuntimeException(sprintf(
                "Command failed with exit code %d:\n%s",
                $result['exit_code'],
                $result['stderr']
            ));
        }
    }

    public function output(string $command): string
    {
        $result = $this->run($command);

        if ($result['exit_code'] !== 0) {
            throw new \RuntimeException($result['stderr']);
        }

        return $result['stdout'];
    }

    private function maskSecrets(string $command): string
    {
        return preg_replace('/(--password=)[^\s]+/i', '$1***', $command) ?? $command;
    }
}
