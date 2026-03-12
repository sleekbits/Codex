<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Admin']);
$id=(int)($_GET['id']??0);
if($id===user()['id']){$_SESSION['flash_error']='You cannot delete your own user.'; header('Location:index.php'); exit;}
$pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
log_activity(user()['id'],'Delete user','Deleted user '.$id);
$_SESSION['flash_success']='User deleted';
header('Location:index.php');
