<?php
require_once __DIR__ . '/includes/bootstrap.php';
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $stmt = db()->prepare('SELECT id FROM users WHERE username=? OR email=? LIMIT 1');
    $stmt->execute([$identity, $identity]);
    $msg = $stmt->fetch() ? 'Password reset request accepted. Contact ERP/IT admin for reset.' : 'No matching user found.';
}
?><!doctype html><html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="<?= e(app_url('assets/css/app.css')) ?>"></head><body class="p-5"><div class="container"><div class="card p-4 col-md-5 mx-auto"><h4>Forgot Password</h4><?php if($msg):?><div class="alert alert-info"><?=e($msg)?></div><?php endif;?><form method="post" action="<?= e(app_url('forgot_password.php')) ?>"><input class="form-control mb-3" name="identity" placeholder="Username or email" required><button class="btn btn-warning w-100">Submit</button></form><a href="<?= e(app_url('login.php')) ?>" class="btn btn-link mt-2">Back to login</a></div></div></body></html>
