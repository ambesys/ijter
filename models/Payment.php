<?php
// models/Payment.php

class Payment {
    private $pdo;
    
    // Payment status constants
    const STATUS_PENDING = 'PENDING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_FAILED = 'FAILED';
    const STATUS_REFUNDED = 'REFUNDED';
    const STATUS_WAIVED = 'WAIVED';
    
    // Payment methods
    const METHOD_CREDIT_CARD = 'CREDIT_CARD';
    const METHOD_BANK_TRANSFER = 'BANK_TRANSFER';
    const METHOD_PAYPAL = 'PAYPAL';
    const METHOD_WAIVER = 'WAIVER';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get payment by ID
     * 
     * @param int $paymentId Payment ID
     * @return array|false Payment data or false if not found
     */
    public function getPaymentById($paymentId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM payments
            WHERE payment_id = ?
        ");
        $stmt->execute([$paymentId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get payments by paper ID
     * 
     * @param string $paperId Paper ID
     * @return array Payments
     */
    public function getPaymentsByPaperId($paperId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM payments
            WHERE payment_paper_id = ?
            ORDER BY payment_created_at DESC
        ");
        $stmt->execute([$paperId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get payments by user ID
     * 
     * @param int $userId User ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Payments
     */
    public function getPaymentsByUserId($userId, $limit = null, $offset = 0) {
        $sql = "
            SELECT p.*, pp.paper_title
            FROM payments p
            JOIN papers pp ON p.payment_paper_id = pp.paper_id
            WHERE p.payment_user_id = ?
            ORDER BY p.payment_created_at DESC
        ";
        
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);
        } else {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId]);
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Create payment
     * 
     * @param array $paymentData Payment data
     * @return int|false Payment ID or false on failure
     */
    public function createPayment($paymentData) {
        // Set default values
        if (!isset($paymentData['payment_created_at'])) {
            $paymentData['payment_created_at'] = date('Y-m-d H:i:s');
        }
        
        $fields = [];
        $placeholders = [];
        $params = [];
        
        foreach ($paymentData as $field => $value) {
            $fields[] = $field;
            $placeholders[] = "?";
            $params[] = $value;
        }
        
        $sql = "INSERT INTO payments (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute($params);
        
        return $success ? $this->pdo->lastInsertId() : false;
    }
    
    /**
     * Update payment
     * 
     * @param int $paymentId Payment ID
     * @param array $paymentData Payment data
     * @return bool Success status
     */
    public function updatePayment($paymentId, $paymentData) {
        $setFields = [];
        $params = [];
        
        foreach ($paymentData as $field => $value) {
            // Skip payment_id field
            if ($field === 'payment_id') {
                continue;
            }
            
            $setFields[] = "{$field} = ?";
            $params[] = $value;
        }
        
        if (empty($setFields)) {
            return false;
        }
        
        $params[] = $paymentId;
        
        $sql = "UPDATE payments SET " . implode(', ', $setFields) . " WHERE payment_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update payment status
     * 
     * @param int $paymentId Payment ID
     * @param string $status New status
     * @param string $transactionId Transaction ID (optional)
     * @return bool Success status
     */
    public function updatePaymentStatus($paymentId, $status, $transactionId = null) {
        $data = [
            'payment_status' => $status
        ];
        
        if ($status === self::STATUS_COMPLETED) {
            $data['payment_completed_at'] = date('Y-m-d H:i:s');
        }
        
        if ($transactionId !== null) {
            $data['payment_transaction_id'] = $transactionId;
        }
        
        return $this->updatePayment($paymentId, $data);
    }
    
    /**
     * Get latest payment for a paper
     * 
     * @param string $paperId Paper ID
     * @return array|false Payment data or false if not found
     */
    public function getLatestPaymentForPaper($paperId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM payments
            WHERE payment_paper_id = ?
            ORDER BY payment_created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$paperId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Check if paper has a successful payment
     * 
     * @param string $paperId Paper ID
     * @return bool True if has successful payment, false otherwise
     */
    public function hasPaperBeenPaid($paperId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM payments
            WHERE payment_paper_id = ? AND (payment_status = ? OR payment_status = ?)
        ");
        $stmt->execute([$paperId, self::STATUS_COMPLETED, self::STATUS_WAIVED]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Get all payments
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param array $filters Filter options
     * @return array Payments
     */
    public function getAllPayments($limit = null, $offset = 0, $filters = []) {
        $sql = "
            SELECT p.*, pp.paper_title, u.user_fname, u.user_lname
            FROM payments p
            JOIN papers pp ON p.payment_paper_id = pp.paper_id
            JOIN users u ON p.payment_user_id = u.user_id
        ";
        
        $params = [];
        $whereConditions = [];
        
        // Apply filters
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                if ($field === 'status') {
                    $whereConditions[] = "p.payment_status = ?";
                    $params[] = $value;
                } elseif ($field === 'method') {
                    $whereConditions[] = "p.payment_method = ?";
                    $params[] = $value;
                } elseif ($field === 'date_from') {
                    $whereConditions[] = "p.payment_created_at >= ?";
                    $params[] = $value;
                } elseif ($field === 'date_to') {
                    $whereConditions[] = "p.payment_created_at <= ?";
                    $params[] = $value;
                } elseif ($field === 'user_id') {
                    $whereConditions[] = "p.payment_user_id = ?";
                    $params[] = $value;
                } elseif ($field === 'paper_id') {
                    $whereConditions[] = "p.payment_paper_id = ?";
                    $params[] = $value;
                } else {
                    $whereConditions[] = "p.{$field} = ?";
                    $params[] = $value;
                }
            }
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " ORDER BY p.payment_created_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Count payments
     * 
     * @param array $filters Filter options
     * @return int Payment count
     */
    public function countPayments($filters = []) {
        $sql = "SELECT COUNT(*) FROM payments p";
        
        $params = [];
        $whereConditions = [];
        
        // Apply filters
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                if ($field === 'status') {
                    $whereConditions[] = "p.payment_status = ?";
                    $params[] = $value;
                } elseif ($field === 'method') {
                    $whereConditions[] = "p.payment_method = ?";
                    $params[] = $value;
                } elseif ($field === 'date_from') {
                    $whereConditions[] = "p.payment_created_at >= ?";
                    $params[] = $value;
                } elseif ($field === 'date_to') {
                    $whereConditions[] = "p.payment_created_at <= ?";
                    $params[] = $value;
                } elseif ($field === 'user_id') {
                    $whereConditions[] = "p.payment_user_id = ?";
                    $params[] = $value;
                } elseif ($field === 'paper_id') {
                    $whereConditions[] = "p.payment_paper_id = ?";
                    $params[] = $value;
                } else {
                    $whereConditions[] = "p.{$field} = ?";
                    $params[] = $value;
                }
            }
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Get payment statistics
     * 
     * @param string $period Period (day, week, month, year)
     * @return array Payment statistics
     */
    public function getPaymentStatistics($period = 'month') {
        $stats = [];
        
        // Total payments
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count, SUM(payment_amount) as total
            FROM payments
            WHERE payment_status = ?
        ");
        $stmt->execute([self::STATUS_COMPLETED]);
        $result = $stmt->fetch();
        
        $stats['total_payments'] = $result['count'];
        $stats['total_amount'] = $result['total'];
        
        // Period format
        $format = '%Y-%m-%d';
        $interval = '1 DAY';
        
        if ($period === 'week') {
            $format = '%Y-%u';
            $interval = '1 WEEK';
        } elseif ($period === 'month') {
            $format = '%Y-%m';
            $interval = '1 MONTH';
        } elseif ($period === 'year') {
            $format = '%Y';
            $interval = '1 YEAR';
        }
        
        // Payments by period
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE_FORMAT(payment_created_at, ?) as period,
                COUNT(*) as count,
                SUM(payment_amount) as total
            FROM payments
            WHERE payment_status = ?
            AND payment_created_at >= DATE_SUB(CURDATE(), INTERVAL 12 {$interval})
            GROUP BY period
            ORDER BY period ASC
        ");
        $stmt->execute([$format, self::STATUS_COMPLETED]);
        
        $stats['period_data'] = $stmt->fetchAll();
        
        // Payment methods
        $stmt = $this->pdo->prepare("
            SELECT 
                payment_method,
                COUNT(*) as count,
                SUM(payment_amount) as total
            FROM payments
            WHERE payment_status = ?
            GROUP BY payment_method
        ");
        $stmt->execute([self::STATUS_COMPLETED]);
        
        $stats['payment_methods'] = $stmt->fetchAll();
        
        return $stats;
    }
}
