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
        <div class="ms-auto dropdown">
            <button class="btn btn-sm btn-outline-light dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i>
                <span class="d-none d-md-inline"><?= e(user()['full_name']) ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end profile-dropdown p-0 overflow-hidden">
                <div class="p-3 border-bottom bg-light-subtle">
                    <div class="d-flex align-items-start gap-2">
                        <?php if (!empty(user()['profile_image'])): ?><img src="/Codex/<?= e(user()['profile_image']) ?>" class="avatar-circle" style="object-fit:cover;"><?php else: ?><div class="avatar-circle"><i class="bi bi-person"></i></div><?php endif; ?>
                        <div>
                            <div class="fw-bold text-dark"><?= e(user()['full_name']) ?></div>
                            <div class="small text-muted-custom"><?= e(user()['role_name']) ?></div>
                        </div>
                    </div>
                    <div class="small mt-2"><i class="bi bi-envelope me-1"></i><?= e(user()['email'] ?? 'Not set') ?></div>
                    <div class="small"><i class="bi bi-briefcase me-1"></i>Designation: <?= e(user()['designation'] ?? 'Not provided') ?></div>
                    <div class="small"><i class="bi bi-telephone me-1"></i>Phone: <?= e(user()['phone'] ?? 'Not provided') ?></div>
                </div>
                <a class="dropdown-item py-2" href="/Codex/settings/profile.php"><i class="bi bi-person-gear me-2"></i>Profile</a>
                <a class="dropdown-item py-2 text-danger" href="/Codex/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </div>
        </div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
