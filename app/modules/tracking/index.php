<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $ids = $_POST['ids'] ?? [];
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        db()->prepare("DELETE FROM tracking_records WHERE id IN ($in)")->execute($ids);
        log_audit('delete', 'tracking', 'Bulk delete tracking records');
    }
}
$rows = db()->query('SELECT * FROM tracking_records ORDER BY pr_receival_date DESC LIMIT 200')->fetchAll();
?>
<div class="card p-3">
<div class="d-flex justify-content-between mb-2"><h5>Tracking Database</h5><form method="post"><input type="hidden" name="action" value="delete"><button class="btn btn-danger btn-sm">Bulk Delete</button></div>
<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th><input type="checkbox" onclick="$('input[name*=ids]').prop('checked',this.checked)"></th><th>S NO.</th><th>PR No.</th><th>Assigned To</th><th>Amount</th><th>Contractor</th><th>PO No.</th><th>PO Status</th><th>Type</th></tr></thead><tbody>
<?php foreach($rows as $r): ?>
<tr><td><input type="checkbox" name="ids[]" value="<?= $r['id'] ?>"></td><td><?= e($r['s_no']) ?></td><td><?= e($r['pr_no']) ?></td><td><?= e($r['assigned_to']) ?></td><td><?= number_format((float)$r['amount_aed'],2) ?></td><td><?= e($r['contractor_name']) ?></td><td><?= e($r['po_no']) ?></td><td><span class="badge bg-<?= $r['po_status']==='Released'?'success':($r['po_status']==='Rejected'?'danger':'warning') ?> status-badge"><?= e($r['po_status']) ?></span></td><td><?= e($r['type']) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></form></div>
