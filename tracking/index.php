<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

$filters = query_filters();
$filters['pr_no'] = trim($_GET['pr_no'] ?? '');
$filters['po_no'] = trim($_GET['po_no'] ?? '');
$filters['date_from'] = trim($_GET['date_from'] ?? '');
$filters['date_to'] = trim($_GET['date_to'] ?? '');
$params = [];
$whereSql = tracking_where_sql($filters, $params);
if ($filters['pr_no']) { $whereSql .= ' AND tr.pr_no LIKE ?'; $params[] = '%' . $filters['pr_no'] . '%'; }
if ($filters['po_no']) { $whereSql .= ' AND tr.po_no LIKE ?'; $params[] = '%' . $filters['po_no'] . '%'; }
if ($filters['date_from']) { $whereSql .= ' AND tr.pr_receival_date >= ?'; $params[] = $filters['date_from']; }
if ($filters['date_to']) { $whereSql .= ' AND tr.pr_receival_date <= ?'; $params[] = $filters['date_to']; }

$sql = "SELECT tr.*, u.full_name assigned_to_name, c.contractor_name, ps.status_name, t.type_name,
(prd.cnt > 1) pr_duplicate, (pod.cnt > 1 AND tr.po_no IS NOT NULL AND tr.po_no <> '') po_duplicate
FROM tracking_records tr
LEFT JOIN users u ON u.id = tr.assigned_to_user_id
LEFT JOIN contractors c ON c.id = tr.contractor_id
LEFT JOIN po_statuses ps ON ps.id = tr.po_status_id
LEFT JOIN types t ON t.id = tr.type_id
LEFT JOIN (SELECT pr_no, COUNT(*) cnt FROM tracking_records WHERE deleted_at IS NULL GROUP BY pr_no) prd ON prd.pr_no=tr.pr_no
LEFT JOIN (SELECT po_no, COUNT(*) cnt FROM tracking_records WHERE deleted_at IS NULL AND po_no IS NOT NULL AND po_no<>'' GROUP BY po_no) pod ON pod.po_no=tr.po_no
$whereSql ORDER BY tr.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$users = $pdo->query("SELECT id,full_name FROM users WHERE is_active=1 ORDER BY full_name")->fetchAll();
$contractors = $pdo->query("SELECT id,contractor_name FROM contractors WHERE status=1 ORDER BY contractor_name")->fetchAll();
$statuses = $pdo->query("SELECT id,status_name FROM po_statuses ORDER BY id")->fetchAll();
$types = $pdo->query("SELECT id,type_name FROM types ORDER BY type_name")->fetchAll();
$years = $pdo->query("SELECT DISTINCT YEAR(pr_receival_date) y FROM tracking_records WHERE deleted_at IS NULL ORDER BY y DESC")->fetchAll();
$months = $pdo->query("SELECT DISTINCT MONTH(pr_receival_date) m FROM tracking_records WHERE deleted_at IS NULL ORDER BY m ASC")->fetchAll();
?>
<div class="d-flex justify-content-between mb-2 align-items-center"><h3>Tracking Records</h3><?php if (can_manage()): ?><a class="btn btn-primary" href="form.php"><i class="bi bi-plus-lg me-1"></i>Add Record</a><?php endif; ?></div>

<div class="filter-card p-3 mb-3">
<form class="row g-2" method="get">
<div class="col-md-2"><label class="form-label">Year</label><select class="form-select multi-select" name="year[]" multiple><?php foreach($years as $y): ?><option value="<?= $y['y'] ?>" <?= selected_multi($filters['year'], $y['y']) ?>><?= $y['y'] ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Month</label><select class="form-select multi-select" name="month[]" multiple><?php foreach($months as $m): $ml=date('M', mktime(0,0,0,$m['m'],1)); ?><option value="<?= $m['m'] ?>" <?= selected_multi($filters['month'], $m['m']) ?>><?= $ml ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Assigned To</label><select class="form-select multi-select" name="assigned_to[]" multiple><?php foreach($users as $u): ?><option value="<?= $u['id'] ?>" <?= selected_multi($filters['assigned_to'], $u['id']) ?>><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Contractor</label><select class="form-select multi-select" name="contractor_id[]" multiple><?php foreach($contractors as $c): ?><option value="<?= $c['id'] ?>" <?= selected_multi($filters['contractor_id'], $c['id']) ?>><?= e($c['contractor_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">PO Status</label><select class="form-select multi-select" name="po_status_id[]" multiple><?php foreach($statuses as $s): ?><option value="<?= $s['id'] ?>" <?= selected_multi($filters['po_status_id'], $s['id']) ?>><?= e($s['status_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">Type</label><select class="form-select multi-select" name="type_id[]" multiple><?php foreach($types as $t): ?><option value="<?= $t['id'] ?>" <?= selected_multi($filters['type_id'], $t['id']) ?>><?= e($t['type_name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="form-label">PR No.</label><input class="form-control" name="pr_no" value="<?= e($filters['pr_no']) ?>"></div>
<div class="col-md-2"><label class="form-label">PO No.</label><input class="form-control" name="po_no" value="<?= e($filters['po_no']) ?>"></div>
<div class="col-md-2"><label class="form-label">Start Date</label><input type="date" class="form-control" name="date_from" value="<?= e($filters['date_from']) ?>"></div>
<div class="col-md-2"><label class="form-label">End Date</label><input type="date" class="form-control" name="date_to" value="<?= e($filters['date_to']) ?>"></div>
<div class="col-md-4"><label class="form-label">Search</label><input class="form-control" name="search" value="<?= e($filters['search']) ?>" placeholder="Search PR/PO/description..."></div>
<div class="col-md-2 d-flex gap-2 align-items-end"><button class="btn btn-primary w-100">Filter</button><a class="btn btn-outline-secondary w-100" href="index.php">Reset</a></div>
</form>
</div>

<form method="post" action="bulk_delete.php" onsubmit="return confirm('Delete selected records?');">
<div id="bulkToolbar" class="d-none mb-2 p-2 bg-light border rounded">
  <span class="me-2"><strong id="bulkCount">0</strong> selected</span>
  <?php if (has_role(['Admin'])): ?><button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Bulk Delete</button><?php endif; ?>
</div>
<table class="table table-bordered align-middle" id="trackingTable"><thead><tr><th><input type="checkbox" id="selectAllRows"></th><th>S NO.</th><th>PR Date</th><th>PR No.</th><th>Assigned</th><th>Contractor</th><th>Amount</th><th>PO No.</th><th>Status</th><th>Type</th><th>PO Release Date</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr>
<td><input type="checkbox" class="row-check" name="ids[]" value="<?= $row['id'] ?>"></td>
<td><?= e($row['s_no']) ?></td>
<td><?= e(format_date($row['pr_receival_date'])) ?></td>
<td><?= e($row['pr_no']) ?> <?php if($row['pr_duplicate']): ?><span class="dup-badge">Duplicate</span><?php endif; ?></td>
<td><?= e($row['assigned_to_name']) ?></td>
<td><?= e($row['contractor_name']) ?></td>
<td><?= number_format((float)$row['amount_aed'],2) ?></td>
<td><?= e($row['po_no']) ?> <?php if($row['po_duplicate']): ?><span class="dup-badge">Duplicate</span><?php endif; ?></td>
<td><span class="badge badge-pill <?= status_badge_class($row['status_name']) ?>"><?= e($row['status_name']) ?></span></td>
<td><?= e($row['type_name']) ?></td>
<td><?= e(format_date($row['po_release_date'])) ?></td>
<td class="text-nowrap">
<a class="btn btn-sm action-btn btn-outline-info" title="View" href="view.php?id=<?= $row['id'] ?>"><i class="bi bi-eye"></i></a>
<?php if (can_manage()): ?><a class="btn btn-sm action-btn btn-outline-warning" title="Edit" href="form.php?id=<?= $row['id'] ?>"><i class="bi bi-pencil"></i></a><?php endif; ?>
<?php if (has_role(['Admin'])): ?><a class="btn btn-sm action-btn btn-outline-danger" title="Delete" onclick="return confirm('Delete?')" href="delete.php?id=<?= $row['id'] ?>"><i class="bi bi-trash"></i></a><?php endif; ?>
</td></tr><?php endforeach; ?>
</tbody></table>
</form>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
