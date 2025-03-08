CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    paper_id CHAR(36) NOT NULL,
    reviewer_id INT NOT NULL,
    
    -- Review Content
    review_comments TEXT NOT NULL,
    review_recommendation ENUM('ACCEPT', 'MINOR_REVISION', 'MAJOR_REVISION', 'REJECT') NOT NULL,
    review_score DECIMAL(3,1),
    review_confidential_comments TEXT,
    
    -- Review Metadata
    review_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    review_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    review_status ENUM('PENDING', 'COMPLETED', 'DECLINED') DEFAULT 'PENDING',
    review_assigned_date DATETIME,
    review_deadline DATE,
    
    -- Review Criteria Scores
    review_originality_score INT,
    review_technical_quality_score INT,
    review_clarity_score INT,
    review_significance_score INT,
    review_literature_score INT,
    
    -- Foreign Keys
    FOREIGN KEY (paper_id) REFERENCES papers(paper_id) ON DELETE RESTRICT,
    FOREIGN KEY (reviewer_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    
    -- Unique constraint
    UNIQUE KEY unique_paper_reviewer (paper_id, reviewer_id),
    
    -- Indexes
    INDEX idx_review_status (review_status, review_deadline),
    INDEX idx_reviewer (reviewer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
