<?php
require_once __DIR__ . '/../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['flash_success'] = 'Password reset request received. Please contact admin for account reset.';
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Asteco Procurement Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/Codex/assets/css/app.css" rel="stylesheet">
</head>
<body class="auth-page d-flex align-items-center">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card auth-card border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="auth-brand mb-2">Asteco Procurement Dashboard</div>
                    <h1 class="auth-title mb-1">Forgot Password</h1>
                    <p class="auth-subtitle mb-4">Enter your username to request a password reset from system administrators.</p>

                    <?php if ($msg = flash('flash_error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input class="form-control form-control-lg" name="username" placeholder="Enter your username" required>
                        </div>
                        <button class="btn btn-primary auth-btn w-100">Submit Request</button>
                    </form>

                    <div class="mt-3 text-center">
                        <a href="login.php" class="auth-link"><i class="bi bi-arrow-left me-1"></i>Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
