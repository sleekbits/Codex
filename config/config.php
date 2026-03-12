<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Dubai');

$env = [
    'db_host' => 'sql104.ezyro.com',
    'db_name' => 'ezyro_41363280_codex',
    'db_user' => 'ezyro_41363280',
    'db_pass' => 'baf55bc17e1',
    'app_name' => 'Asteco Procurement Dashboard',
    'base_url' => '/Codex'
];

try {
    $dsn = "mysql:host={$env['db_host']};dbname={$env['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $env['db_user'], $env['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}
