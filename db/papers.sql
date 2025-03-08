CREATE TABLE papers (
    paper_id CHAR(36) PRIMARY KEY,
    paper_title VARCHAR(255) NOT NULL,
    paper_abstract TEXT,
    paper_keywords VARCHAR(255),
    paper_file_path VARCHAR(255),
    paper_file_size INT,
    paper_pages INT,
    paper_language VARCHAR(50) DEFAULT 'English',
    paper_doi VARCHAR(100) UNIQUE,
    
    -- Paper metadata
    paper_volume INT,
    paper_issue INT,
    paper_year INT,
    paper_month VARCHAR(20),
    paper_publication_date DATE,
    paper_downloads INT DEFAULT 0,
    paper_citations INT DEFAULT 0,
    
    -- Authors
    paper_author_id INT NOT NULL,
    paper_co_author_1_id INT,
    paper_co_author_2_id INT,
    paper_co_author_3_id INT,
    paper_co_author_4_id INT,
    
    -- Status fields
    paper_status ENUM('DRAFT', 'SUBMITTED', 'UNDER_REVIEW', 'REVISION_REQUESTED', 'ACCEPTED', 'REJECTED', 'PUBLISHED', 'WITHDRAWN') DEFAULT 'DRAFT',
    paper_submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    paper_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Review fields
    paper_reviewer_id INT,
    paper_review_status ENUM('PENDING', 'IN_PROGRESS', 'COMPLETED', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    paper_review_comments TEXT,
    paper_review_date DATETIME,
    paper_review_deadline DATE,
    
    -- Payment reference
    paper_pay_status ENUM('PENDING', 'COMPLETED', 'FAILED', 'WAIVED') DEFAULT 'PENDING',
    
    -- Foreign keys
    FOREIGN KEY (paper_author_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (paper_co_author_1_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (paper_co_author_2_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (paper_co_author_3_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (paper_co_author_4_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (paper_reviewer_id) REFERENCES users(user_id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_paper_status (paper_status),
    INDEX idx_paper_submission_date (paper_submission_date),
    INDEX idx_paper_author (paper_author_id),
    INDEX idx_paper_review_status (paper_review_status),
    INDEX idx_paper_pay_status (paper_pay_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
