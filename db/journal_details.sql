CREATE TABLE journal_details (
    journal_id INT AUTO_INCREMENT PRIMARY KEY,
    journal_short_name VARCHAR(50) NOT NULL UNIQUE,
    journal_full_name VARCHAR(255) NOT NULL,
    journal_logo_url VARCHAR(255),
    meta_keywords TEXT,
    journal_issn VARCHAR(20) UNIQUE,
    journal_doi_prefix VARCHAR(20) UNIQUE,
    journal_website_url VARCHAR(255),
    journal_impact_factor DECIMAL(5,3),
    journal_publisher VARCHAR(255) NOT NULL,
    journal_publication_frequency ENUM('Monthly', 'Bi-monthly', 'Quarterly', 'Semi-annually', 'Annually'),
    journal_language VARCHAR(50) DEFAULT 'English',
    journal_country_of_publication VARCHAR(100),
    journal_established_year INT(4),
    journal_contact_email VARCHAR(100),
    
    -- Current issue information
    current_volume INT DEFAULT 1,
    current_issue INT DEFAULT 1,
    current_issue_year INT DEFAULT YEAR(CURRENT_TIMESTAMP),
    current_issue_month ENUM('January', 'February', 'March', 'April', 'May', 'June', 
                           'July', 'August', 'September', 'October', 'November', 'December'),
    
    -- Call for papers
    cfp_title VARCHAR(255),
    cfp_content TEXT,
    cfp_topics JSON,
    cfp_deadline DATE,
    cfp_notification_date DATE,
    cfp_camera_ready_date DATE,
    cfp_publication_date DATE,
    cfp_active TINYINT(1) DEFAULT 0,
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_journal_status (cfp_active, cfp_deadline),
    INDEX idx_current_issue (current_volume, current_issue, current_issue_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
