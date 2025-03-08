<?php
// core/RoleMiddleware.php

class RoleMiddleware {
    public static function requireRole($role) {
        return function() use ($role) {
            if (!isLoggedIn()) {
                redirect('auth/login');
                exit;
            }

            $user = auth()->user();
            if (!($user->user_roles & $role)) {
                $_SESSION['error'] = "You don't have permission to access this area.";
                redirect('');
                exit;
            }
        };
    }

    public static function requireAnyRole(array $roles) {
        return function() use ($roles) {
            if (!isLoggedIn()) {
                redirect('auth/login');
                exit;
            }

            $user = auth()->user();
            foreach ($roles as $role) {
                if ($user->user_roles & $role) {
                    return true;
                }
            }

            $_SESSION['error'] = "You don't have permission to access this area.";
            redirect('');
            exit;
        };
    }
}
