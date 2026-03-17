<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);

$permissionGroups = [
    'Dashboard' => ['dashboard_view'],
    'Tracking Database' => ['tracking_view','tracking_add','tracking_edit','tracking_delete','bulk_delete','tracking_filter','tracking_export'],
    'Import' => ['import_view','import_access'],
    'Export' => ['export_view','export_access'],
    'Users' => ['user_management_view','users_add','users_edit','users_delete','users_reset_password'],
    'Profile' => ['profile_view','profile_edit'],
    'Roles' => ['role_management_view','roles_add','roles_edit','roles_delete','permission_management'],
    'Master Data' => ['master_data_view','master_data_add','master_data_edit','master_data_delete'],
    'DOA' => ['doa_view','doa_add','doa_edit','doa_delete','doa_manage_hierarchy'],
    'POA' => ['poa_view','poa_add','poa_edit','poa_delete','poa_manage_hierarchy'],
    'Supplier/Vendor Management' => ['supplier_view','supplier_add','supplier_edit','supplier_delete','supplier_import','supplier_export'],
    'Settings' => ['settings_view','settings_edit'],
    'Future Workflows' => ['pr_workflow_access','po_workflow_access']
];
$allKeys = array_merge(...array_values($permissionGroups));

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
        foreach ($allKeys as $key) {
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
?>
<h3>Role & Permission Management</h3>
<div class="row g-3">
  <div class="col-md-3">
    <div class="card p-3">
      <h6 class="mb-2">Roles</h6>
      <ul class="list-group mb-2">
        <?php foreach($roles as $r): ?><li class="list-group-item d-flex justify-content-between align-items-center">
          <a href="?edit=<?= $r['id'] ?>"><?= e($r['role_name']) ?></a>
          <a href="?delete=<?= $r['id'] ?>" class="text-danger" onclick="return confirm('Delete role?')"><i class="bi bi-trash"></i></a>
        </li><?php endforeach; ?>
      </ul>
      <form method="post" action="seed_roles.php"><button class="btn btn-outline-secondary btn-sm w-100">Seed Required Roles</button></form>
    </div>
  </div>
  <div class="col-md-9">
    <div class="card p-3">
      <h6 class="mb-3"><?= $selected ? 'Edit Role Permissions' : 'Create Role Permissions' ?></h6>
      <form method="post">
        <input type="hidden" name="action" value="save_role">
        <input type="hidden" name="id" value="<?= (int)($selected['id'] ?? 0) ?>">
        <div class="mb-3"><label class="form-label">Role Name</label><input class="form-control" name="role_name" required value="<?= e($selected['role_name'] ?? '') ?>"></div>
        <div class="row g-2">
        <?php foreach($permissionGroups as $group => $keys): ?>
          <div class="col-md-6">
            <div class="border rounded p-2 h-100">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong><?= e($group) ?></strong>
                <div>
                  <button type="button" class="btn btn-sm btn-link p-0 me-2 perm-select-all" data-group="<?= md5($group) ?>">Select All</button>
                  <button type="button" class="btn btn-sm btn-link p-0 text-danger perm-clear-all" data-group="<?= md5($group) ?>">Clear</button>
                </div>
              </div>
              <?php foreach($keys as $key): ?>
              <label class="form-check d-block perm-group-<?= md5($group) ?>">
                <input class="form-check-input" type="checkbox" name="permissions[<?= $key ?>]" value="1" <?= !empty($permMap[$key]) ? 'checked' : '' ?>>
                <span class="form-check-label"><?= e(str_replace('_',' ',ucwords($key,'_'))) ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
        <button class="btn btn-primary mt-3">Save Role</button>
      </form>
    </div>
  </div>
</div>
<script>
$(function(){
  $('.perm-select-all').on('click', function(){ $('.perm-group-'+$(this).data('group')+' input').prop('checked', true); });
  $('.perm-clear-all').on('click', function(){ $('.perm-group-'+$(this).data('group')+' input').prop('checked', false); });
});
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
