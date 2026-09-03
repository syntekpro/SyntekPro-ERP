<?php

declare(strict_types=1);

foreach (glob(__DIR__ . '/*.php') as $file) {
    if (basename($file) === 'server.php') {
        continue;
    }

    require $file;
}

$env = static function (string $key, mixed $default = ''): mixed {
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

    return $value === false || $value === null ? $default : $value;
};

if (PHP_SAPI === 'cli' && ($argv[1] ?? null) === '__run_job') {
    $server = new UpdaterServer(
        token: (string) $env('UPDATER_API_TOKEN', ''),
        currentVersion: (string) $env('SYNTEK_VERSION', 'unknown'),
        projectDir: (string) $env('UPDATER_PROJECT_DIR', '/project'),
        dryRun: filter_var($env('UPDATER_DRY_RUN', false), FILTER_VALIDATE_BOOL),
    );

    exit($server->runUpdateJob(
        jobId: (string) ($argv[2] ?? ''),
        version: (string) ($argv[3] ?? '')
    ));
}

$server = new UpdaterServer(
    token: (string) $env('UPDATER_API_TOKEN', ''),
    currentVersion: (string) $env('SYNTEK_VERSION', 'unknown'),
    projectDir: (string) $env('UPDATER_PROJECT_DIR', '/project'),
    dryRun: filter_var($env('UPDATER_DRY_RUN', false), FILTER_VALIDATE_BOOL),
);

$headers = function_exists('getallheaders') ? getallheaders() : [];

[$status, $data] = $server->handle(
    method: $_SERVER['REQUEST_METHOD'] ?? 'GET',
    uri: $_SERVER['REQUEST_URI'] ?? '/',
    authHeader: $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
        ?? $headers['Authorization']
        ?? $headers['authorization']
        ?? null,
    body: file_get_contents('php://input'),
);

http_response_code($status);
header('Content-Type: application/json');
echo json_encode($data);
