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
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light d-flex align-items-center min-vh-100"><div class="container"><div class="row justify-content-center"><div class="col-md-4">
<div class="card shadow-sm"><div class="card-body"><h4 class="mb-3">Asteco Procurement Dashboard</h4>
<?php if ($msg = flash('flash_error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
<form method="post">
<div class="mb-2"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
<button class="btn btn-primary w-100">Login</button>
<a class="d-block mt-2" href="forgot_password.php">Forgot Password?</a>
</form></div></div></div></div></div></body></html>
