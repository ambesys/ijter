<?php
// controllers/ReviewController.php

class ReviewController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function index() {
        // Show list of papers to review
        return Helper::view('review/index');
    }
    
    public function reviewForm($id) {
        // Show review form for specific paper
        return Helper::view('review/form', ['paper_id' => $id]);
    }
    
    public function submitReview($id) {
        // Process review submission
        // Add your review submission logic here
    }
    
    public function history() {
        // Show review history
        return Helper::view('review/history');
    }
}
