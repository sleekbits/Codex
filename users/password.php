<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare('SELECT id,username,full_name FROM users WHERE id=?');$stmt->execute([$id]);$u=$stmt->fetch();
if(!$u){echo '<div class="alert alert-danger">User not found</div>'; require_once __DIR__.'/../layouts/footer.php'; exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){
  $pwd=$_POST['password']??'';
  if(strlen($pwd)<8){$_SESSION['flash_error']='Password must be at least 8 chars';}
  else {
    $pdo->prepare('UPDATE users SET password=?, updated_at=NOW() WHERE id=?')->execute([password_hash($pwd,PASSWORD_DEFAULT),$id]);
    log_activity(user()['id'],'Reset password','Reset password for user '.$id);
    $_SESSION['flash_success']='Password updated'; header('Location:index.php'); exit;
  }
}
?>
<h3>Reset User Password</h3>
<div class="card p-3"><p><strong><?= e($u['full_name']) ?></strong> (<?= e($u['username']) ?>)</p>
<form method="post" class="row g-3"><div class="col-md-5"><label class="form-label">New Password</label><input class="form-control" type="password" name="password" required></div><div class="col-12"><button class="btn btn-primary">Update Password</button></div></form></div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
