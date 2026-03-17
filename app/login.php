<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (is_logged_in()) { header('Location: index.php'); exit; }
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT u.id, u.username, u.email, u.password_hash, u.is_active, p.full_name AS name, r.role_name FROM users u LEFT JOIN user_profiles p ON p.user_id=u.id LEFT JOIN roles r ON r.id=u.role_id WHERE (u.username=? OR u.email=?) LIMIT 1');
    $stmt->execute([$identity, $identity]);
    $user = $stmt->fetch();
    if ($user && $user['is_active'] && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'] ?: $user['username'], 'role' => $user['role_name']];
        if (!empty($_POST['remember'])) {
            setcookie('remember_identity', $identity, time() + (86400 * 30), '/');
        }
        log_audit('login', 'authentication', 'User logged in');
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid credentials or inactive account.';
}
?>
<!doctype html><html><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/app.css"><title>Login</title></head>
<body class="d-flex align-items-center" style="min-height:100vh;background:#EEE6DA">
<div class="container"><div class="row justify-content-center"><div class="col-md-5"><div class="card p-4">
<h3 class="mb-3">Asteco Procurement ERP</h3>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post">
<div class="mb-3"><label class="form-label">Username / Email</label><input class="form-control" name="identity" value="<?= e($_COOKIE['remember_identity'] ?? '') ?>" required></div>
<div class="mb-3"><label class="form-label">Password</label><div class="input-group"><input type="password" id="pass" class="form-control" name="password" required><button type="button" class="btn btn-outline-secondary" onclick="pass.type=pass.type==='password'?'text':'password'">Show</button></div></div>
<div class="d-flex justify-content-between mb-3"><div><input type="checkbox" name="remember" class="form-check-input"> Remember me</div><a href="forgot_password.php">Forgot Password?</a></div>
<button class="btn btn-warning w-100">Login</button>
</form></div></div></div></div>
</body></html>
