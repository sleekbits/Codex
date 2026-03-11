# PR to PO Tracking Dashboard (PHP + MySQL)

A simple web-based dashboard to track Purchase Requests (PR) to Purchase Orders (PO), including variation orders tied to the same PO.

## Features
- Add PR to PO records.
- Capture contract reference, bidder name, and description.
- Save a sub-PR link for variation orders.
- Mark whether an entry is a variation order.
- View all entries in a table sorted by latest first.

## Setup
1. Import database schema:
   ```bash
   mysql -u root -p < schema.sql
   ```
2. Configure database credentials via environment variables (optional):
   - `DB_HOST` (default `127.0.0.1`)
   - `DB_NAME` (default `pr_po_dashboard`)
   - `DB_USER` (default `root`)
   - `DB_PASS` (default empty)
3. Start a local PHP server:
   ```bash
   php -S 0.0.0.0:8000
   ```
4. Open: `http://localhost:8000`

## File Overview
- `index.php` — form handling + table rendering.
- `config.php` — PDO MySQL connection setup.
- `schema.sql` — database and table structure.
- `styles.css` — dashboard styling.
