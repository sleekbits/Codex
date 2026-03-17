<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);

$permissionKeys = [
'dashboard_view','tracking_view','tracking_add','tracking_edit','tracking_delete','bulk_delete',
'import_access','export_access','user_management_view','user_add_edit_delete','role_management_view_edit',
'master_data_view_add_edit_delete','settings_access','profile_access','pr_workflow_access','po_workflow_access','doa_access','poa_access'
];

if (isset($_POST['action']) && $_POST['action'] === 'save_role') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['role_name'] ?? '');
    if ($name !== '') {
        if ($id > 0) {
            $pdo->prepare('UPDATE roles SET role_name=?, updated_at=NOW() WHERE id=?')->execute([$name, $id]);
        } else {
            $pdo->prepare('INSERT INTO roles(role_name, created_at, updated_at) VALUES (?,NOW(),NOW())')->execute([$name]);
            $id = (int)$pdo->lastInsertId();
        }
        foreach ($permissionKeys as $key) {
            $allowed = isset($_POST['permissions'][$key]) ? 1 : 0;
            $pdo->prepare('INSERT INTO role_permissions(role_id, permission_key, is_allowed, created_at, updated_at) VALUES (?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE is_allowed=VALUES(is_allowed), updated_at=NOW()')
                ->execute([$id, $key, $allowed, date('Y-m-d H:i:s')]);
        }
        $_SESSION['flash_success'] = 'Role saved with permissions.';
    }
    header('Location: index.php'); exit;
}
if (isset($_GET['delete'])) {
    $id=(int)$_GET['delete'];
    $pdo->prepare('DELETE FROM roles WHERE id=?')->execute([$id]);
    $_SESSION['flash_success']='Role deleted';
    header('Location:index.php'); exit;
}

$roles = $pdo->query('SELECT * FROM roles ORDER BY role_name')->fetchAll();
$selectedId = (int)($_GET['edit'] ?? 0);
$selected = null;
$permMap = [];
if ($selectedId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM roles WHERE id=?'); $stmt->execute([$selectedId]); $selected = $stmt->fetch();
    $stmt = $pdo->prepare('SELECT permission_key, is_allowed FROM role_permissions WHERE role_id=?'); $stmt->execute([$selectedId]);
    foreach($stmt->fetchAll() as $p){$permMap[$p['permission_key']] = (int)$p['is_allowed'];}
}
$defaultRoles=[
'Admin','ERP/IT','Chief Executive Officer (CEO)','Chief Procurement Officer (CPO)','Executive Director Procurement','Director Procurement','Associate Director Procurement','Senior Manager Procurement','Manager Procurement','Assistant Manager Procurement','Senior Buyer','Buyer','Senior Procurement Officer','Procurement Officer'
];
?>
<h3>Role Management</h3>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card p-3">
      <h6>Roles</h6>
      <ul class="list-group mb-2">
        <?php foreach($roles as $r): ?><li class="list-group-item d-flex justify-content-between align-items-center">
          <a href="?edit=<?= $r['id'] ?>"><?= e($r['role_name']) ?></a>
          <a href="?delete=<?= $r['id'] ?>" class="text-danger" onclick="return confirm('Delete role?')"><i class="bi bi-trash"></i></a>
        </li><?php endforeach; ?>
      </ul>
      <form method="post" action="seed_roles.php"><button class="btn btn-outline-secondary btn-sm">Seed Required Roles</button></form>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card p-3">
      <h6><?= $selected ? 'Edit Role' : 'Add Role' ?></h6>
      <form method="post" class="row g-2">
        <input type="hidden" name="action" value="save_role">
        <input type="hidden" name="id" value="<?= (int)($selected['id'] ?? 0) ?>">
        <div class="col-12"><label class="form-label">Role Name</label><input class="form-control" name="role_name" required value="<?= e($selected['role_name'] ?? '') ?>"></div>
        <div class="col-12"><label class="form-label">Permissions Matrix</label><div class="row g-1">
          <?php foreach($permissionKeys as $key): ?><div class="col-md-6"><label class="form-check"><input class="form-check-input" type="checkbox" name="permissions[<?= $key ?>]" value="1" <?= !empty($permMap[$key]) ? 'checked' : '' ?>> <span class="form-check-label"><?= e(str_replace('_',' ',ucwords($key,'_'))) ?></span></label></div><?php endforeach; ?>
        </div></div>
        <div class="col-12"><button class="btn btn-primary">Save Role</button></div>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
