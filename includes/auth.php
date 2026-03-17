<?php
require_once __DIR__ . '/functions.php';

if (!is_logged_in()) {
    $_SESSION['flash_error'] = 'Please login to continue.';
    header('Location: /Codex/auth/login.php');
    exit;
}
