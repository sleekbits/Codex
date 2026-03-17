-- Safe migration for existing deployments
USE ezyro_41363280_codex;

-- 1) Allow duplicate PR numbers
SET @idx := (SELECT INDEX_NAME FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME='tracking_records'
             AND COLUMN_NAME='pr_no' AND NON_UNIQUE=0 LIMIT 1);
SET @sql := IF(@idx IS NOT NULL, CONCAT('ALTER TABLE tracking_records DROP INDEX ', @idx), 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) Helpful indexes for slicers/filters
ALTER TABLE tracking_records
  ADD INDEX idx_tr_pr_date (pr_receival_date),
  ADD INDEX idx_tr_po_no (po_no),
  ADD INDEX idx_tr_pr_no (pr_no),
  ADD INDEX idx_tr_status (po_status_id),
  ADD INDEX idx_tr_type (type_id),
  ADD INDEX idx_tr_assigned (assigned_to_user_id),
  ADD INDEX idx_tr_contractor (contractor_id);

-- 3) User profile extensions
ALTER TABLE users
  ADD COLUMN designation VARCHAR(120) NULL AFTER full_name,
  ADD COLUMN phone VARCHAR(30) NULL AFTER email,
  ADD COLUMN profile_image VARCHAR(255) NULL AFTER password;

-- 4) Role permissions
CREATE TABLE IF NOT EXISTS role_permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  permission_key VARCHAR(100) NOT NULL,
  is_allowed TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uk_role_perm (role_id, permission_key),
  CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- 5) DOA and POA matrices
CREATE TABLE IF NOT EXISTS doa_hierarchy (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hierarchy_name VARCHAR(150) NOT NULL,
  type VARCHAR(120) NOT NULL,
  threshold_from DECIMAL(14,2) NOT NULL DEFAULT 0,
  threshold_to DECIMAL(14,2) NOT NULL DEFAULT 0,
  stage_name VARCHAR(120) NOT NULL DEFAULT 'Stage',
  stage_type VARCHAR(30) NOT NULL DEFAULT 'approval',
  stage_sequence INT NOT NULL DEFAULT 1,
  approver_role_id INT NOT NULL,
  parallel_group_id INT NOT NULL DEFAULT 0,
  status TINYINT(1) NOT NULL DEFAULT 1,
  remarks TEXT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_doa_role FOREIGN KEY (approver_role_id) REFERENCES roles(id)
);

CREATE TABLE IF NOT EXISTS poa_hierarchy (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hierarchy_name VARCHAR(150) NOT NULL,
  type VARCHAR(120) NOT NULL,
  threshold_from DECIMAL(14,2) NOT NULL DEFAULT 0,
  threshold_to DECIMAL(14,2) NOT NULL DEFAULT 0,
  stage_name VARCHAR(120) NOT NULL DEFAULT 'Stage',
  stage_type VARCHAR(30) NOT NULL DEFAULT 'approval',
  stage_sequence INT NOT NULL DEFAULT 1,
  approver_role_id INT NOT NULL,
  parallel_group_id INT NOT NULL DEFAULT 0,
  status TINYINT(1) NOT NULL DEFAULT 1,
  remarks TEXT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_poa_role FOREIGN KEY (approver_role_id) REFERENCES roles(id)
);

-- 6) Supplier/Vendor master table
CREATE TABLE IF NOT EXISTS suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_code VARCHAR(80) NOT NULL UNIQUE,
  supplier_legal_name VARCHAR(180) NOT NULL,
  supplier_license_number VARCHAR(120) NULL,
  supplier_email VARCHAR(140) NULL,
  supplier_phone VARCHAR(40) NULL,
  supplier_mobile VARCHAR(40) NULL,
  supplier_address TEXT NULL,
  supplier_vat_number VARCHAR(80) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  remarks TEXT NULL,
  created_by INT NULL,
  updated_by INT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);


-- 7) PR and PO transactional tables
CREATE TABLE IF NOT EXISTS pr_headers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pr_number VARCHAR(80) NOT NULL UNIQUE,
  pr_date DATE NOT NULL,
  pr_type VARCHAR(120) NULL,
  requestor_id INT NULL,
  department VARCHAR(120) NULL,
  business_unit VARCHAR(120) NULL,
  company_code VARCHAR(50) NULL,
  plant_location VARCHAR(120) NULL,
  purchasing_group VARCHAR(120) NULL,
  currency VARCHAR(20) NULL,
  required_delivery_date DATE NULL,
  priority VARCHAR(40) NULL,
  status VARCHAR(60) NOT NULL DEFAULT 'Draft',
  justification TEXT NULL,
  remarks TEXT NULL,
  total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  created_by INT NULL,
  updated_by INT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS pr_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pr_header_id INT NOT NULL,
  item_no INT NOT NULL,
  material_service_code VARCHAR(120) NULL,
  short_description VARCHAR(255) NULL,
  detailed_description TEXT NULL,
  quantity DECIMAL(14,2) NOT NULL DEFAULT 0,
  uom VARCHAR(30) NULL,
  estimated_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  delivery_date DATE NULL,
  plant_location VARCHAR(120) NULL,
  cost_center VARCHAR(120) NULL,
  gl_account VARCHAR(120) NULL,
  wbs_project_code VARCHAR(120) NULL,
  account_assignment_category VARCHAR(80) NULL,
  purchasing_group VARCHAR(120) NULL,
  suggested_vendor_id INT NULL,
  status VARCHAR(60) NULL,
  remarks TEXT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_pr_item_header FOREIGN KEY (pr_header_id) REFERENCES pr_headers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS po_headers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  po_number VARCHAR(80) NOT NULL UNIQUE,
  po_date DATE NOT NULL,
  po_type VARCHAR(120) NULL,
  vendor_id INT NOT NULL,
  company_code VARCHAR(50) NULL,
  purchasing_organization VARCHAR(120) NULL,
  purchasing_group VARCHAR(120) NULL,
  currency VARCHAR(20) NULL,
  payment_terms VARCHAR(120) NULL,
  delivery_terms VARCHAR(120) NULL,
  incoterms VARCHAR(120) NULL,
  contract_reference VARCHAR(120) NULL,
  pr_reference VARCHAR(120) NULL,
  quotation_reference VARCHAR(120) NULL,
  tender_reference VARCHAR(120) NULL,
  validity_date DATE NULL,
  status VARCHAR(60) NOT NULL DEFAULT 'Draft',
  remarks TEXT NULL,
  total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  created_by INT NULL,
  updated_by INT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_po_header_vendor FOREIGN KEY (vendor_id) REFERENCES suppliers(id)
);

CREATE TABLE IF NOT EXISTS po_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  po_header_id INT NOT NULL,
  item_no INT NOT NULL,
  material_service_code VARCHAR(120) NULL,
  short_description VARCHAR(255) NULL,
  detailed_description TEXT NULL,
  quantity DECIMAL(14,2) NOT NULL DEFAULT 0,
  uom VARCHAR(30) NULL,
  unit_price DECIMAL(14,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  delivery_date DATE NULL,
  delivery_location VARCHAR(120) NULL,
  account_assignment VARCHAR(120) NULL,
  cost_center VARCHAR(120) NULL,
  gl_account VARCHAR(120) NULL,
  tax_code VARCHAR(40) NULL,
  pr_reference_item VARCHAR(80) NULL,
  contract_reference VARCHAR(120) NULL,
  status VARCHAR(60) NULL,
  remarks TEXT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_po_item_header FOREIGN KEY (po_header_id) REFERENCES po_headers(id) ON DELETE CASCADE
);

-- 8) Workflow engine foundation for PR/PO/future document approvals
ALTER TABLE po_headers
  ADD COLUMN IF NOT EXISTS department VARCHAR(120) NULL AFTER vendor_id,
  ADD COLUMN IF NOT EXISTS business_unit VARCHAR(120) NULL AFTER department;

CREATE TABLE IF NOT EXISTS workflow_hierarchy_headers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hierarchy_name VARCHAR(180) NOT NULL,
  document_category VARCHAR(120) NOT NULL,
  applies_to_module VARCHAR(80) NOT NULL DEFAULT 'ALL',
  type_mode ENUM('all_types','selected_types','grouped_types') NOT NULL DEFAULT 'all_types',
  threshold_from DECIMAL(14,2) NOT NULL DEFAULT 0,
  threshold_to DECIMAL(14,2) NOT NULL DEFAULT 999999999,
  department VARCHAR(120) NULL,
  business_unit VARCHAR(120) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  remarks TEXT NULL,
  created_by INT NULL,
  updated_by INT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS workflow_hierarchy_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hierarchy_header_id INT NOT NULL,
  type_name VARCHAR(120) NOT NULL,
  type_group VARCHAR(120) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_wh_type_header FOREIGN KEY (hierarchy_header_id) REFERENCES workflow_hierarchy_headers(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS workflow_hierarchy_stages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hierarchy_header_id INT NOT NULL,
  stage_name VARCHAR(150) NOT NULL,
  stage_no INT NOT NULL,
  stage_type VARCHAR(50) NOT NULL,
  approval_mode ENUM('sequential','parallel') NOT NULL DEFAULT 'sequential',
  role_id INT NULL,
  user_id INT NULL,
  parallel_rule VARCHAR(50) NULL,
  allow_delegate TINYINT(1) NOT NULL DEFAULT 0,
  allow_sign_on_behalf TINYINT(1) NOT NULL DEFAULT 0,
  is_mandatory TINYINT(1) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  remarks TEXT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_wh_stage_header FOREIGN KEY (hierarchy_header_id) REFERENCES workflow_hierarchy_headers(id) ON DELETE CASCADE,
  CONSTRAINT fk_wh_stage_role FOREIGN KEY (role_id) REFERENCES roles(id),
  CONSTRAINT fk_wh_stage_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS workflow_transactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  document_type VARCHAR(120) NOT NULL,
  document_id INT NOT NULL,
  document_number VARCHAR(120) NOT NULL,
  hierarchy_header_id INT NOT NULL,
  current_stage_no INT NULL,
  current_status VARCHAR(80) NOT NULL DEFAULT 'Submitted',
  created_by INT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_wt_header FOREIGN KEY (hierarchy_header_id) REFERENCES workflow_hierarchy_headers(id)
);

CREATE TABLE IF NOT EXISTS workflow_transaction_steps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  workflow_transaction_id INT NOT NULL,
  stage_no INT NOT NULL,
  stage_name VARCHAR(150) NOT NULL,
  stage_type VARCHAR(50) NOT NULL,
  approver_role_id INT NULL,
  approver_user_id INT NULL,
  assigned_user_id INT NULL,
  action_status VARCHAR(60) NOT NULL DEFAULT 'Queued',
  action_date DATETIME NULL,
  comments TEXT NULL,
  rejection_reason TEXT NULL,
  return_reason TEXT NULL,
  delegated_to_user_id INT NULL,
  reassigned_by INT NULL,
  signed_on_behalf_by INT NULL,
  allow_delegate TINYINT(1) NOT NULL DEFAULT 0,
  allow_sign_on_behalf TINYINT(1) NOT NULL DEFAULT 0,
  is_mandatory TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_wts_tx FOREIGN KEY (workflow_transaction_id) REFERENCES workflow_transactions(id) ON DELETE CASCADE,
  CONSTRAINT fk_wts_assigned FOREIGN KEY (assigned_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS workflow_audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  workflow_transaction_id INT NOT NULL,
  document_type VARCHAR(120) NOT NULL,
  document_id INT NOT NULL,
  action_type VARCHAR(80) NOT NULL,
  action_by INT NULL,
  action_role VARCHAR(120) NULL,
  action_to INT NULL,
  comments TEXT NULL,
  metadata_json JSON NULL,
  created_at DATETIME NULL,
  CONSTRAINT fk_wal_tx FOREIGN KEY (workflow_transaction_id) REFERENCES workflow_transactions(id) ON DELETE CASCADE
);

-- 9) ERP unification refactor (cross-module normalized links)
CREATE TABLE IF NOT EXISTS departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  department_name VARCHAR(120) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS business_units (
  id INT AUTO_INCREMENT PRIMARY KEY,
  business_unit_name VARCHAR(120) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS cost_centers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cost_center_code VARCHAR(80) NOT NULL UNIQUE,
  cost_center_name VARCHAR(150) NULL,
  department_id INT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_cost_center_dept FOREIGN KEY (department_id) REFERENCES departments(id)
);

CREATE TABLE IF NOT EXISTS currencies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  currency_code VARCHAR(10) NOT NULL UNIQUE,
  currency_name VARCHAR(80) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE IF NOT EXISTS purchasing_groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_code VARCHAR(40) NOT NULL UNIQUE,
  group_name VARCHAR(120) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

ALTER TABLE pr_headers
  ADD COLUMN IF NOT EXISTS pr_type_id INT NULL AFTER pr_type,
  ADD COLUMN IF NOT EXISTS department_id INT NULL AFTER department,
  ADD COLUMN IF NOT EXISTS business_unit_id INT NULL AFTER business_unit,
  ADD COLUMN IF NOT EXISTS purchasing_group_id INT NULL AFTER purchasing_group,
  ADD COLUMN IF NOT EXISTS currency_id INT NULL AFTER currency,
  ADD COLUMN IF NOT EXISTS workflow_transaction_id INT NULL AFTER status,
  ADD CONSTRAINT fk_pr_type_id FOREIGN KEY (pr_type_id) REFERENCES types(id),
  ADD CONSTRAINT fk_pr_department_id FOREIGN KEY (department_id) REFERENCES departments(id),
  ADD CONSTRAINT fk_pr_bu_id FOREIGN KEY (business_unit_id) REFERENCES business_units(id),
  ADD CONSTRAINT fk_pr_pg_id FOREIGN KEY (purchasing_group_id) REFERENCES purchasing_groups(id),
  ADD CONSTRAINT fk_pr_cur_id FOREIGN KEY (currency_id) REFERENCES currencies(id);

ALTER TABLE po_headers
  ADD COLUMN IF NOT EXISTS po_type_id INT NULL AFTER po_type,
  ADD COLUMN IF NOT EXISTS source_pr_header_id INT NULL AFTER vendor_id,
  ADD COLUMN IF NOT EXISTS department_id INT NULL AFTER department,
  ADD COLUMN IF NOT EXISTS business_unit_id INT NULL AFTER business_unit,
  ADD COLUMN IF NOT EXISTS purchasing_group_id INT NULL AFTER purchasing_group,
  ADD COLUMN IF NOT EXISTS currency_id INT NULL AFTER currency,
  ADD COLUMN IF NOT EXISTS workflow_transaction_id INT NULL AFTER status,
  ADD CONSTRAINT fk_po_type_id FOREIGN KEY (po_type_id) REFERENCES types(id),
  ADD CONSTRAINT fk_po_pr_header FOREIGN KEY (source_pr_header_id) REFERENCES pr_headers(id),
  ADD CONSTRAINT fk_po_department_id FOREIGN KEY (department_id) REFERENCES departments(id),
  ADD CONSTRAINT fk_po_bu_id FOREIGN KEY (business_unit_id) REFERENCES business_units(id),
  ADD CONSTRAINT fk_po_pg_id FOREIGN KEY (purchasing_group_id) REFERENCES purchasing_groups(id),
  ADD CONSTRAINT fk_po_cur_id FOREIGN KEY (currency_id) REFERENCES currencies(id);

ALTER TABLE tracking_records
  ADD COLUMN IF NOT EXISTS pr_header_id INT NULL AFTER pr_no,
  ADD COLUMN IF NOT EXISTS po_header_id INT NULL AFTER po_no,
  ADD COLUMN IF NOT EXISTS supplier_id INT NULL AFTER contractor_id,
  ADD CONSTRAINT fk_tracking_pr_header FOREIGN KEY (pr_header_id) REFERENCES pr_headers(id),
  ADD CONSTRAINT fk_tracking_po_header FOREIGN KEY (po_header_id) REFERENCES po_headers(id),
  ADD CONSTRAINT fk_tracking_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id);

CREATE TABLE IF NOT EXISTS finance_ap_invoices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  po_header_id INT NOT NULL,
  invoice_no VARCHAR(120) NOT NULL,
  invoice_date DATE NULL,
  invoice_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  status VARCHAR(60) NOT NULL DEFAULT 'Pending',
  remarks TEXT NULL,
  created_by INT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_ap_invoice_po FOREIGN KEY (po_header_id) REFERENCES po_headers(id) ON DELETE CASCADE,
  UNIQUE KEY uk_po_invoice (po_header_id, invoice_no)
);

CREATE TABLE IF NOT EXISTS finance_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ap_invoice_id INT NOT NULL,
  payment_no VARCHAR(120) NOT NULL,
  payment_date DATE NULL,
  paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
  payment_method VARCHAR(80) NULL,
  status VARCHAR(60) NOT NULL DEFAULT 'Booked',
  remarks TEXT NULL,
  created_by INT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_payment_invoice FOREIGN KEY (ap_invoice_id) REFERENCES finance_ap_invoices(id) ON DELETE CASCADE,
  UNIQUE KEY uk_invoice_payment (ap_invoice_id, payment_no)
);

CREATE OR REPLACE VIEW vw_erp_document_facts AS
SELECT
  'PR' AS source_module,
  pr.id AS source_id,
  pr.pr_date AS fact_date,
  pr.pr_number AS document_number,
  pr.status,
  pr.total_amount AS amount_aed,
  pr.pr_type_id AS type_id,
  COALESCE(t.type_name, pr.pr_type) AS type_name,
  pr.department_id,
  COALESCE(d.department_name, pr.department) AS department_name,
  pr.business_unit_id,
  COALESCE(bu.business_unit_name, pr.business_unit) AS business_unit_name,
  NULL AS supplier_id,
  NULL AS supplier_name
FROM pr_headers pr
LEFT JOIN types t ON t.id = pr.pr_type_id
LEFT JOIN departments d ON d.id = pr.department_id
LEFT JOIN business_units bu ON bu.id = pr.business_unit_id
UNION ALL
SELECT
  'PO' AS source_module,
  po.id AS source_id,
  po.po_date AS fact_date,
  po.po_number AS document_number,
  po.status,
  po.total_amount AS amount_aed,
  po.po_type_id AS type_id,
  COALESCE(t.type_name, po.po_type) AS type_name,
  po.department_id,
  COALESCE(d.department_name, po.department) AS department_name,
  po.business_unit_id,
  COALESCE(bu.business_unit_name, po.business_unit) AS business_unit_name,
  po.vendor_id AS supplier_id,
  s.supplier_legal_name AS supplier_name
FROM po_headers po
LEFT JOIN suppliers s ON s.id = po.vendor_id
LEFT JOIN types t ON t.id = po.po_type_id
LEFT JOIN departments d ON d.id = po.department_id
LEFT JOIN business_units bu ON bu.id = po.business_unit_id
UNION ALL
SELECT
  'TRACKING' AS source_module,
  tr.id AS source_id,
  tr.pr_receival_date AS fact_date,
  tr.pr_no AS document_number,
  ps.status_name AS status,
  COALESCE(tr.amount_aed,0) AS amount_aed,
  tr.type_id AS type_id,
  ty.type_name AS type_name,
  NULL AS department_id,
  NULL AS department_name,
  NULL AS business_unit_id,
  NULL AS business_unit_name,
  tr.supplier_id AS supplier_id,
  s.supplier_legal_name AS supplier_name
FROM tracking_records tr
LEFT JOIN po_statuses ps ON ps.id = tr.po_status_id
LEFT JOIN types ty ON ty.id = tr.type_id
LEFT JOIN suppliers s ON s.id = tr.supplier_id
WHERE tr.deleted_at IS NULL;

INSERT INTO departments(department_name,is_active,created_at,updated_at)
VALUES ('Procurement',1,NOW(),NOW()),('Finance',1,NOW(),NOW()),('Operations',1,NOW(),NOW())
ON DUPLICATE KEY UPDATE updated_at=NOW();

INSERT INTO business_units(business_unit_name,is_active,created_at,updated_at)
VALUES ('Corporate',1,NOW(),NOW()),('Operations',1,NOW(),NOW()),('Projects',1,NOW(),NOW())
ON DUPLICATE KEY UPDATE updated_at=NOW();

INSERT INTO currencies(currency_code,currency_name,is_active,created_at,updated_at)
VALUES ('AED','UAE Dirham',1,NOW(),NOW()),('USD','US Dollar',1,NOW(),NOW())
ON DUPLICATE KEY UPDATE updated_at=NOW();

INSERT INTO purchasing_groups(group_code,group_name,is_active,created_at,updated_at)
VALUES ('PG01','Central Procurement',1,NOW(),NOW()),('PG02','Operations Procurement',1,NOW(),NOW())
ON DUPLICATE KEY UPDATE updated_at=NOW();

-- Backfill normalized foreign keys from legacy text columns
UPDATE pr_headers pr
LEFT JOIN types t ON t.type_name = pr.pr_type
LEFT JOIN departments d ON d.department_name = pr.department
LEFT JOIN business_units bu ON bu.business_unit_name = pr.business_unit
LEFT JOIN purchasing_groups pg ON pg.group_code = pr.purchasing_group
LEFT JOIN currencies c ON c.currency_code = pr.currency
SET pr.pr_type_id = COALESCE(pr.pr_type_id, t.id),
    pr.department_id = COALESCE(pr.department_id, d.id),
    pr.business_unit_id = COALESCE(pr.business_unit_id, bu.id),
    pr.purchasing_group_id = COALESCE(pr.purchasing_group_id, pg.id),
    pr.currency_id = COALESCE(pr.currency_id, c.id);

UPDATE po_headers po
LEFT JOIN types t ON t.type_name = po.po_type
LEFT JOIN departments d ON d.department_name = po.department
LEFT JOIN business_units bu ON bu.business_unit_name = po.business_unit
LEFT JOIN purchasing_groups pg ON pg.group_code = po.purchasing_group
LEFT JOIN currencies c ON c.currency_code = po.currency
LEFT JOIN pr_headers pr ON pr.pr_number = po.pr_reference
SET po.po_type_id = COALESCE(po.po_type_id, t.id),
    po.department_id = COALESCE(po.department_id, d.id),
    po.business_unit_id = COALESCE(po.business_unit_id, bu.id),
    po.purchasing_group_id = COALESCE(po.purchasing_group_id, pg.id),
    po.currency_id = COALESCE(po.currency_id, c.id),
    po.source_pr_header_id = COALESCE(po.source_pr_header_id, pr.id);

UPDATE tracking_records tr
LEFT JOIN pr_headers pr ON pr.pr_number = tr.pr_no
LEFT JOIN po_headers po ON po.po_number = tr.po_no
SET tr.pr_header_id = COALESCE(tr.pr_header_id, pr.id),
    tr.po_header_id = COALESCE(tr.po_header_id, po.id),
    tr.supplier_id = COALESCE(tr.supplier_id, po.vendor_id);
