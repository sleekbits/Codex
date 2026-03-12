<div class="col-lg-2 col-md-3 sidebar min-vh-100 p-3">
    <ul class="nav flex-column gap-1">
        <li class="nav-item"><a class="nav-link" href="/Codex/dashboard/index.php"><i class="bi bi-house-door me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="/Codex/tracking/index.php"><i class="bi bi-table me-2"></i>Tracking Database</a></li>
        <li class="nav-item"><a class="nav-link" href="/Codex/imports/index.php"><i class="bi bi-upload me-2"></i>Import</a></li>
        <li class="nav-item"><a class="nav-link" href="/Codex/exports/index.php"><i class="bi bi-download me-2"></i>Export</a></li>
        <?php if (has_role(['Admin'])): ?>
            <li class="nav-item"><a class="nav-link" href="/Codex/users/index.php"><i class="bi bi-people me-2"></i>Users</a></li>
            <li class="nav-item"><a class="nav-link" href="/Codex/roles/index.php"><i class="bi bi-shield-lock me-2"></i>Roles</a></li>
            <li class="nav-item"><a class="nav-link" href="/Codex/master/index.php"><i class="bi bi-diagram-3 me-2"></i>Master Data</a></li>
            <li class="nav-item"><a class="nav-link" href="/Codex/settings/index.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="/Codex/settings/profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
        <li class="nav-item mt-2"><a class="nav-link text-danger-emphasis" href="/Codex/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
    </ul>
</div>
<div class="col-lg-10 col-md-9 p-4 content-wrap">
<?php if ($msg = flash('flash_success')): ?><div class="alert alert-success shadow-sm"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('flash_error')): ?><div class="alert alert-danger shadow-sm"><?= e($msg) ?></div><?php endif; ?>
