<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $id=(int)($_POST['id']??0);
  $fields=[trim($_POST['hierarchy_name']),trim($_POST['type']),(float)$_POST['threshold_from'],(float)$_POST['threshold_to'],(int)$_POST['approval_level'],(int)$_POST['approver_role_id'],(int)$_POST['approval_order'],(int)($_POST['status']??1),trim($_POST['remarks']) ?: null];
  if($id>0){
    $pdo->prepare('UPDATE doa_hierarchy SET hierarchy_name=?, type=?, threshold_from=?, threshold_to=?, approval_level=?, approver_role_id=?, approval_order=?, status=?, remarks=?, updated_at=NOW() WHERE id=?')->execute([...$fields,$id]);
  } else {
    $pdo->prepare('INSERT INTO doa_hierarchy(hierarchy_name,type,threshold_from,threshold_to,approval_level,approver_role_id,approval_order,status,remarks,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,NOW(),NOW())')->execute($fields);
  }
  $_SESSION['flash_success']='DOA rule saved'; header('Location:index.php'); exit;
}
if(isset($_GET['delete'])){$pdo->prepare('DELETE FROM doa_hierarchy WHERE id=?')->execute([(int)$_GET['delete']]); $_SESSION['flash_success']='DOA rule deleted'; header('Location:index.php'); exit;}
$roles=$pdo->query('SELECT id,role_name FROM roles ORDER BY role_name')->fetchAll();
$rows=$pdo->query('SELECT d.*, r.role_name FROM doa_hierarchy d LEFT JOIN roles r ON r.id=d.approver_role_id ORDER BY d.type, d.threshold_from')->fetchAll();
$edit=null; if(isset($_GET['edit'])){$st=$pdo->prepare('SELECT * FROM doa_hierarchy WHERE id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
?>
<h3>Customizable Hierarchy / Delegation of Authority (DOA)</h3>
<div class="row g-3"><div class="col-md-4"><div class="card p-3"><h6><?= $edit?'Edit':'Add' ?> DOA Rule</h6>
<form method="post" class="row g-2"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
<div class="col-12"><input class="form-control" name="hierarchy_name" placeholder="Hierarchy Name" value="<?= e($edit['hierarchy_name']??'') ?>" required></div>
<div class="col-12"><input class="form-control" name="type" placeholder="Type" value="<?= e($edit['type']??'') ?>" required></div>
<div class="col-6"><input class="form-control" type="number" step="0.01" name="threshold_from" placeholder="Threshold From" value="<?= e($edit['threshold_from']??'0') ?>"></div>
<div class="col-6"><input class="form-control" type="number" step="0.01" name="threshold_to" placeholder="Threshold To" value="<?= e($edit['threshold_to']??'0') ?>"></div>
<div class="col-4"><input class="form-control" type="number" name="approval_level" placeholder="Level" value="<?= e($edit['approval_level']??'1') ?>"></div>
<div class="col-4"><input class="form-control" type="number" name="approval_order" placeholder="Order" value="<?= e($edit['approval_order']??'1') ?>"></div>
<div class="col-4"><select class="form-select" name="status"><option value="1" <?= (($edit['status']??1)==1)?'selected':'' ?>>Active</option><option value="0" <?= (($edit['status']??1)==0)?'selected':'' ?>>Inactive</option></select></div>
<div class="col-12"><select class="form-select" name="approver_role_id" required><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>" <?= (($edit['approver_role_id']??0)==$r['id'])?'selected':'' ?>><?= e($r['role_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-12"><textarea class="form-control" name="remarks" placeholder="Remarks"><?= e($edit['remarks']??'') ?></textarea></div>
<div class="col-12"><button class="btn btn-primary">Save Rule</button></div></form></div></div>
<div class="col-md-8"><div class="card p-3"><h6>DOA Rules</h6><table class="table table-bordered"><tr><th>Name</th><th>Type</th><th>Threshold</th><th>Level</th><th>Role</th><th>Status</th><th>Action</th></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['hierarchy_name']) ?></td><td><?= e($r['type']) ?></td><td><?= number_format((float)$r['threshold_from'],2) ?> - <?= number_format((float)$r['threshold_to'],2) ?></td><td><?= (int)$r['approval_level'] ?>/<?= (int)$r['approval_order'] ?></td><td><?= e($r['role_name']) ?></td><td><?= $r['status']?'Active':'Inactive' ?></td><td><a href="?edit=<?= $r['id'] ?>">Edit</a> | <a class="text-danger" onclick="return confirm('Delete?')" href="?delete=<?= $r['id'] ?>">Delete</a></td></tr><?php endforeach; ?></table></div></div></div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
