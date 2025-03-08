<?php
// models/Review.php

class Review {
    private $pdo;
    
    // Review status constants
    const STATUS_PENDING = 'PENDING';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_COMPLETED = 'COMPLETED';
    
    // Review decision constants
    const DECISION_ACCEPT = 'ACCEPT';
    const DECISION_MINOR_REVISION = 'MINOR_REVISION';
    const DECISION_MAJOR_REVISION = 'MAJOR_REVISION';
    const DECISION_REJECT = 'REJECT';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get review by ID
     * 
     * @param int $reviewId Review ID
     * @return array|false Review data or false if not found
     */
    public function getReviewById($reviewId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reviews
            WHERE review_id = ?
        ");
        $stmt->execute([$reviewId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get reviews by paper ID
     * 
     * @param string $paperId Paper ID
     * @return array Reviews
     */
    public function getReviewsByPaperId($paperId) {
        $stmt = $this->pdo->prepare("
            SELECT r.*, u.user_fname, u.user_lname, u.user_prefixname
            FROM reviews r
            JOIN users u ON r.review_reviewer_id = u.user_id
            WHERE r.review_paper_id = ?
            ORDER BY r.review_created_at DESC
        ");
        $stmt->execute([$paperId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get reviews by reviewer ID
     * 
     * @param int $reviewerId Reviewer ID
     * @param string $status Review status (optional)
     * @return array Reviews
     */
    public function getReviewsByReviewerId($reviewerId, $status = null) {
        $sql = "
            SELECT r.*, p.paper_title, p.paper_abstract, p.paper_status
            FROM reviews r
            JOIN papers p ON r.review_paper_id = p.paper_id
            WHERE r.review_reviewer_id = ?
        ";
        
        $params = [$reviewerId];
        
        if ($status !== null) {
            $sql .= " AND r.review_status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY r.review_deadline ASC, r.review_created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get review by paper and reviewer
     * 
     * @param string $paperId Paper ID
     * @param int $reviewerId Reviewer ID
     * @return array|false Review data or false if not found
     */
    public function getReviewByPaperAndReviewer($paperId, $reviewerId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM reviews
            WHERE review_paper_id = ? AND review_reviewer_id = ?
        ");
        $stmt->execute([$paperId, $reviewerId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Create review
     * 
     * @param array $reviewData Review data
     * @return int|false Review ID or false on failure
     */
    public function createReview($reviewData) {
        $fields = [];
        $placeholders = [];
        $params = [];
        
        foreach ($reviewData as $field => $value) {
            $fields[] = $field;
            $placeholders[] = "?";
            $params[] = $value;
        }
        
        $sql = "INSERT INTO reviews (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute($params);
        
        return $success ? $this->pdo->lastInsertId() : false;
    }
    
    /**
     * Update review
     * 
     * @param int $reviewId Review ID
     * @param array $reviewData Review data
     * @return bool Success status
     */
    public function updateReview($reviewId, $reviewData) {
        $setFields = [];
        $params = [];
        
        foreach ($reviewData as $field => $value) {
            // Skip review_id field
            if ($field === 'review_id') {
                continue;
            }
            
            $setFields[] = "{$field} = ?";
            $params[] = $value;
        }
        
        if (empty($setFields)) {
            return false;
        }
        
        $params[] = $reviewId;
        
        $sql = "UPDATE reviews SET " . implode(', ', $setFields) . " WHERE review_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete review
     * 
     * @param int $reviewId Review ID
     * @return bool Success status
     */
    public function deleteReview($reviewId) {
        $stmt = $this->pdo->prepare("DELETE FROM reviews WHERE review_id = ?");
        return $stmt->execute([$reviewId]);
    }
    
    /**
     * Update review status
     * 
     * @param int $reviewId Review ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateReviewStatus($reviewId, $status) {
        $stmt = $this->pdo->prepare("
            UPDATE reviews
            SET review_status = ?
            WHERE review_id = ?
        ");
        
        return $stmt->execute([$status, $reviewId]);
    }
    
    /**
     * Complete review
     * 
     * @param int $reviewId Review ID
     * @param array $reviewData Review data
     * @return bool Success status
     */
    public function completeReview($reviewId, $reviewData) {
        $reviewData['review_status'] = self::STATUS_COMPLETED;
        $reviewData['review_completed_at'] = date('Y-m-d H:i:s');
        
        return $this->updateReview($reviewId, $reviewData);
    }
    
    /**
     * Assign reviewer
     * 
     * @param string $paperId Paper ID
     * @param int $reviewerId Reviewer ID
     * @param string $deadline Review deadline
     * @return int|false Review ID or false on failure
     */
    public function assignReviewer($paperId, $reviewerId, $deadline) {
        // Check if reviewer is already assigned to this paper
        $existingReview = $this->getReviewByPaperAndReviewer($paperId, $reviewerId);
        
        if ($existingReview) {
            return false;
        }
        
        $reviewData = [
            'review_paper_id' => $paperId,
            'review_reviewer_id' => $reviewerId,
            'review_status' => self::STATUS_PENDING,
            'review_deadline' => $deadline,
            'review_created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->createReview($reviewData);
    }
    
    /**
     * Get pending reviews count
     * 
     * @return int Pending reviews count
     */
    public function getPendingReviewsCount() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM reviews
            WHERE review_status = ?
        ");
        $stmt->execute([self::STATUS_PENDING]);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Get in progress reviews count
     * 
     * @return int In progress reviews count
     */
    public function getInProgressReviewsCount() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM reviews
            WHERE review_status = ?
        ");
        $stmt->execute([self::STATUS_IN_PROGRESS]);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Get completed reviews count
     * 
     * @return int Completed reviews count
     */
    public function getCompletedReviewsCount() {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM reviews
            WHERE review_status = ?
        ");
        $stmt->execute([self::STATUS_COMPLETED]);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Get overdue reviews
     * 
     * @return array Overdue reviews
     */
    public function getOverdueReviews() {
        $stmt = $this->pdo->prepare("
            SELECT r.*, p.paper_title, u.user_fname, u.user_lname
            FROM reviews r
            JOIN papers p ON r.review_paper_id = p.paper_id
            JOIN users u ON r.review_reviewer_id = u.user_id
            WHERE r.review_status != ? AND r.review_deadline < CURDATE()
            ORDER BY r.review_deadline ASC
        ");
        $stmt->execute([self::STATUS_COMPLETED]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get reviews due soon (within 7 days)
     * 
     * @return array Reviews due soon
     */
    public function getReviewsDueSoon() {
        $stmt = $this->pdo->prepare("
            SELECT r.*, p.paper_title, u.user_fname, u.user_lname
            FROM reviews r
            JOIN papers p ON r.review_paper_id = p.paper_id
            JOIN users u ON r.review_reviewer_id = u.user_id
            WHERE r.review_status != ? 
            AND r.review_deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY r.review_deadline ASC
        ");
        $stmt->execute([self::STATUS_COMPLETED]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Calculate average score for a paper
     * 
     * @param string $paperId Paper ID
     * @return float|null Average score or null if no completed reviews
     */
    public function calculateAverageScore($paperId) {
        $stmt = $this->pdo->prepare("
            SELECT AVG(review_score) FROM reviews
            WHERE review_paper_id = ? AND review_status = ?
        ");
        $stmt->execute([$paperId, self::STATUS_COMPLETED]);
        
        $avgScore = $stmt->fetchColumn();
        
        return $avgScore !== false ? (float)$avgScore : null;
    }
    
    /**
     * Get review decision counts for a paper
     * 
     * @param string $paperId Paper ID
     * @return array Decision counts
     */
    public function getDecisionCounts($paperId) {
        $stmt = $this->pdo->prepare("
            SELECT review_decision, COUNT(*) as count
            FROM reviews
            WHERE review_paper_id = ? AND review_status = ?
            GROUP BY review_decision
        ");
        $stmt->execute([$paperId, self::STATUS_COMPLETED]);
        
        $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $decisions = [
            self::DECISION_ACCEPT => 0,
            self::DECISION_MINOR_REVISION => 0,
            self::DECISION_MAJOR_REVISION => 0,
            self::DECISION_REJECT => 0
        ];
        
        foreach ($results as $decision => $count) {
            $decisions[$decision] = (int)$count;
        }
        
        return $decisions;
    }
}
