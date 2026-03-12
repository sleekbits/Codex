<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT tr.*, u.full_name assigned_to_name, c.contractor_name, ps.status_name, t.type_name
FROM tracking_records tr
LEFT JOIN users u ON u.id = tr.assigned_to_user_id
LEFT JOIN contractors c ON c.id = tr.contractor_id
LEFT JOIN po_statuses ps ON ps.id = tr.po_status_id
LEFT JOIN types t ON t.id = tr.type_id
WHERE tr.id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) { echo '<div class="alert alert-danger">Record not found</div>'; require_once __DIR__ . '/../layouts/footer.php'; exit; }
?>
<h3>Record Details</h3>
<table class="table table-bordered">
<?php foreach ($row as $k => $v): ?><tr><th><?= e($k) ?></th><td><?= e((string)$v) ?></td></tr><?php endforeach; ?>
</table>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
