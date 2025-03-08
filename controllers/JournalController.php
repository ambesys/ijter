<?php
// controllers/JournalController.php

class JournalController {
    private $pdo;
    private $journalModel;
    private $paperModel;
    private $activityModel;
    private $config;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->config = require ROOT_PATH . '/config/config.php';
        if (!is_array($this->config)) {
            throw new Exception("Invalid configuration");
        }
        $this->journalModel = new Journal($pdo);
        $this->paperModel = new Paper($pdo);
        $this->activityModel = new Activity($pdo);
    }
    
    /**
     * Display journal home page
     */
    public function home() {
        $journalDetails = $this->journalModel->getJournalDetails();
        
        // Get latest issue
        $latestIssue = $this->journalModel->getLatestIssue();
        $latestPapers = [];
        
        if ($latestIssue) {
            $latestPapers = $this->journalModel->getIssuePapers(
                $latestIssue['paper_volume'],
                $latestIssue['paper_issue'],
                $latestIssue['paper_year']
            );
        }
        
        // Get journal statistics
        $stats = $this->journalModel->getJournalStatistics();
        
        // Get latest papers
        $latestPublishedPapers = $this->paperModel->getPublishedPapers(6);
        
        // Get latest issues
        $issues = $this->journalModel->getLatestIssues(3);
        
        render('journal/home', [
            'journalDetails' => $journalDetails,
            'latestIssue' => $latestIssue,
            'latestPapers' => $latestPapers,
            'latestPublishedPapers' => $latestPublishedPapers,
            'issues' => $issues,
            'stats' => $stats,
            'pageTitle' => $journalDetails['journal_full_name']
        ]);
    }
    
    /**
     * Display journal about page
     */
    public function about() {
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('journal/about', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'About | ' . $journalDetails['journal_full_name']
        ]);
    }
    
    /**
     * Display editorial board page
     */
    public function editorialBoard() {
        $journalDetails = $this->journalModel->getJournalDetails();
        
        // Get editorial board members
        $editorialBoard = $this->journalModel->getEditorialBoard();
        
        // Group members by role
        $groupedMembers = [];
        foreach ($editorialBoard as $member) {
            $role = $member['role'];
            if (!isset($groupedMembers[$role])) {
                $groupedMembers[$role] = [];
            }
            $groupedMembers[$role][] = $member;
        }
        
        render('journal/editorial_board', [
            'journalDetails' => $journalDetails,
            'groupedMembers' => $groupedMembers,
            'pageTitle' => 'Editorial Board | ' . $journalDetails['journal_full_name']
        ]);
    }
    
    /**
     * Display submission guidelines page
     */
    public function submissionGuidelines() {
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('journal/submission_guidelines', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Submission Guidelines | ' . $journalDetails['journal_full_name']
        ]);
    }
    
    /**
     * Display author guidelines page
     */
    public function authorGuidelines() {
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('journal/guidelines/author', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Author Guidelines | ' . $journalDetails['journal_full_name']
        ]);
    }
    
    /**
     * Display reviewer guidelines page
     */
    public function reviewerGuidelines() {
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('journal/guidelines/reviewer', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Reviewer Guidelines | ' . $journalDetails['journal_full_name']
        ]);
    }
    
    /**
     * Display ethics policy page
     */
    public function ethicsPolicy() {
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('journal/guidelines/ethics', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Ethics Policy | ' . $journalDetails['journal_full_name']
        ]);
    }
    
    /**
     * Display peer review process page
     */
    public function peerReviewProcess() {
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('journal/guidelines/peer_review', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Peer Review Process | ' . $journalDetails['journal_full_name']
        ]);
    }
    
    /**
     * Display journal contact page
     */
    public function contact() {
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('journal/contact', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Contact | ' . $journalDetails['journal_full_name']
        ]);
    }
    
    /**
     * Process contact form submission
     */
    public function submitContact() {
        checkCSRF();
        
        $journalDetails = $this->journalModel->getJournalDetails();
        
        // Initialize variables
        $formData = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'subject' => $_POST['subject'] ?? '',
            'message' => $_POST['message'] ?? ''
        ];
        
        $errors = [];
        
        // Validate form data
        if (empty($formData['name'])) {
            $errors['name'] = 'Name is required';
        }
        
        if (empty($formData['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($formData['subject'])) {
            $errors['subject'] = 'Subject is required';
        }
        
        if (empty($formData['message'])) {
            $errors['message'] = 'Message is required';
        }
        
        // If there are errors, re-render the form
        if (!empty($errors)) {
            render('journal/contact', [
                'journalDetails' => $journalDetails,
                'formData' => $formData,
                'errors' => $errors,
                'pageTitle' => 'Contact | ' . $journalDetails['journal_full_name']
            ]);
            return;
        }
        
        // Process the form (send email, save to database, etc.)
        // Log the contact form submission
        $this->activityModel->log([
            'activity_user_id' => isLoggedIn() ? getCurrentUserId() : null,
            'activity_action' => 'contact_form',
            'activity_description' => 'Contact form submitted by ' . $formData['name'] . ' (' . $formData['email'] . ')',
            'activity_data' => json_encode($formData)
        ]);
        
        // Send email to journal admin
        $adminEmail = $journalDetails['journal_email'] ?? config('mail.from_address');
        $subject = 'Contact Form: ' . $formData['subject'];
        $message = "
            <p><strong>Name:</strong> {$formData['name']}</p>
            <p><strong>Email:</strong> {$formData['email']}</p>
            <p><strong>Subject:</strong> {$formData['subject']}</p>
            <p><strong>Message:</strong></p>
            <p>{$formData['message']}</p>";
        
        $emailSent = sendEmail($adminEmail, $subject, $message, [
            'reply_to' => $formData['email'],
            'reply_to_name' => $formData['name']
        ]);
        
        // Show success message
        setFlashMessage('success', 'Your message has been sent. Thank you!');
        redirect(config('app.url') . 'contact');
    }
    
    /**
     * Display privacy policy page
     */
    public function privacyPolicy() {
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('journal/privacy_policy', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Privacy Policy | ' . $journalDetails['journal_full_name']
        ]);
    }
    
    /**
     * Display terms and conditions page
     */
    public function termsConditions() {
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('journal/terms_conditions', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Terms and Conditions | ' . $journalDetails['journal_full_name']
        ]);
    }
    
    /**
     * Display journal settings page (admin only)
     */
    public function settings() {
        requireAdmin();
        
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('admin/journal/settings', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Journal Settings | Admin'
        ]);
    }
    
    /**
     * Update journal settings (admin only)
     */
    public function updateSettings() {
        requireAdmin();
        checkCSRF();
        
        $journalName = $_POST['journal_name'] ?? '';
        $journalFullName = $_POST['journal_full_name'] ?? '';
        $journalDescription = $_POST['journal_description'] ?? '';
        $journalIssn = $_POST['journal_issn'] ?? '';
        $journalEissn = $_POST['journal_eissn'] ?? '';
        $journalDoiPrefix = $_POST['journal_doi_prefix'] ?? '';
        $journalPublisher = $_POST['journal_publisher'] ?? '';
        $journalEmail = $_POST['journal_email'] ?? '';
        $journalPhone = $_POST['journal_phone'] ?? '';
        $journalAddress = $_POST['journal_address'] ?? '';
        $journalWebsite = $_POST['journal_website'] ?? '';
        $journalSubmissionFee = $_POST['journal_submission_fee'] ?? 0;
        $journalPublicationFee = $_POST['journal_publication_fee'] ?? 0;
        $journalCurrency = $_POST['journal_currency'] ?? 'USD';
        
        // Validate input
        $errors = [];
        
        if (empty($journalName)) {
            $errors[] = 'Journal name is required';
        }
        
        if (empty($journalFullName)) {
            $errors[] = 'Journal full name is required';
        }
        
        if (empty($journalEmail)) {
            $errors[] = 'Journal email is required';
        } elseif (!filter_var($journalEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect(config('app.url') . 'admin/journal/settings');
            return;
        }
        
        // Update journal settings
        $journalData = [
            'journal_name' => $journalName,
            'journal_full_name' => $journalFullName,
            'journal_description' => $journalDescription,
            'journal_issn' => $journalIssn,
            'journal_eissn' => $journalEissn,
            'journal_doi_prefix' => $journalDoiPrefix,
            'journal_publisher' => $journalPublisher,
            'journal_email' => $journalEmail,
            'journal_phone' => $journalPhone,
            'journal_address' => $journalAddress,
            'journal_website' => $journalWebsite,
            'journal_submission_fee' => $journalSubmissionFee,
            'journal_publication_fee' => $journalPublicationFee,
            'journal_currency' => $journalCurrency,
            'journal_updated_at' => date('Y-m-d H:i:s')
        ];
        
        $success = $this->journalModel->updateJournalDetails($journalData);
        
        if (!$success) {
            setFlashMessage('error', 'Failed to update journal settings');
            redirect(config('app.url') . 'admin/journal/settings');
            return;
        }
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'journal_details_updated',
            'activity_description' => 'Updated journal settings'
        ]);
        
        setFlashMessage('success', 'Journal settings updated successfully');
        redirect(config('app.url') . 'admin/journal/settings');
    }
    
    /**
     * Display journal appearance settings page (admin only)
     */
    public function appearanceSettings() {
        requireAdmin();
        
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('admin/journal/appearance', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Appearance Settings | Admin'
        ]);
    }
    
    /**
     * Update journal appearance settings (admin only)
     */
    public function updateAppearance() {
        requireAdmin();
        checkCSRF();
        
        $journalTheme = $_POST['journal_theme'] ?? 'default';
        $journalPrimaryColor = $_POST['journal_primary_color'] ?? '#007bff';
        $journalSecondaryColor = $_POST['journal_secondary_color'] ?? '#6c757d';
        $journalFooterText = $_POST['journal_footer_text'] ?? '';
        
        // Handle logo upload
        $journalLogo = $_POST['journal_logo_current'] ?? '';
        
        if (isset($_FILES['journal_logo']) && $_FILES['journal_logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['journal_logo'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                setFlashMessage('error', 'Invalid logo file type. Allowed types: JPG, PNG, GIF');
                redirect(config('app.url') . 'admin/journal/appearance');
                return;
            }
            
            if ($file['size'] > 2 * 1024 * 1024) { // 2MB
                setFlashMessage('error', 'Logo file size exceeds the limit (2MB)');
                redirect(config('app.url') . 'admin/journal/appearance');
                return;
            }
            
            $uploadDir = ROOT_PATH . '/public/uploads/';
            $fileName = 'journal_logo_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Delete old logo if it exists
                if (!empty($journalLogo) && file_exists($uploadDir . $journalLogo)) {
                    unlink($uploadDir . $journalLogo);
                }
                
                $journalLogo = $fileName;
            } else {
                setFlashMessage('error', 'Failed to upload logo');
                redirect(config('app.url') . 'admin/journal/appearance');
                return;
            }
        }
        
        // Handle favicon upload
        $journalFavicon = $_POST['journal_favicon_current'] ?? '';
        
        if (isset($_FILES['journal_favicon']) && $_FILES['journal_favicon']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['journal_favicon'];
            $allowedExtensions = ['ico', 'png'];
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                setFlashMessage('error', 'Invalid favicon file type. Allowed types: ICO, PNG');
                redirect(config('app.url') . 'admin/journal/appearance');
                return;
            }
            
            if ($file['size'] > 1 * 1024 * 1024) { // 1MB
                setFlashMessage('error', 'Favicon file size exceeds the limit (1MB)');
                redirect(config('app.url') . 'admin/journal/appearance');
                return;
            }
            
            $uploadDir = ROOT_PATH . '/public/uploads/';
            $fileName = 'journal_favicon_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Delete old favicon if it exists
                if (!empty($journalFavicon) && file_exists($uploadDir . $journalFavicon)) {
                    unlink($uploadDir . $journalFavicon);
                }
                
                $journalFavicon = $fileName;
            } else {
                setFlashMessage('error', 'Failed to upload favicon');
                redirect(config('app.url') . 'admin/journal/appearance');
                return;
            }
        }
        
        // Update journal appearance settings
        $journalData = [
            'journal_theme' => $journalTheme,
            'journal_primary_color' => $journalPrimaryColor,
            'journal_secondary_color' => $journalSecondaryColor,
            'journal_footer_text' => $journalFooterText,
            'journal_logo' => $journalLogo,
            'journal_favicon' => $journalFavicon,
            'journal_updated_at' => date('Y-m-d H:i:s')
        ];
        
        $success = $this->journalModel->updateJournalDetails($journalData);
        
        if (!$success) {
            setFlashMessage('error', 'Failed to update appearance settings');
            redirect(config('app.url') . 'admin/journal/appearance');
            return;
        }
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'journal_appearance_updated',
            'activity_description' => 'Updated journal appearance settings'
        ]);
        
        setFlashMessage('success', 'Appearance settings updated successfully');
        redirect(config('app.url') . 'admin/journal/appearance');
    }
    
    /**
     * Display journal content settings page (admin only)
     */
    public function contentSettings() {
        requireAdmin();
        
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('admin/journal/content', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Content Settings | Admin'
        ]);
    }
    
    /**
     * Update journal content settings (admin only)
     */
    public function updateContent() {
        requireAdmin();
        checkCSRF();
        
        $aboutContent = $_POST['journal_about_content'] ?? '';
        $submissionGuidelinesContent = $_POST['journal_submission_guidelines'] ?? '';
        $authorGuidelinesContent = $_POST['journal_author_guidelines'] ?? '';
        $reviewerGuidelinesContent = $_POST['journal_reviewer_guidelines'] ?? '';
        $ethicsPolicyContent = $_POST['journal_ethics_policy'] ?? '';
        $peerReviewProcessContent = $_POST['journal_peer_review_process'] ?? '';
        $editorialBoardContent = $_POST['journal_editorial_board'] ?? '';
        $contactContent = $_POST['journal_contact_content'] ?? '';
        $privacyPolicyContent = $_POST['journal_privacy_policy'] ?? '';
        $termsContent = $_POST['journal_terms_conditions'] ?? '';
        
        // Update journal content settings
        $journalData = [
            'journal_about_content' => $aboutContent,
            'journal_submission_guidelines' => $submissionGuidelinesContent,
            'journal_author_guidelines' => $authorGuidelinesContent,
            'journal_reviewer_guidelines' => $reviewerGuidelinesContent,
            'journal_ethics_policy' => $ethicsPolicyContent,
            'journal_peer_review_process' => $peerReviewProcessContent,
            'journal_editorial_board' => $editorialBoardContent,
            'journal_contact_content' => $contactContent,
            'journal_privacy_policy' => $privacyPolicyContent,
            'journal_terms_conditions' => $termsContent,
            'journal_updated_at' => date('Y-m-d H:i:s')
        ];
        
        $success = $this->journalModel->updateJournalDetails($journalData);
        
        if (!$success) {
            setFlashMessage('error', 'Failed to update content settings');
            redirect(config('app.url') . 'admin/journal/content');
            return;
        }
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'journal_content_updated',
            'activity_description' => 'Updated journal content settings'
        ]);
        
        setFlashMessage('success', 'Content settings updated successfully');
        redirect(config('app.url') . 'admin/journal/content');
    }
    
    /**
     * Display journal email settings page (admin only)
     */
    public function emailSettings() {
        requireAdmin();
        
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('admin/journal/email', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Email Settings | Admin'
        ]);
    }
    
    /**
     * Update journal email settings (admin only)
     */
    public function updateEmailSettings() {
        requireAdmin();
        checkCSRF();
        
        $smtpHost = $_POST['journal_smtp_host'] ?? '';
        $smtpPort = $_POST['journal_smtp_port'] ?? '';
        $smtpUsername = $_POST['journal_smtp_username'] ?? '';
        $smtpPassword = $_POST['journal_smtp_password'] ?? '';
        $smtpEncryption = $_POST['journal_smtp_encryption'] ?? '';
        $emailFromAddress = $_POST['journal_email_from_address'] ?? '';
        $emailFromName = $_POST['journal_email_from_name'] ?? '';
        
        // Validate input
        $errors = [];
        
        if (empty($smtpHost)) {
            $errors[] = 'SMTP host is required';
        }
        
        if (empty($smtpPort)) {
            $errors[] = 'SMTP port is required';
        }
        
        if (empty($emailFromAddress)) {
            $errors[] = 'From email address is required';
        } elseif (!filter_var($emailFromAddress, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid from email address format';
        }
        
        if (empty($emailFromName)) {
            $errors[] = 'From name is required';
        }
        
        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect(config('app.url') . 'admin/journal/email');
            return;
        }
        
        // Update journal email settings
        $journalData = [
            'journal_smtp_host' => $smtpHost,
            'journal_smtp_port' => $smtpPort,
            'journal_smtp_username' => $smtpUsername,
            'journal_smtp_encryption' => $smtpEncryption,
            'journal_email_from_address' => $emailFromAddress,
            'journal_email_from_name' => $emailFromName,
            'journal_updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Only update password if provided
        if (!empty($smtpPassword)) {
            $journalData['journal_smtp_password'] = $smtpPassword;
        }
        
        $success = $this->journalModel->updateJournalDetails($journalData);
        
        if (!$success) {
            setFlashMessage('error', 'Failed to update email settings');
            redirect(config('app.url') . 'admin/journal/email');
            return;
        }
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'journal_email_settings_updated',
            'activity_description' => 'Updated journal email settings'
        ]);
        
        setFlashMessage('success', 'Email settings updated successfully');
        redirect(config('app.url') . 'admin/journal/email');
    }
    
    /**
     * Display journal payment settings page (admin only)
     */
    public function paymentSettings() {
        requireAdmin();
        
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('admin/journal/payment', [
            'journalDetails' => $journalDetails,
            'pageTitle' => 'Payment Settings | Admin'
        ]);
    }
    
    /**
     * Update journal payment settings (admin only)
     */
    public function updatePaymentSettings() {
        requireAdmin();
        checkCSRF();
        
        $enablePayments = isset($_POST['journal_enable_payments']) ? 1 : 0;
        $submissionFee = $_POST['journal_submission_fee'] ?? 0;
        $publicationFee = $_POST['journal_publication_fee'] ?? 0;
        $currency = $_POST['journal_currency'] ?? 'USD';
        $paypalClientId = $_POST['journal_paypal_client_id'] ?? '';
        $paypalSecret = $_POST['journal_paypal_secret'] ?? '';
        $paypalSandbox = isset($_POST['journal_paypal_sandbox']) ? 1 : 0;
        $stripePublishableKey = $_POST['journal_stripe_publishable_key'] ?? '';
        $stripeSecretKey = $_POST['journal_stripe_secret_key'] ?? '';
        $stripeTestMode = isset($_POST['journal_stripe_test_mode']) ? 1 : 0;
        
        // Update journal payment settings
        $journalData = [
            'journal_enable_payments' => $enablePayments,
            'journal_submission_fee' => $submissionFee,
            'journal_publication_fee' => $publicationFee,
            'journal_currency' => $currency,
            'journal_paypal_client_id' => $paypalClientId,
            'journal_paypal_sandbox' => $paypalSandbox,
            'journal_stripe_publishable_key' => $stripePublishableKey,
            'journal_stripe_test_mode' => $stripeTestMode,
            'journal_updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Only update secrets if provided
        if (!empty($paypalSecret)) {
            $journalData['journal_paypal_secret'] = $paypalSecret;
        }
        
        if (!empty($stripeSecretKey)) {
            $journalData['journal_stripe_secret_key'] = $stripeSecretKey;
        }
        
        $success = $this->journalModel->updateJournalDetails($journalData);
        
        if (!$success) {
            setFlashMessage('error', 'Failed to update payment settings');
            redirect(config('app.url') . 'admin/journal/payment');
            return;
        }
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'journal_payment_settings_updated',
            'activity_description' => 'Updated journal payment settings'
        ]);
        
        setFlashMessage('success', 'Payment settings updated successfully');
        redirect(config('app.url') . 'admin/journal/payment');
    }
    
    /**
     * Display journal statistics
     */
    public function statistics() {
        requireAdmin();
        
        $stats = $this->journalModel->getJournalStatistics();
        
        // Get papers by status
        $papersByStatus = [
            'submitted' => $this->paperModel->countPapers(['status' => Paper::STATUS_SUBMITTED]),
            'under_review' => $this->paperModel->countPapers(['status' => Paper::STATUS_UNDER_REVIEW]),
            'revision_requested' => $this->paperModel->countPapers(['status' => Paper::STATUS_REVISION_REQUESTED]),
            'accepted' => $this->paperModel->countPapers(['status' => Paper::STATUS_ACCEPTED]),
            'rejected' => $this->paperModel->countPapers(['status' => Paper::STATUS_REJECTED]),
            'published' => $this->paperModel->countPapers(['status' => Paper::STATUS_PUBLISHED]),
            'withdrawn' => $this->paperModel->countPapers(['status' => Paper::STATUS_WITHDRAWN])
        ];
        
        render('admin/journal/statistics', [
            'stats' => $stats,
            'papersByStatus' => $papersByStatus,
            'pageTitle' => 'Journal Statistics | Admin'
        ]);
    }

    // controllers/JournalController.php

/**
 * Display call for papers page
 */
/**
 * Display call for papers page
 */
public function callForPapers() {
    $journalDetails = $this->journalModel->getJournalDetails();
    
    // Get active call for papers from journal details
    $cfpDetails = [
        'cfp_title' => $journalDetails['cfp_title'] ?? 'Call for Papers',
        'cfp_content' => $journalDetails['cfp_content'] ?? '',
        'cfp_topics' => json_decode($journalDetails['cfp_topics'] ?? '[]', true),
        'cfp_deadline' => $journalDetails['cfp_deadline'] ?? null,
        'cfp_notification_date' => $journalDetails['cfp_notification_date'] ?? null,
        'cfp_camera_ready_date' => $journalDetails['cfp_camera_ready_date'] ?? null,
        'cfp_publication_date' => $journalDetails['cfp_publication_date'] ?? null,
        'cfp_active' => $journalDetails['cfp_active'] ?? 0
    ];
    
    render('journal/call_for_papers', [
        'journalDetails' => $journalDetails,
        'cfpDetails' => $cfpDetails,
        'pageTitle' => 'Call for Papers | ' . $journalDetails['journal_full_name']
    ]);
}

// In controllers/JournalController.php

public function guidelines($type = 'author') {
    $pageTitle = '';
    $template = '';
    
    switch($type) {
        case 'author':
            $pageTitle = 'Author Guidelines';
            $template = 'journal/guidelines/author';
            break;
        case 'reviewer':
            $pageTitle = 'Reviewer Guidelines';
            $template = 'journal/guidelines/reviewer';
            break;
        case 'ethics':
            $pageTitle = 'Publication Ethics';
            $template = 'journal/guidelines/ethics';
            break;
        default:
            $pageTitle = 'Guidelines';
            $template = 'journal/guidelines/author';
    }

    render($template, [
        'pageTitle' => $pageTitle,
        'type' => $type
    ]);
}



}
