CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_referral_id VARCHAR(20) UNIQUE,
    user_prefixname ENUM('Mr', 'Ms', 'Mrs', 'Dr', 'Prof'),
    user_fname VARCHAR(50) NOT NULL,
    user_mname VARCHAR(50),
    user_lname VARCHAR(50) NOT NULL,
    user_designation VARCHAR(100),
    user_institution VARCHAR(255),
    user_email VARCHAR(100) UNIQUE NOT NULL,
    user_countryCode VARCHAR(5),
    user_mobile VARCHAR(15),
    
    -- Address information
    user_address_line1 VARCHAR(255),
    user_address_line2 VARCHAR(255),
    user_city VARCHAR(100),
    user_state VARCHAR(100),
    user_country VARCHAR(100),
    user_pin_code VARCHAR(20),
    user_profile_image VARCHAR(255),
    user_about_me TEXT,
    user_comments TEXT,
    
    -- User roles (using bit flags for better performance)
    user_roles INT UNSIGNED DEFAULT 0,
    user_permissions JSON,
    
    -- Account status
    user_status ENUM('ACTIVE', 'INACTIVE', 'BLOCKED', 'PENDING') DEFAULT 'PENDING',
    user_email_verified TINYINT(1) DEFAULT 0,
    user_mobile_verified TINYINT(1) DEFAULT 0,
    
    -- Authentication & Security
    user_username VARCHAR(50) UNIQUE,
    user_password_hash VARCHAR(255) NOT NULL,
    user_failed_login_attempts INT(1) DEFAULT 0,
    user_lockout_until DATETIME,
    user_password_changed_at DATETIME,
    user_reset_token VARCHAR(255),
    user_reset_token_expiry DATETIME,
    user_oauth_provider ENUM('GOOGLE', 'FACEBOOK', 'ORCID') DEFAULT NULL,
    user_oauth_uid VARCHAR(255) DEFAULT NULL,
    
    -- Session management
    user_session_token VARCHAR(255),
    user_session_expiry DATETIME,
    
    -- Timestamps and metadata
    user_reg_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_last_login DATETIME,
    user_last_activity DATETIME,
    user_ip_address VARCHAR(45),
    user_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_user_status (user_status),
    INDEX idx_user_roles (user_roles),
    INDEX idx_user_email (user_email),
    INDEX idx_user_username (user_username),
    INDEX idx_user_oauth (user_oauth_provider, user_oauth_uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create table for password history
CREATE TABLE user_password_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

DELIMITER //
CREATE TRIGGER after_password_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.user_password_hash != OLD.user_password_hash THEN
        INSERT INTO user_password_history (user_id, password_hash)
        VALUES (NEW.user_id, NEW.user_password_hash);
    END IF;
END;//
DELIMITER ;
