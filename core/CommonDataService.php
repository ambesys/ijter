<?php
// core/CommonDataService.php

class CommonDataService {
    private $pdo;
    private $cache = [];
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    public function getJournalInfo() {
        if (!isset($this->cache['journal_info'])) {
            $stmt = $this->pdo->query("SELECT * FROM journal_info LIMIT 1");
            $this->cache['journal_info'] = $stmt->fetch();
        }
        return $this->cache['journal_info'];
    }
    
    public function getCategories() {
        if (!isset($this->cache['categories'])) {
            $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name");
            $this->cache['categories'] = $stmt->fetchAll();
        }
        return $this->cache['categories'];
    }
    
    public function getCurrentIssue() {
        if (!isset($this->cache['current_issue'])) {
            $stmt = $this->pdo->query("SELECT * FROM issues WHERE is_current = 1 LIMIT 1");
            $this->cache['current_issue'] = $stmt->fetch();
        }
        return $this->cache['current_issue'];
    }
    
    // Add more methods for other common data
}
