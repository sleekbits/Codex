<?php

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function e(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function flash(?string $key = null)
{
    if ($key === null) {
        return $_SESSION['flash'] ?? [];
    }
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function set_flash(string $key, string $msg): void
{
    $_SESSION['flash'][$key] = $msg;
}

function log_audit(string $action, string $module, string $description): void
{
    if (!isset($_SESSION['user']['id'])) return;
    $stmt = db()->prepare('INSERT INTO audit_logs (user_id, action, module_name, description, created_at) VALUES (?,?,?,?,NOW())');
    $stmt->execute([$_SESSION['user']['id'], $action, $module, $description]);
}

function app_setting(string $key, ?string $default = null): ?string
{
    static $cache = [];
    if (!isset($cache[$key])) {
        $stmt = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row['setting_value'] ?? $default;
    }
    return $cache[$key];
}
