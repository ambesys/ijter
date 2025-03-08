<?php
// models/Notification.php

class Notification {
    private $pdo;
    
    // Notification types
    const TYPE_PAPER_SUBMITTED = 'PAPER_SUBMITTED';
    const TYPE_PAPER_ACCEPTED = 'PAPER_ACCEPTED';
    const TYPE_PAPER_REJECTED = 'PAPER_REJECTED';
    const TYPE_PAPER_PUBLISHED = 'PAPER_PUBLISHED';
    const TYPE_REVIEW_ASSIGNED = 'REVIEW_ASSIGNED';
    const TYPE_REVIEW_SUBMITTED = 'REVIEW_SUBMITTED';
    const TYPE_REVISION_REQUESTED = 'REVISION_REQUESTED';
    const TYPE_PAYMENT_RECEIVED = 'PAYMENT_RECEIVED';
    const TYPE_PAYMENT_FAILED = 'PAYMENT_FAILED';
    const TYPE_ACCOUNT_CREATED = 'ACCOUNT_CREATED';
    const TYPE_PASSWORD_RESET = 'PASSWORD_RESET';
    const TYPE_SYSTEM = 'SYSTEM';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get notification by ID
     * 
     * @param int $notificationId Notification ID
     * @return array|false Notification data or false if not found
     */
    public function getNotificationById($notificationId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notifications
            WHERE notification_id = ?
        ");
        $stmt->execute([$notificationId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get notifications by user ID
     * 
     * @param int $userId User ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @param bool $unreadOnly Only unread notifications
     * @return array Notifications
     */
    public function getNotificationsByUserId($userId, $limit = null, $offset = 0, $unreadOnly = false) {
        $sql = "
            SELECT * FROM notifications
            WHERE notification_user_id = ?
        ";
        
        $params = [$userId];
        
        if ($unreadOnly) {
            $sql .= " AND notification_read = 0";
        }
        
        $sql .= " ORDER BY notification_created_at DESC";
        
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
     * Create notification
     * 
     * @param array $notificationData Notification data
     * @return int|false Notification ID or false on failure
     */
    public function createNotification($notificationData) {
        // Set default values
        if (!isset($notificationData['notification_created_at'])) {
            $notificationData['notification_created_at'] = date('Y-m-d H:i:s');
        }
        
        if (!isset($notificationData['notification_read'])) {
            $notificationData['notification_read'] = 0;
        }
        
        $fields = [];
        $placeholders = [];
        $params = [];
        
        foreach ($notificationData as $field => $value) {
            $fields[] = $field;
            $placeholders[] = "?";
            $params[] = $value;
        }
        
        $sql = "INSERT INTO notifications (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute($params);
        
        return $success ? $this->pdo->lastInsertId() : false;
    }
    
    /**
     * Mark notification as read
     * 
     * @param int $notificationId Notification ID
     * @return bool Success status
     */
    public function markAsRead($notificationId) {
        $stmt = $this->pdo->prepare("
            UPDATE notifications
            SET notification_read = 1,
                notification_read_at = NOW()
            WHERE notification_id = ?
        ");
        
        return $stmt->execute([$notificationId]);
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function markAllAsRead($userId) {
        $stmt = $this->pdo->prepare("
            UPDATE notifications
            SET notification_read = 1,
                notification_read_at = NOW()
            WHERE notification_user_id = ? AND notification_read = 0
        ");
        
        return $stmt->execute([$userId]);
    }
    
    /**
     * Delete notification
     * 
     * @param int $notificationId Notification ID
     * @return bool Success status
     */
    public function deleteNotification($notificationId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM notifications
            WHERE notification_id = ?
        ");
        
        return $stmt->execute([$notificationId]);
    }
    
    /**
     * Delete all notifications for a user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deleteAllNotifications($userId) {
        $stmt = $this->pdo->prepare("
            DELETE FROM notifications
            WHERE notification_user_id = ?
        ");
        
        return $stmt->execute([$userId]);
    }
    
    /**
     * Count unread notifications for a user
     * 
     * @param int $userId User ID
     * @return int Unread notifications count
     */
    public function countUnreadNotifications($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE notification_user_id = ? AND notification_read = 0
        ");
        $stmt->execute([$userId]);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Send notification to user
     * 
     * @param int $userId User ID
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $data Additional data
     * @return int|false Notification ID or false on failure
     */
    public function sendNotification($userId, $type, $message, $data = []) {
        $notificationData = [
            'notification_user_id' => $userId,
            'notification_type' => $type,
            'notification_message' => $message,
            'notification_data' => !empty($data) ? json_encode($data) : null
        ];
        
        return $this->createNotification($notificationData);
    }
    
    /**
     * Send notification to multiple users
     * 
     * @param array $userIds User IDs
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $data Additional data
     * @return array Created notification IDs
     */
    public function sendNotificationToMultipleUsers($userIds, $type, $message, $data = []) {
        $notificationIds = [];
        
        foreach ($userIds as $userId) {
            $notificationId = $this->sendNotification($userId, $type, $message, $data);
            
            if ($notificationId) {
                $notificationIds[] = $notificationId;
            }
        }
        
        return $notificationIds;
    }
    
    /**
     * Send notification to all admins
     * 
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $data Additional data
     * @return array Created notification IDs
     */
    public function sendNotificationToAdmins($type, $message, $data = []) {
        $stmt = $this->pdo->prepare("
            SELECT user_id FROM users
            WHERE user_is_admin = 1
        ");
        $stmt->execute();
        
        $adminIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $this->sendNotificationToMultipleUsers($adminIds, $type, $message, $data);
    }
    
    /**
     * Send notification to all moderators
     * 
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $data Additional data
     * @return array Created notification IDs
     */
    public function sendNotificationToModerators($type, $message, $data = []) {
        $stmt = $this->pdo->prepare("
            SELECT user_id FROM users
            WHERE user_is_moderator = 1
        ");
        $stmt->execute();
        
        $moderatorIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $this->sendNotificationToMultipleUsers($moderatorIds, $type, $message, $data);
    }
}
