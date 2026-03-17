<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin', 'Manager']);
?>
<h3>Import Tracking Data</h3>
<p>Supported formats: CSV/XLSX/XLS. This implementation includes CSV import; XLSX/XLS requires PhpSpreadsheet installed via Composer.</p>
<form method="post" action="process.php" enctype="multipart/form-data" class="card p-3">
<input type="file" name="import_file" class="form-control mb-2" required>
<label class="form-check mb-2"><input type="checkbox" name="update_existing" value="1" class="form-check-input"> Update existing by PR No.</label>
<button class="btn btn-primary">Import</button>
</form>
<a href="/Codex/database/sample_tracking_import_template.csv" class="btn btn-link mt-2">Download tracking import template</a>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
