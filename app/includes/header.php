<?php require_once __DIR__ . '/bootstrap.php'; require_login(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(app_setting('app_name', $config['app_name'])) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="d-flex" id="wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div id="page-content-wrapper" class="w-100">
        <nav class="navbar navbar-expand-lg topbar px-3">
            <button class="btn btn-outline-light btn-sm" id="sidebarToggle"><i class="bi bi-list"></i></button>
            <div class="ms-auto dropdown">
                <a class="btn btn-sm btn-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <?= e($_SESSION['user']['name']) ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="index.php?module=admin/profile"><i class="bi bi-person"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </nav>
        <main class="container-fluid p-4">
