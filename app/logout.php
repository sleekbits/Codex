<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (is_logged_in()) {
    log_audit('logout', 'authentication', 'User logged out');
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
}
session_destroy();
redirect('login.php');
