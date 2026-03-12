<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin', 'Manager']);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = ['s_no'=>'','pr_receival_date'=>'','pr_no'=>'','assigned_to_user_id'=>'','brief_description'=>'','wo_dwo_vo_ref'=>'','amount_aed'=>'','contract_reference'=>'','contractor_id'=>'','po_no'=>'','po_status_id'=>'','po_release_date'=>'','remarks'=>'','type_id'=>''];
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM tracking_records WHERE id = ? AND deleted_at IS NULL');
    $stmt->execute([$id]);
    $data = $stmt->fetch() ?: $data;
}
$users = $pdo->query("SELECT id, full_name FROM users WHERE is_active=1 ORDER BY full_name")->fetchAll();
$contractors = $pdo->query("SELECT id, contractor_name FROM contractors WHERE status=1 ORDER BY contractor_name")->fetchAll();
$statuses = $pdo->query("SELECT id, status_name FROM po_statuses ORDER BY id")->fetchAll();
$types = $pdo->query("SELECT id, type_name FROM types ORDER BY id")->fetchAll();
?>
<h3><?= $id ? 'Edit' : 'Add' ?> Tracking Record</h3>
<form method="post" action="save.php">
<input type="hidden" name="id" value="<?= $id ?>">
<div class="row g-3">
<div class="col-md-2"><label class="form-label">S NO.</label><input class="form-control" name="s_no" value="<?= e($data['s_no']) ?>"></div>
<div class="col-md-3"><label class="form-label">PR Receival Date*</label><input type="date" class="form-control" name="pr_receival_date" value="<?= e($data['pr_receival_date']) ?>" required></div>
<div class="col-md-3"><label class="form-label">PR No.*</label><input class="form-control" name="pr_no" value="<?= e($data['pr_no']) ?>" required></div>
<div class="col-md-4"><label class="form-label">Assigned to*</label><select class="form-select" name="assigned_to_user_id" required><?php foreach($users as $u): ?><option value="<?= $u['id'] ?>" <?= $data['assigned_to_user_id']==$u['id']?'selected':'' ?>><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Brief Description*</label><textarea class="form-control" name="brief_description" required><?= e($data['brief_description']) ?></textarea></div>
<div class="col-md-3"><label class="form-label">WO/DWO/VO Ref.</label><input class="form-control" name="wo_dwo_vo_ref" value="<?= e($data['wo_dwo_vo_ref']) ?>"></div>
<div class="col-md-3"><label class="form-label">Amount (AED)</label><input type="number" step="0.01" class="form-control" name="amount_aed" value="<?= e($data['amount_aed']) ?>"></div>
<div class="col-md-4"><label class="form-label">Contract Reference</label><input class="form-control" name="contract_reference" value="<?= e($data['contract_reference']) ?>"></div>
<div class="col-md-4"><label class="form-label">Contractor's Name*</label><select class="form-select" name="contractor_id" required><?php foreach($contractors as $c): ?><option value="<?= $c['id'] ?>" <?= $data['contractor_id']==$c['id']?'selected':'' ?>><?= e($c['contractor_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">PO No.</label><input class="form-control" name="po_no" value="<?= e($data['po_no']) ?>"></div>
<div class="col-md-4"><label class="form-label">PO Status*</label><select class="form-select" name="po_status_id" required><?php foreach($statuses as $s): ?><option value="<?= $s['id'] ?>" <?= $data['po_status_id']==$s['id']?'selected':'' ?>><?= e($s['status_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">PO Release Date</label><input type="date" class="form-control" name="po_release_date" value="<?= e($data['po_release_date']) ?>"></div>
<div class="col-md-4"><label class="form-label">Type*</label><select class="form-select" name="type_id" required><?php foreach($types as $t): ?><option value="<?= $t['id'] ?>" <?= $data['type_id']==$t['id']?'selected':'' ?>><?= e($t['type_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks"><?= e($data['remarks']) ?></textarea></div>
</div><button class="btn btn-success mt-3">Save</button></form>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
