<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
$filters = query_filters();
$params = [];
$whereSql = tracking_where_sql($filters, $params);
$sql = "SELECT tr.*, u.full_name assigned_to_name, c.contractor_name, ps.status_name, t.type_name
FROM tracking_records tr
LEFT JOIN users u ON u.id = tr.assigned_to_user_id
LEFT JOIN contractors c ON c.id = tr.contractor_id
LEFT JOIN po_statuses ps ON ps.id = tr.po_status_id
LEFT JOIN types t ON t.id = tr.type_id $whereSql ORDER BY tr.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="d-flex justify-content-between mb-2"><h3>Tracking Records</h3><?php if (can_manage()): ?><a class="btn btn-primary" href="form.php">Add Record</a><?php endif; ?></div>
<table class="table table-bordered" id="trackingTable"><thead><tr><th>S NO.</th><th>PR Date</th><th>PR No.</th><th>Assigned</th><th>Contractor</th><th>Amount</th><th>PO No.</th><th>Status</th><th>Type</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr>
<td><?= e($row['s_no']) ?></td><td><?= e($row['pr_receival_date']) ?></td><td><?= e($row['pr_no']) ?></td><td><?= e($row['assigned_to_name']) ?></td><td><?= e($row['contractor_name']) ?></td><td><?= number_format((float)$row['amount_aed'],2) ?></td><td><?= e($row['po_no']) ?></td><td><?= e($row['status_name']) ?></td><td><?= e($row['type_name']) ?></td>
<td><a class="btn btn-sm btn-info" href="view.php?id=<?= $row['id'] ?>">View</a>
<?php if (can_manage()): ?><a class="btn btn-sm btn-warning" href="form.php?id=<?= $row['id'] ?>">Edit</a><?php endif; ?>
<?php if (has_role(['Admin'])): ?><a class="btn btn-sm btn-danger" onclick="return confirm('Delete?')" href="delete.php?id=<?= $row['id'] ?>">Delete</a><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
