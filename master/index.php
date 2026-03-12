<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table = $_POST['table'];
    $value = trim($_POST['value']);
    $allowed = ['contractors' => 'contractor_name', 'po_statuses' => 'status_name', 'types' => 'type_name'];
    if (isset($allowed[$table]) && $value !== '') {
        $stmt = $pdo->prepare("INSERT INTO {$table} ({$allowed[$table]}, status, created_at, updated_at) VALUES (?, 1, NOW(), NOW())");
        if ($table !== 'contractors') {
            $stmt = $pdo->prepare("INSERT INTO {$table} ({$allowed[$table]}, created_at, updated_at) VALUES (?, NOW(), NOW())");
        }
        $stmt->execute([$value]);
        $_SESSION['flash_success'] = 'Master data added.';
        log_activity(user()['id'], 'Master data', "Added {$value} to {$table}");
    }
    header('Location: index.php'); exit;
}
$contractors = $pdo->query('SELECT * FROM contractors ORDER BY contractor_name')->fetchAll();
$statuses = $pdo->query('SELECT * FROM po_statuses ORDER BY id')->fetchAll();
$types = $pdo->query('SELECT * FROM types ORDER BY id')->fetchAll();
?>
<h3>Master Data Management</h3>
<div class="row"><div class="col-md-4"><h6>Contractors</h6><form method="post" class="input-group mb-2"><input type="hidden" name="table" value="contractors"><input class="form-control" name="value"><button class="btn btn-primary">Add</button></form><?php foreach($contractors as $x) echo '<div>'.e($x['contractor_name']).'</div>'; ?></div>
<div class="col-md-4"><h6>PO Statuses</h6><form method="post" class="input-group mb-2"><input type="hidden" name="table" value="po_statuses"><input class="form-control" name="value"><button class="btn btn-primary">Add</button></form><?php foreach($statuses as $x) echo '<div>'.e($x['status_name']).'</div>'; ?></div>
<div class="col-md-4"><h6>Types</h6><form method="post" class="input-group mb-2"><input type="hidden" name="table" value="types"><input class="form-control" name="value"><button class="btn btn-primary">Add</button></form><?php foreach($types as $x) echo '<div>'.e($x['type_name']).'</div>'; ?></div></div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
