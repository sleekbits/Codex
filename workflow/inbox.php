<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../includes/workflow_engine.php';
require_permission('workflow_action_view');

$users = $pdo->query('SELECT id, full_name FROM users WHERE is_active=1 ORDER BY full_name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stepId = (int)($_POST['step_id'] ?? 0);
    $action = trim((string)($_POST['action_type'] ?? ''));
    $comment = trim((string)($_POST['comment'] ?? ''));
    $target = $_POST['target_user_id'] !== '' ? (int)$_POST['target_user_id'] : null;

    $result = workflow_action_step($stepId, $action, (int)user()['id'], $comment, $target);
    $_SESSION[$result['ok'] ? 'flash_success' : 'flash_error'] = $result['message'];
    header('Location: inbox.php');
    exit;
}

$pending = $pdo->prepare("SELECT s.*, t.document_type, t.document_number, t.current_status FROM workflow_transaction_steps s JOIN workflow_transactions t ON t.id=s.workflow_transaction_id WHERE s.action_status='Pending' AND (s.assigned_user_id=? OR ?=1) ORDER BY s.updated_at DESC");
$pending->execute([user()['id'], has_permission('workflow_sign_on_behalf') ? 1 : 0]);
$rows = $pending->fetchAll();

$history = $pdo->prepare('SELECT * FROM workflow_audit_logs ORDER BY id DESC LIMIT 100');
$history->execute();
?>
<h3>Workflow Action Inbox</h3>
<div class="card p-3 mb-3">
  <h6>Pending Actions</h6>
  <table class="table table-sm">
    <thead><tr><th>Document</th><th>Stage</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach($rows as $r): ?>
      <tr>
        <td><strong><?= e($r['document_type']) ?></strong> - <?= e($r['document_number']) ?><div class="small text-muted-custom">Assigned User ID: <?= (int)$r['assigned_user_id'] ?></div></td>
        <td><?= (int)$r['stage_no'] ?> - <?= e($r['stage_name']) ?><div class="small text-muted-custom"><?= e($r['stage_type']) ?></div></td>
        <td><?= e($r['current_status']) ?></td>
        <td>
          <form method="post" class="d-flex flex-column gap-1" style="min-width:260px;">
            <input type="hidden" name="step_id" value="<?= (int)$r['id'] ?>">
            <select class="form-select form-select-sm" name="action_type" required>
              <option value="endorse">Endorse</option><option value="approve">Approve</option><option value="reject">Reject</option><option value="return">Return for Correction</option><option value="delegate">Delegate</option><?php if (has_permission('workflow_reassign')): ?><option value="reassign">Admin Reassign</option><?php endif; ?>
            </select>
            <select class="form-select form-select-sm" name="target_user_id"><option value="">Target user (delegate/reassign)</option><?php foreach($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['full_name']) ?></option><?php endforeach; ?></select>
            <textarea class="form-control form-control-sm" name="comment" placeholder="Comment (required for reject/return)"></textarea>
            <button class="btn btn-sm btn-primary">Submit Action</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card p-3">
  <h6>Approval History / Audit Trail</h6>
  <table class="table table-sm table-bordered">
    <thead><tr><th>Date</th><th>Document</th><th>Action</th><th>By</th><th>Comment</th></tr></thead>
    <tbody>
      <?php foreach($history->fetchAll() as $log): ?>
      <tr>
        <td><?= e($log['created_at']) ?></td>
        <td><?= e($log['document_type']) ?> #<?= (int)$log['document_id'] ?></td>
        <td><?= e($log['action_type']) ?></td>
        <td><?= (int)$log['action_by'] ?></td>
        <td><?= e($log['comments'] ?? '-') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
