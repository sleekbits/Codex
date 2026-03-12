<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);
$rows = $pdo->query('SELECT u.*, r.role_name FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC')->fetchAll();
$roles = $pdo->query('SELECT * FROM roles')->fetchAll();
?>
<h3>User Management</h3>
<form method="post" action="save.php" class="row g-2 mb-3">
<div class="col"><input class="form-control" name="full_name" placeholder="Full Name" required></div>
<div class="col"><input class="form-control" name="email" placeholder="Email" required></div>
<div class="col"><input class="form-control" name="username" placeholder="Username" required></div>
<div class="col"><select class="form-select" name="role_id"><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['role_name']) ?></option><?php endforeach; ?></select></div>
<div class="col"><input class="form-control" type="password" name="password" placeholder="Password" required></div>
<div class="col"><button class="btn btn-primary">Add User</button></div></form>
<table class="table table-bordered"><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th></tr>
<?php foreach($rows as $r): ?><tr><td><?= e($r['full_name']) ?></td><td><?= e($r['username']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['role_name']) ?></td><td><?= $r['is_active']?'Active':'Inactive' ?></td></tr><?php endforeach; ?></table>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
