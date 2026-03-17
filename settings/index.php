<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $k => $v) {
        $stmt = $pdo->prepare('INSERT INTO app_settings(setting_key,setting_value,updated_at) VALUES(?,?,NOW()) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), updated_at=NOW()');
        $stmt->execute([$k, trim($v)]);
    }
    log_activity(user()['id'], 'Settings changes', 'Updated app settings');
    $_SESSION['flash_success'] = 'Settings updated';
    header('Location: index.php'); exit;
}
?>
<h3>App Settings</h3>
<form method="post" class="row g-3">
<div class="col-md-4"><label>App name</label><input name="app_name" class="form-control" value="<?= e(app_setting('app_name', 'Asteco Procurement Dashboard')) ?>"></div>
<div class="col-md-4"><label>Currency label</label><input name="currency_label" class="form-control" value="<?= e(app_setting('currency_label', 'AED')) ?>"></div>
<div class="col-md-4"><label>Records per page</label><input name="records_per_page" class="form-control" value="<?= e(app_setting('records_per_page', '25')) ?>"></div>
<div class="col-md-12"><button class="btn btn-primary">Save</button></div></form>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
