<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);

$map = ['po_statuses' => 'status_name', 'types' => 'type_name'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'] ?? '';
    $value = trim($_POST['value'] ?? '');
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? 'add';
    if (isset($map[$table]) && $value !== '') {
        $col = $map[$table];
        if ($action === 'edit' && $id > 0) {
            $pdo->prepare("UPDATE {$table} SET {$col}=?, updated_at=NOW() WHERE id=?")->execute([$value, $id]);
        } else {
            $pdo->prepare("INSERT INTO {$table} ({$col}, created_at, updated_at) VALUES (?, NOW(), NOW())")->execute([$value]);
        }
        $_SESSION['flash_success'] = 'Master data saved.';
    }
    header('Location: index.php'); exit;
}
if (isset($_GET['delete_table'], $_GET['delete_id'])) {
    $table = $_GET['delete_table'];
    $id = (int)$_GET['delete_id'];
    if (isset($map[$table])) {
        $pdo->prepare("DELETE FROM {$table} WHERE id=?")->execute([$id]);
        $_SESSION['flash_success'] = 'Master data deleted.';
    }
    header('Location: index.php'); exit;
}
$statuses = $pdo->query('SELECT * FROM po_statuses ORDER BY id')->fetchAll();
$types = $pdo->query('SELECT * FROM types ORDER BY id')->fetchAll();
$editTable = $_GET['edit_table'] ?? '';
$editId = (int)($_GET['edit_id'] ?? 0);
$editValue = '';
if ($editId > 0 && isset($map[$editTable])) {
    $col=$map[$editTable];
    $st=$pdo->prepare("SELECT {$col} v FROM {$editTable} WHERE id=?"); $st->execute([$editId]);
    $editValue = $st->fetchColumn() ?: '';
}
?>
<h3>Master Data Management</h3>
<p class="text-muted-custom">Contractor maintenance removed. Contractors are managed through import data flow.</p>
<div class="row g-3">
  <div class="col-md-6"><div class="card p-3"><h6>PO Statuses</h6>
    <form method="post" class="input-group mb-2"><input type="hidden" name="table" value="po_statuses"><input type="hidden" name="action" value="<?= $editTable==='po_statuses'?'edit':'add' ?>"><input type="hidden" name="id" value="<?= $editTable==='po_statuses'?(int)$editId:0 ?>"><input class="form-control" name="value" value="<?= $editTable==='po_statuses'?e($editValue):'' ?>" placeholder="Status Name" required><button class="btn btn-primary"><?= $editTable==='po_statuses'?'Update':'Add' ?></button></form>
    <table class="table table-sm table-bordered"><tr><th>ID</th><th>Status</th><th>Action</th></tr><?php foreach($statuses as $x): ?><tr><td><?= $x['id'] ?></td><td><?= e($x['status_name']) ?></td><td><a href="?edit_table=po_statuses&edit_id=<?= $x['id'] ?>">Edit</a> | <a class="text-danger" onclick="return confirm('Delete?')" href="?delete_table=po_statuses&delete_id=<?= $x['id'] ?>">Delete</a></td></tr><?php endforeach; ?></table>
  </div></div>
  <div class="col-md-6"><div class="card p-3"><h6>Types</h6>
    <form method="post" class="input-group mb-2"><input type="hidden" name="table" value="types"><input type="hidden" name="action" value="<?= $editTable==='types'?'edit':'add' ?>"><input type="hidden" name="id" value="<?= $editTable==='types'?(int)$editId:0 ?>"><input class="form-control" name="value" value="<?= $editTable==='types'?e($editValue):'' ?>" placeholder="Type Name" required><button class="btn btn-primary"><?= $editTable==='types'?'Update':'Add' ?></button></form>
    <table class="table table-sm table-bordered"><tr><th>ID</th><th>Type</th><th>Action</th></tr><?php foreach($types as $x): ?><tr><td><?= $x['id'] ?></td><td><?= e($x['type_name']) ?></td><td><a href="?edit_table=types&edit_id=<?= $x['id'] ?>">Edit</a> | <a class="text-danger" onclick="return confirm('Delete?')" href="?delete_table=types&delete_id=<?= $x['id'] ?>">Delete</a></td></tr><?php endforeach; ?></table>
  </div></div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
