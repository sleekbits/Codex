<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

$years = $pdo->query("SELECT DISTINCT YEAR(fact_date) y FROM vw_erp_document_facts ORDER BY y DESC")->fetchAll();
$months = $pdo->query("SELECT DISTINCT MONTH(fact_date) m FROM vw_erp_document_facts ORDER BY m ASC")->fetchAll();
$types = $pdo->query("SELECT id, type_name FROM types ORDER BY type_name")->fetchAll();
$filters = query_filters();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Dashboard Analytics</h3>
    <a class="btn btn-outline-secondary btn-sm" href="index.php"><i class="bi bi-arrow-counterclockwise me-1"></i>Clear All</a>
</div>
<div id="dashboardAjaxTop"></div>
<div class="row g-3" id="dashboardFilters">
  <div class="col-md-3">
    <div class="card p-3"><div class="slicer-title">Type Slicer</div><div class="slicer-grid cols-1" style="max-height:420px;overflow:auto;">
      <?php foreach($types as $t): ?><button type="button" class="slicer-tile <?= in_array((string)$t['id'], array_map('strval', $filters['type_id']), true)?'active':'' ?>" data-filter="type_id" data-value="<?= $t['id'] ?>"><?= e($t['type_name']) ?></button><?php endforeach; ?>
    </div></div>
  </div>
  <div class="col-md-6"><div id="dashboardAjaxCharts"></div></div>
  <div class="col-md-3">
    <div class="card p-3 mb-3"><div class="slicer-title">Year Slicer</div><div class="slicer-grid cols-2">
      <?php foreach($years as $y): ?><button type="button" class="slicer-tile <?= in_array((string)$y['y'], array_map('strval', $filters['year']), true)?'active':'' ?>" data-filter="year" data-value="<?= $y['y'] ?>"><?= $y['y'] ?></button><?php endforeach; ?>
    </div></div>
    <div class="card p-3"><div class="slicer-title">Month Slicer</div><div class="slicer-grid cols-2">
      <?php foreach($months as $m): $label=date('M', mktime(0,0,0,$m['m'],1)); ?><button type="button" class="slicer-tile <?= in_array((string)$m['m'], array_map('strval', $filters['month']), true)?'active':'' ?>" data-filter="month" data-value="<?= $m['m'] ?>"><?= $label ?></button><?php endforeach; ?>
    </div></div>
  </div>
</div>
<script>
$(function(){
  const p=new URLSearchParams(window.location.search);
  $.get('/Codex/dashboard/data.php', p.toString(), function(r){
    $('#dashboardAjaxTop').html(r.kpi_html);
    $('#dashboardAjaxCharts').html(r.chart_html);
  },'json');
});
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
