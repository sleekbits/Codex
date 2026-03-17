<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_permission('workflow_hierarchy_view');

$roles = $pdo->query('SELECT id, role_name FROM roles ORDER BY role_name')->fetchAll();
$users = $pdo->query('SELECT id, full_name FROM users WHERE is_active=1 ORDER BY full_name')->fetchAll();
$types = $pdo->query('SELECT type_name FROM types ORDER BY type_name')->fetchAll();
$docCategories = ['PR', 'PO', 'Tendering Strategy', 'ARR (Competitive)', 'ARR (Non-Competitive)', 'VORR', 'VO'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_header' && has_permission('workflow_hierarchy_add')) {
    $id = (int)($_POST['id'] ?? 0);
    $data = [
        trim($_POST['hierarchy_name']),
        trim($_POST['document_category']),
        trim($_POST['applies_to_module']),
        trim($_POST['type_mode']),
        (float)($_POST['threshold_from'] ?: 0),
        (float)($_POST['threshold_to'] ?: 999999999),
        trim($_POST['department']),
        trim($_POST['business_unit']),
        (int)($_POST['is_active'] ?? 1),
        trim($_POST['remarks']) ?: null,
        user()['id'],
    ];

    if ($id > 0) {
        $pdo->prepare('UPDATE workflow_hierarchy_headers SET hierarchy_name=?,document_category=?,applies_to_module=?,type_mode=?,threshold_from=?,threshold_to=?,department=?,business_unit=?,is_active=?,remarks=?,updated_by=?,updated_at=NOW() WHERE id=?')
            ->execute([...$data, $id]);
        $headerId = $id;
    } else {
        $pdo->prepare('INSERT INTO workflow_hierarchy_headers (hierarchy_name,document_category,applies_to_module,type_mode,threshold_from,threshold_to,department,business_unit,is_active,remarks,created_by,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())')
            ->execute([...$data, user()['id']]);
        $headerId = (int)$pdo->lastInsertId();
    }

    $pdo->prepare('DELETE FROM workflow_hierarchy_types WHERE hierarchy_header_id=?')->execute([$headerId]);
    foreach ((array)($_POST['types'] ?? []) as $typeName) {
        $clean = trim((string)$typeName);
        if ($clean === '') { continue; }
        $pdo->prepare('INSERT INTO workflow_hierarchy_types(hierarchy_header_id,type_name,created_at,updated_at) VALUES (?,?,NOW(),NOW())')
            ->execute([$headerId, $clean]);
    }

    $_SESSION['flash_success'] = 'Workflow hierarchy saved.';
    header('Location: index.php?edit=' . $headerId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_stage' && has_permission('workflow_hierarchy_edit')) {
    $id = (int)($_POST['stage_id'] ?? 0);
    $headerId = (int)($_POST['hierarchy_header_id'] ?? 0);
    $values = [
        $headerId,
        trim($_POST['stage_name']),
        (int)$_POST['stage_no'],
        trim($_POST['stage_type']),
        trim($_POST['approval_mode']),
        $_POST['role_id'] !== '' ? (int)$_POST['role_id'] : null,
        $_POST['user_id'] !== '' ? (int)$_POST['user_id'] : null,
        trim($_POST['parallel_rule']) ?: null,
        (int)!empty($_POST['allow_delegate']),
        (int)!empty($_POST['allow_sign_on_behalf']),
        (int)!empty($_POST['is_mandatory']),
        (int)!empty($_POST['is_active']),
        trim($_POST['remarks']) ?: null,
    ];

    if ($id > 0) {
        $pdo->prepare('UPDATE workflow_hierarchy_stages SET hierarchy_header_id=?,stage_name=?,stage_no=?,stage_type=?,approval_mode=?,role_id=?,user_id=?,parallel_rule=?,allow_delegate=?,allow_sign_on_behalf=?,is_mandatory=?,is_active=?,remarks=?,updated_at=NOW() WHERE id=?')
            ->execute([...$values, $id]);
    } else {
        $pdo->prepare('INSERT INTO workflow_hierarchy_stages (hierarchy_header_id,stage_name,stage_no,stage_type,approval_mode,role_id,user_id,parallel_rule,allow_delegate,allow_sign_on_behalf,is_mandatory,is_active,remarks,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())')
            ->execute($values);
    }

    $_SESSION['flash_success'] = 'Stage saved.';
    header('Location: index.php?edit=' . $headerId);
    exit;
}

if (isset($_GET['delete']) && has_permission('workflow_hierarchy_delete')) {
    $pdo->prepare('DELETE FROM workflow_hierarchy_headers WHERE id=?')->execute([(int)$_GET['delete']]);
    $_SESSION['flash_success'] = 'Hierarchy deleted.';
    header('Location: index.php');
    exit;
}

if (isset($_GET['toggle']) && has_permission('workflow_hierarchy_edit')) {
    $id = (int)$_GET['toggle'];
    $pdo->prepare('UPDATE workflow_hierarchy_headers SET is_active=IF(is_active=1,0,1), updated_by=?, updated_at=NOW() WHERE id=?')->execute([user()['id'], $id]);
    $_SESSION['flash_success'] = 'Hierarchy status updated.';
    header('Location: index.php');
    exit;
}

if (isset($_GET['clone']) && has_permission('workflow_hierarchy_clone')) {
    $id = (int)$_GET['clone'];
    $q = $pdo->prepare('SELECT * FROM workflow_hierarchy_headers WHERE id=?');
    $q->execute([$id]);
    $header = $q->fetch();
    if ($header) {
        $pdo->prepare('INSERT INTO workflow_hierarchy_headers(hierarchy_name,document_category,applies_to_module,type_mode,threshold_from,threshold_to,department,business_unit,is_active,remarks,created_by,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())')
            ->execute([$header['hierarchy_name'] . ' (Copy)', $header['document_category'], $header['applies_to_module'], $header['type_mode'], $header['threshold_from'], $header['threshold_to'], $header['department'], $header['business_unit'], 0, $header['remarks'], user()['id'], user()['id']]);
        $newId = (int)$pdo->lastInsertId();

        $pdo->prepare('INSERT INTO workflow_hierarchy_types(hierarchy_header_id,type_name,type_group,created_at,updated_at) SELECT ?,type_name,type_group,NOW(),NOW() FROM workflow_hierarchy_types WHERE hierarchy_header_id=?')->execute([$newId, $id]);
        $pdo->prepare('INSERT INTO workflow_hierarchy_stages(hierarchy_header_id,stage_name,stage_no,stage_type,approval_mode,role_id,user_id,parallel_rule,allow_delegate,allow_sign_on_behalf,is_mandatory,is_active,remarks,created_at,updated_at) SELECT ?,stage_name,stage_no,stage_type,approval_mode,role_id,user_id,parallel_rule,allow_delegate,allow_sign_on_behalf,is_mandatory,is_active,remarks,NOW(),NOW() FROM workflow_hierarchy_stages WHERE hierarchy_header_id=?')->execute([$newId, $id]);

        $_SESSION['flash_success'] = 'Hierarchy cloned.';
    }
    header('Location: index.php?edit=' . ($newId ?? 0));
    exit;
}

$filters = ['document_category' => trim($_GET['document_category'] ?? ''), 'active' => trim($_GET['active'] ?? '')];
$sql = 'SELECT h.*, u.full_name created_by_name FROM workflow_hierarchy_headers h LEFT JOIN users u ON u.id=h.created_by WHERE 1=1';
$params = [];
if ($filters['document_category'] !== '') { $sql .= ' AND h.document_category=?'; $params[] = $filters['document_category']; }
if ($filters['active'] !== '') { $sql .= ' AND h.is_active=?'; $params[] = (int)$filters['active']; }
$sql .= ' ORDER BY h.updated_at DESC, h.id DESC';
$st = $pdo->prepare($sql); $st->execute($params); $headers = $st->fetchAll();

$editId = (int)($_GET['edit'] ?? 0);
$editHeader = null;
$editTypes = [];
$stages = [];
if ($editId > 0) {
    $st = $pdo->prepare('SELECT * FROM workflow_hierarchy_headers WHERE id=?'); $st->execute([$editId]); $editHeader = $st->fetch();
    $st = $pdo->prepare('SELECT type_name FROM workflow_hierarchy_types WHERE hierarchy_header_id=?'); $st->execute([$editId]); $editTypes = array_column($st->fetchAll(), 'type_name');
    $st = $pdo->prepare('SELECT s.*, r.role_name, u.full_name FROM workflow_hierarchy_stages s LEFT JOIN roles r ON r.id=s.role_id LEFT JOIN users u ON u.id=s.user_id WHERE s.hierarchy_header_id=? ORDER BY stage_no, id');
    $st->execute([$editId]); $stages = $st->fetchAll();
}
?>
<h3>Workflow Hierarchy Management</h3>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card p-3">
      <h6><?= $editHeader ? 'Edit' : 'Create' ?> Hierarchy Header</h6>
      <form method="post" class="row g-2">
        <input type="hidden" name="action" value="save_header"><input type="hidden" name="id" value="<?= (int)($editHeader['id'] ?? 0) ?>">
        <div class="col-12"><label class="form-label">Hierarchy Name</label><input class="form-control" name="hierarchy_name" required value="<?= e($editHeader['hierarchy_name'] ?? '') ?>"></div>
        <div class="col-6"><label class="form-label">Document Category</label><select class="form-select" name="document_category"><?php foreach($docCategories as $cat): ?><option <?= (($editHeader['document_category'] ?? '')===$cat)?'selected':'' ?>><?= e($cat) ?></option><?php endforeach; ?></select></div>
        <div class="col-6"><label class="form-label">Applies To</label><select class="form-select" name="applies_to_module"><option value="ALL" <?= (($editHeader['applies_to_module'] ?? '')==='ALL')?'selected':'' ?>>All Modules</option><option value="PR" <?= (($editHeader['applies_to_module'] ?? '')==='PR')?'selected':'' ?>>PR</option><option value="PO" <?= (($editHeader['applies_to_module'] ?? '')==='PO')?'selected':'' ?>>PO</option></select></div>
        <div class="col-6"><label class="form-label">Type Mode</label><select class="form-select" name="type_mode"><option value="all_types" <?= (($editHeader['type_mode'] ?? '')==='all_types')?'selected':'' ?>>All Types</option><option value="selected_types" <?= (($editHeader['type_mode'] ?? '')==='selected_types')?'selected':'' ?>>Selected Types</option><option value="grouped_types" <?= (($editHeader['type_mode'] ?? '')==='grouped_types')?'selected':'' ?>>Grouped Types</option></select></div>
        <div class="col-6"><label class="form-label">Types</label><select class="form-select" multiple name="types[]"><?php foreach($types as $t): ?><option value="<?= e($t['type_name']) ?>" <?= in_array($t['type_name'], $editTypes, true)?'selected':'' ?>><?= e($t['type_name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-6"><label class="form-label">Threshold From</label><input class="form-control" type="number" step="0.01" name="threshold_from" value="<?= e((string)($editHeader['threshold_from'] ?? '0')) ?>"></div>
        <div class="col-6"><label class="form-label">Threshold To</label><input class="form-control" type="number" step="0.01" name="threshold_to" value="<?= e((string)($editHeader['threshold_to'] ?? '999999999')) ?>"></div>
        <div class="col-6"><label class="form-label">Department</label><input class="form-control" name="department" value="<?= e($editHeader['department'] ?? '') ?>"></div>
        <div class="col-6"><label class="form-label">Business Unit</label><input class="form-control" name="business_unit" value="<?= e($editHeader['business_unit'] ?? '') ?>"></div>
        <div class="col-6"><label class="form-label">Status</label><select class="form-select" name="is_active"><option value="1" <?= (($editHeader['is_active'] ?? 1)==1)?'selected':'' ?>>Active</option><option value="0" <?= (($editHeader['is_active'] ?? 1)==0)?'selected':'' ?>>Inactive</option></select></div>
        <div class="col-12"><label class="form-label">Remarks</label><textarea class="form-control" name="remarks"><?= e($editHeader['remarks'] ?? '') ?></textarea></div>
        <div class="col-12"><button class="btn btn-primary">Save Hierarchy</button></div>
      </form>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card p-3 mb-3">
      <form class="row g-2 mb-2" method="get">
        <div class="col-md-4"><select class="form-select" name="document_category"><option value="">All Document Categories</option><?php foreach($docCategories as $cat): ?><option <?= $filters['document_category']===$cat?'selected':'' ?>><?= e($cat) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><select class="form-select" name="active"><option value="">All Status</option><option value="1" <?= $filters['active']==='1'?'selected':'' ?>>Active</option><option value="0" <?= $filters['active']==='0'?'selected':'' ?>>Inactive</option></select></div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div>
      </form>
      <table class="table table-sm">
        <thead><tr><th>Hierarchy</th><th>Category</th><th>Threshold</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach($headers as $h): ?>
          <tr>
            <td><?= e($h['hierarchy_name']) ?><div class="small text-muted-custom">Module: <?= e($h['applies_to_module']) ?> | Type mode: <?= e($h['type_mode']) ?></div></td>
            <td><?= e($h['document_category']) ?></td>
            <td><?= number_format((float)$h['threshold_from'],2) ?> - <?= number_format((float)$h['threshold_to'],2) ?></td>
            <td><?= $h['is_active'] ? 'Active' : 'Inactive' ?></td>
            <td><a href="?edit=<?= $h['id'] ?>">Edit</a> | <a href="?clone=<?= $h['id'] ?>">Clone</a> | <a href="?toggle=<?= $h['id'] ?>">Activate/Deactivate</a> | <a class="text-danger" onclick="return confirm('Delete hierarchy?')" href="?delete=<?= $h['id'] ?>">Delete</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($editHeader): ?>
    <div class="card p-3">
      <h6>Stage Builder: <?= e($editHeader['hierarchy_name']) ?></h6>
      <form method="post" class="row g-2 border-bottom pb-2 mb-2">
        <input type="hidden" name="action" value="save_stage"><input type="hidden" name="hierarchy_header_id" value="<?= (int)$editHeader['id'] ?>"><input type="hidden" name="stage_id" value="0">
        <div class="col-md-3"><input class="form-control" name="stage_name" placeholder="Stage name" required></div>
        <div class="col-md-1"><input class="form-control" type="number" name="stage_no" value="1"></div>
        <div class="col-md-2"><select class="form-select" name="stage_type"><option>Endorsement</option><option>Approval</option><option>Parallel Approval</option></select></div>
        <div class="col-md-2"><select class="form-select" name="approval_mode"><option value="sequential">Sequential</option><option value="parallel">Parallel</option></select></div>
        <div class="col-md-2"><select class="form-select" name="role_id"><option value="">Role</option><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['role_name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><select class="form-select" name="user_id"><option value="">Person</option><?php foreach($users as $u): ?><option value="<?= $u['id'] ?>"><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><input class="form-control" name="parallel_rule" placeholder="all_must_approve"></div>
        <div class="col-md-6 d-flex gap-3 align-items-center"><label><input type="checkbox" name="allow_delegate" checked> Allow delegate</label><label><input type="checkbox" name="allow_sign_on_behalf"> Sign on behalf</label><label><input type="checkbox" name="is_mandatory" checked> Mandatory</label><label><input type="checkbox" name="is_active" checked> Active</label></div>
        <div class="col-md-2"><button class="btn btn-primary">Add Stage</button></div>
      </form>

      <table class="table table-sm table-bordered">
        <thead><tr><th>No</th><th>Name</th><th>Type</th><th>Mode</th><th>Role/Person</th><th>Rule</th><th>Flags</th></tr></thead>
        <tbody>
          <?php foreach($stages as $s): ?>
            <tr>
              <td><?= (int)$s['stage_no'] ?></td>
              <td><?= e($s['stage_name']) ?></td>
              <td><?= e($s['stage_type']) ?></td>
              <td><?= e($s['approval_mode']) ?></td>
              <td><?= e($s['role_name'] ?: '-') ?> / <?= e($s['full_name'] ?: '-') ?></td>
              <td><?= e($s['parallel_rule'] ?: '-') ?></td>
              <td><?= $s['allow_delegate']?'Delegate ':'' ?><?= $s['allow_sign_on_behalf']?'SOBO ':'' ?><?= $s['is_mandatory']?'Mandatory':'' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
