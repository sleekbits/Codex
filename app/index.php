<?php
require_once __DIR__ . '/includes/header.php';
$module = $_GET['module'] ?? 'dashboard/home';
$module = preg_replace('#[^a-zA-Z0-9_\-/]#', '', $module);
$module = trim($module, '/');
$path = __DIR__ . '/modules/' . $module . '.php';
$modulesRoot = realpath(__DIR__ . '/modules');
$realPath = $path && file_exists($path) ? realpath($path) : false;

if ($realPath && $modulesRoot && str_starts_with($realPath, $modulesRoot)) {
    include $realPath;
} else {
    echo '<div class="alert alert-warning">Module not found.</div>';
}

require_once __DIR__ . '/includes/footer.php';
