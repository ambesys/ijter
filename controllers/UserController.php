<?php
// controllers/UserController.php

class UserController
{
    private $pdo;
    private $userModel;
    private $activityModel;
    private $notificationModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
        $this->activityModel = new Activity($pdo);
        $this->notificationModel = new Notification($pdo);
    }

    /**
     * Display user profile
     */
    public function profile()
    {
        if (!Helper::isLoggedIn()) {
            header('Location: ' . Helper::config('app.url') . 'login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $userModel = new User($this->pdo);
        $userDetails = $userModel->getUserDetails($userId);

        if (!$userDetails) {
            $_SESSION['error'] = 'User details not found';
            header('Location: ' . Helper::config('app.url') . 'dashboard');
            exit;
        }

        // Debug log
        error_log('User Details in Profile: ' . json_encode($userDetails));

        echo Helper::view('users/profile', [
            'userDetails' => $userDetails,
            'pageTitle' => 'My Profile'
        ]);
    }


    /**
     * Display edit profile form
     */
    public function editProfileForm()
    {
        requireLogin();

        $user = $this->userModel->getUserById(getCurrentUserId());

        render('users/edit_profile', [
            'user' => $user
        ]);
    }

    /**
     * Process profile update
     */
    public function updateProfile()
    {
        requireLogin();
        checkCSRF();

        $userId = getCurrentUserId();
        $user = $this->userModel->getUserById($userId);

        $prefixName = $_POST['prefix_name'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $middleName = $_POST['middle_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $designation = $_POST['designation'] ?? '';
        $institution = $_POST['institution'] ?? '';
        $bio = $_POST['bio'] ?? '';

        // Validate input
        $errors = [];

        if (empty($firstName)) {
            $errors[] = 'First name is required';
        }

        if (empty($lastName)) {
            $errors[] = 'Last name is required';
        }

        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } elseif ($email !== $user['user_email'] && $this->userModel->emailExists($email, $userId)) {
            $errors[] = 'Email already exists';
        }

        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect(config('app.url') . 'users/edit-profile');
            return;
        }

        // Update user
        $userData = [
            'user_prefixname' => $prefixName,
            'user_fname' => $firstName,
            'user_mname' => $middleName,
            'user_lname' => $lastName,
            'user_email' => $email,
            'user_phone' => $phone,
            'user_designation' => $designation,
            'user_institution' => $institution,
            'user_bio' => $bio
        ];

        $success = $this->userModel->updateUser($userId, $userData);

        if (!$success) {
            setFlashMessage('error', 'Failed to update profile');
            redirect(config('app.url') . 'users/edit-profile');
            return;
        }

        // Update session variables if email changed
        if ($email !== $user['user_email']) {
            $_SESSION['user_email'] = $email;
        }

        // Log activity
        $this->activityModel->log([
            'activity_user_id' => $userId,
            'activity_action' => 'profile_updated',
            'activity_description' => 'Updated profile information'
        ]);

        setFlashMessage('success', 'Profile updated successfully');
        redirect(config('app.url') . 'users/profile');
    }

    /**
     * Display change password form
     */
    public function changePasswordForm()
    {
        requireLogin();

        render('users/change_password');
    }

    /**
     * Process password change
     */
    public function changePassword()
    {
        requireLogin();
        checkCSRF();

        $userId = getCurrentUserId();
        $user = $this->userModel->getUserById($userId);

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate input
        $errors = [];

        if (empty($currentPassword)) {
            $errors[] = 'Current password is required';
        } elseif (!password_verify($currentPassword, $user['user_password'])) {
            $errors[] = 'Current password is incorrect';
        }

        if (empty($newPassword)) {
            $errors[] = 'New password is required';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters long';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect(config('app.url') . 'users/change-password');
            return;
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $success = $this->userModel->updatePassword($userId, $hashedPassword);

        if (!$success) {
            setFlashMessage('error', 'Failed to change password');
            redirect(config('app.url') . 'users/change-password');
            return;
        }

        // Log activity
        $this->activityModel->log([
            'activity_user_id' => $userId,
            'activity_action' => 'password_changed',
            'activity_description' => 'Changed account password'
        ]);

        setFlashMessage('success', 'Password changed successfully');
        redirect(config('app.url') . 'users/profile');
    }

    /**
     * Display user activity
     */
    public function activity()
    {
        requireLogin();

        $userId = getCurrentUserId();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $activities = $this->activityModel->getUserActivities($userId, $limit, $offset);
        $totalActivities = $this->activityModel->countActivities(['user_id' => $userId]);
        $totalPages = ceil($totalActivities / $limit);

        render('users/activity', [
            'activities' => $activities,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalActivities' => $totalActivities
        ]);
    }

    /**
     * Display user notifications
     */
    public function notifications()
    {
        requireLogin();

        $userId = getCurrentUserId();
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $notifications = $this->notificationModel->getNotificationsByUserId($userId, $limit, $offset);

        render('users/notifications', [
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark notification as read
     * 
     * @param int $notificationId Notification ID
     */
    public function markNotificationAsRead($notificationId)
    {
        requireLogin();

        $notification = $this->notificationModel->getNotificationById($notificationId);

        if (!$notification || $notification['notification_user_id'] != getCurrentUserId()) {
            setFlashMessage('error', 'Notification not found');
            redirect(config('app.url') . 'users/notifications');
            return;
        }

        $this->notificationModel->markAsRead($notificationId);

        // Redirect to the notification target if available
        if (!empty($notification['notification_data'])) {
            $data = json_decode($notification['notification_data'], true);

            if (isset($data['paper_id'])) {
                redirect(config('app.url') . 'papers/view/' . $data['paper_id']);
                return;
            }
        }

        redirect(config('app.url') . 'users/notifications');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead()
    {
        requireLogin();

        $userId = getCurrentUserId();
        $this->notificationModel->markAllAsRead($userId);

        setFlashMessage('success', 'All notifications marked as read');
        redirect(config('app.url') . 'users/notifications');
    }

    /**
     * Display all users (admin only)
     */
    public function allUsers()
    {
        requireAdmin();

        $role = $_GET['role'] ?? null;
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $filters = [];

        if ($role) {
            $filters['role'] = $role;
        }

        $users = $this->userModel->getAllUsers($limit, $offset, $filters);
        $totalUsers = $this->userModel->countUsers($filters);
        $totalPages = ceil($totalUsers / $limit);

        render('admin/users/index', [
            'users' => $users,
            'role' => $role,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers
        ]);
    }

    /**
     * Display user details (admin only)
     * 
     * @param int $userId User ID
     */
    public function viewUser($userId)
    {
        requireAdmin();

        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            setFlashMessage('error', 'User not found');
            redirect(config('app.url') . 'admin/users');
            return;
        }

        // Get user activities
        $activities = $this->activityModel->getUserActivities($userId, 10);

        render('admin/users/view', [
            'user' => $user,
            'activities' => $activities
        ]);
    }

    /**
     * Display edit user form (admin only)
     * 
     * @param int $userId User ID
     */
    public function editUserForm($userId)
    {
        requireAdmin();

        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            setFlashMessage('error', 'User not found');
            redirect(config('app.url') . 'admin/users');
            return;
        }

        render('admin/users/edit', [
            'user' => $user
        ]);
    }

    /**
     * Process user update (admin only)
     * 
     * @param int $userId User ID
     */
    public function updateUser($userId)
    {
        requireAdmin();
        checkCSRF();

        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            setFlashMessage('error', 'User not found');
            redirect(config('app.url') . 'admin/users');
            return;
        }

        $prefixName = $_POST['prefix_name'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $middleName = $_POST['middle_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $designation = $_POST['designation'] ?? '';
        $institution = $_POST['institution'] ?? '';
        $bio = $_POST['bio'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $isVerified = isset($_POST['is_verified']) ? 1 : 0;
        $isAuthor = isset($_POST['is_author']) ? 1 : 0;
        $isReviewer = isset($_POST['is_reviewer']) ? 1 : 0;
        $isModerator = isset($_POST['is_moderator']) ? 1 : 0;
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;

        // Validate input
        $errors = [];

        if (empty($firstName)) {
            $errors[] = 'First name is required';
        }

        if (empty($lastName)) {
            $errors[] = 'Last name is required';
        }

        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } elseif ($email !== $user['user_email'] && $this->userModel->emailExists($email, $userId)) {
            $errors[] = 'Email already exists';
        }

        if (!empty($errors)) {
            setFlashMessage('error', implode('<br>', $errors));
            redirect(config('app.url') . 'admin/users/edit/' . $userId);
            return;
        }

        // Update user
        $userData = [
            'user_prefixname' => $prefixName,
            'user_fname' => $firstName,
            'user_mname' => $middleName,
            'user_lname' => $lastName,
            'user_email' => $email,
            'user_phone' => $phone,
            'user_designation' => $designation,
            'user_institution' => $institution,
            'user_bio' => $bio,
            'user_is_active' => $isActive,
            'user_is_verified' => $isVerified,
            'user_is_author' => $isAuthor,
            'user_is_reviewer' => $isReviewer,
            'user_is_moderator' => $isModerator,
            'user_is_admin' => $isAdmin
        ];

        $success = $this->userModel->updateUser($userId, $userData);

        if (!$success) {
            setFlashMessage('error', 'Failed to update user');
            redirect(config('app.url') . 'admin/users/edit/' . $userId);
            return;
        }

        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'user_updated',
            'activity_description' => 'Updated user: ' . $firstName . ' ' . $lastName,
            'activity_data' => json_encode(['user_id' => $userId])
        ]);

        setFlashMessage('success', 'User updated successfully');
        redirect(config('app.url') . 'admin/users/view/' . $userId);
    }

    /**
     * Delete user (admin only)
     * 
     * @param int $userId User ID
     */
    public function deleteUser($userId)
    {
        requireAdmin();
        checkCSRF();

        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            setFlashMessage('error', 'User not found');
            redirect(config('app.url') . 'admin/users');
            return;
        }

        // Prevent deleting self
        if ($userId == getCurrentUserId()) {
            setFlashMessage('error', 'You cannot delete your own account');
            redirect(config('app.url') . 'admin/users');
            return;
        }

        $success = $this->userModel->deleteUser($userId);

        if (!$success) {
            setFlashMessage('error', 'Failed to delete user');
            redirect(config('app.url') . 'admin/users');
            return;
        }

        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'user_deleted',
            'activity_description' => 'Deleted user: ' . $user['user_fname'] . ' ' . $user['user_lname'],
            'activity_data' => json_encode(['user_id' => $userId])
        ]);

        setFlashMessage('success', 'User deleted successfully');
        redirect(config('app.url') . 'admin/users');
    }

    /**
     * Toggle user status (admin only)
     * 
     * @param int $userId User ID
     * @param string $status Status to toggle (active, author, reviewer, moderator, admin)
     */
    public function toggleUserStatus($userId, $status)
    {
        requireAdmin();

        $user = $this->userModel->getUserById($userId);

        if (!$user) {
            setFlashMessage('error', 'User not found');
            redirect(config('app.url') . 'admin/users');
            return;
        }

        // Prevent changing own admin status
        if ($userId == getCurrentUserId() && $status === 'admin') {
            setFlashMessage('error', 'You cannot change your own admin status');
            redirect(config('app.url') . 'admin/users');
            return;
        }

        $success = false;
        $newStatus = false;
        $statusDescription = '';

        switch ($status) {
            case 'active':
                $newStatus = $user['user_is_active'] == 1 ? 0 : 1;
                $success = $newStatus ? $this->userModel->activateUser($userId) : $this->userModel->deactivateUser($userId);
                $statusDescription = $newStatus ? 'activated' : 'deactivated';
                break;

            case 'author':
                $newStatus = $user['user_is_author'] == 1 ? 0 : 1;
                $success = $this->userModel->setAuthorStatus($userId, $newStatus);
                $statusDescription = $newStatus ? 'granted author role' : 'removed author role';
                break;

            case 'reviewer':
                $newStatus = $user['user_is_reviewer'] == 1 ? 0 : 1;
                $success = $this->userModel->setReviewerStatus($userId, $newStatus);
                $statusDescription = $newStatus ? 'granted reviewer role' : 'removed reviewer role';
                break;

            case 'moderator':
                $newStatus = $user['user_is_moderator'] == 1 ? 0 : 1;
                $success = $this->userModel->setModeratorStatus($userId, $newStatus);
                $statusDescription = $newStatus ? 'granted moderator role' : 'removed moderator role';
                break;

            case 'admin':
                $newStatus = $user['user_is_admin'] == 1 ? 0 : 1;
                $success = $this->userModel->setAdminStatus($userId, $newStatus);
                $statusDescription = $newStatus ? 'granted admin role' : 'removed admin role';
                break;
        }

        if (!$success) {
            setFlashMessage('error', 'Failed to update user status');
            redirect(config('app.url') . 'admin/users');
            return;
        }

        // Log activity
        $this->activityModel->log([
            'activity_user_id' => getCurrentUserId(),
            'activity_action' => 'user_status_changed',
            'activity_description' => 'Changed user status: ' . $user['user_fname'] . ' ' . $user['user_lname'] . ' - ' . $statusDescription,
            'activity_data' => json_encode([
                'user_id' => $userId,
                'status' => $status,
                'new_value' => $newStatus
            ])
        ]);

        // Notify user
        $this->notificationModel->sendNotification(
            $userId,
            Notification::TYPE_SYSTEM,
            'Your account status has been updated: ' . $statusDescription,
            [
                'status' => $status,
                'new_value' => $newStatus
            ]
        );

        setFlashMessage('success', 'User status updated successfully');
        redirect(config('app.url') . 'admin/users/view/' . $userId);
    }


}
