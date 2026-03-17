<div id="sidebar-wrapper" class="sidebar">
    <div class="sidebar-heading px-3 py-3"><?= e(app_setting('app_name', 'Asteco Procurement ERP')) ?></div>
    <div class="list-group list-group-flush">
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php')) ?>"><i class="bi bi-speedometer2 me-2"></i>Home Dashboard</a>
        <div class="menu-group">Procurement</div>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=tracking/index')) ?>">Tracking Database</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=pr/index')) ?>">Create PR</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=po/index')) ?>">Create PO</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=po/print')) ?>">Print PO</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=import_export/index')) ?>">Import / Export</a>
        <div class="menu-group">Vendors</div>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=vendors/index')) ?>">Supplier / Vendor Management</a>
        <div class="menu-group">Workflow</div>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=workflow/doa')) ?>">Approval Hierarchy / DOA</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=workflow/poa')) ?>">Power of Attorney / POA</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=workflow/logs')) ?>">Approval Logs / Workflow History</a>
        <div class="menu-group">Finance & Accounts</div>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=finance/dashboard')) ?>">Finance Dashboard</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=finance/summary')) ?>">Budget / Cost Summary</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=finance/invoices')) ?>">Invoice / Payment Tracking</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=finance/gl')) ?>">GL / Cost Center Reference</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=finance/commitments')) ?>">Commitments / Spend Summary</a>
        <div class="menu-group">Administration</div>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=admin/users')) ?>">User Management</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=admin/roles')) ?>">Role & Permissions</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=admin/master_data')) ?>">Master Data Management</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=settings/index')) ?>">Settings</a>
        <a class="list-group-item list-group-item-action" href="<?= e(app_url('index.php?module=audit/index')) ?>">Audit Trail</a>
    </div>
</div>
