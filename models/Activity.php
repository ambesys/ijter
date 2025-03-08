<?php
// models/Activity.php

class Activity {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Log activity
     * 
     * @param array $data Activity data
     * @return int|false Activity ID or false on failure
     */
    public function log($data) {
        // Set default values
        $data['activity_ip'] = $data['activity_ip'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        $data['activity_created_at'] = $data['activity_created_at'] ?? date('Y-m-d H:i:s');
        
        // Convert data array to JSON if needed
        if (isset($data['activity_data']) && is_array($data['activity_data'])) {
            $data['activity_data'] = json_encode($data['activity_data']);
        }
        
        $fields = [];
        $placeholders = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            $fields[] = $field;
            $placeholders[] = "?";
            $params[] = $value;
        }
        
        $sql = "INSERT INTO activity_logs (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute($params);
        
        return $success ? $this->pdo->lastInsertId() : false;
    }
    
    /**
     * Get activities
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param array $filters Filter options
     * @return array Activities
     */
    public function getActivities($limit = null, $offset = 0, $filters = []) {
        $sql = "
            SELECT a.*, u.user_fname, u.user_lname
            FROM activity_logs a
            LEFT JOIN users u ON a.activity_user_id = u.user_id
        ";
        
        $params = [];
        $whereConditions = [];
        
        // Apply filters
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                if ($field === 'user_id') {
                    $whereConditions[] = "a.activity_user_id = ?";
                    $params[] = $value;
                } elseif ($field === 'action') {
                    $whereConditions[] = "a.activity_action = ?";
                    $params[] = $value;
                } elseif ($field === 'date_from') {
                    $whereConditions[] = "a.activity_created_at >= ?";
                    $params[] = $value;
                } elseif ($field === 'date_to') {
                    $whereConditions[] = "a.activity_created_at <= ?";
                    $params[] = $value;
                } else {
                    $whereConditions[] = "a.{$field} = ?";
                    $params[] = $value;
                }
            }
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " ORDER BY a.activity_created_at DESC";
        
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
     * Get user activities
     * 
     * @param int $userId User ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Activities
     */
    public function getUserActivities($userId, $limit = null, $offset = 0) {
        return $this->getActivities($limit, $offset, ['user_id' => $userId]);
    }
    
    /**
     * Count activities
     * 
     * @param array $filters Filter options
     * @return int Activity count
     */
    public function countActivities($filters = []) {
        $sql = "SELECT COUNT(*) FROM activity_logs a";
        
        $params = [];
        $whereConditions = [];
        
        // Apply filters
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                if ($field === 'user_id') {
                    $whereConditions[] = "a.activity_user_id = ?";
                    $params[] = $value;
                } elseif ($field === 'action') {
                    $whereConditions[] = "a.activity_action = ?";
                    $params[] = $value;
                } elseif ($field === 'date_from') {
                    $whereConditions[] = "a.activity_created_at >= ?";
                    $params[] = $value;
                } elseif ($field === 'date_to') {
                    $whereConditions[] = "a.activity_created_at <= ?";
                    $params[] = $value;
                } else {
                    $whereConditions[] = "a.{$field} = ?";
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
     * Get recent activities
     * 
     * @param int $limit Limit
     * @return array Recent activities
     */
    public function getRecentActivities($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, u.user_fname, u.user_lname
            FROM activity_logs a
            LEFT JOIN users u ON a.activity_user_id = u.user_id
            ORDER BY a.activity_created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Delete old activities
     * 
     * @param int $days Days to keep
     * @return int Number of deleted activities
     */
    public function deleteOldActivities($days = 90) {
        $stmt = $this->pdo->prepare("
            DELETE FROM activity_logs
            WHERE activity_created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$days]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Get activity by ID
     * 
     * @param int $activityId Activity ID
     * @return array|false Activity data or false if not found
     */
    public function getActivityById($activityId) {
        $stmt = $this->pdo->prepare("
            SELECT a.*, u.user_fname, u.user_lname
            FROM activity_logs a
            LEFT JOIN users u ON a.activity_user_id = u.user_id
            WHERE a.activity_id = ?
        ");
        $stmt->execute([$activityId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get login activities
     * 
     * @param int $userId User ID
     * @param int $limit Limit
     * @return array Login activities
     */
    public function getLoginActivities($userId, $limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM activity_logs
            WHERE activity_user_id = ? AND activity_action = 'login'
            ORDER BY activity_created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get paper activities
     * 
     * @param string $paperId Paper ID
     * @param int $limit Limit
     * @return array Paper activities
     */
    public function getPaperActivities($paperId, $limit = null) {
        $sql = "
            SELECT a.*, u.user_fname, u.user_lname
            FROM activity_logs a
            LEFT JOIN users u ON a.activity_user_id = u.user_id
            WHERE a.activity_data LIKE ?
            ORDER BY a.activity_created_at DESC
        ";
        
        $params = ['%"paper_id":"' . $paperId . '"%'];
        
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
}

