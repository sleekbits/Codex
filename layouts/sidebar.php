<div class="col-md-2 bg-light sidebar min-vh-100 p-3">
    <ul class="nav flex-column gap-2">
        <li class="nav-item"><a class="nav-link" href="/Codex/dashboard/index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="/Codex/tracking/index.php">Tracking Database</a></li>
        <li class="nav-item"><a class="nav-link" href="/Codex/imports/index.php">Import</a></li>
        <li class="nav-item"><a class="nav-link" href="/Codex/exports/index.php">Export</a></li>
        <?php if (has_role(['Admin'])): ?>
            <li class="nav-item"><a class="nav-link" href="/Codex/users/index.php">Users</a></li>
            <li class="nav-item"><a class="nav-link" href="/Codex/roles/index.php">Roles</a></li>
            <li class="nav-item"><a class="nav-link" href="/Codex/master/index.php">Master Data</a></li>
            <li class="nav-item"><a class="nav-link" href="/Codex/settings/index.php">Settings</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="/Codex/settings/profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link text-danger" href="/Codex/auth/logout.php">Logout</a></li>
    </ul>
</div>
<div class="col-md-10 p-4">
<?php if ($msg = flash('flash_success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('flash_error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
