<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

$years = $pdo->query("SELECT DISTINCT YEAR(pr_receival_date) y FROM tracking_records WHERE deleted_at IS NULL ORDER BY y DESC")->fetchAll();
$months = $pdo->query("SELECT DISTINCT MONTH(pr_receival_date) m FROM tracking_records WHERE deleted_at IS NULL ORDER BY m ASC")->fetchAll();
$types = $pdo->query("SELECT id, type_name FROM types ORDER BY type_name")->fetchAll();
$filters = query_filters();
?>
<div class="d-flex justify-content-between align-items-center mb-3"><h3 class="mb-0">Dashboard Analytics</h3><a class="btn btn-outline-secondary btn-sm" href="index.php">Clear All</a></div>
<div class="row g-3 mb-3" id="dashboardFilters">
  <div class="col-md-4">
    <div class="card p-3"><div class="slicer-title">Year Slicer</div><div class="slicer-grid cols-2">
      <?php foreach($years as $y): ?><button class="slicer-tile <?= (string)$filters['year']===(string)$y['y']?'active':'' ?>" data-filter="year" data-value="<?= $y['y'] ?>"><?= $y['y'] ?></button><?php endforeach; ?>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card p-3"><div class="slicer-title">Month Slicer</div><div class="slicer-grid cols-2">
      <?php foreach($months as $m): $label=date('M', mktime(0,0,0,$m['m'],1)); ?><button class="slicer-tile <?= (string)$filters['month']===(string)$m['m']?'active':'' ?>" data-filter="month" data-value="<?= $m['m'] ?>"><?= $label ?></button><?php endforeach; ?>
    </div></div>
  </div>
  <div class="col-md-4">
    <div class="card p-3"><div class="slicer-title">Type Slicer</div><div class="slicer-grid cols-1" style="max-height:180px;overflow:auto;">
      <?php foreach($types as $t): ?><button class="slicer-tile <?= (string)$filters['type_id']===(string)$t['id']?'active':'' ?>" data-filter="type_id" data-value="<?= $t['id'] ?>"><?= e($t['type_name']) ?></button><?php endforeach; ?>
    </div></div>
  </div>
</div>
<div id="dashboardAjax"></div>
<script>$(function(){ const p=new URLSearchParams(window.location.search); $.get('/Codex/dashboard/data.php', p.toString(), function(r){$('#dashboardAjax').html(r.html);},'json'); });</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
