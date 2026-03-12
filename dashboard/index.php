<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

$filters = query_filters();
$params = [];
$whereSql = tracking_where_sql($filters, $params);

$kpiStmt = $pdo->prepare("SELECT COUNT(*) total_records, COALESCE(SUM(amount_aed),0) total_amount,
SUM(CASE WHEN ps.status_name='Released' THEN 1 ELSE 0 END) released_records,
SUM(CASE WHEN ps.status_name='Rejected' THEN 1 ELSE 0 END) rejected_records,
SUM(CASE WHEN ps.status_name='Deleted' THEN 1 ELSE 0 END) deleted_records,
SUM(CASE WHEN ps.status_name LIKE 'Pending%' THEN 1 ELSE 0 END) pending_records
FROM tracking_records tr
LEFT JOIN po_statuses ps ON ps.id = tr.po_status_id
LEFT JOIN contractors c ON c.id = tr.contractor_id $whereSql");
$kpiStmt->execute($params);
$kpi = $kpiStmt->fetch();

$chartStmt = $pdo->prepare("SELECT ps.status_name, COUNT(*) c FROM tracking_records tr
LEFT JOIN po_statuses ps ON ps.id=tr.po_status_id LEFT JOIN contractors c ON c.id = tr.contractor_id $whereSql GROUP BY ps.status_name");
$chartStmt->execute($params);
$statusData = $chartStmt->fetchAll();

$contractorStmt = $pdo->prepare("SELECT c.contractor_name, COUNT(*) c FROM tracking_records tr
LEFT JOIN contractors c ON c.id=tr.contractor_id $whereSql GROUP BY c.contractor_name ORDER BY c DESC LIMIT 10");
$contractorStmt->execute($params);
$contractorData = $contractorStmt->fetchAll();

$recentStmt = $pdo->prepare("SELECT tr.*, u.full_name assigned_to_name, c.contractor_name, ps.status_name, t.type_name
FROM tracking_records tr
LEFT JOIN users u ON u.id = tr.assigned_to_user_id
LEFT JOIN contractors c ON c.id = tr.contractor_id
LEFT JOIN po_statuses ps ON ps.id = tr.po_status_id
LEFT JOIN types t ON t.id = tr.type_id $whereSql ORDER BY tr.id DESC LIMIT 10");
$recentStmt->execute($params);
$recent = $recentStmt->fetchAll();
?>
<h3>Dashboard</h3>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card p-3"><h6>Total Records</h6><h4><?= (int)$kpi['total_records'] ?></h4></div></div>
    <div class="col-md-3"><div class="card p-3"><h6>Total Amount (AED)</h6><h4><?= number_format((float)$kpi['total_amount'],2) ?></h4></div></div>
    <div class="col-md-2"><div class="card p-3"><h6>Pending</h6><h4><?= (int)$kpi['pending_records'] ?></h4></div></div>
    <div class="col-md-2"><div class="card p-3"><h6>Released</h6><h4><?= (int)$kpi['released_records'] ?></h4></div></div>
    <div class="col-md-2"><div class="card p-3"><h6>Rejected</h6><h4><?= (int)$kpi['rejected_records'] ?></h4></div></div>
</div>
<div class="row g-3">
    <div class="col-md-6"><div class="card p-3"><canvas id="statusChart"></canvas></div></div>
    <div class="col-md-6"><div class="card p-3"><canvas id="contractorChart"></canvas></div></div>
</div>
<h5 class="mt-4">Recent Records</h5>
<table class="table table-striped table-sm"><thead><tr><th>PR No.</th><th>Date</th><th>Assigned</th><th>Contractor</th><th>Amount</th><th>PO No.</th><th>Status</th><th>Type</th></tr></thead><tbody>
<?php foreach ($recent as $row): ?>
<tr><td><?= e($row['pr_no']) ?></td><td><?= e($row['pr_receival_date']) ?></td><td><?= e($row['assigned_to_name']) ?></td><td><?= e($row['contractor_name']) ?></td><td><?= number_format((float)$row['amount_aed'],2) ?></td><td><?= e($row['po_no']) ?></td><td><?= e($row['status_name']) ?></td><td><?= e($row['type_name']) ?></td></tr>
<?php endforeach; ?></tbody></table>
<script>
new Chart(document.getElementById('statusChart'), {type:'pie', data:{labels:<?= json_encode(array_column($statusData,'status_name')) ?>, datasets:[{data:<?= json_encode(array_map('intval',array_column($statusData,'c'))) ?>}]}});
new Chart(document.getElementById('contractorChart'), {type:'bar', data:{labels:<?= json_encode(array_column($contractorData,'contractor_name')) ?>, datasets:[{label:'Records', data:<?= json_encode(array_map('intval',array_column($contractorData,'c'))) ?>}]}});
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
