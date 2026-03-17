<?php

$config = require __DIR__ . '/../config/config.php';

if (!empty($config['session_name'])) {
    session_name($config['session_name']);
}

$cookiePath = ($config['base_path'] ?? '') . '/';
$cookiePath = preg_replace('#//+#', '/', $cookiePath);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => $cookiePath,
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (($config['debug'] ?? false) === true) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';
