<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
$users = $pdo->query("SELECT id,full_name FROM users WHERE is_active=1 ORDER BY full_name")->fetchAll();
$contractors = $pdo->query("SELECT id,contractor_name FROM contractors WHERE status=1 ORDER BY contractor_name")->fetchAll();
$statuses = $pdo->query("SELECT id,status_name FROM po_statuses ORDER BY id")->fetchAll();
$types = $pdo->query("SELECT id,type_name FROM types ORDER BY type_name")->fetchAll();
?>
<h3>Export Tracking Data</h3>
<div class="card p-3">
<form method="get" action="download.php" class="row g-2">
<div class="col-md-1"><input class="form-control" name="year" placeholder="Year"></div>
<div class="col-md-1"><input class="form-control" name="month" placeholder="Month"></div>
<div class="col-md-2"><select class="form-select" name="assigned_to"><option value="">Assigned To</option><?php foreach($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><select class="form-select" name="contractor_id"><option value="">Contractor</option><?php foreach($contractors as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['contractor_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><select class="form-select" name="po_status_id"><option value="">PO Status</option><?php foreach($statuses as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['status_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><select class="form-select" name="type_id"><option value="">Type</option><?php foreach($types as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['type_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><input class="form-control" name="pr_no" placeholder="PR No."></div>
<div class="col-md-2"><input class="form-control" name="po_no" placeholder="PO No."></div>
<div class="col-md-2"><input type="date" class="form-control" name="date_from"></div>
<div class="col-md-2"><input type="date" class="form-control" name="date_to"></div>
<div class="col-md-2"><select class="form-select" name="status_group"><option value="">Status Group</option><option value="released">Released only</option><option value="rejected">Rejected only</option><option value="pending">Pending only</option></select></div>
<div class="col-md-2"><select class="form-select" name="format"><option value="csv">CSV</option><option value="xlsx">Excel (if PhpSpreadsheet)</option></select></div>
<div class="col-md-2"><button class="btn btn-success w-100">Export</button></div>
</form>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
