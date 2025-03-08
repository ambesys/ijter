<?php
// controllers/AuthController.php

class AuthController
{
    private $pdo;
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 1800; // 30 minutes in seconds

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function loginForm()
    {
        if (Helper::isLoggedIn()) {
            header('Location: ' . config('app.url') . 'user/dashboard');
            //    echo Helper::view('users/dashboard');
            exit;
        } else {
            echo Helper::view('auth/login');
        }
    }


public function login() {
    try {
        // Validate CSRF token
        if (!Helper::verifyCsrfToken($_POST['csrf_token'])) {
            error_log('ELP001: Invalid CSRF token');
            throw new Exception('ELP001: Invalid CSRF token');
        }

        // Validate input
        $validator = new Validator($_POST, $this->pdo);
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required'],
            'captcha' => ['required']
        ];

        if (!$validator->validate($rules)) {
            $_SESSION['error'] = $validator->getFirstError();
            $_SESSION['old'] = $_POST;
            error_log('ELP002: Invalid Credentials - failure at validator');
            header('Location: ' . config('app.url') . 'login');
            exit;
        }

        // Check if the user exists
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_email = :email");
        $stmt->execute([':email' => $_POST['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Debug information
        error_log("ELP003: Login attempt for email: " . $_POST['email']);
        error_log("ELP004: Password sent: " . $_POST['password']);
        error_log("ELP005: Stored hash: " . ($user ? $user['user_password_hash'] : 'no user found'));

        if (!$user) {
            error_log("ELP006: No user found with email: " . $_POST['email']);
            throw new Exception('ELP006: Error #ERR0INC003: Invalid credentials');
        }

        // Verify password
        if (!password_verify($_POST['password'], $user['user_password_hash'])) {
            error_log("ELP007: Password verification failed for user: " . $_POST['email']);
            throw new Exception('ELP007: Invalid credentials');
        }

        // If we get here, login is successful
        $userModel = new User($this->pdo);
        $userDetails = $userModel->getUserDetails($user['user_id']);

        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_email'] = $user['user_email'];
        $_SESSION['user_status'] = $user['user_status'];
        $_SESSION['user_roles'] = $user['user_roles'];
        $_SESSION['user_fname'] = $user['user_fname'];
        $_SESSION['user_details'] = $userDetails;
        $_SESSION['session_token'] = bin2hex(random_bytes(32));

        // Update login info
        // $userModel->updateLoginInfo($user['user_id']);

        // Redirect to dashboard
        header('Location: ' . config('app.url') . 'user/dashboard');
        exit;

    } catch (Exception $e) {
        error_log('Login error: ' . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        header('Location: ' . config('app.url') . 'login');
        exit;
    }
}



    public function registerForm()
    {
        // Clear any old session data
        if (isset($_SESSION['old'])) {
            unset($_SESSION['old']);
        }

        echo Helper::view('auth/register');
    }

    public function register()
    {
        try {
            // Validate CSRF token
            if (!Helper::verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Invalid CSRF token');
            }

            // Validate input
            $validator = new Validator($_POST, $this->pdo); // Pass PDO here
            $rules = [
                'first_name' => ['required', 'min:1'],
                'last_name' => ['required', 'min:1'],
                'email' => ['required', 'email', 'unique:users'],
                'password' => ['required', 'min:8'],
                'confirm_password' => ['required', 'match:password'],
                'terms' => ['required']
            ];

            if (!$validator->validate($rules)) {
                $_SESSION['error'] = $validator->getFirstError();
                $_SESSION['old'] = $_POST;
                header('Location: ' . config('app.url') . 'register');
                exit;
            }

            // Generate a unique referral ID
            $user_referral_id = bin2hex(random_bytes(10));

            // Hash the password
            $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

            // Default role is author (assuming author role is represented by 1 in bit flag)
            $user_roles = 8;

            // Insert user into the database
            $stmt = $this->pdo->prepare("
                INSERT INTO users (
                    user_referral_id, user_prefixname, user_fname, user_mname, user_lname, user_email, user_password_hash, user_roles, user_status
                ) VALUES (
                    :user_referral_id, :user_prefixname, :user_fname, :user_mname, :user_lname, :user_email, :user_password_hash, :user_roles, :user_status
                )
            ");
            $stmt->execute([
                ':user_referral_id' => $user_referral_id,
                ':user_prefixname' => $_POST['prefix_name'],
                ':user_fname' => $_POST['first_name'],
                ':user_mname' => $_POST['middle_name'],
                ':user_lname' => $_POST['last_name'],
                ':user_email' => $_POST['email'],
                ':user_password_hash' => $password_hash,
                ':user_roles' => $user_roles,
                ':user_status' => 'PENDING'
            ]);

            // If registration is successful
            $_SESSION['success'] = 'Registration successful. Please check your email for confirmation.';
            header('Location: ' . config('app.url') . 'login');
            exit;
        } catch (Exception $e) {
            // Log the error message
            error_log('Registration error: ' . $e->getMessage());

            // Set the error message in the session
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . config('app.url') . 'register');
            exit;
        }
    }

    public function dashboard()
    {
        if (!Helper::isLoggedIn()) {
            header('Location: ' . Helper::config('app.url') . 'login');
            exit;
        }

        // Fetch review papers if the user is a reviewer
        $reviewPapers = [];
        $userRoles = $_SESSION['user_roles'];

        // Ensure userRoles is an array
        if (!is_array($userRoles)) {
            $userRoles = [$userRoles];
        }

        error_log('BEFORE IF ' . $_SESSION['user_id']);
        if (in_array(2, $userRoles)) { // Assuming reviewer role is represented by 2
            $stmt = $this->db->prepare("SELECT * FROM papers WHERE paper_reviewer_id = :reviewer_id");
            $stmt->execute([':reviewer_id' => $_SESSION['user_id']]);
            $reviewPapers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        error_log('AFTER IF ' . $_SESSION['user_id']);

        // If we get here, login is successful
        $userModel = new User($this->pdo);
        $userDetails = $userModel->getUserDetails($_SESSION['user_id']);
        $_SESSION['user_details'] = $userDetails;

        echo Helper::view('users/dashboard', ['reviewPapers' => $reviewPapers]);
    }




    public function submitPaper()
    {
        if (!Helper::isLoggedIn()) {
            header('Location: ' . config('app.url') . 'login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle paper submission
            // Validate and process the form data
            // Save the paper details to the database
            // Redirect to the dashboard with a success message
        } else {
            Helper::view('papers/submit');
        }
    }

    public function applyReviewer()
    {
        if (!Helper::isLoggedIn()) {
            header('Location: ' . config('app.url') . 'login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle reviewer application
            // Validate and process the form data
            // Save the reviewer details to the database
            // Redirect to the dashboard with a success message
        } else {
            Helper::view('users/apply-reviewer');
        }
    }

    public function profile()
    {
        if (!Helper::isLoggedIn()) {
            header('Location: ' . config('app.url') . 'login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle profile update
            // Validate and process the form data
            // Update the user details in the database
            // Redirect to the dashboard with a success message
        } else {
            Helper::view('users/profile');
        }
    }
    public function forgotPassword()
    {
        $email = $_POST['email'];

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save token to database
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET user_reset_token = ?,
                user_reset_token_expiry = ? 
            WHERE email = ?
        ");
        $stmt->execute([$token, $expiry, $email]);

        // Send reset email
        $resetLink = config('app.url') . "auth/reset-password?token=" . $token;
        $to = $email;
        $subject = "Password Reset Request";
        $message = "Click the following link to reset your password: " . $resetLink;

        mail($to, $subject, $message);

        $_SESSION['success'] = 'Password reset instructions have been sent to your email.';
        redirect(config('app.url') . 'login');
    }

    public function resetPassword()
    {
        $token = $_GET['token'];
        $password = $_POST['password'];

        // Verify token and expiry
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE user_reset_token = ? 
            AND user_reset_token_expiry > NOW()
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['error'] = 'Invalid or expired reset token';
            redirect(config('app.url') . 'login');
            return;
        }

        // Update password and clear token
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET password = ?,
                user_reset_token = NULL,
                user_reset_token_expiry = NULL,
                user_failed_login_attempts = 0
            WHERE id = ?
        ");
        $stmt->execute([$hashedPassword, $user['id']]);

        $_SESSION['success'] = 'Password has been reset successfully. Please login.';
        redirect(config('app.url') . 'login');
    }

    private function verifyCaptcha($userInput)
    {
        return isset($_SESSION['captcha']) &&
            strtolower($_SESSION['captcha']) === strtolower($userInput);
    }

    public function logout()
    {
        // Clear session
        session_destroy();

        // Clear remember me cookie
        setcookie('remember_token', '', time() - 3600, '/');

        redirect(config('app.url') . 'login');
    }

    // ... existing code ...

    /**
     * Check if account is locked
     */
    private function isAccountLocked($userId)
    {
        $user = $this->user->find($userId);
        if (!$user)
            return false;

        // Check lockout timestamp
        if (
            $user['user_lockout_until'] &&
            strtotime($user['user_lockout_until']) > time()
        ) {
            return true;
        }

        // Check failed attempts
        if ($user['user_failed_login_attempts'] >= 5) {
            // Set lockout if not already set
            if (!$user['user_lockout_until']) {
                $this->user->updateLockout($userId, date('Y-m-d H:i:s', strtotime('+30 minutes')));
            }
            return true;
        }

        return false;
    }

    /**
     * Handle failed login attempt
     */
    private function handleFailedLogin($userId)
    {
        $user = $this->user->find($userId);
        if (!$user)
            return;

        $attempts = $user['user_failed_login_attempts'] + 1;

        // Update failed attempts
        $this->user->update($userId, [
            'user_failed_login_attempts' => $attempts
        ]);

        // Lock account if max attempts reached
        if ($attempts >= 5) {
            $lockoutUntil = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $this->user->updateLockout($userId, $lockoutUntil);

            // Log security event
            ActivityLogger::log('SECURITY_ACCOUNT_LOCKED', 'Account locked due to multiple failed attempts', [
                'user_id' => $userId,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);

            // Send notification email
            $this->mailer->sendAccountLockedNotification($user['user_email']);
        }
    }

    /**
     * Validate login request
     */
    private function validateLoginRequest()
    {
        $validator = new Validator($_POST);

        return $validator
            ->required(['username', 'password', 'captcha'])
            ->email('username')
            ->minLength('password', 8)
            ->custom('captcha', function ($value) {
                return isset($_SESSION['captcha']) &&
                    strtolower($_SESSION['captcha']) === strtolower($value);
            }, 'Invalid captcha')
            ->isValid();
    }

    /**
     * Check if password was previously used
     */
    private function isPasswordPreviouslyUsed($userId, $newPassword)
    {
        // Get password history
        $history = $this->user->getPasswordHistory($userId);

        // Check against current password
        $currentUser = $this->user->find($userId);
        if (password_verify($newPassword, $currentUser['user_password_hash'])) {
            return true;
        }

        // Check against password history
        foreach ($history as $record) {
            if (password_verify($newPassword, $record['password_hash'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reset failed login attempts
     */
    private function resetFailedAttempts($userId)
    {
        $this->user->update($userId, [
            'user_failed_login_attempts' => 0,
            'user_lockout_until' => null
        ]);
    }

    /**
     * Verify two-factor authentication
     */
    private function verifyTwoFactor($userId, $code)
    {
        $user = $this->user->find($userId);
        if (!$user || !$user['user_2fa_secret']) {
            return false;
        }

        $tfa = new TwoFactorAuth();
        return $tfa->verifyCode($user['user_2fa_secret'], $code);
    }

    /**
     * Generate secure session token
     */
    private function generateSessionToken()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Validate password strength
     */
    private function validatePasswordStrength($password)
    {
        $minLength = 8;
        $requireUppercase = true;
        $requireLowercase = true;
        $requireNumbers = true;
        $requireSpecial = true;

        if (strlen($password) < $minLength) {
            return false;
        }

        if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
            return false;
        }

        if ($requireLowercase && !preg_match('/[a-z]/', $password)) {
            return false;
        }

        if ($requireNumbers && !preg_match('/[0-9]/', $password)) {
            return false;
        }

        if ($requireSpecial && !preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Check if IP is blocked
     */
    private function isIpBlocked($ip)
    {
        $attempts = $this->user->getIpAttempts($ip);
        return $attempts >= 10; // Block IP after 10 failed attempts
    }

    /**
     * Log authentication attempt
     */
    private function logAuthAttempt($userId, $success, $ip)
    {
        ActivityLogger::log(
            $success ? 'AUTH_SUCCESS' : 'AUTH_FAILURE',
            $success ? 'Successful login' : 'Failed login attempt',
            [
                'user_id' => $userId,
                'ip' => $ip,
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ]
        );
    }

    /**
     * Send security notification
     */
    private function sendSecurityNotification($userId, $type, $data = [])
    {
        $user = $this->user->find($userId);
        if (!$user)
            return;

        switch ($type) {
            case 'new_login':
                $this->mailer->sendNewLoginAlert($user['user_email'], [
                    'ip' => $data['ip'],
                    'location' => $data['location'],
                    'device' => $data['device']
                ]);
                break;
            case 'password_changed':
                $this->mailer->sendPasswordChangeNotification($user['user_email']);
                break;
            case 'account_locked':
                $this->mailer->sendAccountLockedNotification($user['user_email']);
                break;
        }
    }
}


