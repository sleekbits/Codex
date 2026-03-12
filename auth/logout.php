<?php
require_once __DIR__ . '/../includes/functions.php';
if (is_logged_in()) {
    log_activity(user()['id'], 'Logout', 'User logged out');
}
session_destroy();
header('Location: /Codex/auth/login.php');
exit;
