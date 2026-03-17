<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $id=(int)($_POST['id']??0);
  $fields=[trim($_POST['hierarchy_name']),trim($_POST['type']),(float)$_POST['threshold_from'],(float)$_POST['threshold_to'],trim($_POST['stage_name']),trim($_POST['stage_type']),(int)$_POST['stage_sequence'],(int)$_POST['approver_role_id'],(int)($_POST['parallel_group_id']?:0),(int)($_POST['status']??1),trim($_POST['remarks']) ?: null];
  if($id>0){
    $pdo->prepare('UPDATE doa_hierarchy SET hierarchy_name=?, type=?, threshold_from=?, threshold_to=?, stage_name=?, stage_type=?, stage_sequence=?, approver_role_id=?, parallel_group_id=?, status=?, remarks=?, updated_at=NOW() WHERE id=?')->execute([...$fields,$id]);
  } else {
    $pdo->prepare('INSERT INTO doa_hierarchy(hierarchy_name,type,threshold_from,threshold_to,stage_name,stage_type,stage_sequence,approver_role_id,parallel_group_id,status,remarks,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())')->execute($fields);
  }
  $_SESSION['flash_success']='DOA stage rule saved'; header('Location:index.php'); exit;
}
if(isset($_GET['delete'])){$pdo->prepare('DELETE FROM doa_hierarchy WHERE id=?')->execute([(int)$_GET['delete']]); $_SESSION['flash_success']='DOA rule deleted'; header('Location:index.php'); exit;}
$roles=$pdo->query('SELECT id,role_name FROM roles ORDER BY role_name')->fetchAll();
$rows=$pdo->query('SELECT d.*, r.role_name FROM doa_hierarchy d LEFT JOIN roles r ON r.id=d.approver_role_id ORDER BY d.hierarchy_name, d.stage_sequence')->fetchAll();
$edit=null; if(isset($_GET['edit'])){$st=$pdo->prepare('SELECT * FROM doa_hierarchy WHERE id=?');$st->execute([(int)$_GET['edit']]);$edit=$st->fetch();}
?>
<h3>Delegation of Authority (DOA)</h3>
<div class="row g-3"><div class="col-md-4"><div class="card p-3"><h6><?= $edit?'Edit':'Add' ?> DOA Stage</h6>
<form method="post" class="row g-2"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
<div class="col-12"><label class="form-label">Hierarchy Name</label><input class="form-control" name="hierarchy_name" value="<?= e($edit['hierarchy_name']??'') ?>" required></div>
<div class="col-12"><label class="form-label">Type</label><input class="form-control" name="type" value="<?= e($edit['type']??'') ?>" required></div>
<div class="col-6"><label class="form-label">Threshold From</label><input class="form-control" type="number" step="0.01" name="threshold_from" value="<?= e($edit['threshold_from']??'0') ?>"></div>
<div class="col-6"><label class="form-label">Threshold To</label><input class="form-control" type="number" step="0.01" name="threshold_to" value="<?= e($edit['threshold_to']??'0') ?>"></div>
<div class="col-6"><label class="form-label">Stage Name</label><input class="form-control" name="stage_name" value="<?= e($edit['stage_name']??'') ?>" placeholder="e.g. Endorsement 1" required></div>
<div class="col-6"><label class="form-label">Stage Type</label><select class="form-select" name="stage_type"><option value="endorsement" <?= (($edit['stage_type']??'')==='endorsement')?'selected':'' ?>>Endorsement</option><option value="approval" <?= (($edit['stage_type']??'')==='approval')?'selected':'' ?>>Approval</option><option value="parallel_approval" <?= (($edit['stage_type']??'')==='parallel_approval')?'selected':'' ?>>Parallel Approval</option></select></div>
<div class="col-4"><label class="form-label">Sequence</label><input class="form-control" type="number" name="stage_sequence" value="<?= e($edit['stage_sequence']??'1') ?>"></div>
<div class="col-4"><label class="form-label">Parallel Group</label><input class="form-control" type="number" name="parallel_group_id" value="<?= e($edit['parallel_group_id']??'0') ?>"></div>
<div class="col-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="1" <?= (($edit['status']??1)==1)?'selected':'' ?>>Active</option><option value="0" <?= (($edit['status']??1)==0)?'selected':'' ?>>Inactive</option></select></div>
<div class="col-12"><label class="form-label">Approver Role</label><select class="form-select" name="approver_role_id" required><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>" <?= (($edit['approver_role_id']??0)==$r['id'])?'selected':'' ?>><?= e($r['role_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks"><?= e($edit['remarks']??'') ?></textarea></div>
<div class="col-12"><button class="btn btn-primary">Save Stage</button></div></form></div></div>
<div class="col-md-8"><div class="card p-3"><h6>DOA Hierarchy Stages</h6><table class="table table-bordered"><tr><th>Hierarchy</th><th>Type</th><th>Threshold</th><th>Stage</th><th>Seq</th><th>Parallel</th><th>Role</th><th>Status</th><th>Action</th></tr><?php foreach($rows as $r): ?><tr><td><?= e($r['hierarchy_name']) ?></td><td><?= e($r['type']) ?></td><td><?= number_format((float)$r['threshold_from'],2) ?> - <?= number_format((float)$r['threshold_to'],2) ?></td><td><span class="badge bg-light text-dark"><?= e(ucfirst(str_replace('_',' ',$r['stage_type']))) ?></span> <?= e($r['stage_name']) ?></td><td><?= (int)$r['stage_sequence'] ?></td><td><?= (int)$r['parallel_group_id'] ?></td><td><?= e($r['role_name']) ?></td><td><?= $r['status']?'Active':'Inactive' ?></td><td><a href="?edit=<?= $r['id'] ?>">Edit</a> | <a class="text-danger" onclick="return confirm('Delete?')" href="?delete=<?= $r['id'] ?>">Delete</a></td></tr><?php endforeach; ?></table></div></div></div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
