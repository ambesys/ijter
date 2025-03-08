<?php
// controllers/PaymentController.php

class PaymentController {
    private $pdo;
    private $paymentModel;
    private $paperModel;
    private $userModel;
    private $activityModel;
    private $notificationModel;
    private $journalModel;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->paymentModel = new Payment($pdo);
        $this->paperModel = new Paper($pdo);
        $this->userModel = new User($pdo);
        $this->activityModel = new Activity($pdo);
        $this->notificationModel = new Notification($pdo);
        $this->journalModel = new Journal($pdo);
    }
    
    /**
     * Display payment form for a paper
     * 
     * @param string $paperId Paper ID
     */
    public function paymentForm($paperId) {
        requireLogin();
        
        $paper = $this->paperModel->getPaperById($paperId);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user is the author of this paper
        if ($paper['paper_author_id'] != getCurrentUserId() && !isAdmin() && !isModerator()) {
            setFlashMessage('error', 'You do not have permission to make payment for this paper');
            redirect(config('app.url'));
            return;
        }
        
        // Check if payment is already completed
        if ($this->paymentModel->hasPaperBeenPaid($paperId)) {
            setFlashMessage('info', 'Payment has already been completed for this paper');
            redirect(config('app.url') . 'papers/view/' . $paperId);
            return;
        }
        
        // Get journal details for payment settings
        $journalDetails = $this->journalModel->getJournalDetails();
        
        // Check if payments are enabled
        if ($journalDetails['journal_enable_payments'] != 1) {
            setFlashMessage('error', 'Payments are currently disabled');
            redirect(config('app.url') . 'papers/view/' . $paperId);
            return;
        }
        
        // Determine fee based on paper status
        $fee = 0;
        $feeType = '';
        
        if ($paper['paper_status'] === Paper::STATUS_SUBMITTED) {
            $fee = $journalDetails['journal_submission_fee'];
            $feeType = 'submission';
        } elseif ($paper['paper_status'] === Paper::STATUS_ACCEPTED) {
            $fee = $journalDetails['journal_publication_fee'];
            $feeType = 'publication';
        }
        
        render('payments/form', [
            'paper' => $paper,
            'journalDetails' => $journalDetails,
            'fee' => $fee,
            'feeType' => $feeType
        ]);
    }
    
    /**
     * Process payment
     * 
     * @param string $paperId Paper ID
     */
    public function processPayment($paperId) {
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
            setFlashMessage('error', 'You do not have permission to make payment for this paper');
            redirect(config('app.url'));
            return;
        }
        
        // Check if payment is already completed
        if ($this->paymentModel->hasPaperBeenPaid($paperId)) {
            setFlashMessage('info', 'Payment has already been completed for this paper');
            redirect(config('app.url') . 'papers/view/' . $paperId);
            return;
        }
        
        // Get journal details for payment settings
        $journalDetails = $this->journalModel->getJournalDetails();
        
        // Check if payments are enabled
        if ($journalDetails['journal_enable_payments'] != 1) {
            setFlashMessage('error', 'Payments are currently disabled');
            redirect(config('app.url') . 'papers/view/' . $paperId);
            return;
        }
        
        $paymentMethod = $_POST['payment_method'] ?? '';
        
        // Determine fee based on paper status
        $fee = 0;
        $feeType = '';
        
        if ($paper['paper_status'] === Paper::STATUS_SUBMITTED) {
            $fee = $journalDetails['journal_submission_fee'];
            $feeType = 'submission';
        } elseif ($paper['paper_status'] === Paper::STATUS_ACCEPTED) {
            $fee = $journalDetails['journal_publication_fee'];
            $feeType = 'publication';
        }
        
        // Create payment record
        $paymentData = [
            'payment_paper_id' => $paperId,
            'payment_user_id' => getCurrentUserId(),
            'payment_amount' => $fee,
            'payment_currency' => $journalDetails['journal_currency'],
            'payment_method' => $paymentMethod,
            'payment_type' => $feeType,
            'payment_status' => Payment::STATUS_PENDING,
            'payment_created_at' => date('Y-m-d H:i:s')
        ];
        
        $paymentId = $this->paymentModel->createPayment($paymentData);
        
        if (!$paymentId) {
            setFlashMessage('error', 'Failed to create payment record');
            redirect(config('app.url') . 'payments/pay/' . $paperId);
            return;
        }
        
        // Process payment based on method
        switch ($paymentMethod) {
            case Payment::METHOD_PAYPAL:
                redirect(config('app.url') . 'payments/paypal/' . $paymentId);
                break;
                
            case Payment::METHOD_CREDIT_CARD:
                redirect(config('app.url') . 'payments/stripe/' . $paymentId);
                break;
                
            case Payment::METHOD_BANK_TRANSFER:
                redirect(config('app.url') . 'payments/bank-transfer/' . $paymentId);
                break;
                
            case Payment::METHOD_WAIVER:
                redirect(config('app.url') . 'payments/waiver/' . $paymentId);
                break;
                
            default:
                setFlashMessage('error', 'Invalid payment method');
                redirect(config('app.url') . 'payments/pay/' . $paperId);
                break;
        }
    }
    
    /**
     * Process PayPal payment
     * 
     * @param int $paymentId Payment ID
     */
    public function paypalPayment($paymentId) {
        requireLogin();
        
        $payment = $this->paymentModel->getPaymentById($paymentId);
        
        if (!$payment) {
            setFlashMessage('error', 'Payment not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user is the owner of this payment
        if ($payment['payment_user_id'] != getCurrentUserId() && !isAdmin() && !isModerator()) {
            setFlashMessage('error', 'You do not have permission to access this payment');
            redirect(config('app.url'));
            return;
        }
        
        // Get paper details
        $paper = $this->paperModel->getPaperById($payment['payment_paper_id']);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Get journal details for payment settings
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('payments/paypal', [
            'payment' => $payment,
            'paper' => $paper,
            'journalDetails' => $journalDetails
        ]);
    }
    
    /**
     * Process PayPal payment success
     */
    public function paypalSuccess() {
        $paymentId = $_GET['payment_id'] ?? '';
        $paypalPaymentId = $_GET['paypal_payment_id'] ?? '';
        $payerId = $_GET['payer_id'] ?? '';
        
        if (empty($paymentId) || empty($paypalPaymentId) || empty($payerId)) {
            setFlashMessage('error', 'Invalid payment data');
            redirect(config('app.url'));
            return;
        }
        
        $payment = $this->paymentModel->getPaymentById($paymentId);
        
        if (!$payment) {
            setFlashMessage('error', 'Payment not found');
            redirect(config('app.url'));
            return;
        }
        
        // Update payment status
        $this->paymentModel->updatePaymentStatus($paymentId, Payment::STATUS_COMPLETED, $paypalPaymentId);
        
        // Update paper payment status
        $this->paperModel->updatePaymentStatus($payment['payment_paper_id'], Payment::STATUS_COMPLETED);
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => $payment['payment_user_id'],
            'activity_action' => 'payment_completed',
            'activity_description' => 'Completed payment for paper: ' . $payment['payment_paper_id'],
            'activity_data' => json_encode([
                'payment_id' => $paymentId,
                'payment_method' => Payment::METHOD_PAYPAL,
                'transaction_id' => $paypalPaymentId
            ])
        ]);
        
        // Notify user
        $this->notificationModel->sendNotification(
            $payment['payment_user_id'],
            Notification::TYPE_PAYMENT_RECEIVED,
            'Your payment has been received for paper: ' . $payment['payment_paper_id'],
            [
                'payment_id' => $paymentId,
                'paper_id' => $payment['payment_paper_id'],
                'amount' => $payment['payment_amount'],
                'currency' => $payment['payment_currency']
            ]
        );
        
        // Notify admins
        $this->notificationModel->sendNotificationToAdmins(
            Notification::TYPE_PAYMENT_RECEIVED,
            'Payment received for paper: ' . $payment['payment_paper_id'],
            [
                'payment_id' => $paymentId,
                'paper_id' => $payment['payment_paper_id'],
                'user_id' => $payment['payment_user_id'],
                'amount' => $payment['payment_amount'],
                'currency' => $payment['payment_currency']
            ]
        );
        
        setFlashMessage('success', 'Payment completed successfully');
        redirect(config('app.url') . 'papers/view/' . $payment['payment_paper_id']);
    }
    
    /**
     * Process PayPal payment cancel
     */
    public function paypalCancel() {
        $paymentId = $_GET['payment_id'] ?? '';
        
        if (empty($paymentId)) {
            setFlashMessage('error', 'Invalid payment data');
            redirect(config('app.url'));
            return;
        }
        
        $payment = $this->paymentModel->getPaymentById($paymentId);
        
        if (!$payment) {
            setFlashMessage('error', 'Payment not found');
            redirect(config('app.url'));
            return;
        }
        
        // Update payment status
        $this->paymentModel->updatePaymentStatus($paymentId, Payment::STATUS_FAILED);
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => $payment['payment_user_id'],
            'activity_action' => 'payment_cancelled',
            'activity_description' => 'Cancelled payment for paper: ' . $payment['payment_paper_id'],
            'activity_data' => json_encode([
                'payment_id' => $paymentId,
                'payment_method' => Payment::METHOD_PAYPAL
            ])
        ]);
        
        setFlashMessage('info', 'Payment was cancelled');
        redirect(config('app.url') . 'payments/pay/' . $payment['payment_paper_id']);
    }
    
    /**
     * Process Stripe payment
     * 
     * @param int $paymentId Payment ID
     */
    public function stripePayment($paymentId) {
        requireLogin();
        
        $payment = $this->paymentModel->getPaymentById($paymentId);
        
        if (!$payment) {
            setFlashMessage('error', 'Payment not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user is the owner of this payment
        if ($payment['payment_user_id'] != getCurrentUserId() && !isAdmin() && !isModerator()) {
            setFlashMessage('error', 'You do not have permission to access this payment');
            redirect(config('app.url'));
            return;
        }
        
        // Get paper details
        $paper = $this->paperModel->getPaperById($payment['payment_paper_id']);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Get journal details for payment settings
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('payments/stripe', [
            'payment' => $payment,
            'paper' => $paper,
            'journalDetails' => $journalDetails
        ]);
    }
    
    /**
     * Process Stripe payment success
     */
    public function stripeSuccess() {
        $paymentId = $_POST['payment_id'] ?? '';
        $stripeToken = $_POST['stripe_token'] ?? '';
        
        if (empty($paymentId) || empty($stripeToken)) {
            setFlashMessage('error', 'Invalid payment data');
            redirect(config('app.url'));
            return;
        }
        
        $payment = $this->paymentModel->getPaymentById($paymentId);
        
        if (!$payment) {
            setFlashMessage('error', 'Payment not found');
            redirect(config('app.url'));
            return;
        }
        
        // Update payment status
        $this->paymentModel->updatePaymentStatus($paymentId, Payment::STATUS_COMPLETED, $stripeToken);
        
        // Update paper payment status
        $this->paperModel->updatePaymentStatus($payment['payment_paper_id'], Payment::STATUS_COMPLETED);
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => $payment['payment_user_id'],
            'activity_action' => 'payment_completed',
            'activity_description' => 'Completed payment for paper: ' . $payment['payment_paper_id'],
            'activity_data' => json_encode([
                'payment_id' => $paymentId,
                'payment_method' => Payment::METHOD_CREDIT_CARD,
                'transaction_id' => $stripeToken
            ])
        ]);
        
        // Notify user
        $this->notificationModel->sendNotification(
            $payment['payment_user_id'],
            Notification::TYPE_PAYMENT_RECEIVED,
            'Your payment has been received for paper: ' . $payment['payment_paper_id'],
            [
                'payment_id' => $paymentId,
                'paper_id' => $payment['payment_paper_id'],
                'amount' => $payment['payment_amount'],
                'currency' => $payment['payment_currency']
            ]
        );
        
        // Notify admins
        $this->notificationModel->sendNotificationToAdmins(
            Notification::TYPE_PAYMENT_RECEIVED,
            'Payment received for paper: ' . $payment['payment_paper_id'],
            [
                'payment_id' => $paymentId,
                'paper_id' => $payment['payment_paper_id'],
                'user_id' => $payment['payment_user_id'],
                'amount' => $payment['payment_amount'],
                'currency' => $payment['payment_currency']
            ]
        );
        
        setFlashMessage('success', 'Payment completed successfully');
        redirect(config('app.url') . 'papers/view/' . $payment['payment_paper_id']);
    }
    
    /**
     * Display bank transfer instructions
     * 
     * @param int $paymentId Payment ID
     */
    public function bankTransfer($paymentId) {
        requireLogin();
        
        $payment = $this->paymentModel->getPaymentById($paymentId);
        
        if (!$payment) {
            setFlashMessage('error', 'Payment not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user is the owner of this payment
        if ($payment['payment_user_id'] != getCurrentUserId() && !isAdmin() && !isModerator()) {
            setFlashMessage('error', 'You do not have permission to access this payment');
            redirect(config('app.url'));
            return;
        }
        
        // Get paper details
        $paper = $this->paperModel->getPaperById($payment['payment_paper_id']);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        // Get journal details for payment settings
        $journalDetails = $this->journalModel->getJournalDetails();
        
        render('payments/bank_transfer', [
            'payment' => $payment,
            'paper' => $paper,
            'journalDetails' => $journalDetails
        ]);
    }
    
    /**
     * Display fee waiver request form
     * 
     * @param int $paymentId Payment ID
     */
    public function waiverForm($paymentId) {
        requireLogin();
        
        $payment = $this->paymentModel->getPaymentById($paymentId);
        
        if (!$payment) {
            setFlashMessage('error', 'Payment not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user is the owner of this payment
        if ($payment['payment_user_id'] != getCurrentUserId() && !isAdmin() && !isModerator()) {
            setFlashMessage('error', 'You do not have permission to access this payment');
            redirect(config('app.url'));
            return;
        }
        
        // Get paper details
        $paper = $this->paperModel->getPaperById($payment['payment_paper_id']);
        
        if (!$paper) {
            setFlashMessage('error', 'Paper not found');
            redirect(config('app.url'));
            return;
        }
        
        render('payments/waiver', [
            'payment' => $payment,
            'paper' => $paper
        ]);
    }
    
    /**
     * Process fee waiver request
     * 
     * @param int $paymentId Payment ID
     */
    public function requestWaiver($paymentId) {
        requireLogin();
        checkCSRF();
        
        $payment = $this->paymentModel->getPaymentById($paymentId);
        
        if (!$payment) {
            setFlashMessage('error', 'Payment not found');
            redirect(config('app.url'));
            return;
        }
        
        // Check if user is the owner of this payment
        if ($payment['payment_user_id'] != getCurrentUserId() && !isAdmin() && !isModerator()) {
            setFlashMessage('error', 'You do not have permission to access this payment');
            redirect(config('app.url'));
            return;
        }
        
        $reason = $_POST['reason'] ?? '';
        
        if (empty($reason)) {
            setFlashMessage('error', 'Waiver reason is required');
            redirect(config('app.url') . 'payments/waiver/' . $paymentId);
            return;
        }
        
        // Update payment with waiver request
        $this->paymentModel->updatePayment($paymentId, [
            'payment_waiver_reason' => $reason,
            'payment_waiver_requested_at' => date('Y-m-d H:i:s')
        ]);
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => $payment['payment_user_id'],
            'activity_action' => 'waiver_requested',
            'activity_description' => 'Requested fee waiver for paper: ' . $payment['payment_paper_id'],
            'activity_data' => json_encode([
                'payment_id' => $paymentId,
                'reason' => $reason
            ])
        ]);
        
        // Notify admins
        $this->notificationModel->sendNotificationToAdmins(
            Notification::TYPE_SYSTEM,
            'Fee waiver requested for paper: ' . $payment['payment_paper_id'],
            [
                'payment_id' => $paymentId,
                'paper_id' => $payment['payment_paper_id'],
                'user_id' => $payment['payment_user_id'],
                'amount' => $payment['payment_amount'],
                'currency' => $payment['payment_currency'],
                'reason' => $reason
            ]
        );
        
        setFlashMessage('success', 'Fee waiver request submitted successfully');
        redirect(config('app.url') . 'papers/view/' . $payment['payment_paper_id']);
    }
    
    /**
     * Display all payments (admin only)
     */
    public function allPayments() {
        requireAdmin();
        
        $status = $_GET['status'] ?? null;
        $method = $_GET['method'] ?? null;
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $filters = [];
        
        if ($status) {
            $filters['status'] = $status;
        }
        
        if ($method) {
            $filters['method'] = $method;
        }
        
        $payments = $this->paymentModel->getAllPayments($limit, $offset, $filters);
        $totalPayments = $this->paymentModel->countPayments($filters);
        $totalPages = ceil($totalPayments / $limit);
        
        render('admin/payments/index', [
            'payments' => $payments,
            'status' => $status,
            'method' => $method,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalPayments' => $totalPayments
        ]);
    }
    
    /**
     * Display payment details (admin only)
     * 
     * @param int $paymentId Payment ID
     */
    public function viewPayment($paymentId) {
        requireAdmin();
        
        $payment = $this->paymentModel->getPaymentById($paymentId);
        
        if (!$payment) {
            setFlashMessage('error', 'Payment not found');
            redirect(config('app.url') . 'admin/payments');
            return;
        }
        
        // Get paper details
        $paper = $this->paperModel->getPaperById($payment['payment_paper_id']);
        
        // Get user details
        $user = $this->userModel->getUserById($payment['payment_user_id']);
        
        render('admin/payments/view', [
            'payment' => $payment,
            'paper' => $paper,
            'user' => $user
        ]);
    }
    
    /**
     * Update payment status (admin only)
     * 
     * @param int $paymentId Payment ID
     */
    public function updatePaymentStatus($paymentId) {
        requireAdmin();
        checkCSRF();
        
        $payment = $this->paymentModel->getPaymentById($paymentId);
        
        if (!$payment) {
            setFlashMessage('error', 'Payment not found');
            redirect(config('app.url') . 'admin/payments');
            return;
        }
        
        $status = $_POST['status'] ?? '';
        $transactionId = $_POST['transaction_id'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (empty($status)) {
            setFlashMessage('error', 'Status is required');
            redirect(config('app.url') . 'admin/payments/view/' . $paymentId);
            return;
        }
        
        // Update payment status
        $this->paymentModel->updatePayment($paymentId, [
            'payment_status' => $status,
            'payment_transaction_id' => $transactionId,
            'payment_notes' => $notes,
            'payment_updated_at' => date('Y-m-d H:i:s'),
            'payment_updated_by' => getCurrentUserId()
        ]);
        
        // If payment is completed, update paper payment status
        if ($status === Payment::STATUS_COMPLETED || $status === Payment::STATUS_WAIVED) {
            $this->paperModel->updatePaymentStatus($payment['payment_paper_id'], $status);
        }
        
        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'payment_status_updated',
                   'activity_description' => 'Updated payment status for paper: ' . $payment['payment_paper_id'] . ' to ' . $status,
            'activity_data' => json_encode([
                'payment_id' => $paymentId,
                'status' => $status,
                'transaction_id' => $transactionId,
                'notes' => $notes
            ])
        ]);
        
        // Notify user
        $notificationType = $status === Payment::STATUS_COMPLETED || $status === Payment::STATUS_WAIVED
            ? Notification::TYPE_PAYMENT_RECEIVED
            : Notification::TYPE_SYSTEM;
            
        $notificationMessage = $status === Payment::STATUS_COMPLETED
            ? 'Your payment has been confirmed for paper: ' . $payment['payment_paper_id']
            : ($status === Payment::STATUS_WAIVED
                ? 'Your fee waiver has been approved for paper: ' . $payment['payment_paper_id']
                : 'Your payment status has been updated for paper: ' . $payment['payment_paper_id']);
        
        $this->notificationModel->sendNotification(
            $payment['payment_user_id'],
            $notificationType,
            $notificationMessage,
            [
                'payment_id' => $paymentId,
                'paper_id' => $payment['payment_paper_id'],
                'status' => $status,
                'notes' => $notes
            ]
        );
        
        setFlashMessage('success', 'Payment status updated successfully');
        redirect(config('app.url') . 'admin/payments/view/' . $paymentId);
    }
    
    /**
     * Display payment statistics (admin only)
     */
    public function statistics() {
        requireAdmin();
        
        $period = $_GET['period'] ?? 'month';
        $stats = $this->paymentModel->getPaymentStatistics($period);
        
        render('admin/payments/statistics', [
            'stats' => $stats,
            'period' => $period
        ]);
    }
    
    /**
     * Display user payments
     */
    public function userPayments() {
        requireLogin();
        
        $userId = getCurrentUserId();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $payments = $this->paymentModel->getPaymentsByUserId($userId, $limit, $offset);
        
        render('payments/user_payments', [
            'payments' => $payments
        ]);
    }
}


