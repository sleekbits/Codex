<?php
require_once __DIR__ . '/../includes/functions.php';
if (is_logged_in()) {
    header('Location: /Codex/dashboard/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT u.*, r.role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE username = ? AND is_active = 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'role_name' => $user['role_name'],
        ];
        log_activity($user['id'], 'Login', 'User logged in');
        header('Location: /Codex/dashboard/index.php');
        exit;
    }

    $_SESSION['flash_error'] = 'Invalid credentials or inactive account.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Asteco Procurement Dashboard</title>
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
                    <h1 class="auth-title mb-1">Welcome Back</h1>
                    <p class="auth-subtitle mb-4">Sign in to continue to your procurement workspace.</p>

                    <?php if ($msg = flash('flash_success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
                    <?php if ($msg = flash('flash_error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

                    <form method="post" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Username / Email</label>
                            <input type="text" name="username" class="form-control form-control-lg" placeholder="Enter your username" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Password</label>
                            <div class="input-group auth-input-group">
                                <input type="password" name="password" id="loginPassword" class="form-control form-control-lg" placeholder="Enter your password" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="const el=document.getElementById('loginPassword');el.type=el.type==='password'?'text':'password'; this.innerHTML=el.type==='password'?'<i class=\'bi bi-eye\'></i>':'<i class=\'bi bi-eye-slash\'></i>';">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rememberMe" disabled>
                                <label class="form-check-label text-muted-custom" for="rememberMe">Remember me (coming soon)</label>
                            </div>
                            <a class="auth-link" href="forgot_password.php">Forgot password?</a>
                        </div>
                        <button class="btn btn-primary auth-btn w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
