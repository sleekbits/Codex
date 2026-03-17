# Asteco Procurement Dashboard

Modernized traditional PHP + MySQL dashboard app for PR/PO/Contract tracking.

## Stack
- Core PHP 8+
- MySQL (phpMyAdmin-ready SQL + migration script)
- Bootstrap 5 + jQuery + DataTables
- Chart.js
- PhpSpreadsheet-ready export/import hooks

## Key Updated Features
- Asteco branded UI theme and polished responsive admin layout
- Dashboard slicers (Year/Month/Type), AJAX chart refresh, enhanced KPI highlights
- Pie chart by Type and Top 10 Contractor value bar chart
- Tracking page advanced filters, status badges, icon actions, DD-MMM-YYYY dates
- Bulk row select + bulk soft delete (Admin)
- Duplicate indicators for PR No. and PO No.
- Export page with granular filters (year/month/assigned/contractor/status/type/PR/PO/date range/status groups)
- User management modernization with edit/delete/reset-password actions
- Profile page modernization with improved layout and password section
- New Workflow Hierarchy engine with configurable stages (endorsement/approval/parallel), threshold/type/department/BU rules, and runtime approval inbox with audit trail

## Setup (XAMPP/WAMP/LAMP)
1. Copy project to web root (e.g. `htdocs/Codex`).
2. Create database in phpMyAdmin.
3. Import `database/asteco_procurement_dashboard.sql`.
4. For existing deployments, run `database/migrations_20260312.sql`.
5. Update DB credentials only in `config/config.php`.
6. (Optional for XLSX features) install PhpSpreadsheet:
   ```bash
   composer require phpoffice/phpspreadsheet
   ```
7. Open: `http://localhost/Codex/`

## Default Login
- Username: `admin`
- Password: `password123`
