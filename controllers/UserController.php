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
        // error_log('User Details in Profile: ' . json_encode($userDetails));

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
public function updateProfile()
{
    try {
        if (!Helper::isLoggedIn()) {
            throw new Exception('Please login to continue');
        }

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || !Helper::verifyCsrfToken($_POST['csrf_token'])) {
            throw new Exception('Invalid security token');
        }

        $userId = Helper::getCurrentUserId();
        $data = [
            'user_prefixname' => $_POST['user_prefixname'] ?? '',
            'user_fname' => $_POST['user_fname'] ?? '',
            'user_mname' => $_POST['user_mname'] ?? '',
            'user_lname' => $_POST['user_lname'] ?? '',
            'user_designation' => $_POST['user_designation'] ?? '',
            'user_institution' => $_POST['user_institution'] ?? '',
            'user_mobile' => $_POST['user_mobile'] ?? '',
            'user_countryCode' => $_POST['user_countryCode'] ?? '',
            'user_address_line1' => $_POST['user_address_line1'] ?? '',
            'user_address_line2' => $_POST['user_address_line2'] ?? '',
            'user_city' => $_POST['user_city'] ?? '',
            'user_state' => $_POST['user_state'] ?? '',
            'user_country' => $_POST['user_country'] ?? '',
            'user_pin_code' => $_POST['user_pin_code'] ?? '',
            'user_about_me' => $_POST['user_about_me'] ?? ''
        ];

        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            error_log("Processing profile image upload for user ID: " . $userId);
            
            // Get file extension
            $fileInfo = pathinfo($_FILES['profile_image']['name']);
            $extension = strtolower($fileInfo['extension']);

            // Validate file type
            $allowedTypes = ['jpg', 'jpeg', 'png'];
            if (!in_array($extension, $allowedTypes)) {
                throw new Exception('Invalid file type. Only JPG and PNG files are allowed.');
            }

            // Generate new filename
            $newFileName = $userId . '_profile_image.' . $extension;
            $uploadPath = ROOT_PATH . '/uploads/users/' . $newFileName;

            error_log("Attempting to upload file to: " . $uploadPath);
            error_log("Temp file location: " . $_FILES['profile_image']['tmp_name']);
            error_log("File size: " . $_FILES['profile_image']['size']);

            // Check if upload directory exists and is writable
            $uploadDir = ROOT_PATH . '/uploads/users';
            if (!is_writable($uploadDir)) {
                error_log("Upload directory is not writable: " . $uploadDir);
                throw new Exception('Upload directory is not writable');
            }

            // Remove old profile image if exists
            $oldImage = $this->userModel->getUserProfileImage($userId);
            if ($oldImage && file_exists($uploadDir . '/' . $oldImage)) {
                unlink($uploadDir . '/' . $oldImage);
                error_log("Removed old profile image: " . $oldImage);
            }

            // Move uploaded file
            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                error_log("Failed to move uploaded file. Upload error code: " . $_FILES['profile_image']['error']);
                error_log("PHP error: " . error_get_last()['message']);
                throw new Exception('Failed to upload profile image');
            }

            error_log("File successfully uploaded to: " . $uploadPath);
            $data['user_profile_image'] = $newFileName;
        }

        // Update user profile
        if ($this->userModel->updateProfile($userId, $data)) {
            // Refresh user details in session
            $userDetails = $this->userModel->getUserDetails($userId);
            $_SESSION['user_details'] = $userDetails;

            // Log activity
            Helper::logActivity($userId, 'PROFILE_UPDATED', 'User profile updated successfully');

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Profile updated successfully'
            ];
        } else {
            throw new Exception('Failed to update profile');
        }

        header('Location: ' . Helper::config('app.url') . 'user/profile');
        exit;

    } catch (Exception $e) {
        error_log("Profile update error: " . $e->getMessage());
        $_SESSION['flash_message'] = [
            'type' => 'danger',
            'message' => $e->getMessage()
        ];
        header('Location: ' . Helper::config('app.url') . 'user/profile');
        exit;
    }
}


    public function changePassword()
    {
        try {
            if (!Helper::isLoggedIn()) {
                throw new Exception('Please login to continue');
            }

            if (!Helper::verifyCsrfToken()) {
                throw new Exception('Invalid security token');
            }

            $userId = Helper::getCurrentUserId();
            $currentPassword = $_POST['currentPassword'] ?? '';
            $newPassword = $_POST['newPassword'] ?? '';
            $confirmPassword = $_POST['renewPassword'] ?? '';

            // Validate input
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                error_log('All password fields are required');
                throw new Exception('All password fields are required');
            }

            if ($newPassword !== $confirmPassword) {
                error_log('New passwords do not match');
                throw new Exception('New passwords do not match');
            }

            // Get user details
            $user = $this->userModel->findById($userId);
            if (!$user) {
                error_log('User not found');
                throw new Exception('User not found');
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['user_password_hash'])) {
                error_log('Current password is incorrect');
                throw new Exception('Current password is incorrect');
            }

            // Update password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            if ($this->userModel->updatePassword($userId, $newPasswordHash)) {
                // Log activity
                Helper::logActivity($userId, 'PASSWORD_CHANGED', 'Password changed successfully');

                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => 'Password changed successfully'
                ];
            } else {
                
                throw new Exception('Failed to update password');
            }

            header('Location: ' . Helper::config('app.url') . 'user/profile');
            exit;

        } catch (Exception $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => $e->getMessage()
            ];
            header('Location: ' . Helper::config('app.url') . 'user/profile');
            exit;
        }
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
