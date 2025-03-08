<?php
// models/Journal.php

class Journal
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Get journal details
     * 
     * @return array Journal details
     */
    public function getJournalDetails()
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM journal_details
            WHERE journal_id = 1
        ");
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Update journal details
     * 
     * @param array $data Journal data
     * @return bool Success status
     */
    public function updateJournalDetails($data)
    {
        $setFields = [];
        $params = [];

        foreach ($data as $field => $value) {
            // Skip journal_id field
            if ($field === 'journal_id') {
                continue;
            }

            $setFields[] = "{$field} = ?";
            $params[] = $value;
        }

        if (empty($setFields)) {
            return false;
        }

        $params[] = 1; // Journal ID is always 1

        $sql = "UPDATE journal_details SET " . implode(', ', $setFields) . " WHERE journal_id = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get journal volumes
     * 
     * @return array Journal volumes
     */
    public function getJournalVolumes()
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT paper_volume, paper_year
            FROM papers
            WHERE paper_status = 'PUBLISHED' AND paper_volume IS NOT NULL
            ORDER BY paper_year DESC, paper_volume DESC
        ");
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get journal issues for a volume
     * 
     * @param int $volume Volume number
     * @param int $year Year
     * @return array Journal issues
     */
    public function getJournalIssues($volume, $year)
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT paper_issue
            FROM papers
            WHERE paper_status = 'PUBLISHED' AND paper_volume = ? AND paper_year = ?
            ORDER BY paper_issue ASC
        ");
        $stmt->execute([$volume, $year]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get papers for an issue
     * 
     * @param int $volume Volume number
     * @param int $issue Issue number
     * @param int $year Year
     * @return array Papers
     */
    public function getIssuePapers($volume, $issue, $year)
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM papers
            WHERE paper_status = 'PUBLISHED' AND paper_volume = ? AND paper_issue = ? AND paper_year = ?
            ORDER BY paper_title ASC
        ");
        $stmt->execute([$volume, $issue, $year]);

        return $stmt->fetchAll();
    }

    /**
     * Get latest issue
     * 
     * @return array|false Latest issue or false if none
     */
    public function getLatestIssue()
    {
        $stmt = $this->pdo->prepare("
            SELECT paper_volume, paper_issue, paper_year, COUNT(*) as paper_count
            FROM papers
            WHERE paper_status = 'PUBLISHED' AND paper_volume IS NOT NULL AND paper_issue IS NOT NULL
            GROUP BY paper_volume, paper_issue, paper_year
            ORDER BY paper_year DESC, paper_volume DESC, paper_issue DESC
            LIMIT 1
        ");
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Get journal statistics
     * 
     * @return array Journal statistics
     */
// models/Journal.php

public function getJournalStatistics() {
    $sql = "
    SELECT 
        (SELECT COUNT(*) FROM papers WHERE paper_status = 'PUBLISHED') as published_papers,
        (SELECT COUNT(*) FROM papers WHERE paper_status = 'UNDER_REVIEW') as papers_under_review,
        (SELECT COUNT(*) FROM users WHERE (user_roles & ?) > 0) as total_authors,
        (SELECT COUNT(*) FROM users WHERE (user_roles & ?) > 0) as total_reviewers
    ";

    try {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            User::ROLE_AUTHOR,    // Replace user_is_author with role check
            User::ROLE_REVIEWER   // Replace user_is_reviewer with role check
        ]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting journal statistics: " . $e->getMessage());
        return false;
    }
}


    /**
     * Get journal settings
     * 
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    public function getSetting($key, $default = null)
    {
        // Check if the column exists in journal_details
        try {
            $stmt = $this->pdo->prepare("
                SELECT {$key} FROM journal_details
                WHERE journal_id = 1
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_COLUMN);
            return $result !== false ? $result : $default;
        } catch (PDOException $e) {
            return $default;
        }
    }

    /**
     * Update journal setting
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool Success status
     */
    public function updateSetting($key, $value)
    {
        // Check if the column exists in journal_details
        try {
            $stmt = $this->pdo->prepare("
                UPDATE journal_details
                SET {$key} = ?
                WHERE journal_id = 1
            ");
            return $stmt->execute([$value]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get editorial board members
     * 
     * @return array Editorial board members
     */
public function getEditorialBoard() {
    $sql = "
        SELECT 
            user_id,
            user_prefixname,
            user_fname as first_name,
            user_lname as last_name,
            user_email as email,
            user_institution as affiliation,
            user_country as country,
            user_about_me as bio,
            user_profile_image as profile_image
        FROM users 
        WHERE (user_roles & ?) > 0 
        AND user_status = 'ACTIVE'
        ORDER BY user_fname ASC
    ";

    try {
        $stmt = $this->db->prepare($sql);
        $stmt->execute([ROLE_REVIEWER]); // Using the ROLE_REVIEWER constant (8)
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}


    /**
     * Get latest issues
     * 
     * @param int $limit Limit
     * @return array Latest issues
     */
    public function getLatestIssues($limit = 3)
    {
        $stmt = $this->pdo->prepare("
        SELECT paper_volume, paper_issue, paper_year, COUNT(*) as paper_count
        FROM papers
        WHERE paper_status = 'PUBLISHED' AND paper_volume IS NOT NULL AND paper_issue IS NOT NULL
        GROUP BY paper_volume, paper_issue, paper_year
        ORDER BY paper_year DESC, paper_volume DESC, paper_issue DESC
        LIMIT ?
    ");
        $stmt->execute([$limit]);

        return $stmt->fetchAll();
    }

    /**
     * Add editorial board member
     * 
     * @param array $data Member data
     * @return int|false Member ID or false on failure
     */
    public function addEditorialBoardMember($data)
    {
        $stmt = $this->pdo->prepare("
        INSERT INTO editorial_board (user_id, role, order_number, is_active)
        VALUES (?, ?, ?, ?)
    ");

        $success = $stmt->execute([
            $data['user_id'],
            $data['role'],
            $data['order_number'] ?? 999,
            $data['is_active'] ?? 1
        ]);

        return $success ? $this->pdo->lastInsertId() : false;
    }

    /**
     * Update editorial board member
     * 
     * @param int $id Member ID
     * @param array $data Member data
     * @return bool Success status
     */
    public function updateEditorialBoardMember($id, $data)
    {
        $setFields = [];
        $params = [];

        foreach ($data as $field => $value) {
            $setFields[] = "{$field} = ?";
            $params[] = $value;
        }

        if (empty($setFields)) {
            return false;
        }

        $params[] = $id;

        $sql = "UPDATE editorial_board SET " . implode(', ', $setFields) . " WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete editorial board member
     * 
     * @param int $id Member ID
     * @return bool Success status
     */
    public function deleteEditorialBoardMember($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM editorial_board WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get editorial board roles
     * 
     * @return array Editorial board roles
     */
    public function getEditorialBoardRoles()
    {
        $stmt = $this->pdo->prepare("
        SELECT DISTINCT role
        FROM editorial_board
        ORDER BY role ASC
    ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // models/Journal.php

/**
 * Get active call for papers
 */
public function getActiveCallForPapers() {
    $sql = "
        SELECT 
            cfp_title,
            cfp_content,
            cfp_topics,
            cfp_deadline,
            cfp_notification_date,
            cfp_camera_ready_date,
            cfp_publication_date,
            cfp_active
        FROM journal_details
        WHERE cfp_active = 1
        AND cfp_deadline >= CURDATE()
        ORDER BY cfp_deadline ASC
        LIMIT 1
    ";

    try {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Convert JSON topics to array
            $result['cfp_topics'] = json_decode($result['cfp_topics'] ?? '[]', true);
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Error getting call for papers: " . $e->getMessage());
        return false;
    }
}

}
