-- Safe migration for existing deployments
USE asteco_procurement_dashboard;

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
