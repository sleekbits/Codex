<?php

$env = static function (string $key, ?string $default = null): ?string {
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
};

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/Codex/app/index.php';
$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$basePath = preg_replace('#/app$#', '', $basePath) ?: '';

return [
    'app_name' => $env('APP_NAME', 'Asteco Procurement ERP'),
    'env' => $env('APP_ENV', 'production'),
    'debug' => $env('APP_DEBUG', '0') === '1',
    'base_path' => $env('APP_BASE_PATH', $basePath),
    'base_url' => rtrim((string)$env('APP_BASE_URL', ''), '/'),
    'db' => [
        'host' => $env('DB_HOST', 'localhost'),
        'port' => $env('DB_PORT', '3306'),
        'name' => $env('DB_NAME', 'ezyro_41363280_codex'),
        'user' => $env('DB_USER', ''),
        'pass' => $env('DB_PASS', ''),
        'charset' => $env('DB_CHARSET', 'utf8mb4'),
    ],
    'default_currency' => $env('APP_CURRENCY', 'AED'),
    'records_per_page' => (int)$env('APP_RECORDS_PER_PAGE', '10'),
    'session_name' => $env('SESSION_NAME', 'codex_erp_session'),
];
