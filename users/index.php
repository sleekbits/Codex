<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);
$rows = $pdo->query('SELECT u.*, r.role_name FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC')->fetchAll();
$roles = $pdo->query('SELECT * FROM roles')->fetchAll();
?>
<h3>User Management</h3>
<div class="card p-3 mb-3">
<h6 class="mb-3">Add User</h6>
<form method="post" action="save.php" class="row g-3">
<div class="col-md-4"><label class="form-label">Full Name</label><input class="form-control" name="full_name" placeholder="Enter full name" required></div>
<div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" placeholder="name@example.com" required></div>
<div class="col-md-4"><label class="form-label">Username</label><input class="form-control" name="username" placeholder="username" required></div>
<div class="col-md-4"><label class="form-label">Role</label><select class="form-select" name="role_id"><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['role_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Password</label><div class="input-group"><input class="form-control" type="password" name="password" id="newPassword" required><button type="button" class="btn btn-outline-secondary" onclick="const i=document.getElementById('newPassword');i.type=i.type==='password'?'text':'password';"><i class="bi bi-eye"></i></button></div></div>
<div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="is_active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
<div class="col-md-12"><button class="btn btn-primary">Add User</button></div>
</form></div>

<div class="card p-3">
<table class="table table-bordered align-middle"><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
<?php foreach($rows as $r): ?><tr>
<td><?= e($r['full_name']) ?></td><td><?= e($r['username']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['role_name']) ?></td><td><span class="badge <?= $r['is_active']?'bg-success-subtle text-success-emphasis':'bg-secondary-subtle text-secondary-emphasis' ?>"><?= $r['is_active']?'Active':'Inactive' ?></span></td>
<td class="text-nowrap">
<a class="btn btn-sm action-btn btn-outline-warning" title="Edit" href="edit.php?id=<?= $r['id'] ?>"><i class="bi bi-pencil"></i></a>
<a class="btn btn-sm action-btn btn-outline-info" title="Reset Password" href="password.php?id=<?= $r['id'] ?>"><i class="bi bi-key"></i></a>
<?php if ($r['id'] != user()['id']): ?><a class="btn btn-sm action-btn btn-outline-danger" title="Delete" onclick="return confirm('Delete user?')" href="delete.php?id=<?= $r['id'] ?>"><i class="bi bi-trash"></i></a><?php endif; ?>
</td>
</tr><?php endforeach; ?></table>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
