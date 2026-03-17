# Asteco Procurement ERP (Core PHP)

Complete ERP-style Procurement Management Web Application built using **Core PHP + MySQL + Bootstrap 5 + jQuery + Chart.js** with modular procurement, workflow, finance, and administration sections.

## Stack
- Core PHP (no heavy framework)
- MySQL / phpMyAdmin
- Bootstrap 5, HTML5, CSS3, JavaScript, jQuery
- Chart.js for dashboard charts
- PhpSpreadsheet-ready import/export hooks

## Database
- **New DB Name:** `ezyro_41363280_codex`
- SQL schema + seed data file: `sql/schema_and_seed.sql`

## Modules Included
- Authentication (login, forgot password, logout)
- Dashboard analytics (KPI cards + charts + slicer tiles)
- Tracking Database
- Import / Export (template-driven)
- Supplier / Vendor Management
- PR creation
- PO creation
- PO print layout
- Workflow (DOA / POA / logs)
- Finance & Accounts (dashboard, invoices, GL/cost centers, commitments)
- User / Profile / Roles / Master Data
- Settings
- Audit Trail

## Sidebar Structure
Implemented with Procurement, Vendors, Workflow, Finance, Administration, and My Account pathways in left navigation and top-right profile/logout dropdown.

## Setup
1. Copy project to web root (e.g. `htdocs/Codex/app`).
2. Create database by importing:
   - `sql/schema_and_seed.sql`
3. Update DB credentials in:
   - `app/config/config.php`
4. Open in browser:
   - `http://localhost/Codex/app/login.php`

## Default Credentials
- **Username:** `admin`
- **Password:** `Admin@123`
- **Email login:** `admin@asteco.local`

## Sample Data Coverage
Seed includes:
- 14 procurement/management roles and users
- permission matrix
- 120 tracking records (including duplicate PR/PO examples)
- 22 suppliers
- 12 PR headers + PR items
- 12 PO headers + PO items
- 10+ DOA rules + POA rules
- workflow transactions for approved/rejected/returned/delegated/reassigned/sign-on-behalf scenarios
- finance records (commitments, invoices, payments, budgets)

## Notes on Permissions
- Role/permission tables are normalized (`roles`, `permissions`, `role_permissions`)
- Module-level actions supported: View/Add/Edit/Delete/Submit/Approve/Import/Export/Manage/Reset Password/Delegate/Reassign/Sign on Behalf

## Notes on Workflow Engine
- Reusable hierarchy by document type, threshold, department, business unit
- Stage types: Endorsement, Approval, Parallel Approval
- Transaction and step tables support comments, status changes, history, and auditability

## Notes on Print PO & Finance
- PO print page available at `index.php?module=po/print&id={id}` with print-friendly document style
- Finance dashboards summarize commitments, released spend, pending invoices, and monthly trend visualization

## Import Templates
- `templates/tracking_import_template.csv`
- `templates/suppliers_import_template.csv`

