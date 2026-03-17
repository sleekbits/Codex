<?php

$config = require __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    global $config;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $db = $config['db'];
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $db['host'],
        $db['port'],
        $db['name'],
        $db['charset']
    );

    try {
        $pdo = new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable $e) {
        error_log('Database connection error: ' . $e->getMessage());
        http_response_code(500);
        if (!headers_sent()) {
            header('Content-Type: text/plain; charset=utf-8');
        }
        echo ($config['debug'] ?? false)
            ? 'Database connection failed: ' . $e->getMessage()
            : 'Application database connection failed. Please verify DB credentials in config.';
        exit;
    }

    return $pdo;
}
