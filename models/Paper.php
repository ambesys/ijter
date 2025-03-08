<?php
// models/Paper.php

class Paper {
    private $pdo;
    
    // Paper status constants
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_UNDER_REVIEW = 'UNDER_REVIEW';
    const STATUS_REVISION_REQUESTED = 'REVISION_REQUESTED';
    const STATUS_ACCEPTED = 'ACCEPTED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_PUBLISHED = 'PUBLISHED';
    const STATUS_WITHDRAWN = 'WITHDRAWN';
    
    // Review status constants
    const REVIEW_STATUS_PENDING = 'PENDING';
    const REVIEW_STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const REVIEW_STATUS_COMPLETED = 'COMPLETED';
    const REVIEW_STATUS_APPROVED = 'APPROVED';
    const REVIEW_STATUS_REJECTED = 'REJECTED';
    
    // Payment status constants
    const PAY_STATUS_PENDING = 'PENDING';
    const PAY_STATUS_COMPLETED = 'COMPLETED';
    const PAY_STATUS_FAILED = 'FAILED';
    const PAY_STATUS_WAIVED = 'WAIVED';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get paper by ID
     * 
     * @param string $paperId Paper ID
     * @return array|false Paper data or false if not found
     */
    public function getPaperById($paperId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM papers
            WHERE paper_id = ?
        ");
        $stmt->execute([$paperId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Get papers by author
     * 
     * @param int $authorId Author ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Papers
     */
    public function getPapersByAuthor($authorId, $limit = null, $offset = 0) {
        $sql = "
            SELECT * FROM papers
            WHERE paper_author_id = ?
               OR paper_co_author_1_id = ?
               OR paper_co_author_2_id = ?
               OR paper_co_author_3_id = ?
               OR paper_co_author_4_id = ?
            ORDER BY paper_submission_date DESC
        ";
        
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$authorId, $authorId, $authorId, $authorId, $authorId, $limit, $offset]);
        } else {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$authorId, $authorId, $authorId, $authorId, $authorId]);
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get papers by reviewer
     * 
     * @param int $reviewerId Reviewer ID
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array Papers
     */
    public function getPapersByReviewer($reviewerId, $limit = null, $offset = 0) {
        $sql = "
            SELECT * FROM papers
            WHERE paper_reviewer_id = ?
            ORDER BY paper_review_deadline ASC, paper_submission_date DESC
        ";
        
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$reviewerId, $limit, $offset]);
        } else {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$reviewerId]);
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get all papers
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param array $filters Filter options
     * @return array Papers
     */
    public function getAllPapers($limit = null, $offset = 0, $filters = []) {
        $sql = "SELECT * FROM papers";
        $params = [];
        $whereConditions = [];
        
        // Apply filters
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                if ($field === 'status') {
                    $whereConditions[] = "paper_status = ?";
                    $params[] = $value;
                } elseif ($field === 'review_status') {
                    $whereConditions[] = "paper_review_status = ?";
                    $params[] = $value;
                } elseif ($field === 'pay_status') {
                    $whereConditions[] = "paper_pay_status = ?";
                    $params[] = $value;
                } elseif ($field === 'year') {
                    $whereConditions[] = "paper_year = ?";
                    $params[] = $value;
                } elseif ($field === 'volume') {
                    $whereConditions[] = "paper_volume = ?";
                    $params[] = $value;
                } elseif ($field === 'issue') {
                    $whereConditions[] = "paper_issue = ?";
                    $params[] = $value;
                } elseif ($field === 'author_id') {
                    $whereConditions[] = "(paper_author_id = ? OR paper_co_author_1_id = ? OR paper_co_author_2_id = ? OR paper_co_author_3_id = ? OR paper_co_author_4_id = ?)";
                    $params[] = $value;
                    $params[] = $value;
                    $params[] = $value;
                    $params[] = $value;
                    $params[] = $value;
                } elseif ($field === 'reviewer_id') {
                    $whereConditions[] = "paper_reviewer_id = ?";
                    $params[] = $value;
                } else {
                    $whereConditions[] = "{$field} = ?";
                    $params[] = $value;
                }
            }
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " ORDER BY paper_submission_date DESC";
        
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
     * Count papers
     * 
     * @param array $filters Filter options
     * @return int Paper count
     */
    public function countPapers($filters = []) {
        $sql = "SELECT COUNT(*) FROM papers";
        $params = [];
        $whereConditions = [];
        
        // Apply filters
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                if ($field === 'status') {
                    $whereConditions[] = "paper_status = ?";
                    $params[] = $value;
                } elseif ($field === 'review_status') {
                    $whereConditions[] = "paper_review_status = ?";
                    $params[] = $value;
                } elseif ($field === 'pay_status') {
                    $whereConditions[] = "paper_pay_status = ?";
                    $params[] = $value;
                } elseif ($field === 'year') {
                    $whereConditions[] = "paper_year = ?";
                    $params[] = $value;
                } elseif ($field === 'volume') {
                    $whereConditions[] = "paper_volume = ?";
                    $params[] = $value;
                } elseif ($field === 'issue') {
                    $whereConditions[] = "paper_issue = ?";
                    $params[] = $value;
                } elseif ($field === 'author_id') {
                    $whereConditions[] = "(paper_author_id = ? OR paper_co_author_1_id = ? OR paper_co_author_2_id = ? OR paper_co_author_3_id = ? OR paper_co_author_4_id = ?)";
                    $params[] = $value;
                    $params[] = $value;
                    $params[] = $value;
                    $params[] = $value;
                    $params[] = $value;
                } elseif ($field === 'reviewer_id') {
                    $whereConditions[] = "paper_reviewer_id = ?";
                    $params[] = $value;
                } else {
                    $whereConditions[] = "{$field} = ?";
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
     * Create a new paper
     * 
     * @param array $paperData Paper data
     * @return string|false Paper ID or false on failure
     */
    public function createPaper($paperData) {
        // Generate paper ID if not provided
        if (empty($paperData['paper_id'])) {
            $paperData['paper_id'] = $this->generatePaperId();
        }
        
        $fields = [];
        $placeholders = [];
        $params = [];
        
        foreach ($paperData as $field => $value) {
            $fields[] = $field;
            $placeholders[] = "?";
            $params[] = $value;
        }
        
        $sql = "INSERT INTO papers (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute($params);
        
        return $success ? $paperData['paper_id'] : false;
    }
    
    /**
     * Update paper
     * 
     * @param string $paperId Paper ID
     * @param array $paperData Paper data
     * @return bool Success status
     */
    public function updatePaper($paperId, $paperData) {
        $setFields = [];
        $params = [];
        
        foreach ($paperData as $field => $value) {
            // Skip paper_id field
            if ($field === 'paper_id') {
                continue;
            }
            
            $setFields[] = "{$field} = ?";
            $params[] = $value;
        }
        
        if (empty($setFields)) {
            return false;
        }
        
        $params[] = $paperId;
        
        $sql = "UPDATE papers SET " . implode(', ', $setFields) . " WHERE paper_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Delete paper
     * 
     * @param string $paperId Paper ID
     * @return bool Success status
     */
    public function deletePaper($paperId) {
        $stmt = $this->pdo->prepare("DELETE FROM papers WHERE paper_id = ?");
        return $stmt->execute([$paperId]);
    }
    
    /**
     * Search papers
     * 
     * @param string $query Search query
     * @param int $limit Limit
     * @return array Papers
     */
    public function searchPapers($query, $limit = 20) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM papers
            WHERE paper_title LIKE ? OR paper_abstract LIKE ? OR paper_keywords LIKE ?
            ORDER BY paper_submission_date DESC
            LIMIT ?
        ");
        
        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get published papers
     * 
     * @param int $limit Limit
     * @param int $offset Offset
     * @param array $filters Filter options
     * @return array Papers
     */
    public function getPublishedPapers($limit = null, $offset = 0, $filters = []) {
        $sql = "SELECT * FROM papers WHERE paper_status = ?";
        $params = [self::STATUS_PUBLISHED];
        $whereConditions = [];
        
        // Apply filters
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                if ($field === 'year') {
                    $whereConditions[] = "paper_year = ?";
                    $params[] = $value;
                } elseif ($field === 'volume') {
                    $whereConditions[] = "paper_volume = ?";
                    $params[] = $value;
                } elseif ($field === 'issue') {
                    $whereConditions[] = "paper_issue = ?";
                    $params[] = $value;
                } elseif ($field === 'author_id') {
                    $whereConditions[] = "(paper_author_id = ? OR paper_co_author_1_id = ? OR paper_co_author_2_id = ? OR paper_co_author_3_id = ? OR paper_co_author_4_id = ?)";
                    $params[] = $value;
                    $params[] = $value;
                    $params[] = $value;
                    $params[] = $value;
                    $params[] = $value;
                } else {
                    $whereConditions[] = "{$field} = ?";
                    $params[] = $value;
                }
            }
        }
        
        if (!empty($whereConditions)) {
            $sql .= " AND " . implode(' AND ', $whereConditions);
        }
        
        $sql .= " ORDER BY paper_publication_date DESC, paper_title ASC";
        
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
     * Update paper status
     * 
     * @param string $paperId Paper ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updatePaperStatus($paperId, $status) {
        $stmt = $this->pdo->prepare("
            UPDATE papers
            SET paper_status = ?
            WHERE paper_id = ?
        ");
        
        return $stmt->execute([$status, $paperId]);
    }
    
    /**
     * Update review status
     * 
     * @param string $paperId Paper ID
     * @param string $status New review status
     * @return bool Success status
     */
    public function updateReviewStatus($paperId, $status) {
        $stmt = $this->pdo->prepare("
            UPDATE papers
            SET paper_review_status = ?
            WHERE paper_id = ?
        ");
        
        return $stmt->execute([$status, $paperId]);
    }
    
    /**
     * Update payment status
     * 
     * @param string $paperId Paper ID
     * @param string $status New payment status
     * @return bool Success status
     */
    public function updatePaymentStatus($paperId, $status) {
        $stmt = $this->pdo->prepare("
            UPDATE papers
            SET paper_pay_status = ?
            WHERE paper_id = ?
        ");
        
        return $stmt->execute([$status, $paperId]);
    }
    
    /**
     * Assign reviewer
     * 
     * @param string $paperId Paper ID
     * @param int $reviewerId Reviewer ID
     * @param string $deadline Review deadline
     * @return bool Success status
     */
    public function assignReviewer($paperId, $reviewerId, $deadline) {
        $stmt = $this->pdo->prepare("
            UPDATE papers
            SET paper_reviewer_id = ?,
                paper_review_status = ?,
                paper_review_deadline = ?
            WHERE paper_id = ?
        ");
        
        return $stmt->execute([$reviewerId, self::REVIEW_STATUS_PENDING, $deadline, $paperId]);
    }
    
    /**
     * Increment downloads
     * 
     * @param string $paperId Paper ID
     * @return bool Success status
     */
    public function incrementDownloads($paperId) {
        $stmt = $this->pdo->prepare("
            UPDATE papers
            SET paper_downloads = paper_downloads + 1
            WHERE paper_id = ?
        ");
        
        return $stmt->execute([$paperId]);
    }
    
    /**
     * Increment citations
     * 
     * @param string $paperId Paper ID
     * @return bool Success status
     */
    public function incrementCitations($paperId) {
        $stmt = $this->pdo->prepare("
            UPDATE papers
            SET paper_citations = paper_citations + 1
            WHERE paper_id = ?
        ");
        
        return $stmt->execute([$paperId]);
    }
    
    /**
     * Generate paper ID
     * 
     * @return string Paper ID
     */
    private function generatePaperId() {
        $prefix = date('Ymd');
        $suffix = substr(uniqid(), -6);
        
        return $prefix . $suffix;
    }
    
    /**
     * Get author details
     * 
     * @param string $paperId Paper ID
     * @return array Author details
     */
    public function getAuthorDetails($paperId) {
        $paper = $this->getPaperById($paperId);
        
        if (!$paper) {
            return [];
        }
        
        $authorIds = [
            $paper['paper_author_id'],
            $paper['paper_co_author_1_id'],
            $paper['paper_co_author_2_id'],
            $paper['paper_co_author_3_id'],
            $paper['paper_co_author_4_id']
        ];
        
        // Filter out null values
        $authorIds = array_filter($authorIds);
        
        if (empty($authorIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($authorIds), '?'));
        
        $stmt = $this->pdo->prepare("
            SELECT user_id, user_prefixname, user_fname, user_mname, user_lname, user_designation, user_institution
            FROM users
            WHERE user_id IN ({$placeholders})
        ");
        
        $stmt->execute(array_values($authorIds));
        
        $authors = $stmt->fetchAll();
        $result = [];
        
        // Organize authors in the correct order
        foreach ($authors as $author) {
            if ($author['user_id'] == $paper['paper_author_id']) {
                $result['main_author'] = $author;
            } elseif ($author['user_id'] == $paper['paper_co_author_1_id']) {
                $result['co_authors'][1] = $author;
            } elseif ($author['user_id'] == $paper['paper_co_author_2_id']) {
                $result['co_authors'][2] = $author;
            } elseif ($author['user_id'] == $paper['paper_co_author_3_id']) {
                $result['co_authors'][3] = $author;
            } elseif ($author['user_id'] == $paper['paper_co_author_4_id']) {
                $result['co_authors'][4] = $author;
            }
        }
        
        return $result;
    }
    
    /**
     * Get reviewer details
     * 
     * @param string $paperId Paper ID
     * @return array|false Reviewer details or false if not found
     */
    public function getReviewerDetails($paperId) {
        $paper = $this->getPaperById($paperId);
        
        if (!$paper || !$paper['paper_reviewer_id']) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("
            SELECT user_id, user_prefixname, user_fname, user_mname, user_lname, user_designation, user_institution
            FROM users
            WHERE user_id = ?
        ");
        
        $stmt->execute([$paper['paper_reviewer_id']]);
        
        return $stmt->fetch();
    }
}
