<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);

if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='save') {
  $id=(int)($_POST['id']??0);
  $fields=[trim($_POST['supplier_code']),trim($_POST['supplier_legal_name']),trim($_POST['supplier_license_number']),trim($_POST['supplier_email']),trim($_POST['supplier_phone']),trim($_POST['supplier_mobile']),trim($_POST['supplier_address']),trim($_POST['supplier_vat_number']),(int)($_POST['status']??1),trim($_POST['remarks'])?:null,user()['id']];
  if($id>0){
    $pdo->prepare('UPDATE suppliers SET supplier_code=?,supplier_legal_name=?,supplier_license_number=?,supplier_email=?,supplier_phone=?,supplier_mobile=?,supplier_address=?,supplier_vat_number=?,status=?,remarks=?,updated_by=?,updated_at=NOW() WHERE id=?')->execute([...$fields,$id]);
  } else {
    $pdo->prepare('INSERT INTO suppliers(supplier_code,supplier_legal_name,supplier_license_number,supplier_email,supplier_phone,supplier_mobile,supplier_address,supplier_vat_number,status,remarks,created_by,updated_by,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?, ?,NOW(),NOW())')->execute([...$fields,user()['id']]);
  }
  $_SESSION['flash_success']='Supplier saved'; header('Location:index.php'); exit;
}
if(isset($_GET['delete'])){$pdo->prepare('DELETE FROM suppliers WHERE id=?')->execute([(int)$_GET['delete']]); $_SESSION['flash_success']='Supplier deleted'; header('Location:index.php'); exit;}

$search=trim($_GET['search']??'');
$sql='SELECT * FROM suppliers WHERE 1'; $params=[];
if($search!==''){$sql.=' AND (supplier_code LIKE ? OR supplier_legal_name LIKE ? OR supplier_email LIKE ? OR supplier_phone LIKE ?)'; for($i=0;$i<4;$i++) $params[]='%'.$search.'%';}
if(isset($_GET['status']) && $_GET['status']!==''){$sql.=' AND status=?'; $params[]=(int)$_GET['status'];}
$sql.=' ORDER BY id DESC';
$st=$pdo->prepare($sql); $st->execute($params); $rows=$st->fetchAll();
$edit=null; if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM suppliers WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
?>
<div class="d-flex justify-content-between align-items-center mb-2"><h3>Supplier / Vendor Management</h3><div><a class="btn btn-outline-primary" href="import.php"><i class="bi bi-upload"></i> Import</a> <a class="btn btn-outline-success" href="export.php"><i class="bi bi-download"></i> Export</a></div></div>
<div class="row g-3"><div class="col-md-4"><div class="card p-3"><h6><?= $edit?'Edit':'Add' ?> Supplier</h6>
<form method="post" class="row g-2"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
<div class="col-6"><label class="form-label">Supplier Code</label><input class="form-control" name="supplier_code" value="<?= e($edit['supplier_code']??'') ?>" required></div>
<div class="col-6"><label class="form-label">Legal Name</label><input class="form-control" name="supplier_legal_name" value="<?= e($edit['supplier_legal_name']??'') ?>" required></div>
<div class="col-6"><label class="form-label">License No.</label><input class="form-control" name="supplier_license_number" value="<?= e($edit['supplier_license_number']??'') ?>"></div>
<div class="col-6"><label class="form-label">Email</label><input class="form-control" name="supplier_email" value="<?= e($edit['supplier_email']??'') ?>"></div>
<div class="col-6"><label class="form-label">Phone</label><input class="form-control" name="supplier_phone" value="<?= e($edit['supplier_phone']??'') ?>"></div>
<div class="col-6"><label class="form-label">Mobile</label><input class="form-control" name="supplier_mobile" value="<?= e($edit['supplier_mobile']??'') ?>"></div>
<div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="supplier_address"><?= e($edit['supplier_address']??'') ?></textarea></div>
<div class="col-6"><label class="form-label">VAT No.</label><input class="form-control" name="supplier_vat_number" value="<?= e($edit['supplier_vat_number']??'') ?>"></div>
<div class="col-6"><label class="form-label">Status</label><select class="form-select" name="status"><option value="1" <?= (($edit['status']??1)==1)?'selected':'' ?>>Active</option><option value="0" <?= (($edit['status']??1)==0)?'selected':'' ?>>Inactive</option></select></div>
<div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks"><?= e($edit['remarks']??'') ?></textarea></div>
<div class="col-12"><button class="btn btn-primary">Save</button></div></form></div></div>
<div class="col-md-8"><div class="card p-3"><form class="row g-2 mb-2"><div class="col-md-6"><input class="form-control" name="search" value="<?= e($search) ?>" placeholder="Search suppliers"></div><div class="col-md-3"><select class="form-select" name="status"><option value="">Any Status</option><option value="1" <?= (($_GET['status']??'')==='1')?'selected':'' ?>>Active</option><option value="0" <?= (($_GET['status']??'')==='0')?'selected':'' ?>>Inactive</option></select></div><div class="col-md-3 d-flex gap-2"><button class="btn btn-primary w-100">Filter</button><a class="btn btn-outline-secondary w-100" href="index.php">Reset</a></div></form>
<table class="table table-bordered" id="supplierTable"><thead><tr><th>Code</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= e($r['supplier_code']) ?></td><td><?= e($r['supplier_legal_name']) ?></td><td><?= e($r['supplier_email']) ?></td><td><?= e($r['supplier_phone']) ?></td><td><span class="badge <?= $r['status']?'bg-success-subtle text-success-emphasis':'bg-secondary-subtle text-secondary-emphasis' ?>"><?= $r['status']?'Active':'Inactive' ?></span></td><td><a href="?edit=<?= $r['id'] ?>">Edit</a> | <a class="text-danger" onclick="return confirm('Delete?')" href="?delete=<?= $r['id'] ?>">Delete</a></td></tr><?php endforeach; ?></tbody></table>
</div></div></div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
