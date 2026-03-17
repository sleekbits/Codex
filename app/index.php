<?php
require_once __DIR__ . '/includes/header.php';
$module = $_GET['module'] ?? 'dashboard/home';
$path = __DIR__ . '/modules/' . $module . '.php';
if (file_exists($path)) {
    include $path;
} else {
    echo '<div class="alert alert-warning">Module not found.</div>';
}
require_once __DIR__ . '/includes/footer.php';
