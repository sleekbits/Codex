CREATE DATABASE IF NOT EXISTS pr_po_dashboard;
USE pr_po_dashboard;

CREATE TABLE IF NOT EXISTS pr_po_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pr_number VARCHAR(100) NOT NULL,
    po_number VARCHAR(100) NOT NULL,
    contract_reference VARCHAR(255) NOT NULL,
    bidder_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    sub_pr_link VARCHAR(500) DEFAULT NULL,
    is_variation_order TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
