USE ezyro_41363280_codex;

CREATE TABLE IF NOT EXISTS password_reset_otps (
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

UPDATE users
SET password_hash = '$2y$12$dsHsVId7jKSS2pNUVXN25.3X4hP0n1DYfg7UJAHnMFFSICaigIs9C', is_active = 1
WHERE username = 'admin' OR email = 'admin@asteco.local';
