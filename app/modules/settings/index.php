<?php
if($_SERVER['REQUEST_METHOD']==='POST'){
  foreach(['app_name','date_format','currency','records_per_page','dashboard_default_year'] as $k){
    $v=$_POST[$k]??'';
    db()->prepare('INSERT INTO settings(setting_key,setting_value,updated_at) VALUES(?,?,NOW()) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value),updated_at=NOW()')->execute([$k,$v]);
  }
  log_audit('edit','settings','Updated settings');
}
$rows=db()->query('SELECT setting_key,setting_value FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<div class="card p-3"><h5>Settings</h5><form method="post" class="row g-2"><div class="col-md-4"><label>App Name</label><input class="form-control" name="app_name" value="<?=e($rows['app_name'] ?? 'Asteco Procurement ERP')?>"></div><div class="col-md-2"><label>Date Format</label><input class="form-control" name="date_format" value="<?=e($rows['date_format'] ?? 'd-M-Y')?>"></div><div class="col-md-2"><label>Currency</label><input class="form-control" name="currency" value="<?=e($rows['currency'] ?? 'AED')?>"></div><div class="col-md-2"><label>Records / Page</label><input class="form-control" name="records_per_page" value="<?=e($rows['records_per_page'] ?? '10')?>"></div><div class="col-md-2"><label>Dashboard Year</label><input class="form-control" name="dashboard_default_year" value="<?=e($rows['dashboard_default_year'] ?? date('Y'))?>"></div><div class="col-md-3"><button class="btn btn-warning mt-4">Save Settings</button></div></form></div>
