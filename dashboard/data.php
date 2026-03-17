<?php
require_once __DIR__ . '/../includes/auth.php';

$years = array_map('intval', (array)($_GET['year'] ?? []));
$months = array_map('intval', (array)($_GET['month'] ?? []));
$typeIds = array_map('intval', (array)($_GET['type_id'] ?? []));

$where = ['1=1'];
$params = [];
if ($years) {
    $where[] = 'YEAR(f.fact_date) IN (' . implode(',', array_fill(0, count($years), '?')) . ')';
    $params = array_merge($params, $years);
}
if ($months) {
    $where[] = 'MONTH(f.fact_date) IN (' . implode(',', array_fill(0, count($months), '?')) . ')';
    $params = array_merge($params, $months);
}
if ($typeIds) {
    $where[] = 'f.type_id IN (' . implode(',', array_fill(0, count($typeIds), '?')) . ')';
    $params = array_merge($params, $typeIds);
}
$whereSql = ' WHERE ' . implode(' AND ', $where);

$k = $pdo->prepare("SELECT
COUNT(*) total_records,
COALESCE(SUM(CASE WHEN f.source_module='PO' AND f.status='Released' THEN f.amount_aed ELSE 0 END),0) released_amount,
SUM(CASE WHEN f.source_module='PO' AND f.status='Released' THEN 1 ELSE 0 END) released_count,
SUM(CASE WHEN f.source_module='PR' AND f.status='Rejected' THEN 1 ELSE 0 END) rejected_count,
SUM(CASE WHEN f.status IN ('Under Endorsement','Under Approval','Submitted') THEN 1 ELSE 0 END) pending_count
FROM vw_erp_document_facts f $whereSql");
$k->execute($params); $kpi=$k->fetch();

$t = $pdo->prepare("SELECT COALESCE(f.type_name,'(Unclassified)') type_name, COUNT(*) total
FROM vw_erp_document_facts f $whereSql
GROUP BY f.type_name ORDER BY total DESC");
$t->execute($params); $typeData=$t->fetchAll();

$s = $pdo->prepare("SELECT COALESCE(f.supplier_name,'(No Supplier)') supplier_name, COALESCE(SUM(f.amount_aed),0) total_amount
FROM vw_erp_document_facts f
$whereSql AND f.source_module='PO'
GROUP BY f.supplier_name ORDER BY total_amount DESC LIMIT 10");
$s->execute($params); $suppliers=$s->fetchAll();

ob_start(); ?>
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="card p-3 kpi-card"><div class="kpi-label">Total ERP Documents</div><div class="kpi-value"><?= (int)$kpi['total_records'] ?></div></div></div>
  <div class="col-md-3"><div class="card p-3 kpi-card"><div class="kpi-label">Released PO Amount (AED)</div><div class="kpi-value"><?= number_format((float)$kpi['released_amount'],2) ?></div></div></div>
  <div class="col-md-3"><div class="card p-3 kpi-card"><div class="kpi-label">Released PO Count</div><div class="kpi-value"><?= (int)$kpi['released_count'] ?></div></div></div>
  <div class="col-md-3"><div class="card p-3 kpi-card"><div class="kpi-label">Pending Workflow Docs</div><div class="kpi-value"><?= (int)$kpi['pending_count'] ?></div></div></div>
</div>
<?php $kpiHtml=ob_get_clean();
ob_start(); ?>
<div class="row g-3">
  <div class="col-md-6"><div class="card p-3"><h6>Document Distribution by Type</h6><canvas id="typeChart"></canvas></div></div>
  <div class="col-md-6"><div class="card p-3"><h6>Top 10 Suppliers by PO Amount (AED)</h6><canvas id="supplierValueChart"></canvas></div></div>
</div>
<script>
new Chart(document.getElementById('typeChart'),{type:'pie',data:{labels:<?= json_encode(array_column($typeData,'type_name')) ?>,datasets:[{data:<?= json_encode(array_map('intval',array_column($typeData,'total'))) ?>,backgroundColor:['#C9A0C0','#FED34C','#CDA78E','#72B096','#78C6E0','#EF6A00','#D4BE97','#6F635F','#24272C','#EEE6DA']}]},options:{responsive:true,plugins:{legend:{position:'bottom'}}}});
new Chart(document.getElementById('supplierValueChart'),{type:'bar',data:{labels:<?= json_encode(array_column($suppliers,'supplier_name')) ?>,datasets:[{label:'AED',data:<?= json_encode(array_map('floatval',array_column($suppliers,'total_amount'))) ?>,backgroundColor:'#72B096',borderRadius:6}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{x:{ticks:{maxRotation:30,minRotation:20}},y:{ticks:{callback:(v)=>'AED '+Number(v).toLocaleString()}}}}});
</script>
<?php $chartHtml=ob_get_clean();
header('Content-Type: application/json');
echo json_encode(['kpi_html'=>$kpiHtml, 'chart_html'=>$chartHtml]);
