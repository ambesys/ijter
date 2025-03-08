<?php
// core/auth.php

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * 
 * @return bool True if admin, false otherwise
 */
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_is_admin'] === 1;
}

/**
 * Check if user is moderator
 * 
 * @return bool True if moderator, false otherwise
 */
function isModerator() {
    return isLoggedIn() && ($_SESSION['user_is_moderator'] === 1 || $_SESSION['user_is_admin'] === 1);
}

/**
 * Check if user is reviewer
 * 
 * @return bool True if reviewer, false otherwise
 */
function isReviewer() {
    return isLoggedIn() && ($_SESSION['user_is_reviewer'] === 1 || $_SESSION['user_is_admin'] === 1);
}

/**
 * Check if user is author
 * 
 * @return bool True if author, false otherwise
 */
function isAuthor() {
    return isLoggedIn() && $_SESSION['user_is_author'] === 1;
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user name
 * 
 * @return string|null User name or null if not logged in
 */
function getCurrentUserName() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $fname = $_SESSION['user_fname'] ?? '';
    $lname = $_SESSION['user_lname'] ?? '';
    
    return trim($fname . ' ' . $lname);
}

/**
 * Get current user email
 * 
 * @return string|null User email or null if not logged in
 */
function getCurrentUserEmail() {
    return $_SESSION['user_email'] ?? null;
}

/**
 * Get current user
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $userModel = model('user');
    return $userModel->getUserById(getCurrentUserId());
}

/**
 * Require login
 * 
 * @param string $redirect Redirect URL
 */
function requireLogin($redirect = null) {
    if (!isLoggedIn()) {
        $redirectUrl = $redirect ?? getCurrentUrl();
        redirect(config('app.url') . '/login?redirect=' . urlencode($redirectUrl));
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        setFlashMessage('error', 'You do not have permission to access this page.');
        redirect(config('app.url'));
    }
}

/**
 * Require moderator role
 */
function requireModerator() {
    requireLogin();
    
    if (!isModerator()) {
        setFlashMessage('error', 'You do not have permission to access this page.');
        redirect(config('app.url'));
    }
}

/**
 * Require reviewer role
 */
function requireReviewer() {
    requireLogin();
    
    if (!isReviewer()) {
        setFlashMessage('error', 'You do not have permission to access this page.');
        redirect(config('app.url'));
    }
}

/**
 * Require author role
 */
function requireAuthor() {
    requireLogin();
    
    if (!isAuthor()) {
        setFlashMessage('error', 'You need to be registered as an author to access this page.');
        redirect(config('app.url'));
    }
}

/**
 * Check remember me cookie and log in user
 */
function checkRememberMe() {
    if (isLoggedIn() || !isset($_COOKIE['remember_token'])) {
        return;
    }
    
    $token = $_COOKIE['remember_token'];
    
    $userModel = model('user');
    $user = $userModel->getUserByRememberToken($token);
    
    if ($user && strtotime($user['user_remember_token_expiry']) > time()) {
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_fname'] = $user['user_fname'];
        $_SESSION['user_lname'] = $user['user_lname'];
        $_SESSION['user_email'] = $user['user_email'];
        $_SESSION['user_is_author'] = $user['user_is_author'];
        $_SESSION['user_is_reviewer'] = $user['user_is_reviewer'];
        $_SESSION['user_is_admin'] = $user['user_is_admin'];
        $_SESSION['user_is_moderator'] = $user['user_is_moderator'];
        
        // Log activity
        $activityModel = model('activity');
        $activityModel->log([
            'user_id' => $user['user_id'],
            'activity_type' => 'login',
            'activity_description' => 'User logged in via remember me cookie'
        ]);
    }
}

/**
 * Generate CSRF token
 * 
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * 
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF token input field
 * 
 * @return string HTML input field
 */
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Check if CSRF token is valid
 * 
 * @return bool True if valid, false otherwise
 */
function checkCSRF() {
    $token = $_POST['csrf_token'] ?? '';
    
    if (!verifyCSRFToken($token)) {
        setFlashMessage('error', 'Invalid security token. Please try again.');
        redirect($_SERVER['HTTP_REFERER'] ?? config('app.url'));
    }
    
    return true;
}

/**
 * Check if user has permission to edit a paper
 * 
 * @param array $paper Paper data
 * @return bool True if has permission, false otherwise
 */
function canEditPaper($paper) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Admins and moderators can edit any paper
    if (isAdmin() || isModerator()) {
        return true;
    }
    
    // Authors can only edit their own papers and only if not published
    if ($paper['paper_author_id'] == getCurrentUserId() && $paper['paper_status'] != 'PUBLISHED') {
        return true;
    }
    
    return false;
}

/**
 * Check if user has permission to review a paper
 * 
 * @param array $paper Paper data
 * @return bool True if has permission, false otherwise
 */
function canReviewPaper($paper) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Admins and moderators can review any paper
    if (isAdmin() || isModerator()) {
        return true;
    }
    
    // Reviewers can only review papers assigned to them
    if (isReviewer()) {
        return $paper['paper_reviewer_id'] == getCurrentUserId();
    }
    
    return false;
}

/**
 * Check if user has permission to view a paper
 * 
 * @param array $paper Paper data
 * @return bool True if has permission, false otherwise
 */
function canViewPaper($paper) {
    // Published papers can be viewed by anyone
    if ($paper['paper_status'] === 'PUBLISHED') {
        return true;
    }
    
    if (!isLoggedIn()) {
        return false;
    }
    
    // Admins and moderators can view any paper
    if (isAdmin() || isModerator()) {
        return true;
    }
    
    // Authors can view their own papers
    if ($paper['paper_author_id'] == getCurrentUserId()) {
        return true;
    }
    
    // Co-authors can view the paper
    if (
        $paper['paper_co_author_1_id'] == getCurrentUserId() ||
        $paper['paper_co_author_2_id'] == getCurrentUserId() ||
        $paper['paper_co_author_3_id'] == getCurrentUserId() ||
        $paper['paper_co_author_4_id'] == getCurrentUserId()
    ) {
        return true;
    }
    
    // Reviewers can view papers assigned to them
    if (isReviewer() && $paper['paper_reviewer_id'] == getCurrentUserId()) {
        return true;
    }
    
    return false;
}

/**
 * Check if user email is verified
 * 
 * @return bool True if verified, false otherwise
 */
function isEmailVerified() {
    if (!isLoggedIn()) {
        return false;
    }
    
    return $_SESSION['user_is_verified'] === 1;
}

/**
 * Require verified email
 * 
 * @param string $redirect Redirect URL
 */
function requireVerifiedEmail($redirect = null) {
    requireLogin();
    
    if (!isEmailVerified()) {
        setFlashMessage('warning', 'Please verify your email address before continuing.');
        redirect($redirect ?? config('app.url') . 'auth/verify-email');
    }
}

/**
 * Log user out
 * 
 * @return void
 */
function logout() {
    // Clear remember me cookie if it exists
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Destroy session
    session_unset();
    session_destroy();
    
    // Redirect to home
    redirect(config('app.url'));
}

/**
 * Set user session
 * 
 * @param array $user User data
 * @param bool $remember Remember login
 * @return void
 */
function setUserSession($user, $remember = false) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user_fname'] = $user['user_fname'];
    $_SESSION['user_lname'] = $user['user_lname'];
    $_SESSION['user_email'] = $user['user_email'];
    $_SESSION['user_is_author'] = $user['user_is_author'];
    $_SESSION['user_is_reviewer'] = $user['user_is_reviewer'];
    $_SESSION['user_is_admin'] = $user['user_is_admin'];
    $_SESSION['user_is_moderator'] = $user['user_is_moderator'];
    $_SESSION['user_is_verified'] = $user['user_is_verified'];
    
    if ($remember) {
        $userModel = model('user');
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 days
        
        $userModel->updateRememberToken($user['user_id'], $token, $expiry);
        
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }
}

/**
 * Check if user has a specific permission
 * 
 * @param string $permission Permission to check
 * @return bool True if has permission, false otherwise
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }
    
    switch ($permission) {
        case 'manage_users':
        case 'manage_settings':
        case 'manage_journal':
            return isAdmin();
            
        case 'manage_papers':
        case 'manage_reviews':
        case 'assign_reviewers':
            return isAdmin() || isModerator();
            
        case 'submit_paper':
            return isAuthor();
            
        case 'review_paper':
            return isReviewer();
            
        default:
            return false;
    }
}

/**
 * Require permission
 * 
 * @param string $permission Permission to require
 */
function requirePermission($permission) {
    requireLogin();
    
    if (!hasPermission($permission)) {
        setFlashMessage('error', 'You do not have permission to perform this action.');
        redirect(config('app.url'));
    }
}

// Check remember me cookie on script execution
checkRememberMe();
