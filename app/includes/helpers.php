<?php

function base_path(): string
{
    global $config;
    $path = rtrim((string)($config['base_path'] ?? ''), '/');
    return $path === '/' ? '' : $path;
}

function base_url(): string
{
    global $config;
    return (string)($config['base_url'] ?? '');
}

function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    if (base_url() !== '') {
        return rtrim(base_url(), '/') . '/' . $path;
    }
    return rtrim(base_path(), '/') . '/' . $path;
}

function app_url(string $path = ''): string
{
    return url('app/' . ltrim($path, '/'));
}

function redirect(string $path): void
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : app_url($path)));
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function e(?string $text): string
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
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
    if (!isset($_SESSION['user']['id'])) {
        return;
    }

    try {
        $stmt = db()->prepare('INSERT INTO audit_logs (user_id, action, module_name, description, created_at) VALUES (?,?,?,?,NOW())');
        $stmt->execute([$_SESSION['user']['id'], $action, $module, $description]);
    } catch (Throwable $e) {
        error_log('Audit log insert failed: ' . $e->getMessage());
    }
}

function app_setting(string $key, ?string $default = null): ?string
{
    static $cache = [];
    if (!array_key_exists($key, $cache)) {
        try {
            $stmt = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
            $stmt->execute([$key]);
            $row = $stmt->fetch();
            $cache[$key] = $row['setting_value'] ?? $default;
        } catch (Throwable $e) {
            $cache[$key] = $default;
        }
    }
    return $cache[$key];
}


function send_email(string $to, string $subject, string $message): bool
{
    global $config;
    $fromEmail = $config['mail']['from_email'] ?? 'no-reply@localhost';
    $fromName = $config['mail']['from_name'] ?? 'Asteco Procurement ERP';
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/plain; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
    ];

    return @mail($to, $subject, $message, implode("\r\n", $headers));
}

function generate_otp_code(): string
{
    return (string) random_int(100000, 999999);
}
