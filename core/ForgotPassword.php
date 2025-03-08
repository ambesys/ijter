<?php
// core/ForgotPassword.php

class ForgotPassword {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Generate reset token
     */
    public function generateToken($email) {
        // Check if email exists
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email not found'];
        }
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        // Save token to database
        $stmt = $this->pdo->prepare("
            UPDATE users SET
                user_reset_token = ?,
                user_reset_token_expiry = ?,
                user_is_password_reset_requested = 1
            WHERE user_email = ?
        ");
        
        if (!$stmt->execute([$token, $expiry, $email])) {
            return ['success' => false, 'message' => 'Failed to generate reset token'];
        }
        
        // Send email
        $resetLink = "https://ijter.com/auth/reset-password.php?token=$token";
        $subject = "IJTER - Password Reset Request";
        $message = "
            <html>
            <head>
                <title>Password Reset Request</title>
            </head>
            <body>
                <h2>Password Reset Request</h2>
                <p>Dear {$user['user_username']},</p>
                <p>You have requested to reset your password. Please click the link below to reset your password:</p>
                <p><a href='$resetLink'>Reset Password</a></p>
                <p>This link will expire in 24 hours.</p>
                <p>If you did not request this, please ignore this email.</p>
                <p>Regards,<br>IJTER Team</p>
            </body>
            </html>
        ";
        // Send email with HTML content
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: IJTER <noreply@ijter.com>' . "\r\n";
        
        if (!mail($email, $subject, $message, $headers)) {
            return ['success' => false, 'message' => 'Failed to send reset email'];
        }
        
        return ['success' => true, 'message' => 'Password reset link has been sent to your email'];
    }
    
    /**
     * Verify reset token
     */
    public function verifyToken($token) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM users 
            WHERE user_reset_token = ? AND user_is_password_reset_requested = 1
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid reset token'];
        }
        
        // Check if token is expired
        if (strtotime($user['user_reset_token_expiry']) < time()) {
            return ['success' => false, 'message' => 'Reset token has expired'];
        }
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Reset password
     */
    public function resetPassword($token, $password) {
        // Verify token
        $result = $this->verifyToken($token);
        if (!$result['success']) {
            return $result;
        }
        
        $user = $result['user'];
        
        // Update password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("
            UPDATE users SET
                user_password_hash = ?,
                user_reset_token = NULL,
                user_reset_token_expiry = NULL,
                user_is_password_reset_requested = 0,
                user_failed_login_attempts = 0,
                user_is_max_failed_attempts = 0
            WHERE user_id = ?
        ");
        
        if (!$stmt->execute([$passwordHash, $user['user_id']])) {
            return ['success' => false, 'message' => 'Failed to update password'];
        }
        
        // Log activity
        logActivity($user['user_id'], 'PASSWORD_RESET', 'Password reset successful');
        
        return ['success' => true, 'message' => 'Password has been reset successfully'];
    }
}
?>

        
