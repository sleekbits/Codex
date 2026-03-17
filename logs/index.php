<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_permission('logs_view');

$limit = max(50, min(1000, (int)($_GET['limit'] ?? 200)));

$rows = $pdo->prepare("SELECT created_at, action AS action_type, description AS details, user_id AS actor_id, ip_address, 'system' AS source FROM activity_logs
UNION ALL
SELECT created_at, action_type, comments AS details, action_by AS actor_id, NULL AS ip_address, 'workflow' AS source FROM workflow_audit_logs
ORDER BY created_at DESC
LIMIT ?");
$rows->bindValue(1, $limit, PDO::PARAM_INT);
$rows->execute();
$data = $rows->fetchAll();

$userMap = [];
foreach ($pdo->query('SELECT id, full_name FROM users') as $u) {
    $userMap[(int)$u['id']] = $u['full_name'];
}
?>
<h3>Audit Trail & Logs</h3>
<div class="card p-3">
  <form class="row g-2 mb-3">
    <div class="col-md-3"><label class="form-label">Rows</label><input type="number" name="limit" class="form-control" value="<?= (int)$limit ?>"></div>
    <div class="col-md-2 d-flex align-items-end"><button class="btn btn-outline-primary w-100">Refresh</button></div>
  </form>
  <div class="table-responsive">
  <table class="table table-sm table-bordered align-middle">
    <thead><tr><th>When</th><th>Source</th><th>Action</th><th>Actor</th><th>Details</th><th>IP</th></tr></thead>
    <tbody>
      <?php foreach($data as $row): ?>
      <tr>
        <td><?= e($row['created_at']) ?></td>
        <td><span class="badge bg-light text-dark"><?= e($row['source']) ?></span></td>
        <td><?= e($row['action_type']) ?></td>
        <td><?= e($userMap[(int)$row['actor_id']] ?? ('User #'.(int)$row['actor_id'])) ?></td>
        <td><?= e($row['details'] ?: '-') ?></td>
        <td><?= e($row['ip_address'] ?: '-') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
