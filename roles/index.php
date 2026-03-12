<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO roles (role_name, created_at, updated_at) VALUES (?,NOW(),NOW())');
    $stmt->execute([trim($_POST['role_name'])]);
    log_activity(user()['id'], 'Add role', 'Added role ' . trim($_POST['role_name']));
    $_SESSION['flash_success'] = 'Role added';
    header('Location: index.php'); exit;
}
$roles = $pdo->query('SELECT * FROM roles ORDER BY id')->fetchAll();
?>
<h3>Role Management</h3>
<form method="post" class="row g-2 mb-3"><div class="col-md-4"><input class="form-control" name="role_name" placeholder="Role Name" required></div><div class="col-md-2"><button class="btn btn-primary">Add Role</button></div></form>
<table class="table table-bordered"><tr><th>ID</th><th>Role</th></tr><?php foreach($roles as $r): ?><tr><td><?= $r['id'] ?></td><td><?= e($r['role_name']) ?></td></tr><?php endforeach; ?></table>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
