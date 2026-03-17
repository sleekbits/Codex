<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (is_logged_in()) log_audit('logout', 'authentication', 'User logged out');
session_destroy();
header('Location: login.php');
exit;
