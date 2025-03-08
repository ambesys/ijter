<?php
// controllers/AdminController.php

class AdminController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function dashboard() {
        // Dashboard logic here
        return Helper::view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $this->getDashboardStats()
        ]);
    }

    public function users() {
        // Users management logic here
        return Helper::view('admin/users', [
            'title' => 'Manage Users',
            'users' => $this->getAllUsers()
        ]);
    }

    public function papers() {
        // Papers management logic here
        return Helper::view('admin/papers', [
            'title' => 'Manage Papers',
            'papers' => $this->getAllPapers()
        ]);
    }

    public function settings() {
        // Settings page logic here
        return Helper::view('admin/settings', [
            'title' => 'Journal Settings',
            'settings' => $this->getJournalSettings()
        ]);
    }

    public function updateSettings() {
        // Settings update logic here
        // Process POST data and update settings
    }

    private function getDashboardStats() {
        // Get statistics for dashboard
        return [
            'total_users' => $this->countUsers(),
            'total_papers' => $this->countPapers(),
            'pending_reviews' => $this->countPendingReviews(),
            'recent_submissions' => $this->getRecentSubmissions()
        ];
    }

    private function getAllUsers() {
        $stmt = $this->pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllPapers() {
        $stmt = $this->pdo->query("SELECT * FROM papers ORDER BY submitted_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getJournalSettings() {
        $stmt = $this->pdo->query("SELECT * FROM journal_settings");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function countUsers() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn();
    }

    private function countPapers() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM papers");
        return $stmt->fetchColumn();
    }

    private function countPendingReviews() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM papers WHERE status = 'pending_review'");
        return $stmt->fetchColumn();
    }

    private function getRecentSubmissions() {
        $stmt = $this->pdo->query("SELECT * FROM papers ORDER BY submitted_at DESC LIMIT 5");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
