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
        $stmt = $pdo->prepare('UPDATE users SET password=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), user()['id']]);
    }
    $_SESSION['flash_success'] = 'Profile updated';
    header('Location: profile.php'); exit;
}
?>
<h3>My Profile</h3>
<form method="post" class="row g-3"><div class="col-md-4"><label>Full Name</label><input class="form-control" name="full_name" value="<?= e(user()['full_name']) ?>"></div>
<div class="col-md-4"><label>Email</label><input class="form-control" name="email" value="<?= e(user()['email']) ?>"></div>
<div class="col-md-4"><label>New Password</label><input type="password" class="form-control" name="password"></div>
<div class="col-12"><button class="btn btn-primary">Save</button></div></form>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
