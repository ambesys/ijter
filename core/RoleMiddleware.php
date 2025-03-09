<?php
// core/RoleMiddleware.php

function check_role($requiredRoles) {
    if (!isset($_SESSION['user_roles'])) {
        header('Location: ' . config('app.url') . 'login');
        exit;
    }

    $userRoles = explode(',', $_SESSION['user_roles']);
    
    // If requiredRoles is a string, convert it to array
    if (!is_array($requiredRoles)) {
        $requiredRoles = [$requiredRoles];
    }

    // Check if user has any of the required roles
    $hasRole = false;
    foreach ($requiredRoles as $role) {
        if (in_array($role, $userRoles)) {
            $hasRole = true;
            break;
        }
    }

    if (!$hasRole) {
        header('Location: ' . config('app.url') . 'unauthorized');
        exit;
    }

    return true;
}
