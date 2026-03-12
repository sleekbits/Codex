<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);
$id=(int)($_GET['id']??0);
$roles=$pdo->query('SELECT * FROM roles')->fetchAll();
$stmt=$pdo->prepare('SELECT * FROM users WHERE id=?');$stmt->execute([$id]);$u=$stmt->fetch();
if(!$u){echo '<div class="alert alert-danger">User not found</div>'; require_once __DIR__.'/../layouts/footer.php'; exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $stmt=$pdo->prepare('UPDATE users SET full_name=?,email=?,username=?,role_id=?,is_active=?,updated_at=NOW() WHERE id=?');
  $stmt->execute([trim($_POST['full_name']),trim($_POST['email']),trim($_POST['username']),(int)$_POST['role_id'],(int)$_POST['is_active'],$id]);
  log_activity(user()['id'],'Edit user','Updated user '.$id);
  $_SESSION['flash_success']='User updated'; header('Location:index.php'); exit;
}
?>
<h3>Edit User</h3>
<div class="card p-3"><form method="post" class="row g-3">
<div class="col-md-4"><label class="form-label">Full Name</label><input class="form-control" name="full_name" value="<?= e($u['full_name']) ?>"></div>
<div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" value="<?= e($u['email']) ?>"></div>
<div class="col-md-4"><label class="form-label">Username</label><input class="form-control" name="username" value="<?= e($u['username']) ?>"></div>
<div class="col-md-4"><label class="form-label">Role</label><select class="form-select" name="role_id"><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>" <?= $u['role_id']==$r['id']?'selected':'' ?>><?= e($r['role_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="is_active"><option value="1" <?= $u['is_active']?'selected':'' ?>>Active</option><option value="0" <?= !$u['is_active']?'selected':'' ?>>Inactive</option></select></div>
<div class="col-12"><button class="btn btn-primary">Save</button></div>
</form></div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
