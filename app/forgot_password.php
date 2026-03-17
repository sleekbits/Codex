<?php
require_once __DIR__ . '/includes/bootstrap.php';

$step = $_GET['step'] ?? 'request';
$info = null;
$error = null;

if ($step === 'request' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $stmt = db()->prepare('SELECT id, email FROM users WHERE email = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'No active user found with this email address.';
    } else {
        $otp = generate_otp_code();
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        db()->prepare('UPDATE password_reset_otps SET used_at = NOW() WHERE email = ? AND used_at IS NULL')->execute([$email]);
        db()->prepare('INSERT INTO password_reset_otps (user_id, email, otp_hash, expires_at, created_at) VALUES (?,?,?,?,NOW())')
            ->execute([(int)$user['id'], $email, $otpHash, date('Y-m-d H:i:s', time() + 600)]);

        $subject = 'Your OTP Code - Asteco Procurement ERP';
        $body = "Your OTP code is: {$otp}\n\nIt expires in 10 minutes.\nIf you did not request this, ignore this email.";
        $mailSent = send_email($email, $subject, $body);

        $_SESSION['password_reset_email'] = $email;
        if (!$mailSent && ($config['debug'] ?? false)) {
            $info = 'Mail function failed in this environment. Debug OTP: ' . $otp;
        } else {
            $info = 'OTP has been sent to your email.';
        }
        $step = 'verify';
    }
}

if ($step === 'verify' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['password_reset_email'] ?? '';
    $otp = trim($_POST['otp'] ?? '');

    if ($email === '' || $otp === '') {
        $error = 'Invalid OTP request state. Please start again.';
        $step = 'request';
    } else {
        $stmt = db()->prepare('SELECT * FROM password_reset_otps WHERE email = ? AND used_at IS NULL ORDER BY id DESC LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row) {
            $error = 'OTP not found. Please request a new one.';
        } elseif (strtotime((string)$row['expires_at']) < time()) {
            $error = 'OTP expired. Please request a new OTP.';
        } elseif (!password_verify($otp, $row['otp_hash'])) {
            db()->prepare('UPDATE password_reset_otps SET attempts = attempts + 1 WHERE id = ?')->execute([$row['id']]);
            $error = 'Invalid OTP code.';
        } else {
            $_SESSION['password_reset_verified_user_id'] = (int)$row['user_id'];
            db()->prepare('UPDATE password_reset_otps SET used_at = NOW() WHERE id = ?')->execute([$row['id']]);
            $info = 'OTP verified. Please set your new password.';
            $step = 'reset';
        }
    }
}

if ($step === 'resend') {
    $email = $_SESSION['password_reset_email'] ?? '';
    if ($email === '') {
        $error = 'Session expired. Please request OTP again.';
        $step = 'request';
    } else {
        $otp = generate_otp_code();
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        db()->prepare('UPDATE password_reset_otps SET used_at = NOW() WHERE email = ? AND used_at IS NULL')->execute([$email]);
        db()->prepare('INSERT INTO password_reset_otps (user_id, email, otp_hash, expires_at, created_at, resend_count) SELECT id, email, ?, ?, NOW(), 1 FROM users WHERE email = ? LIMIT 1')
            ->execute([$otpHash, date('Y-m-d H:i:s', time() + 600), $email]);

        $subject = 'Your OTP Code (Resend) - Asteco Procurement ERP';
        $body = "Your new OTP code is: {$otp}\n\nIt expires in 10 minutes.";
        $mailSent = send_email($email, $subject, $body);
        $info = $mailSent ? 'A new OTP has been sent.' : (($config['debug'] ?? false) ? 'Mail failed. Debug OTP: '.$otp : 'Unable to send OTP now. Try again shortly.');
        $step = 'verify';
    }
}

if ($step === 'reset' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_SESSION['password_reset_verified_user_id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($userId <= 0) {
        $error = 'Password reset session not valid. Please retry forgot password flow.';
        $step = 'request';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Password and confirmation do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $userId]);
        unset($_SESSION['password_reset_verified_user_id'], $_SESSION['password_reset_email']);
        set_flash('success', 'Password reset successful. Please login with your new password.');
        redirect('login.php');
    }
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="<?= e(app_url('assets/css/app.css')) ?>">
<title>Forgot Password</title>
</head>
<body class="p-5" style="background:#EEE6DA">
<div class="container"><div class="card p-4 col-md-6 mx-auto">
<h4 class="mb-3">Forgot Password</h4>
<p class="text-muted">Reset your password using email + OTP verification.</p>
<?php if($info):?><div class="alert alert-info"><?= e($info) ?></div><?php endif; ?>
<?php if($error):?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

<?php if($step === 'request'): ?>
<form method="post" action="<?= e(app_url('forgot_password.php?step=request')) ?>">
    <label class="form-label">Registered Email</label>
    <input class="form-control mb-3" type="email" name="email" required>
    <button class="btn btn-warning w-100">Send OTP</button>
</form>
<?php elseif($step === 'verify'): ?>
<form method="post" action="<?= e(app_url('forgot_password.php?step=verify')) ?>">
    <label class="form-label">Enter OTP</label>
    <input class="form-control mb-3" name="otp" maxlength="6" required>
    <button class="btn btn-warning w-100">Verify OTP</button>
</form>
<a class="btn btn-link mt-2" href="<?= e(app_url('forgot_password.php?step=resend')) ?>">Resend OTP</a>
<?php else: ?>
<form method="post" action="<?= e(app_url('forgot_password.php?step=reset')) ?>">
    <label class="form-label">New Password</label>
    <input class="form-control mb-3" type="password" name="password" minlength="8" required>
    <label class="form-label">Confirm Password</label>
    <input class="form-control mb-3" type="password" name="confirm_password" minlength="8" required>
    <button class="btn btn-warning w-100">Update Password</button>
</form>
<?php endif; ?>

<a href="<?= e(app_url('login.php')) ?>" class="btn btn-link mt-3">Back to Login</a>
</div></div>
</body></html>
