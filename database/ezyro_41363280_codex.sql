CREATE DATABASE IF NOT EXISTS ezyro_41363280_codex;
USE ezyro_41363280_codex;

DROP TABLE IF EXISTS workflow_audit_logs, workflow_transaction_steps, workflow_transactions, workflow_hierarchy_stages, workflow_hierarchy_types, workflow_hierarchy_headers, po_items, po_headers, pr_items, pr_headers, suppliers, poa_hierarchy, doa_hierarchy, role_permissions, activity_logs, export_logs, import_logs, tracking_records, app_settings, contractors, po_statuses, types, users, roles;

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(50) NOT NULL UNIQUE,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  designation VARCHAR(120) NULL,
  email VARCHAR(120) NOT NULL,
  phone VARCHAR(30) NULL,
  username VARCHAR(80) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255) NULL,
  role_id INT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE contractors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  contractor_name VARCHAR(150) NOT NULL UNIQUE,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE po_statuses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  status_name VARCHAR(100) NOT NULL UNIQUE,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type_name VARCHAR(120) NOT NULL UNIQUE,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
);

CREATE TABLE tracking_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  s_no INT NULL,
  pr_receival_date DATE NOT NULL,
  pr_no VARCHAR(100) NOT NULL,
  assigned_to_user_id INT NOT NULL,
  brief_description TEXT NOT NULL,
  wo_dwo_vo_ref VARCHAR(120) NULL,
  amount_aed DECIMAL(14,2) NULL,
  contract_reference VARCHAR(120) NULL,
  contractor_id INT NOT NULL,
  po_no VARCHAR(100) NULL,
  po_status_id INT NOT NULL,
  po_release_date DATE NULL,
  remarks TEXT NULL,
  type_id INT NOT NULL,
  created_by INT NULL,
  updated_by INT NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_tr_user FOREIGN KEY (assigned_to_user_id) REFERENCES users(id),
  CONSTRAINT fk_tr_contractor FOREIGN KEY (contractor_id) REFERENCES contractors(id),
  CONSTRAINT fk_tr_status FOREIGN KEY (po_status_id) REFERENCES po_statuses(id),
  CONSTRAINT fk_tr_type FOREIGN KEY (type_id) REFERENCES types(id)
);

CREATE TABLE app_settings (
  setting_key VARCHAR(120) PRIMARY KEY,
  setting_value TEXT,
  updated_at DATETIME NULL
);

CREATE TABLE import_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  file_name VARCHAR(255),
  imported_count INT DEFAULT 0,
  updated_count INT DEFAULT 0,
  skipped_count INT DEFAULT 0,
  created_at DATETIME,
  CONSTRAINT fk_import_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE export_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  format VARCHAR(20),
  filters TEXT,
  record_count INT DEFAULT 0,
  created_at DATETIME,
  CONSTRAINT fk_export_user FOREIGN KEY (user_id) REFERENCES users(id)
);



CREATE TABLE role_permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  permission_key VARCHAR(100) NOT NULL,
  is_allowed TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uk_role_perm (role_id, permission_key),
  CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE doa_hierarchy (
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

CREATE TABLE poa_hierarchy (
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

CREATE TABLE suppliers (
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

CREATE TABLE pr_headers (
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

CREATE TABLE pr_items (
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

CREATE TABLE po_headers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  po_number VARCHAR(80) NOT NULL UNIQUE,
  po_date DATE NOT NULL,
  po_type VARCHAR(120) NULL,
  vendor_id INT NOT NULL,
  department VARCHAR(120) NULL,
  business_unit VARCHAR(120) NULL,
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

CREATE TABLE po_items (
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


CREATE TABLE workflow_hierarchy_headers (
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

CREATE TABLE workflow_hierarchy_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hierarchy_header_id INT NOT NULL,
  type_name VARCHAR(120) NOT NULL,
  type_group VARCHAR(120) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  CONSTRAINT fk_wh_type_header FOREIGN KEY (hierarchy_header_id) REFERENCES workflow_hierarchy_headers(id) ON DELETE CASCADE
);

CREATE TABLE workflow_hierarchy_stages (
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

CREATE TABLE workflow_transactions (
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

CREATE TABLE workflow_transaction_steps (
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

CREATE TABLE workflow_audit_logs (
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


CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(120) NOT NULL,
  description TEXT,
  ip_address VARCHAR(45),
  created_at DATETIME,
  CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO roles(role_name,created_at,updated_at) VALUES
('Admin',NOW(),NOW()),
('ERP/IT',NOW(),NOW()),
('Chief Executive Officer (CEO)',NOW(),NOW()),
('Chief Procurement Officer (CPO)',NOW(),NOW()),
('Executive Director Procurement',NOW(),NOW()),
('Director Procurement',NOW(),NOW()),
('Associate Director Procurement',NOW(),NOW()),
('Senior Manager Procurement',NOW(),NOW()),
('Manager Procurement',NOW(),NOW()),
('Assistant Manager Procurement',NOW(),NOW()),
('Senior Buyer',NOW(),NOW()),
('Buyer',NOW(),NOW()),
('Senior Procurement Officer',NOW(),NOW()),
('Procurement Officer',NOW(),NOW());

INSERT INTO users(full_name,designation,email,phone,username,password,profile_image,role_id,is_active,created_at,updated_at) VALUES
('System Admin','System Administrator','admin@asteco.local','+971500000001','admin','$2y$12$thhvzlBIxl4FbXdCsH3fXOwVkcrB80.8KGcewK4jEAbjbyUtBJ6aq',NULL,1,1,NOW(),NOW()),
('Aisha Khan','Manager Procurement','aisha@asteco.local','+971500000002','aisha','$2y$12$thhvzlBIxl4FbXdCsH3fXOwVkcrB80.8KGcewK4jEAbjbyUtBJ6aq',NULL,2,1,NOW(),NOW()),
('Bilal Ahmed','Senior Buyer','bilal@asteco.local','+971500000003','bilal','$2y$12$thhvzlBIxl4FbXdCsH3fXOwVkcrB80.8KGcewK4jEAbjbyUtBJ6aq',NULL,2,1,NOW(),NOW()),
('Carla Diaz','Viewer','carla@asteco.local','+971500000004','carla','$2y$12$thhvzlBIxl4FbXdCsH3fXOwVkcrB80.8KGcewK4jEAbjbyUtBJ6aq',NULL,3,1,NOW(),NOW()),
('David Lee','Viewer','david@asteco.local','+971500000005','david','$2y$12$thhvzlBIxl4FbXdCsH3fXOwVkcrB80.8KGcewK4jEAbjbyUtBJ6aq',NULL,3,1,NOW(),NOW());

INSERT INTO po_statuses(status_name,created_at,updated_at) VALUES
('Deleted',NOW(),NOW()),
('Released',NOW(),NOW()),
('Rejected',NOW(),NOW()),
('Pending with Procurement',NOW(),NOW()),
('Pending with Business Unit',NOW(),NOW());

INSERT INTO types(type_name,created_at,updated_at) VALUES
('Business Case',NOW(),NOW()),('Business Case (ZMM02)',NOW(),NOW()),('Sole',NOW(),NOW()),('Single',NOW(),NOW()),('Emergency',NOW(),NOW()),('FM Services',NOW(),NOW()),('Consultancy',NOW(),NOW()),('Security',NOW(),NOW()),('AC',NOW(),NOW()),('Translation',NOW(),NOW()),('Waterproofing',NOW(),NOW()),('Shades',NOW(),NOW()),('Furniture',NOW(),NOW()),('Curtains',NOW(),NOW()),('Manpower',NOW(),NOW()),('CapEx (Tender)',NOW(),NOW()),('CapEx (SOR)',NOW(),NOW()),('DWO',NOW(),NOW()),('DWO (ZMM02)',NOW(),NOW()),('WO (SOR)',NOW(),NOW()),('WO (CMW)',NOW(),NOW());

INSERT INTO contractors(contractor_name,status,created_at,updated_at) VALUES
('Al Noor Contracting',1,NOW(),NOW()),('Gulf Build Co',1,NOW(),NOW()),('Prime Interiors',1,NOW(),NOW()),('Desert Mechanical',1,NOW(),NOW()),('Blue Wave Trading',1,NOW(),NOW()),('Atlas Engineering',1,NOW(),NOW()),('Modern Facilities',1,NOW(),NOW()),('Elite Services LLC',1,NOW(),NOW()),('Summit Works',1,NOW(),NOW()),('Falcon Procurement',1,NOW(),NOW());

INSERT INTO app_settings(setting_key, setting_value, updated_at) VALUES
('app_name','Asteco Procurement Dashboard',NOW()),
('currency_label','AED',NOW()),
('records_per_page','25',NOW());

INSERT INTO workflow_hierarchy_headers(hierarchy_name,document_category,applies_to_module,type_mode,threshold_from,threshold_to,department,business_unit,is_active,remarks,created_by,updated_by,created_at,updated_at) VALUES
('PR Consultancy Corporate <=500K','PR','PR','selected_types',0,500000,'Procurement','Corporate',1,'Sample seeded hierarchy',1,1,NOW(),NOW()),
('PO Emergency Ops >500K','PO','PO','selected_types',500000,999999999,'Procurement','Operations',1,'Sample seeded hierarchy',1,1,NOW(),NOW()),
('ARR Competitive Generic','ARR (Competitive)','ALL','all_types',0,999999999,'','',1,'Sample for future module',1,1,NOW(),NOW());

INSERT INTO workflow_hierarchy_types(hierarchy_header_id,type_name,type_group,created_at,updated_at)
SELECT id,'Consultancy',NULL,NOW(),NOW() FROM workflow_hierarchy_headers WHERE hierarchy_name='PR Consultancy Corporate <=500K';
INSERT INTO workflow_hierarchy_types(hierarchy_header_id,type_name,type_group,created_at,updated_at)
SELECT id,'Emergency',NULL,NOW(),NOW() FROM workflow_hierarchy_headers WHERE hierarchy_name='PO Emergency Ops >500K';

INSERT INTO workflow_hierarchy_stages(hierarchy_header_id,stage_name,stage_no,stage_type,approval_mode,role_id,user_id,parallel_rule,allow_delegate,allow_sign_on_behalf,is_mandatory,is_active,remarks,created_at,updated_at)
SELECT h.id,'Endorsement 1 - Manager Procurement',1,'Endorsement','sequential',r.id,NULL,NULL,1,0,1,1,NULL,NOW(),NOW()
FROM workflow_hierarchy_headers h JOIN roles r ON r.role_name='Manager Procurement' WHERE h.hierarchy_name='PR Consultancy Corporate <=500K';
INSERT INTO workflow_hierarchy_stages(hierarchy_header_id,stage_name,stage_no,stage_type,approval_mode,role_id,user_id,parallel_rule,allow_delegate,allow_sign_on_behalf,is_mandatory,is_active,remarks,created_at,updated_at)
SELECT h.id,'Endorsement 2 - Director Procurement',2,'Endorsement','sequential',r.id,NULL,NULL,1,0,1,1,NULL,NOW(),NOW()
FROM workflow_hierarchy_headers h JOIN roles r ON r.role_name='Director Procurement' WHERE h.hierarchy_name='PR Consultancy Corporate <=500K';
INSERT INTO workflow_hierarchy_stages(hierarchy_header_id,stage_name,stage_no,stage_type,approval_mode,role_id,user_id,parallel_rule,allow_delegate,allow_sign_on_behalf,is_mandatory,is_active,remarks,created_at,updated_at)
SELECT h.id,'Final Approval - CPO',3,'Approval','sequential',r.id,NULL,NULL,1,1,1,1,NULL,NOW(),NOW()
FROM workflow_hierarchy_headers h JOIN roles r ON r.role_name='Chief Procurement Officer (CPO)' WHERE h.hierarchy_name='PR Consultancy Corporate <=500K';


INSERT INTO tracking_records (s_no, pr_receival_date, pr_no, assigned_to_user_id, brief_description, wo_dwo_vo_ref, amount_aed, contract_reference, contractor_id, po_no, po_status_id, po_release_date, remarks, type_id, created_by, updated_by, created_at, updated_at)
SELECT n,
DATE_SUB(CURDATE(), INTERVAL (n % 365) DAY),
CONCAT('PR-', LPAD(n,5,'0')),
((n % 4) + 1),
CONCAT('Sample brief description #', n),
CONCAT('REF-', n),
(1000 + (n * 35.75)),
CONCAT('CR-', LPAD(n,4,'0')),
((n % 10) + 1),
CONCAT('PO-', LPAD(n,5,'0')),
((n % 5) + 1),
DATE_SUB(CURDATE(), INTERVAL (n % 300) DAY),
CONCAT('Sample remarks #', n),
((n % 21) + 1),
1,1,NOW(),NOW()
FROM (
  SELECT ones.n + tens.n*10 + 1 n
  FROM (SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) ones
  CROSS JOIN (SELECT 0 n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) tens
) numbers
WHERE n <= 120;
