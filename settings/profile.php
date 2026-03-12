<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare('UPDATE users SET full_name=?, email=?, updated_at=NOW() WHERE id=?');
    $stmt->execute([$name, $email, user()['id']]);
    $_SESSION['user']['full_name'] = $name;
    $_SESSION['user']['email'] = $email;
    if (!empty($_POST['password'])) {
        if (strlen($_POST['password']) < 8) {
            $_SESSION['flash_error'] = 'Password must be at least 8 characters.';
            header('Location: profile.php'); exit;
        }
        $stmt = $pdo->prepare('UPDATE users SET password=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), user()['id']]);
    }
    $_SESSION['flash_success'] = 'Profile updated';
    header('Location: profile.php'); exit;
}
?>
<h3>My Profile</h3>
<div class="card p-3">
<div class="d-flex align-items-center gap-3 mb-3"><div class="rounded-circle bg-light border d-flex justify-content-center align-items-center" style="width:56px;height:56px;"><i class="bi bi-person fs-3"></i></div><div><h6 class="mb-0"><?= e(user()['full_name']) ?></h6><small class="text-muted"><?= e(user()['role_name']) ?></small></div></div>
<form method="post" class="row g-3">
<div class="col-md-4"><label class="form-label">Full Name</label><input class="form-control" name="full_name" value="<?= e(user()['full_name']) ?>"></div>
<div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" value="<?= e(user()['email']) ?>"></div>
<div class="col-md-4"><label class="form-label">Change Password</label><input type="password" class="form-control" name="password" placeholder="Minimum 8 characters"></div>
<div class="col-12"><button class="btn btn-primary">Save Profile</button></div>
</form>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
