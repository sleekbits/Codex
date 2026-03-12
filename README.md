# Asteco Procurement Dashboard

Traditional PHP + MySQL dashboard app for PR/PO/Contract tracking.

## Stack
- Core PHP 8+
- MySQL (phpMyAdmin-ready SQL)
- Bootstrap 5 + jQuery + AJAX-ready pages
- Chart.js
- PhpSpreadsheet-ready export/import hooks

## Modules
- Authentication (login/logout/forgot password)
- Role-based access (Admin, Manager, Viewer)
- Dashboard KPIs + charts
- Tracking CRUD with soft delete
- Import (CSV now, XLSX/XLS when PhpSpreadsheet installed)
- Export (CSV + XLSX when PhpSpreadsheet available)
- User/Role/Master Data/App Settings management
- Activity logging

## Setup (XAMPP/WAMP/LAMP)
1. Copy project to web root (e.g. `htdocs/Codex`).
2. Create database in phpMyAdmin: `asteco_procurement_dashboard`.
3. Import `database/asteco_procurement_dashboard.sql`.
4. Update DB credentials in `config/config.php`.
5. (Optional for XLSX features) run composer:
   ```bash
   composer require phpoffice/phpspreadsheet
   ```
6. Open: `http://localhost/Codex/`

## Default Login
- Username: `admin`
- Password: `password123`

> The SQL seed includes 5 users, required PO statuses/types, 10 contractors, and 120 sample tracking records.
