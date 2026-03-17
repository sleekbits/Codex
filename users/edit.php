<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);
$id=(int)($_GET['id']??0);
$roles=$pdo->query('SELECT * FROM roles')->fetchAll();
$stmt=$pdo->prepare('SELECT * FROM users WHERE id=?');$stmt->execute([$id]);$u=$stmt->fetch();
if(!$u){echo '<div class="alert alert-danger">User not found</div>'; require_once __DIR__.'/../layouts/footer.php'; exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $image = $u['profile_image'];
  if (!empty($_POST['remove_image'])) { if ($image && file_exists(__DIR__ . '/../' . $image)) @unlink(__DIR__ . '/../' . $image); $image = null; }
  if (!empty($_FILES['profile_image']['tmp_name'])) {
    $ext=strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext,['jpg','jpeg','png','webp'],true)) {
      $dir=__DIR__.'/../uploads/profiles'; if(!is_dir($dir)) mkdir($dir,0775,true);
      $filename='uploads/profiles/u'.$id.'_'.time().'.'.$ext;
      move_uploaded_file($_FILES['profile_image']['tmp_name'], __DIR__.'/../'.$filename);
      if ($image && file_exists(__DIR__.'/../'.$image)) @unlink(__DIR__.'/../'.$image);
      $image=$filename;
    }
  }
  $stmt=$pdo->prepare('UPDATE users SET full_name=?,designation=?,email=?,phone=?,username=?,role_id=?,is_active=?,profile_image=?,updated_at=NOW() WHERE id=?');
  $stmt->execute([trim($_POST['full_name']),trim($_POST['designation']),trim($_POST['email']),trim($_POST['phone']),trim($_POST['username']),(int)$_POST['role_id'],(int)$_POST['is_active'],$image,$id]);
  log_activity(user()['id'],'Edit user','Updated user '.$id);
  $_SESSION['flash_success']='User updated'; header('Location:index.php'); exit;
}
?>
<h3>Edit User</h3>
<div class="card p-3"><form method="post" enctype="multipart/form-data" class="row g-3">
<div class="col-md-3 text-center"><?php if($u['profile_image']): ?><img src="/Codex/<?= e($u['profile_image']) ?>" class="img-thumbnail rounded-circle" style="width:120px;height:120px;object-fit:cover;"><?php else: ?><div class="rounded-circle border d-flex align-items-center justify-content-center mx-auto" style="width:120px;height:120px;"><i class="bi bi-person fs-2"></i></div><?php endif; ?><input type="file" name="profile_image" class="form-control mt-2"><?php if($u['profile_image']): ?><label class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_image" value="1"> Remove image</label><?php endif; ?></div>
<div class="col-md-9"><div class="row g-3">
<div class="col-md-6"><label class="form-label">Name</label><input class="form-control" name="full_name" value="<?= e($u['full_name']) ?>"></div>
<div class="col-md-6"><label class="form-label">Designation</label><input class="form-control" name="designation" value="<?= e($u['designation']) ?>"></div>
<div class="col-md-6"><label class="form-label">Email</label><input class="form-control" name="email" value="<?= e($u['email']) ?>"></div>
<div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone" value="<?= e($u['phone']) ?>"></div>
<div class="col-md-6"><label class="form-label">Username</label><input class="form-control" name="username" value="<?= e($u['username']) ?>"></div>
<div class="col-md-3"><label class="form-label">Role</label><select class="form-select" name="role_id"><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>" <?= $u['role_id']==$r['id']?'selected':'' ?>><?= e($r['role_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="is_active"><option value="1" <?= $u['is_active']?'selected':'' ?>>Active</option><option value="0" <?= !$u['is_active']?'selected':'' ?>>Inactive</option></select></div>
</div></div>
<div class="col-12"><button class="btn btn-primary">Save</button></div>
</form></div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
