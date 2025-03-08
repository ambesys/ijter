<?php
// core/AuthMiddleware.php

class AuthMiddleware {
    public static function requireAuth() {
        return function() {
            if (!isLoggedIn()) {
                $_SESSION['error'] = "Please login to access this area.";
                redirect('auth/login');
                exit;
            }
        };
    }

    public static function guest() {
        return function() {
            if (isLoggedIn()) {
                redirect('');
                exit;
            }
        };
    }
}
