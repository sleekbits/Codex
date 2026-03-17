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
