<?php
// controllers/PaperController.php

class PaperController {
    private $pdo;
    private $paperModel;
    private $userModel;
    private $reviewModel;
    private $notificationModel;
    private $activityModel;
    private $journalModel;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->paperModel = new Paper($pdo);
        $this->userModel = new User($pdo);
        $this->reviewModel = new Review($pdo);
        $this->notificationModel = new Notification($pdo);
        $this->activityModel = new Activity($pdo);
        $this->journalModel = new Journal($pdo);
    }
    
    /**
     * Display paper submission form
     */
    public function submitForm() {
        requireLogin();
        requireAuthor();
        
        $journalDetails = $this->journalModel->getJournalDetails();
        
        // Get co-authors for selection
        $authors = $this->userModel->getAuthors();
        
        // Remove current user from co-authors list
        $authors = array_filter($authors, function($author) {
            return $author['user_id'] != getCurrentUserId();
        });
        
        render('papers/submit', [
            'journalDetails' => $journalDetails,
            'authors' => $authors
        ]);
    }
    
    /**
     * Process paper submission
     */
    public function submitPaper() {
        requireLogin();
        requireAuthor();
        checkCSRF();
        
        $title = $_POST['title'] ?? '';
        $abstract = $_POST['abstract'] ?? '';
        $keywords = $_POST['keywords'] ?? '';
        $coAuthor1 = $_POST['co_author_1'] ?? null;
        $coAuthor2 = $_POST['co_author_2'] ?? null;
        $coAuthor3 = $_POST['co_author_3'] ?? null;
        $coAuthor4 = $_POST['co_author_4'] ?? null;
        
        // Validate input
        $errors = [];
        
        if (empty($title)) {
            $errors[] = 'Paper title is required';
        }
        
        if (empty($abstract)) {
            $errors[] = 'Abstract is required';
        }
        
        if (empty($keywords)) {
            $errors[] = 'Keywords are required';
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['paper_file']) || $_FILES['paper_file']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Paper file is required';
        } else {
            $file = $_FILES['paper_file'];
            $allowedExtensions = ['pdf', 'doc', 'docx'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = 'Invalid file type. Allowed types: PDF, DOC, DOCX';
            }
            
            if ($file['size'] > 10 * 1024 * 1024) { // 10MB
                $errors[] = 'File size exceeds the limit (10MB)';
            }
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect(config('app.url') . 'papers/submit');
            return;
        }
        
        // Generate paper ID
        $paperId = generatePaperId();
        
        // Upload file
        $uploadDir = ROOT_PATH . '/uploads/papers/';
        $fileName = $paperId . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            setFlashMessage('error', 'Failed to upload file');
            redirect(config('app.url') . 'papers/submit');
            return;
        }
        
        // Create paper
        $paperData = [
            'paper_id' => $paperId,
            'paper_title' => $title,
            'paper_abstract' => $abstract,
            'paper_keywords' => $keywords,
            'paper_author_id' => getCurrentUserId(),
            'paper_co_author_1_id' => $coAuthor1,
            'paper_co_author_2_id' => $coAuthor2,
            'paper_co_author_3_id' => $coAuthor3,
            'paper_co_author_4_id' => $coAuthor4,
            'paper_file_path' => $fileName,
            'paper_status' => Paper::STATUS_SUBMITTED,
            'paper_submission_date' => date('Y-m-d H:i:s'),
            'paper_pay_status' => 'PENDING'
        ];
        
        $success = $this->paperModel->createPaper($paperData);
        
        if (!$success) {
            setFlashMessage('error', 'Failed to submit paper');
            redirect(config('app.url') . 'papers/submit');
            return;
        }
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'paper_submitted',
            'activity_description' => 'Submitted paper: ' . $title,
            'activity_data' => json_encode(['paper_id' => $paperId])
        ]);
        
        // Send notification to admins and moderators
        $this->notificationModel->sendNotificationToAdmins(
            Notification::TYPE_PAPER_SUBMITTED,
            'New paper submitted: ' . $title,
            ['paper_id' => $paperId]
        );
        
        $this->notificationModel->sendNotificationToModerators(
            Notification::TYPE_PAPER_SUBMITTED,
            'New paper submitted: ' . $title,
            ['paper_id' => $paperId]
        );
        
        setFlashMessage('success', 'Paper submitted successfully');
        redirect(config('app.url') . 'papers/my');
    }
    
    /**
     * Display user's papers
     */
    public function myPapers() {
        requireLogin();
        
        $userId = getCurrentUserId();
        $papers = $this->paperModel->getPapersByAuthor($userId);
        
        render('papers/my', [
            'papers' => $papers
        ]);
    }
    
    /**
     * Display paper details
     * 
     * @param string $paperId Paper ID
     */
    public function viewPaper($paperId) {
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user has permission to view this paper
        if (!canViewPaper($paper)) {
            setFlashMessage('error', 'You do not have permission to view this paper');
            redirect(config('app.url'));
            return;
        }
        
        $authorDetails = $this->paperModel->getAuthorDetails($paperId);
        $reviewerDetails = $this->paperModel->getReviewerDetails($paperId);
        $reviews = [];
        
        // If user is admin, moderator, or the paper's author, show reviews
        if (isAdmin() || isModerator() || $paper['paper_author_id'] == getCurrentUserId()) {
            $reviews = $this->reviewModel->getReviewsByPaperId($paperId);
        }
        
        // If user is the assigned reviewer, show only their review
        if (isReviewer() && $paper['paper_reviewer_id'] == getCurrentUserId()) {
            $reviews = [$this->reviewModel->getReviewByPaperAndReviewer($paperId, getCurrentUserId())];
        }
        
        render('papers/view', [
            'paper' => $paper,
            'authorDetails' => $authorDetails,
            'reviewerDetails' => $reviewerDetails,
            'reviews' => $reviews
        ]);
    }
    
    /**
     * Download paper file
     * 
     * @param string $paperId Paper ID
     */
    public function downloadPaper($paperId) {
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user has permission to download this paper
        if (!canViewPaper($paper)) {
            setFlashMessage('error', 'You do not have permission to download this paper');
            redirect(config('app.url'));
            return;
        }
        
        $filePath = ROOT_PATH . '/uploads/papers/' . $paper['paper_file_path'];
        
        if (!file_exists($filePath)) {
            setFlashMessage('error', 'Paper file not found');
            redirect(config('app.url') . 'papers/view/' . $paperId);
            return;
        }
        
        // Increment download count
        $this->paperModel->incrementDownloads($paperId);
        
        // Log activity
        if (isLoggedIn()) {
            $this->activityModel->log([
                'activity_user_id' => getCurrentUserId(),
                'activity_action' => 'paper_downloaded',
                'activity_description' => 'Downloaded paper: ' . $paper['paper_title'],
                'activity_data' => json_encode(['paper_id' => $paperId])
            ]);
        }
        
        // Set headers for download
        $fileName = $paper['paper_file_path'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $downloadName = sanitize($paper['paper_title']) . '.' . $fileExtension;
        
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        readfile($filePath);
        exit;
    }
    
    /**
     * Display edit paper form
     * 
     * @param string $paperId Paper ID
     */
    public function editForm($paperId) {
        requireLogin();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user has permission to edit this paper
        if (!canEditPaper($paper)) {
            setFlashMessage('error', 'You do not have permission to edit this paper');
            redirect(config('app.url'));
            return;
        }
        
        // Get co-authors for selection
        $authors = $this->userModel->getAuthors();
        
        // Remove current user from co-authors list
        $authors = array_filter($authors, function($author) {
            return $author['user_id'] != getCurrentUserId();
        });
        
        render('papers/edit', [
            'paper' => $paper,
            'authors' => $authors
        ]);
    }
    
    /**
     * Process paper update
     * 
     * @param string $paperId Paper ID
     */
    public function updatePaper($paperId) {
        requireLogin();
        checkCSRF();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user has permission to edit this paper
        if (!canEditPaper($paper)) {
            setFlashMessage('error', 'You do not have permission to edit this paper');
            redirect(config('app.url'));
            return;
        }
        
        $title = $_POST['title'] ?? '';
        $abstract = $_POST['abstract'] ?? '';
        $keywords = $_POST['keywords'] ?? '';
        $coAuthor1 = $_POST['co_author_1'] ?? null;
        $coAuthor2 = $_POST['co_author_2'] ?? null;
        $coAuthor3 = $_POST['co_author_3'] ?? null;
        $coAuthor4 = $_POST['co_author_4'] ?? null;
        
        // Validate input
        $errors = [];
        
        if (empty($title)) {
            $errors[] = 'Paper title is required';
        }
        
        if (empty($abstract)) {
            $errors[] = 'Abstract is required';
        }
        
        if (empty($keywords)) {
            $errors[] = 'Keywords are required';
        }
        
        // Check if file was uploaded
        $fileName = $paper['paper_file_path'];
        
        if (isset($_FILES['paper_file']) && $_FILES['paper_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['paper_file'];
            $allowedExtensions = ['pdf', 'doc', 'docx'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = 'Invalid file type. Allowed types: PDF, DOC, DOCX';
            }
            
            if ($file['size'] > 10 * 1024 * 1024) { // 10MB
                $errors[] = 'File size exceeds the limit (10MB)';
            }
            
            // Generate new file name
            $fileName = $paperId . '.' . $fileExtension;
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect(config('app.url') . 'papers/edit/' . $paperId);
            return;
        }
        
        // Upload new file if provided
        if (isset($_FILES['paper_file']) && $_FILES['paper_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = ROOT_PATH . '/uploads/papers/';
            $filePath = $uploadDir . $fileName;
            
            // Delete old file if it exists and is different
            if ($paper['paper_file_path'] !== $fileName && file_exists($uploadDir . $paper['paper_file_path'])) {
                unlink($uploadDir . $paper['paper_file_path']);
            }
            
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                setFlashMessage('error', 'Failed to upload file');
                redirect(config('app.url') . 'papers/edit/' . $paperId);
                return;
            }
        }
        
        // Update paper
        $paperData = [
            'paper_title' => $title,
            'paper_abstract' => $abstract,
            'paper_keywords' => $keywords,
            'paper_co_author_1_id' => $coAuthor1,
            'paper_co_author_2_id' => $coAuthor2,
            'paper_co_author_3_id' => $coAuthor3,
            'paper_co_author_4_id' => $coAuthor4,
            'paper_file_path' => $fileName
        ];
        
        // If paper was previously rejected and now being resubmitted
        if ($paper['paper_status'] === Paper::STATUS_REJECTED && isAuthor()) {
            $paperData['paper_status'] = Paper::STATUS_SUBMITTED;
            $paperData['paper_submission_date'] = date('Y-m-d H:i:s');
        }
        
        $success = $this->paperModel->updatePaper($paperId, $paperData);
        
        if (!$success) {
            setFlashMessage('error', 'Failed to update paper');
            redirect(config('app.url') . 'papers/edit/' . $paperId);
            return;
        }
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'paper_updated',
            'activity_description' => 'Updated paper: ' . $title,
            'activity_data' => json_encode(['paper_id' => $paperId])
        ]);
        
        // If paper was resubmitted, notify admins and moderators
        if (isset($paperData['paper_status']) && $paperData['paper_status'] === Paper::STATUS_SUBMITTED) {
            $this->notificationModel->sendNotificationToAdmins(
                Notification::TYPE_PAPER_SUBMITTED,
                'Paper resubmitted: ' . $title,
                ['paper_id' => $paperId]
            );
            
            $this->notificationModel->sendNotificationToModerators(
                Notification::TYPE_PAPER_SUBMITTED,
                'Paper resubmitted: ' . $title,
                ['paper_id' => $paperId]
            );
        }
        
        setFlashMessage('success', 'Paper updated successfully');
        redirect(config('app.url') . 'papers/view/' . $paperId);
    }
    
    /**
     * Display all papers (admin/moderator only)
     */
    public function allPapers() {
        requireModerator();
        
        $status = $_GET['status'] ?? null;
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $filters = [];
        
        if ($status) {
            $filters['status'] = $status;
        }
        
        $papers = $this->paperModel->getAllPapers($limit, $offset, $filters);
        $totalPapers = $this->paperModel->countPapers($filters);
        $totalPages = ceil($totalPapers / $limit);
        
        render('admin/papers/index', [
            'papers' => $papers,
            'status' => $status,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalPapers' => $totalPapers
        ]);
    }
    
    /**
     * Assign reviewer to paper
     * 
     * @param string $paperId Paper ID
     */
    public function assignReviewerForm($paperId) {
        requireModerator();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url') . 'admin/papers');
            return;
        }
        
        // Get reviewers
        $reviewers = $this->userModel->getReviewers();
        
        render('admin/papers/assign_reviewer', [
            'paper' => $paper,
            'reviewers' => $reviewers
        ]);
    }
    
    /**
     * Process reviewer assignment
     * 
     * @param string $paperId Paper ID
     */
    public function assignReviewer($paperId) {
        requireModerator();
        checkCSRF();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url') . 'admin/papers');
            return;
        }
        
        $reviewerId = $_POST['reviewer_id'] ?? null;
        $deadline = $_POST['deadline'] ?? null;
        
        // Validate input
        $errors = [];
        
        if (empty($reviewerId)) {
            $errors[] = 'Reviewer is required';
        }
        
        if (empty($deadline)) {
            $errors[] = 'Deadline is required';
        } else {
            $deadlineDate = new DateTime($deadline);
            $now = new DateTime();
            
            if ($deadlineDate <= $now) {
                $errors[] = 'Deadline must be in the future';
            }
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect(config('app.url') . 'admin/papers/assign-reviewer/' . $paperId);
            return;
        }
        
        // Assign reviewer
        $success = $this->reviewModel->assignReviewer($paperId, $reviewerId, $deadline);
        
        if (!$success) {
            setFlashMessage('error', 'Failed to assign reviewer');
            redirect(config('app.url') . 'admin/papers/assign-reviewer/' . $paperId);
            return;
        }
        
        // Update paper status and reviewer
        $this->paperModel->updatePaper($paperId, [
            'paper_status' => Paper::STATUS_UNDER_REVIEW,
            'paper_reviewer_id' => $reviewerId,
            'paper_review_deadline' => $deadline,
            'paper_review_status' => Paper::REVIEW_STATUS_PENDING
        ]);
        
        // Log activity
        $reviewer = $this->userModel->getUserById($reviewerId);
        $reviewerName = getUserFullName($reviewer);
        
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'reviewer_assigned',
            'activity_description' => 'Assigned reviewer ' . $reviewerName . ' to paper: ' . $paper['paper_title'],
            'activity_data' => json_encode([
                'paper_id' => $paperId,
                'reviewer_id' => $reviewerId,
                'deadline' => $deadline
            ])
        ]);
        
        // Notify reviewer
        $this->notificationModel->sendNotification(
            $reviewerId,
            Notification::TYPE_REVIEW_ASSIGNED,
            'You have been assigned to review a paper: ' . $paper['paper_title'],
            [
                'paper_id' => $paperId,
                'deadline' => $deadline
            ]
        );
        
        setFlashMessage('success', 'Reviewer assigned successfully');
        redirect(config('app.url') . 'papers/view/' . $paperId);
    }
    
    /**
     * Display review form
     * 
     * @param string $paperId Paper ID
     */
    public function reviewForm($paperId) {
        requireReviewer();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user is assigned to review this paper
        if ($paper['paper_reviewer_id'] != getCurrentUserId() && !isAdmin() && !isModerator()) {
            setFlashMessage('error', 'You are not assigned to review this paper');
            redirect(config('app.url'));
            return;
        }
        
        // Get existing review if any
        $review = $this->reviewModel->getReviewByPaperAndReviewer($paperId, getCurrentUserId());
        
        render('papers/review', [
            'paper' => $paper,
            'review' => $review
        ]);
    }
    
    /**
     * Process review submission
     * 
     * @param string $paperId Paper ID
     */
    public function submitReview($paperId) {
        requireReviewer();
        checkCSRF();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user is assigned to review this paper
        if ($paper['paper_reviewer_id'] != getCurrentUserId() && !isAdmin() && !isModerator()) {
            setFlashMessage('error', 'You are not assigned to review this paper');
            redirect(config('app.url'));
            return;
        }
        
        $originality = $_POST['originality'] ?? 0;
        $relevance = $_POST['relevance'] ?? 0;
        $methodology = $_POST['methodology'] ?? 0;
        $presentation = $_POST['presentation'] ?? 0;
        $recommendation = $_POST['recommendation'] ?? '';
        $comments = $_POST['comments'] ?? '';
        $commentsToEditor = $_POST['comments_to_editor'] ?? '';
        
        // Validate input
        $errors = [];
        
        if (empty($originality) || empty($relevance) || empty($methodology) || empty($presentation)) {
            $errors[] = 'All rating fields are required';
        }
        
        if (empty($recommendation)) {
            $errors[] = 'Recommendation is required';
        }
        
        if (empty($comments)) {
            $errors[] = 'Comments are required';
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect(config('app.url') . 'papers/review/' . $paperId);
            return;
        }
        
        // Calculate average score
        $scores = [
            intval($originality),
            intval($relevance),
            intval($methodology),
            intval($presentation)
        ];
        
        $averageScore = calculateReviewScore($scores);
        
        // Get existing review if any
        $review = $this->reviewModel->getReviewByPaperAndReviewer($paperId, getCurrentUserId());
        
        if ($review) {
            // Update existing review
            $reviewData = [
                'review_originality' => $originality,
                'review_relevance' => $relevance,
                'review_methodology' => $methodology,
                'review_presentation' => $presentation,
                'review_score' => $averageScore,
                'review_decision' => $recommendation,
                'review_comments' => $comments,
                'review_comments_to_editor' => $commentsToEditor,
                'review_status' => Review::STATUS_COMPLETED,
                'review_completed_at' => date('Y-m-d H:i:s')
            ];
            
            $success = $this->reviewModel->updateReview($review['review_id'], $reviewData);
        } else {
            // Create new review
            $reviewData = [
                'review_paper_id' => $paperId,
                'review_reviewer_id' => getCurrentUserId(),
                'review_originality' => $originality,
                'review_relevance' => $relevance,
                'review_methodology' => $methodology,
                'review_presentation' => $presentation,
                'review_score' => $averageScore,
                'review_decision' => $recommendation,
                'review_comments' => $comments,
                'review_comments_to_editor' => $commentsToEditor,
                'review_status' => Review::STATUS_COMPLETED,
                'review_created_at' => date('Y-m-d H:i:s'),
                'review_completed_at' => date('Y-m-d H:i:s')
            ];
            
            $success = $this->reviewModel->createReview($reviewData);
        }
        
        if (!$success) {
            setFlashMessage('error', 'Failed to submit review');
            redirect(config('app.url') . 'papers/review/' . $paperId);
            return;
        }
        
        // Update paper review status
        $this->paperModel->updateReviewStatus($paperId, Paper::REVIEW_STATUS_COMPLETED);
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'review_submitted',
            'activity_description' => 'Submitted review for paper: ' . $paper['paper_title'],
            'activity_data' => json_encode([
                'paper_id' => $paperId,
                'recommendation' => $recommendation,
                'score' => $averageScore
            ])
        ]);
        
        // Notify admins and moderators
        $this->notificationModel->sendNotificationToAdmins(
            Notification::TYPE_REVIEW_SUBMITTED,
            'Review submitted for paper: ' . $paper['paper_title'],
            [
                'paper_id' => $paperId,
                'recommendation' => $recommendation,
                'score' => $averageScore
            ]
        );
        
        $this->notificationModel->sendNotificationToModerators(
            Notification::TYPE_REVIEW_SUBMITTED,
            'Review submitted for paper: ' . $paper['paper_title'],
            [
                'paper_id' => $paperId,
                'recommendation' => $recommendation,
                'score' => $averageScore
            ]
        );
        
        setFlashMessage('success', 'Review submitted successfully');
        redirect(config('app.url') . 'papers/view/' . $paperId);
    }
    
    /**
     * Make decision on paper
     * 
     * @param string $paperId Paper ID
     */
    public function makeDecisionForm($paperId) {
        requireModerator();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url') . 'admin/papers');
            return;
        }
        
        // Get reviews
        $reviews = $this->reviewModel->getReviewsByPaperId($paperId);
        
        render('admin/papers/decision', [
            'paper' => $paper,
            'reviews' => $reviews
        ]);
    }
    
    /**
     * Process paper decision
     * 
     * @param string $paperId Paper ID
     */
    public function makeDecision($paperId) {
        requireModerator();
        checkCSRF();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url') . 'admin/papers');
            return;
        }
        
        $decision = $_POST['decision'] ?? '';
        $comments = $_POST['comments'] ?? '';
        
        // Validate input
        if (empty($decision)) {
            setFlashMessage('error', 'Decision is required');
            redirect(config('app.url') . 'admin/papers/decision/' . $paperId);
            return;
        }
        
        // Update paper status
        $paperData = [
            'paper_status' => $decision,
            'paper_decision_comments' => $comments,
            'paper_decision_date' => date('Y-m-d H:i:s'),
            'paper_decision_by' => getCurrentUserId()
        ];
        
        $success = $this->paperModel->updatePaper($paperId, $paperData);
        
        if (!$success) {
            setFlashMessage('error', 'Failed to update paper status');
            redirect(config('app.url') . 'admin/papers/decision/' . $paperId);
            return;
        }
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'paper_decision',
            'activity_description' => 'Made decision on paper: ' . $paper['paper_title'] . ' - ' . $decision,
            'activity_data' => json_encode([
                'paper_id' => $paperId,
                'decision' => $decision,
                'comments' => $comments
            ])
        ]);
        
        // Notify author
        $notificationType = '';
        $notificationMessage = '';
        
        if ($decision === Paper::STATUS_ACCEPTED) {
            $notificationType = Notification::TYPE_PAPER_ACCEPTED;
            $notificationMessage = 'Your paper has been accepted: ' . $paper['paper_title'];
        } elseif ($decision === Paper::STATUS_REJECTED) {
            $notificationType = Notification::TYPE_PAPER_REJECTED;
            $notificationMessage = 'Your paper has been rejected: ' . $paper['paper_title'];
        } elseif ($decision === Paper::STATUS_REVISION_REQUESTED) {
            $notificationType = Notification::TYPE_REVISION_REQUESTED;
            $notificationMessage = 'Revision requested for your paper: ' . $paper['paper_title'];
        }
        
        if ($notificationType) {
            $this->notificationModel->sendNotification(
                $paper['paper_author_id'],
                $notificationType,
                $notificationMessage,
                [
                    'paper_id' => $paperId,
                    'decision' => $decision,
                    'comments' => $comments
                ]
            );
        }
        
        setFlashMessage('success', 'Paper decision made successfully');
        redirect(config('app.url') . 'papers/view/' . $paperId);
    }
    
    /**
     * Publish paper
     * 
     * @param string $paperId Paper ID
     */
    public function publishForm($paperId) {
        requireModerator();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url') . 'admin/papers');
            return;
        }
        
        // Check if paper is accepted
        if ($paper['paper_status'] !== Paper::STATUS_ACCEPTED) {
            setFlashMessage('error', 'Only accepted papers can be published');
            redirect(config('app.url') . 'papers/view/' . $paperId);
            return;
        }
        
        // Get latest volume and issue
        $latestIssue = $this->journalModel->getLatestIssue();
        $currentYear = date('Y');
        
        $volume = $latestIssue ? $latestIssue['paper_volume'] : 1;
        $issue = $latestIssue ? $latestIssue['paper_issue'] : 1;
        
        // If it's a new year, increment volume and reset issue
        if ($latestIssue && $latestIssue['paper_year'] < $currentYear) {
            $volume++;
            $issue = 1;
        }
        // If it's the same year, use the same volume but next issue
        elseif ($latestIssue && $latestIssue['paper_year'] == $currentYear) {
            $issue++;
        }
        
        render('admin/papers/publish', [
            'paper' => $paper,
            'volume' => $volume,
            'issue' => $issue,
            'year' => $currentYear
        ]);
    }
    
    /**
     * Process paper publication
     * 
     * @param string $paperId Paper ID
     */
    public function publishPaper($paperId) {
        requireModerator();
        checkCSRF();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url') . 'admin/papers');
            return;
        }
        
        // Check if paper is accepted
        if ($paper['paper_status'] !== Paper::STATUS_ACCEPTED) {
            setFlashMessage('error', 'Only accepted papers can be published');
            redirect(config('app.url') . 'papers/view/' . $paperId);
            return;
        }
        
        $volume = $_POST['volume'] ?? '';
        $issue = $_POST['issue'] ?? '';
        $year = $_POST['year'] ?? '';
        $pages = $_POST['pages'] ?? '';
        $doi = $_POST['doi'] ?? '';
        $order = $_POST['order'] ?? 1;
        
        // Validate input
        $errors = [];
        
        if (empty($volume)) {
            $errors[] = 'Volume is required';
        }
        
        if (empty($issue)) {
            $errors[] = 'Issue is required';
        }
        
        if (empty($year)) {
            $errors[] = 'Year is required';
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect(config('app.url') . 'admin/papers/publish/' . $paperId);
            return;
        }
        
        // Generate DOI if not provided
        if (empty($doi)) {
            $journalDetails = $this->journalModel->getJournalDetails();
            $doiPrefix = $journalDetails['journal_doi_prefix'] ?? '10.xxxx';
            $doi = generateDOI($doiPrefix, $paperId);
        }
        
        // Update paper
        $paperData = [
            'paper_status' => Paper::STATUS_PUBLISHED,
            'paper_volume' => $volume,
            'paper_issue' => $issue,
            'paper_year' => $year,
            'paper_pages' => $pages,
            'paper_doi' => $doi,
            'paper_order' => $order,
            'paper_publication_date' => date('Y-m-d H:i:s')
        ];
        
        $success = $this->paperModel->updatePaper($paperId, $paperData);
        
        if (!$success) {
            setFlashMessage('error', 'Failed to publish paper');
            redirect(config('app.url') . 'admin/papers/publish/' . $paperId);
            return;
        }
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'paper_published',
            'activity_description' => 'Published paper: ' . $paper['paper_title'],
            'activity_data' => json_encode([
                'paper_id' => $paperId,
                'volume' => $volume,
                'issue' => $issue,
                'year' => $year,
                'doi' => $doi
            ])
        ]);
        
        // Notify author
        $this->notificationModel->sendNotification(
            $paper['paper_author_id'],
            Notification::TYPE_PAPER_PUBLISHED,
            'Your paper has been published: ' . $paper['paper_title'],
            [
                'paper_id' => $paperId,
                'volume' => $volume,
                'issue' => $issue,
                'year' => $year,
                'doi' => $doi
            ]
        );
        
        setFlashMessage('success', 'Paper published successfully');
        redirect(config('app.url') . 'papers/view/' . $paperId);
    }
    
    /**
     * Search papers
     */
    public function search() {
        $query = $_GET['q'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        if (empty($query)) {
            redirect(config('app.url'));
            return;
        }
        
        $papers = $this->paperModel->searchPapers($query, $limit);
        
        render('papers/search', [
            'papers' => $papers,
            'query' => $query
        ]);
    }
    
    /**
     * Display published papers
     */
    public function publishedPapers() {
        $volume = $_GET['volume'] ?? null;
        $issue = $_GET['issue'] ?? null;
        $year = $_GET['year'] ?? null;
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $filters = [
            'status' => Paper::STATUS_PUBLISHED
        ];
        
        if ($volume) {
            $filters['volume'] = $volume;
        }
        
        if ($issue) {
            $filters['issue'] = $issue;
        }
        
        if ($year) {
            $filters['year'] = $year;
        }
        
        $papers = $this->paperModel->getAllPapers($limit, $offset, $filters);
        $totalPapers = $this->paperModel->countPapers($filters);
        $totalPages = ceil($totalPapers / $limit);
        
        // Get volumes for filter
        $volumes = $this->journalModel->getJournalVolumes();
        
        // Get issues for selected volume
        $issues = [];
        if ($volume && $year) {
            $issues = $this->journalModel->getJournalIssues($volume, $year);
        }
        
        render('papers/published', [
            'papers' => $papers,
            'volume' => $volume,
            'issue' => $issue,
            'year' => $year,
            'volumes' => $volumes,
            'issues' => $issues,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalPapers' => $totalPapers
        ]);
    }
    
    /**
     * Display issue details
     * 
     * @param int $volume Volume number
     * @param int $issue Issue number
     * @param int $year Year
     */
    public function viewIssue($volume, $issue, $year) {
        $papers = $this->journalModel->getIssuePapers($volume, $issue, $year);
        
        if (empty($papers)) {
            setFlashMessage('error', 'Issue not found');
            redirect(config('app.url') . 'papers/published');
            return;
        }
        
        render('papers/issue', [
            'papers' => $papers,
            'volume' => $volume,
            'issue' => $issue,
            'year' => $year
        ]);
    }
    
    /**
     * Display paper citation
     * 
     * @param string $paperId Paper ID
     */
    public function citation($paperId) {
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Get author details
        $authorDetails = $this->paperModel->getAuthorDetails($paperId);
        
        // Format citations
        $apaCitation = formatAPACitation($paper, $authorDetails);
        $mlaCitation = formatMLACitation($paper, $authorDetails);
        
        render('papers/citation', [
            'paper' => $paper,
            'apaCitation' => $apaCitation,
            'mlaCitation' => $mlaCitation
        ]);
    }
    
    /**
     * Withdraw paper
     * 
     * @param string $paperId Paper ID
     */
    public function withdrawForm($paperId) {
        requireLogin();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user is the author of this paper
        if ($paper['paper_author_id'] != getCurrentUserId() && !isAdmin() && !isModerator()) {
            setFlashMessage('error', 'You do not have permission to withdraw this paper');
            redirect(config('app.url'));
            return;
        }
        
        // Check if paper can be withdrawn
        if ($paper['paper_status'] === Paper::STATUS_PUBLISHED || $paper['paper_status'] === Paper::STATUS_WITHDRAWN) {
            setFlashMessage('error', 'This paper cannot be withdrawn');
            redirect(config('app.url') . 'papers/view/' . $paperId);
            return;
        }
        
        render('papers/withdraw', [
            'paper' => $paper
        ]);
    }
    
    /**
     * Process paper withdrawal
     * 
     * @param string $paperId Paper ID
     */
    public function withdrawPaper($paperId) {
        requireLogin();
        checkCSRF();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user is the author of this paper
        if ($paper['paper_author_id'] != getCurrentUserId() && !isAdmin() && !isModerator()) {
            setFlashMessage('error', 'You do not have permission to withdraw this paper');
            redirect(config('app.url'));
            return;
        }
        
        // Check if paper can be withdrawn
        if ($paper['paper_status'] === Paper::STATUS_PUBLISHED || $paper['paper_status'] === Paper::STATUS_WITHDRAWN) {
            setFlashMessage('error', 'This paper cannot be withdrawn');
            redirect(config('app.url') . 'papers/view/' . $paperId);
            return;
        }
        
        $reason = $_POST['reason'] ?? '';
        
        if (empty($reason)) {
            setFlashMessage('error', 'Withdrawal reason is required');
            redirect(config('app.url') . 'papers/withdraw/' . $paperId);
            return;
        }
        
        // Update paper status
        $paperData = [
            'paper_status' => Paper::STATUS_WITHDRAWN,
            'paper_withdrawal_reason' => $reason,
            'paper_withdrawal_date' => date('Y-m-d H:i:s')
        ];
        
        $success = $this->paperModel->updatePaper($paperId, $paperData);
        
        if (!$success) {
            setFlashMessage('error', 'Failed to withdraw paper');
            redirect(config('app.url') . 'papers/withdraw/' . $paperId);
            return;
        }
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'paper_withdrawn',
            'activity_description' => 'Withdrew paper: ' . $paper['paper_title'],
            'activity_data' => json_encode([
                'paper_id' => $paperId,
                'reason' => $reason
            ])
        ]);
        
        // Notify admins and moderators
        $this->notificationModel->sendNotificationToAdmins(
            Notification::TYPE_SYSTEM,
            'Paper withdrawn: ' . $paper['paper_title'],
            [
                'paper_id' => $paperId,
                'reason' => $reason
            ]
        );
        
        $this->notificationModel->sendNotificationToModerators(
            Notification::TYPE_SYSTEM,
            'Paper withdrawn: ' . $paper['paper_title'],
            [
                'paper_id' => $paperId,
                'reason' => $reason
            ]
        );
        
        setFlashMessage('success', 'Paper withdrawn successfully');
        redirect(config('app.url') . 'papers/view/' . $paperId);
    }
}


