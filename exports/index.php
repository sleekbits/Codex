<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
$users = $pdo->query("SELECT id,full_name FROM users WHERE is_active=1 ORDER BY full_name")->fetchAll();
$contractors = $pdo->query("SELECT id,contractor_name FROM contractors WHERE status=1 ORDER BY contractor_name")->fetchAll();
$statuses = $pdo->query("SELECT id,status_name FROM po_statuses ORDER BY id")->fetchAll();
$types = $pdo->query("SELECT id,type_name FROM types ORDER BY type_name")->fetchAll();
$years = $pdo->query("SELECT DISTINCT YEAR(pr_receival_date) y FROM tracking_records WHERE deleted_at IS NULL ORDER BY y DESC")->fetchAll();
$months = $pdo->query("SELECT DISTINCT MONTH(pr_receival_date) m FROM tracking_records WHERE deleted_at IS NULL ORDER BY m ASC")->fetchAll();
?>
<h3>Export Tracking Data</h3>
<div class="card p-3">
<form method="get" action="download.php" class="row g-2">
<div class="col-md-2"><label class="form-label">Year</label><select class="form-select multi-select" multiple name="year[]"><?php foreach($years as $y): ?><option value="<?= $y['y'] ?>"><?= $y['y'] ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Month</label><select class="form-select multi-select" multiple name="month[]"><?php foreach($months as $m): $ml=date('M', mktime(0,0,0,$m['m'],1)); ?><option value="<?= $m['m'] ?>"><?= $ml ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Assigned To</label><select class="form-select multi-select" name="assigned_to[]" multiple><?php foreach($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Contractor</label><select class="form-select multi-select" name="contractor_id[]" multiple><?php foreach($contractors as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['contractor_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">PO Status</label><select class="form-select multi-select" name="po_status_id[]" multiple><?php foreach($statuses as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['status_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Type</label><select class="form-select multi-select" name="type_id[]" multiple><?php foreach($types as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['type_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">PR No.</label><input class="form-control" name="pr_no"></div>
<div class="col-md-2"><label class="form-label">PO No.</label><input class="form-control" name="po_no"></div>
<div class="col-md-2"><label class="form-label">Start Date</label><input type="date" class="form-control" name="date_from"></div>
<div class="col-md-2"><label class="form-label">End Date</label><input type="date" class="form-control" name="date_to"></div>
<div class="col-md-2"><label class="form-label">Status Group</label><select class="form-select" name="status_group"><option value="">Any</option><option value="released">Released only</option><option value="rejected">Rejected only</option><option value="pending">Pending only</option></select></div>
<div class="col-md-2"><label class="form-label">Format</label><select class="form-select" name="format"><option value="csv">CSV</option><option value="xlsx">Excel (if PhpSpreadsheet)</option></select></div>
<div class="col-md-4 d-flex gap-2 align-items-end"><button class="btn btn-success w-100">Export</button><a class="btn btn-outline-secondary w-100" href="index.php">Reset</a></div>
</form>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
