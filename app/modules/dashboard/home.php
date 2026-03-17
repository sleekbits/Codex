<?php
$kpi = [
    'released_amount' => db()->query("SELECT IFNULL(SUM(total_amount),0) v FROM po_headers WHERE status='Released'")->fetch()['v'],
    'released_count' => db()->query("SELECT COUNT(*) v FROM po_headers WHERE status='Released'")->fetch()['v'],
    'rejected_pr' => db()->query("SELECT COUNT(*) v FROM pr_headers WHERE status='Rejected'")->fetch()['v'],
    'pending' => db()->query("SELECT COUNT(*) v FROM tracking_records WHERE po_status='Pending'")->fetch()['v'],
];
$pieRows = db()->query("SELECT type, COUNT(*) c FROM tracking_records GROUP BY type")->fetchAll();
$barRows = db()->query("SELECT contractor_name, SUM(amount_aed) a FROM tracking_records GROUP BY contractor_name ORDER BY a DESC LIMIT 10")->fetchAll();
?>
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="card kpi p-3"><small>Released PO Amount (AED)</small><h4><?= number_format((float)$kpi['released_amount'],2) ?></h4></div></div>
  <div class="col-md-3"><div class="card kpi p-3"><small>Released PO Count</small><h4><?= e($kpi['released_count']) ?></h4></div></div>
  <div class="col-md-3"><div class="card kpi p-3"><small>Rejected PR Count</small><h4><?= e($kpi['rejected_pr']) ?></h4></div></div>
  <div class="col-md-3"><div class="card kpi p-3"><small>Total Pending</small><h4><?= e($kpi['pending']) ?></h4></div></div>
</div>
<div class="row g-3">
<div class="col-md-2"><div class="card p-3 slicer"><h6>Type</h6><button class="btn btn-outline-secondary btn-sm">All</button><button class="btn btn-outline-secondary btn-sm">CAPEX</button><button class="btn btn-outline-secondary btn-sm">OPEX</button></div></div>
<div class="col-md-8"><div class="row g-3"><div class="col-md-6"><div class="card p-3"><h6>By Type</h6><canvas id="pie"></canvas></div></div><div class="col-md-6"><div class="card p-3"><h6>Top 10 Contractors</h6><canvas id="bar"></canvas></div></div></div></div>
<div class="col-md-2"><div class="card p-3 slicer"><h6>Year</h6><button class="btn btn-outline-secondary btn-sm">2024</button><button class="btn btn-outline-secondary btn-sm">2025</button><h6 class="mt-3">Month</h6><button class="btn btn-outline-secondary btn-sm">Jan</button><button class="btn btn-outline-secondary btn-sm">Feb</button></div></div>
</div>
<script>
new Chart(document.getElementById('pie'),{type:'pie',data:{labels:<?= json_encode(array_column($pieRows,'type')) ?>,datasets:[{data:<?= json_encode(array_map('intval',array_column($pieRows,'c'))) ?>,backgroundColor:['#EF6A00','#72B096','#78C6E0','#FED34C']}]}});
new Chart(document.getElementById('bar'),{type:'bar',data:{labels:<?= json_encode(array_column($barRows,'contractor_name')) ?>,datasets:[{label:'AED',data:<?= json_encode(array_map('floatval',array_column($barRows,'a'))) ?>,backgroundColor:'#CDA78E'}]}});
</script>
