<?php
require_once __DIR__ . '/../includes/auth.php';
$filters = query_filters();
$params=[];
$whereSql = tracking_where_sql($filters, $params);

$k = $pdo->prepare("SELECT
COALESCE(SUM(CASE WHEN ps.status_name='Released' THEN tr.amount_aed ELSE 0 END),0) released_amount,
SUM(CASE WHEN ps.status_name='Released' THEN 1 ELSE 0 END) released_count,
SUM(CASE WHEN ps.status_name='Rejected' THEN 1 ELSE 0 END) rejected_count,
COUNT(*) total_records
FROM tracking_records tr
LEFT JOIN po_statuses ps ON ps.id=tr.po_status_id
LEFT JOIN contractors c ON c.id=tr.contractor_id $whereSql");
$k->execute($params); $kpi=$k->fetch();

$t = $pdo->prepare("SELECT ty.type_name, COUNT(*) total FROM tracking_records tr
LEFT JOIN types ty ON ty.id=tr.type_id
LEFT JOIN contractors c ON c.id=tr.contractor_id $whereSql GROUP BY ty.type_name ORDER BY total DESC");
$t->execute($params); $typeData=$t->fetchAll();

$c = $pdo->prepare("SELECT c.contractor_name, COALESCE(SUM(tr.amount_aed),0) total_amount FROM tracking_records tr
LEFT JOIN contractors c ON c.id=tr.contractor_id $whereSql GROUP BY c.contractor_name ORDER BY total_amount DESC LIMIT 10");
$c->execute($params); $contractors=$c->fetchAll();

ob_start();
?>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="card p-3 kpi-card"><div class="kpi-label">Amount (AED) of Released Purchase Orders (PO)</div><div class="kpi-value"><?= number_format((float)$kpi['released_amount'],2) ?></div></div></div>
  <div class="col-md-4"><div class="card p-3 kpi-card"><div class="kpi-label">Number of Released Purchase Orders (PO)</div><div class="kpi-value"><?= (int)$kpi['released_count'] ?></div></div></div>
  <div class="col-md-4"><div class="card p-3 kpi-card"><div class="kpi-label">Number of Rejected Purchase Requisition (PR)</div><div class="kpi-value"><?= (int)$kpi['rejected_count'] ?></div></div></div>
</div>
<div class="row g-3">
  <div class="col-md-5"><div class="card p-3"><h6>Pie Graph for Type</h6><canvas id="typeChart"></canvas></div></div>
  <div class="col-md-7"><div class="card p-3"><h6>Top 10 Contractors by Amount (AED)</h6><canvas id="contractorValueChart"></canvas></div></div>
</div>
<script>
new Chart(document.getElementById('typeChart'),{
  type:'pie',
  data:{labels:<?= json_encode(array_column($typeData,'type_name')) ?>,datasets:[{data:<?= json_encode(array_map('intval',array_column($typeData,'total'))) ?>,backgroundColor:['#C9A0C0','#FED34C','#CDA78E','#72B096','#78C6E0','#EF6A00','#D4BE97','#6F635F','#24272C','#EEE6DA']}]},
  options:{responsive:true, plugins:{legend:{position:'bottom', labels:{boxWidth:12, color:'#3D3936', font:{family:'Tahoma'}}}, tooltip:{callbacks:{label:(ctx)=>`${ctx.label}: ${ctx.formattedValue}`}}}, layout:{padding:8}}
});
new Chart(document.getElementById('contractorValueChart'),{
  type:'bar',
  data:{labels:<?= json_encode(array_column($contractors,'contractor_name')) ?>,datasets:[{label:'AED',data:<?= json_encode(array_map('floatval',array_column($contractors,'total_amount'))) ?>,backgroundColor:['#72B096','#78C6E0','#CDA78E','#C9A0C0','#FED34C','#EF6A00','#D4BE97','#6F635F','#3D3936','#24272C'], borderRadius:6}]},
  options:{responsive:true, plugins:{legend:{display:false}, tooltip:{callbacks:{label:(ctx)=>`AED ${Number(ctx.raw).toLocaleString()}`}}}, scales:{x:{ticks:{color:'#3D3936', maxRotation:35, minRotation:20}}, y:{ticks:{color:'#3D3936', callback:(v)=>'AED '+Number(v).toLocaleString()}}}, layout:{padding:8}}
});
</script>
<?php
$html=ob_get_clean();
header('Content-Type: application/json');
echo json_encode(['html'=>$html]);
