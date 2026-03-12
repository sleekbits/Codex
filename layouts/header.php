<?php require_once __DIR__ . '/../includes/auth.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(app_setting('app_name', $env['app_name'])) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="/Codex/assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg topbar shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-semibold" href="/Codex/dashboard/index.php"><i class="bi bi-speedometer2 me-2"></i><?= e(app_setting('app_name', $env['app_name'])) ?></a>
        <div class="ms-auto d-flex align-items-center gap-2 text-on-dark small">
            <span class="d-none d-md-inline"><i class="bi bi-person-circle me-1"></i><?= e(user()['full_name']) ?></span>
            <span class="badge rounded-pill bg-light text-dark"><?= e(user()['role_name']) ?></span>
            <a class="btn btn-sm btn-outline-light" href="/Codex/auth/logout.php" title="Logout"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
