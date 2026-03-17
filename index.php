<?php
require_once __DIR__ . '/includes/functions.php';
if (is_logged_in()) {
    header('Location: /Codex/dashboard/index.php');
} else {
    header('Location: /Codex/auth/login.php');
}
exit;
