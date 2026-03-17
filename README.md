# Asteco Procurement ERP (Core PHP)

Deployment-hardened Core PHP procurement ERP for subfolder hosting (`/Codex`) with centralized base URL/path configuration, secure session handling, and MySQL configuration via environment variables.

## Production Deployment (Shared Hosting)

### 1) Upload paths
Upload this repo so these paths exist on hosting:
- `/Codex/index.php`
- `/Codex/app/...`
- `/Codex/sql/schema_and_seed.sql`
- `/Codex/templates/...`

### 2) Database import
Import:
- `sql/schema_and_seed.sql`

Database name used:
- `ezyro_41363280_codex`

### 3) Configure runtime values
Set hosting environment variables (preferred):
- `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET`
- `APP_BASE_URL` (example: `https://sleekbits.unaux.com/Codex`)
- `APP_BASE_PATH` (example: `/Codex`)
- `APP_ENV=production`
- `APP_DEBUG=0`

If env vars are not available in hosting panel, edit `app/config/config.php` defaults.

### 4) Access URLs
- Entry URL: `https://sleekbits.unaux.com/Codex`
- Login URL: `https://sleekbits.unaux.com/Codex/app/login.php`

## Default Credentials
- Username: `admin`
- Password: `Admin@123`

## Key fixes applied for deployment
- Base URL and base path helpers added so links/assets/forms/redirects work under `/Codex` subfolder.
- Session cookie path scoped to deployment path (prevents login loop/logout anomalies).
- Database connection now supports host/port/env config and graceful production-safe error output.
- Router hardened against invalid module path traversal.
- Root index entry-point added (`/Codex/index.php`) for direct domain-subfolder access.
- Import template links fixed to resolve from app pages to `/Codex/templates/...`.

## Modules
- Authentication
- Dashboard
- Tracking
- Import/Export
- Vendors
- PR / PO / PO Print
- Workflow (DOA/POA/logs)
- Finance
- Administration
- Settings
- Audit Trail
