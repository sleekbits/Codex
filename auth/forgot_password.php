<?php
require_once __DIR__ . '/../includes/functions.php';

$step = 'lookup';
$targetUser = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    if (isset($_POST['lookup'])) {
        $stmt = $pdo->prepare('SELECT id, username, email, full_name FROM users WHERE is_active = 1 AND (username = ? OR email = ?) LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        $targetUser = $stmt->fetch();
        if (!$targetUser) {
            $_SESSION['flash_error'] = 'No active account found with that username or email.';
        } else {
            $step = 'reset';
        }
    } elseif (isset($_POST['reset'])) {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($newPassword === '' || strlen($newPassword) < 8) {
            $_SESSION['flash_error'] = 'Password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'Password confirmation does not match.';
        } else {
            $stmt = $pdo->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
            log_activity(null, 'Forgot password reset', 'Password reset for user ID ' . $userId);
            $_SESSION['flash_success'] = 'Password updated successfully. Please login with your new password.';
            header('Location: login.php');
            exit;
        }
        $stmt = $pdo->prepare('SELECT id, username, email, full_name FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $targetUser = $stmt->fetch();
        $step = 'reset';
    }
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
                    <p class="auth-subtitle mb-4">Enter your username or email to locate your account and set a new password.</p>

                    <?php if ($msg = flash('flash_error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>

                    <?php if ($step === 'lookup'): ?>
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Username or Email</label>
                                <input class="form-control form-control-lg" name="identifier" placeholder="Enter username or email" required>
                                <div class="form-text">You can use either your username or your registered email address.</div>
                            </div>
                            <button class="btn btn-primary auth-btn w-100" name="lookup" value="1">Continue</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">Account found: <strong><?= e($targetUser['full_name']) ?></strong> (<?= e($targetUser['username']) ?>)</div>
                        <form method="post">
                            <input type="hidden" name="user_id" value="<?= (int)$targetUser['id'] ?>">
                            <input type="hidden" name="identifier" value="<?= e($identifier ?? '') ?>">
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control form-control-lg" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control form-control-lg" name="confirm_password" required>
                            </div>
                            <button class="btn btn-primary auth-btn w-100" name="reset" value="1">Update Password</button>
                        </form>
                    <?php endif; ?>

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
