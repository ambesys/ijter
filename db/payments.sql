CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    paper_id CHAR(36) NOT NULL,
    user_id INT NOT NULL,
    
    -- Payment details
    payment_amount DECIMAL(10,2) NOT NULL,
    payment_currency VARCHAR(10) DEFAULT 'USD',
    payment_status ENUM('PENDING', 'COMPLETED', 'FAILED', 'REFUNDED', 'WAIVED') DEFAULT 'PENDING',
    payment_method VARCHAR(50),
    payment_gateway VARCHAR(50),
    payment_transaction_id VARCHAR(255) UNIQUE,
    payment_invoice_id VARCHAR(100) UNIQUE,
    
    -- Payment metadata
    payment_description TEXT,
    payment_date DATETIME,
    payment_notes TEXT,
    payment_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    payment_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (paper_id) REFERENCES papers(paper_id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    
    -- Indexes
    INDEX idx_payment_status (payment_status, payment_date),
    INDEX idx_payment_user (user_id),
    INDEX idx_payment_paper (paper_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


