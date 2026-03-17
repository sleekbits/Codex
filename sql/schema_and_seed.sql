DROP DATABASE IF EXISTS ezyro_41363280_codex;
CREATE DATABASE ezyro_41363280_codex CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ezyro_41363280_codex;

CREATE TABLE roles (id INT AUTO_INCREMENT PRIMARY KEY, role_name VARCHAR(120) UNIQUE, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE permissions (id INT AUTO_INCREMENT PRIMARY KEY, module_name VARCHAR(100), action_name VARCHAR(60));
CREATE TABLE role_permissions (role_id INT, permission_id INT, PRIMARY KEY(role_id,permission_id), FOREIGN KEY (role_id) REFERENCES roles(id), FOREIGN KEY(permission_id) REFERENCES permissions(id));
CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, role_id INT, username VARCHAR(120) UNIQUE, email VARCHAR(190) UNIQUE, password_hash VARCHAR(255), is_active TINYINT DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(role_id) REFERENCES roles(id));
CREATE TABLE user_profiles (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT UNIQUE, full_name VARCHAR(150), designation VARCHAR(150), phone VARCHAR(40), image_path VARCHAR(255), FOREIGN KEY(user_id) REFERENCES users(id));
CREATE TABLE settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(120) UNIQUE, setting_value TEXT, updated_at TIMESTAMP NULL);

CREATE TABLE types (id INT AUTO_INCREMENT PRIMARY KEY, type_name VARCHAR(80));
CREATE TABLE statuses (id INT AUTO_INCREMENT PRIMARY KEY, status_name VARCHAR(80));
CREATE TABLE departments (id INT AUTO_INCREMENT PRIMARY KEY, department_name VARCHAR(120));
CREATE TABLE business_units (id INT AUTO_INCREMENT PRIMARY KEY, business_unit_name VARCHAR(120));
CREATE TABLE purchasing_groups (id INT AUTO_INCREMENT PRIMARY KEY, group_name VARCHAR(120));
CREATE TABLE currencies (id INT AUTO_INCREMENT PRIMARY KEY, currency_code VARCHAR(10));

CREATE TABLE suppliers (id INT AUTO_INCREMENT PRIMARY KEY, supplier_code VARCHAR(40) UNIQUE, legal_name VARCHAR(190), license_number VARCHAR(90), email VARCHAR(190), phone VARCHAR(40), mobile VARCHAR(40), address TEXT, vat_number VARCHAR(80), status VARCHAR(20), remarks TEXT, created_at TIMESTAMP NULL);

CREATE TABLE pr_headers (id INT AUTO_INCREMENT PRIMARY KEY, pr_number VARCHAR(40) UNIQUE, pr_date DATE, pr_type VARCHAR(40), requestor VARCHAR(120), department VARCHAR(120), business_unit VARCHAR(120), currency VARCHAR(10), required_delivery_date DATE, priority VARCHAR(30), status VARCHAR(30), justification TEXT, remarks TEXT, created_at TIMESTAMP NULL);
CREATE TABLE pr_items (id INT AUTO_INCREMENT PRIMARY KEY, pr_id INT, item_no INT, material_code VARCHAR(80), short_description VARCHAR(255), detailed_description TEXT, quantity DECIMAL(14,2), uom VARCHAR(20), estimated_price DECIMAL(14,2), total_amount DECIMAL(14,2), delivery_date DATE, plant_location VARCHAR(120), cost_center VARCHAR(60), gl_account VARCHAR(60), wbs_project_code VARCHAR(80), account_assignment_category VARCHAR(60), purchasing_group VARCHAR(80), suggested_vendor VARCHAR(120), status VARCHAR(30), remarks TEXT, FOREIGN KEY(pr_id) REFERENCES pr_headers(id));

CREATE TABLE po_headers (id INT AUTO_INCREMENT PRIMARY KEY, po_number VARCHAR(40) UNIQUE, po_date DATE, po_type VARCHAR(40), vendor_name VARCHAR(190), supplier_code VARCHAR(40), company_code VARCHAR(40), purchasing_org VARCHAR(80), purchasing_group VARCHAR(80), currency VARCHAR(10), payment_terms VARCHAR(120), delivery_terms VARCHAR(120), incoterms VARCHAR(80), contract_reference VARCHAR(80), pr_reference VARCHAR(80), quotation_reference VARCHAR(80), tender_reference VARCHAR(80), validity_date DATE, status VARCHAR(30), remarks TEXT, total_amount DECIMAL(14,2), created_at TIMESTAMP NULL);
CREATE TABLE po_items (id INT AUTO_INCREMENT PRIMARY KEY, po_id INT, item_no INT, material_code VARCHAR(80), short_description VARCHAR(255), detailed_description TEXT, quantity DECIMAL(14,2), uom VARCHAR(20), unit_price DECIMAL(14,2), total_amount DECIMAL(14,2), delivery_date DATE, delivery_location VARCHAR(120), account_assignment VARCHAR(80), cost_center VARCHAR(60), gl_account VARCHAR(60), tax_code VARCHAR(30), pr_reference_item VARCHAR(60), contract_reference VARCHAR(80), status VARCHAR(30), remarks TEXT, FOREIGN KEY(po_id) REFERENCES po_headers(id));
CREATE TABLE po_print_templates (id INT AUTO_INCREMENT PRIMARY KEY, template_name VARCHAR(100), html_template MEDIUMTEXT);

CREATE TABLE workflow_hierarchy_types (id INT AUTO_INCREMENT PRIMARY KEY, type_name VARCHAR(100));
CREATE TABLE workflow_hierarchy_headers (id INT AUTO_INCREMENT PRIMARY KEY, document_type VARCHAR(60), department VARCHAR(120), business_unit VARCHAR(120), threshold_min DECIMAL(14,2), threshold_max DECIMAL(14,2), stage_logic VARCHAR(60));
CREATE TABLE workflow_hierarchy_stages (id INT AUTO_INCREMENT PRIMARY KEY, hierarchy_id INT, stage_no INT, stage_type VARCHAR(60), approver_role VARCHAR(120), approver_user_id INT NULL, is_final_approver TINYINT DEFAULT 0, FOREIGN KEY(hierarchy_id) REFERENCES workflow_hierarchy_headers(id));
CREATE TABLE workflow_transactions (id INT AUTO_INCREMENT PRIMARY KEY, document_type VARCHAR(40), document_id INT, current_stage INT, status VARCHAR(40), creator_id INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE workflow_transaction_steps (id INT AUTO_INCREMENT PRIMARY KEY, transaction_id INT, stage_no INT, actor_id INT, action VARCHAR(40), comments TEXT, acted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(transaction_id) REFERENCES workflow_transactions(id));
CREATE TABLE workflow_audit_logs (id INT AUTO_INCREMENT PRIMARY KEY, transaction_id INT, action VARCHAR(50), actor_name VARCHAR(120), comments TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);

CREATE TABLE poa_hierarchy_headers (id INT AUTO_INCREMENT PRIMARY KEY, document_type VARCHAR(60), threshold_min DECIMAL(14,2), threshold_max DECIMAL(14,2), signatory_role VARCHAR(120));
CREATE TABLE poa_hierarchy_stages (id INT AUTO_INCREMENT PRIMARY KEY, poa_header_id INT, stage_no INT, signatory_role VARCHAR(120), FOREIGN KEY(poa_header_id) REFERENCES poa_hierarchy_headers(id));

CREATE TABLE tracking_records (id INT AUTO_INCREMENT PRIMARY KEY, s_no INT, pr_receival_date DATE, pr_no VARCHAR(40), assigned_to VARCHAR(120), brief_description VARCHAR(255), wo_dwo_vo_ref VARCHAR(80), amount_aed DECIMAL(14,2), contract_reference VARCHAR(80), contractor_name VARCHAR(190), po_no VARCHAR(40), po_status VARCHAR(30), po_release_date DATE, remarks VARCHAR(255), type VARCHAR(40), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_pr_no(pr_no), INDEX idx_po_no(po_no));

CREATE TABLE cost_centers (id INT AUTO_INCREMENT PRIMARY KEY, cost_center_code VARCHAR(60), cost_center_name VARCHAR(120));
CREATE TABLE gl_accounts (id INT AUTO_INCREMENT PRIMARY KEY, gl_code VARCHAR(60), gl_name VARCHAR(120));
CREATE TABLE commitments (id INT AUTO_INCREMENT PRIMARY KEY, pr_reference VARCHAR(40), po_reference VARCHAR(40), department VARCHAR(120), business_unit VARCHAR(120), pr_estimated_amount DECIMAL(14,2), po_committed_amount DECIMAL(14,2), released_amount DECIMAL(14,2), pending_exposure DECIMAL(14,2));
CREATE TABLE invoices (id INT AUTO_INCREMENT PRIMARY KEY, invoice_number VARCHAR(60), invoice_date DATE, supplier_name VARCHAR(190), po_reference VARCHAR(40), invoice_amount DECIMAL(14,2), payment_status VARCHAR(30), payment_due_date DATE, paid_date DATE NULL, remarks TEXT);
CREATE TABLE payments (id INT AUTO_INCREMENT PRIMARY KEY, invoice_id INT, amount DECIMAL(14,2), paid_date DATE, payment_method VARCHAR(60), FOREIGN KEY(invoice_id) REFERENCES invoices(id));
CREATE TABLE budgets (id INT AUTO_INCREMENT PRIMARY KEY, fiscal_year INT, department VARCHAR(120), business_unit VARCHAR(120), budget_amount DECIMAL(14,2));

CREATE TABLE audit_logs (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, action VARCHAR(50), module_name VARCHAR(80), description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE import_logs (id INT AUTO_INCREMENT PRIMARY KEY, module_name VARCHAR(80), file_name VARCHAR(190), mode_used VARCHAR(30), rows_processed INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE export_logs (id INT AUTO_INCREMENT PRIMARY KEY, module_name VARCHAR(80), format VARCHAR(20), filters TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);

CREATE TABLE password_reset_otps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(190) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    attempts INT DEFAULT 0,
    resend_count INT DEFAULT 0,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id),
    INDEX idx_reset_email(email),
    INDEX idx_reset_expiry(expires_at)
);

INSERT INTO roles(role_name) VALUES
('Admin'),('ERP/IT'),('CEO'),('CPO'),('Executive Director Procurement'),('Director Procurement'),('Associate Director Procurement'),('Senior Manager Procurement'),('Manager Procurement'),('Assistant Manager Procurement'),('Senior Buyer'),('Buyer'),('Senior Procurement Officer'),('Procurement Officer');

INSERT INTO permissions(module_name,action_name) VALUES
('Dashboard','View'),('Tracking','View'),('Tracking','Add'),('Tracking','Edit'),('Tracking','Delete'),('Tracking','Import'),('Tracking','Export'),('Vendors','Manage'),('PR','Submit'),('PO','Approve'),('Workflow','Delegate'),('Workflow','Reassign'),('Workflow','Sign on Behalf'),('Users','Reset Password');
INSERT INTO role_permissions(role_id,permission_id)
SELECT r.id,p.id FROM roles r JOIN permissions p ON (r.role_name='Admin' OR p.action_name IN ('View'));

INSERT INTO users(role_id,username,email,password_hash,is_active) VALUES
(1,'admin','admin@asteco.local','$2y$12$dsHsVId7jKSS2pNUVXN25.3X4hP0n1DYfg7UJAHnMFFSICaigIs9C',1),
(2,'erpit','erpit@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(3,'ceo','ceo@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(4,'cpo','cpo@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(5,'edp','edp@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(6,'dp','dp@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(7,'adp','adp@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(8,'smp','smp@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(9,'mp','mp@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(10,'amp','amp@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(11,'sbuyer','sbuyer@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(12,'buyer','buyer@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(13,'spo','spo@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1),
(14,'pofficer','pofficer@asteco.local','$2y$10$NtQmyjipYJg78Qv2go8Vn..9DIww8FcFPczSMGn6rPsxmvPKR62Dy',1);

INSERT INTO user_profiles(user_id,full_name,designation,phone,image_path)
SELECT id, CONCAT('User ',id), CONCAT('Role - ',id), CONCAT('+97150000',LPAD(id,3,'0')), 'uploads/avatar-default.png' FROM users;

INSERT INTO settings(setting_key,setting_value) VALUES
('app_name','Asteco Procurement ERP'),('date_format','d-M-Y'),('currency','AED'),('records_per_page','10'),('dashboard_default_year','2025');

INSERT INTO types(type_name) VALUES ('CAPEX'),('OPEX'),('ARR Competitive'),('ARR Non-Competitive'),('VO'),('VORR');
INSERT INTO statuses(status_name) VALUES ('Draft'),('Submitted'),('Pending'),('Released'),('Rejected'),('Returned');
INSERT INTO departments(department_name) VALUES ('Procurement'),('Finance'),('Operations'),('Facilities');
INSERT INTO business_units(business_unit_name) VALUES ('UAE'),('KSA'),('Qatar');
INSERT INTO purchasing_groups(group_name) VALUES ('PG01'),('PG02'),('PG03');
INSERT INTO currencies(currency_code) VALUES ('AED'),('USD'),('EUR');

INSERT INTO suppliers(supplier_code,legal_name,license_number,email,phone,mobile,address,vat_number,status,remarks,created_at)
SELECT CONCAT('SUP',LPAD(n,3,'0')), CONCAT('Supplier ',n), CONCAT('LIC-',1000+n), CONCAT('supplier',n,'@mail.com'), '+971400000', CONCAT('+97150000',LPAD(n,3,'0')), CONCAT('Dubai Address ',n), CONCAT('VAT',10000+n), IF(n%5=0,'Inactive','Active'), 'Seed supplier', NOW()
FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION SELECT 21 UNION SELECT 22) t;

INSERT INTO pr_headers(pr_number,pr_date,pr_type,requestor,department,business_unit,currency,required_delivery_date,priority,status,justification,remarks,created_at)
SELECT CONCAT('PR-2025-',LPAD(n,4,'0')), DATE_SUB(CURDATE(), INTERVAL n DAY), IF(n%2=0,'CAPEX','OPEX'),'Requestor', 'Procurement','UAE','AED',DATE_ADD(CURDATE(),INTERVAL n DAY), IF(n%3=0,'High','Medium'), ELT((n%5)+1,'Draft','Submitted','Rejected','Pending','Released'),'Seed Justification','Seed PR',NOW()
FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) q;
INSERT INTO pr_items(pr_id,item_no,material_code,short_description,detailed_description,quantity,uom,estimated_price,total_amount,delivery_date,plant_location,cost_center,gl_account,wbs_project_code,account_assignment_category,purchasing_group,suggested_vendor,status,remarks)
SELECT p.id,1,CONCAT('MAT',p.id),CONCAT('PR Item ',p.id),'Detailed desc',10, 'EA', 1000+p.id, (1000+p.id)*10, DATE_ADD(CURDATE(),INTERVAL 30 DAY), 'Dubai','CC100','GL500','WBSX','K','PG01','Supplier 1','Draft','-'
FROM pr_headers p;

INSERT INTO po_headers(po_number,po_date,po_type,vendor_name,supplier_code,company_code,purchasing_org,purchasing_group,currency,payment_terms,delivery_terms,contract_reference,pr_reference,validity_date,status,remarks,total_amount,created_at)
SELECT CONCAT('PO-2025-',LPAD(n,4,'0')), DATE_SUB(CURDATE(), INTERVAL n DAY), 'Standard', CONCAT('Supplier ',n), CONCAT('SUP',LPAD((n%20)+1,3,'0')), 'AST', 'ORG1', 'PG01', 'AED', '30 Days', 'DAP', CONCAT('CTR-',n), CONCAT('PR-2025-',LPAD(n,4,'0')), DATE_ADD(CURDATE(), INTERVAL 60 DAY), ELT((n%4)+1,'Released','Submitted','Rejected','Pending'), 'Seed PO', (n*4000)+15000, NOW()
FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) p;
INSERT INTO po_items(po_id,item_no,material_code,short_description,detailed_description,quantity,uom,unit_price,total_amount,delivery_date,delivery_location,account_assignment,cost_center,gl_account,tax_code,pr_reference_item,contract_reference,status,remarks)
SELECT id,1,CONCAT('MATPO',id),CONCAT('PO Item ',id),'Detail',5,'EA',2000,10000,DATE_ADD(CURDATE(),INTERVAL 20 DAY),'Warehouse','K','CC100','GL500','TX5','1',contract_reference,status,'-'
FROM po_headers;

INSERT INTO workflow_hierarchy_types(type_name) VALUES ('PR'),('PO'),('Tendering Strategy'),('ARR Competitive'),('ARR Non-Competitive'),('VORR'),('VO');
INSERT INTO workflow_hierarchy_headers(document_type,department,business_unit,threshold_min,threshold_max,stage_logic) VALUES
('PR','Procurement','UAE',0,50000,'Sequential'),('PR','Procurement','UAE',50001,250000,'Mixed'),('PO','Procurement','UAE',0,100000,'Sequential'),('PO','Procurement','UAE',100001,500000,'Parallel'),('VO','Operations','UAE',0,1000000,'Sequential'),('ARR Competitive','Procurement','KSA',0,300000,'Parallel'),('ARR Non-Competitive','Procurement','Qatar',0,400000,'Mixed'),('VORR','Facilities','UAE',0,120000,'Sequential'),('Tendering Strategy','Procurement','UAE',0,999999,'Parallel'),('PO','Finance','UAE',0,900000,'Mixed');
INSERT INTO workflow_hierarchy_stages(hierarchy_id,stage_no,stage_type,approver_role,approver_user_id,is_final_approver) VALUES
(1,1,'Endorsement','Manager Procurement',9,0),(1,2,'Approval','Director Procurement',6,1),
(2,1,'Endorsement','Senior Manager Procurement',8,0),(2,2,'Parallel Approval','CPO',4,0),(2,2,'Parallel Approval','Finance',2,0),(2,3,'Approval','CEO',3,1),
(4,1,'Parallel Approval','Director Procurement',6,0),(4,1,'Parallel Approval','CPO',4,0),(4,2,'Approval','CEO',3,1);
INSERT INTO workflow_transactions(document_type,document_id,current_stage,status,creator_id) VALUES
('PR',1,2,'Released',11),('PR',2,1,'Returned',12),('PO',1,2,'Released',11),('PO',2,1,'Rejected',12),('PO',3,1,'Pending',13),('PR',3,1,'Delegated',14);
INSERT INTO workflow_transaction_steps(transaction_id,stage_no,actor_id,action,comments) VALUES
(1,1,9,'Endorsed','Looks good'),(1,2,6,'Approved','Released without comment'),
(2,1,9,'Returned','Need updated specs'),
(3,1,6,'Approved','Parallel stage done'),(3,2,3,'Approved','Final approval'),
(4,1,4,'Rejected','Budget exceeded'),
(6,1,8,'Delegate','Delegated to buyer');
INSERT INTO workflow_audit_logs(transaction_id,action,actor_name,comments) VALUES
(1,'approve','Director Procurement','Auto released after final approval'),(2,'return','Manager Procurement','Return to creator'),(4,'reject','CPO','Reject with comments'),(6,'delegate','Senior Manager Procurement','Delegated due leave'),(6,'reassign','ERP/IT','Admin reassigned'),(3,'sign_on_behalf','CEO Office','Signed on behalf');

INSERT INTO poa_hierarchy_headers(document_type,threshold_min,threshold_max,signatory_role) VALUES ('PO',0,100000,'Manager Procurement'),('PO',100001,500000,'Director Procurement'),('PO',500001,9999999,'CEO');
INSERT INTO poa_hierarchy_stages(poa_header_id,stage_no,signatory_role) VALUES (1,1,'Manager Procurement'),(2,1,'Director Procurement'),(3,1,'CEO');

INSERT INTO cost_centers(cost_center_code,cost_center_name) VALUES ('CC100','Procurement Core'),('CC200','Operations'),('CC300','Facilities');
INSERT INTO gl_accounts(gl_code,gl_name) VALUES ('GL500','Procurement Expense'),('GL510','Capex Projects'),('GL520','Maintenance Services');

INSERT INTO commitments(pr_reference,po_reference,department,business_unit,pr_estimated_amount,po_committed_amount,released_amount,pending_exposure)
SELECT CONCAT('PR-2025-',LPAD(n,4,'0')), CONCAT('PO-2025-',LPAD(n,4,'0')), 'Procurement', IF(n%2=0,'UAE','KSA'), 20000+n*500, 25000+n*1000, 20000+n*800, 5000+n*200
FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) c;

INSERT INTO invoices(invoice_number,invoice_date,supplier_name,po_reference,invoice_amount,payment_status,payment_due_date,paid_date,remarks)
SELECT CONCAT('INV-2025-',LPAD(n,4,'0')), DATE_SUB(CURDATE(), INTERVAL n DAY), CONCAT('Supplier ',n), CONCAT('PO-2025-',LPAD((n%12)+1,4,'0')), 5000+n*700, IF(n%3=0,'Paid','Pending'), DATE_ADD(CURDATE(), INTERVAL n DAY), IF(n%3=0,DATE_SUB(CURDATE(), INTERVAL 1 DAY),NULL), 'seed invoice'
FROM (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12) i;
INSERT INTO payments(invoice_id,amount,paid_date,payment_method) SELECT id,invoice_amount,paid_date,'Bank Transfer' FROM invoices WHERE paid_date IS NOT NULL;
INSERT INTO budgets(fiscal_year,department,business_unit,budget_amount) VALUES (2025,'Procurement','UAE',5000000),(2025,'Procurement','KSA',3500000),(2025,'Operations','UAE',4200000);

-- 120 tracking records including duplicate PR/PO patterns
INSERT INTO tracking_records(s_no,pr_receival_date,pr_no,assigned_to,brief_description,wo_dwo_vo_ref,amount_aed,contract_reference,contractor_name,po_no,po_status,po_release_date,remarks,type)
SELECT n, DATE_SUB(CURDATE(), INTERVAL n DAY), CONCAT('PR-2025-',LPAD((n%40)+1,4,'0')), ELT((n%4)+1,'Buyer','Senior Buyer','Manager Procurement','Procurement Officer'), CONCAT('Tracking Desc ',n), CONCAT('WO-',n), (n*1000)+5000, CONCAT('CTR-',(n%20)+1), CONCAT('Contractor ',(n%15)+1), CONCAT('PO-2025-',LPAD((n%35)+1,4,'0')), ELT((n%4)+1,'Released','Pending','Rejected','Submitted'), IF(n%4=1,DATE_SUB(CURDATE(), INTERVAL n-2 DAY),NULL), IF(n IN (25,55,95),'Duplicate PO for test','Seed'), ELT((n%3)+1,'CAPEX','OPEX','VORR')
FROM (
SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION SELECT 30 UNION SELECT 31 UNION SELECT 32 UNION SELECT 33 UNION SELECT 34 UNION SELECT 35 UNION SELECT 36 UNION SELECT 37 UNION SELECT 38 UNION SELECT 39 UNION SELECT 40 UNION SELECT 41 UNION SELECT 42 UNION SELECT 43 UNION SELECT 44 UNION SELECT 45 UNION SELECT 46 UNION SELECT 47 UNION SELECT 48 UNION SELECT 49 UNION SELECT 50 UNION SELECT 51 UNION SELECT 52 UNION SELECT 53 UNION SELECT 54 UNION SELECT 55 UNION SELECT 56 UNION SELECT 57 UNION SELECT 58 UNION SELECT 59 UNION SELECT 60 UNION SELECT 61 UNION SELECT 62 UNION SELECT 63 UNION SELECT 64 UNION SELECT 65 UNION SELECT 66 UNION SELECT 67 UNION SELECT 68 UNION SELECT 69 UNION SELECT 70 UNION SELECT 71 UNION SELECT 72 UNION SELECT 73 UNION SELECT 74 UNION SELECT 75 UNION SELECT 76 UNION SELECT 77 UNION SELECT 78 UNION SELECT 79 UNION SELECT 80 UNION SELECT 81 UNION SELECT 82 UNION SELECT 83 UNION SELECT 84 UNION SELECT 85 UNION SELECT 86 UNION SELECT 87 UNION SELECT 88 UNION SELECT 89 UNION SELECT 90 UNION SELECT 91 UNION SELECT 92 UNION SELECT 93 UNION SELECT 94 UNION SELECT 95 UNION SELECT 96 UNION SELECT 97 UNION SELECT 98 UNION SELECT 99 UNION SELECT 100 UNION SELECT 101 UNION SELECT 102 UNION SELECT 103 UNION SELECT 104 UNION SELECT 105 UNION SELECT 106 UNION SELECT 107 UNION SELECT 108 UNION SELECT 109 UNION SELECT 110 UNION SELECT 111 UNION SELECT 112 UNION SELECT 113 UNION SELECT 114 UNION SELECT 115 UNION SELECT 116 UNION SELECT 117 UNION SELECT 118 UNION SELECT 119 UNION SELECT 120
) nset;

INSERT INTO audit_logs(user_id,action,module_name,description) VALUES
(1,'create','users','Initial seed users'),(1,'import','tracking','Imported tracking template'),(2,'export','suppliers','Supplier export'),(9,'submit','pr','Submitted PR-2025-0001'),(6,'approve','workflow','Approved step'),(4,'reject','workflow','Rejected transaction'),(1,'settings_change','settings','Updated app settings');
