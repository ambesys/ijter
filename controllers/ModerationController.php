<?php
// controllers/ModerationController.php

class ModerationController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function queue() {
        // Show moderation queue
        return Helper::view('moderation/queue');
    }
    
    public function approve($id) {
        // Approve paper logic
        // Add your approval logic here
    }
    
    public function reject($id) {
        // Reject paper logic
        // Add your rejection logic here
    }
}
