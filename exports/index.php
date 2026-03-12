<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>
<h3>Export Tracking Data</h3>
<form method="get" action="download.php" class="row g-2">
<div class="col-md-2"><input class="form-control" name="year" placeholder="Year"></div>
<div class="col-md-2"><select class="form-select" name="format"><option value="csv">CSV</option><option value="xlsx">Excel (if PhpSpreadsheet)</option></select></div>
<div class="col-md-2"><button class="btn btn-success">Export</button></div>
</form>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
