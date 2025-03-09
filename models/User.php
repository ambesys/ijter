<?php
// models/User.php

class User
{
    private $pdo;
    private $config;

    // Role constants (matching config)
    const ROLE_ADMIN = 1;
    const ROLE_MODERATOR = 2;
    const ROLE_REVIEWER = 4;
    const ROLE_AUTHOR = 8;
    const ROLE_SUBSCRIBER = 16;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->config = require ROOT_PATH . '/config/config.php';
    }

    public function create($data)
    {
        $sql = "INSERT INTO users (
            user_referral_id, user_prefixname, user_fname, user_mname, user_lname,
            user_designation, user_institution, user_email, user_countryCode,
            user_mobile, user_address_line1, user_address_line2, user_city,
            user_state, user_country, user_pin_code, user_username,
            user_password_hash, user_roles, user_status
        ) VALUES (
            :referral_id, :prefix, :fname, :mname, :lname,
            :designation, :institution, :email, :country_code,
            :mobile, :address1, :address2, :city,
            :state, :country, :pincode, :username,
            :password_hash, :roles, :status
        )";

        try {
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'referral_id' => $this->generateReferralId(),
                'prefix' => $data['prefixname'],
                'fname' => $data['fname'],
                'mname' => $data['mname'] ?? null,
                'lname' => $data['lname'],
                'designation' => $data['designation'] ?? null,
                'institution' => $data['institution'],
                'email' => $data['email'],
                'country_code' => $data['country_code'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'address1' => $data['address1'] ?? null,
                'address2' => $data['address2'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'pincode' => $data['pincode'] ?? null,
                'username' => $data['username'],
                'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                'roles' => $data['roles'] ?? self::ROLE_AUTHOR,
                'status' => 'PENDING'
            ]);

            return $result ? $this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function findByCredentials($identifier)
    {
        $sql = "SELECT * FROM users 
                WHERE (user_email = :identifier OR user_username = :identifier)
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['identifier' => $identifier]);
        return $stmt->fetch();
    }

    public function findById($userId)
    {
        $sql = "SELECT * FROM users WHERE user_id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    public function updateProfile($userId, $data)
    {
        try {
            $sql = "UPDATE users SET
        user_prefixname = :prefix,
        user_fname = :fname,
        user_mname = :mname,
        user_lname = :lname,
        user_designation = :designation,
        user_institution = :institution,
        user_mobile = :mobile,
        user_countryCode = :countryCode,
        user_address_line1 = :address1,
        user_address_line2 = :address2,
        user_city = :city,
        user_state = :state,
        user_country = :country,
        user_pin_code = :pincode,
        user_about_me = :about,
        " . (isset($data['user_profile_image']) ? "user_profile_image = :profile_image," : "") . "
        user_updated_at = CURRENT_TIMESTAMP
        WHERE user_id = :user_id";

            $stmt = $this->pdo->prepare($sql);

            $params = [
                'prefix' => $data['user_prefixname'],
                'fname' => $data['user_fname'],
                'mname' => $data['user_mname'],
                'lname' => $data['user_lname'],
                'designation' => $data['user_designation'],
                'institution' => $data['user_institution'],
                'mobile' => $data['user_mobile'],
                'countryCode' => $data['user_countryCode'],
                'address1' => $data['user_address_line1'],
                'address2' => $data['user_address_line2'],
                'city' => $data['user_city'],
                'state' => $data['user_state'],
                'country' => $data['user_country'],
                'pincode' => $data['user_pin_code'],
                'about' => $data['user_about_me'],
                'user_id' => $userId
            ];

            if (isset($data['user_profile_image'])) {
                $params['profile_image'] = $data['user_profile_image'];
            }

            return $stmt->execute($params);

        } catch (PDOException $e) {
            error_log("Database error in updateProfile: " . $e->getMessage());
            return false;
        }
    }




    public function getUserProfileImage($userId)
    {
        $sql = "SELECT user_profile_image FROM users WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    public function updatePassword($userId, $newPasswordHash)
    {
        $sql = "UPDATE users SET
                user_password_hash = :password,
                user_password_changed_at = CURRENT_TIMESTAMP,
                user_updated_at = CURRENT_TIMESTAMP
                WHERE user_id = :user_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'password' => $newPasswordHash,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateLoginInfo($userId)
    {
        $sql = "UPDATE users SET
                user_last_login = CURRENT_TIMESTAMP,
                user_failed_login_attempts = 0,
                user_lockout_until = NULL,
                user_last_activity = CURRENT_TIMESTAMP,
                user_ip_address = :ip
                WHERE user_id = :user_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'ip' => $_SERVER['REMOTE_ADDR'],
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function saveVerificationToken($userId, $token)
    {
        $sql = "UPDATE users SET 
                verification_token = :token,
                verification_token_expiry = DATE_ADD(NOW(), INTERVAL 24 HOUR)
                WHERE user_id = :user_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'token' => $token,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function verifyEmail($token)
    {
        $sql = "UPDATE users SET 
                user_email_verified = 1,
                verification_token = NULL,
                verification_token_expiry = NULL,
                user_status = CASE WHEN user_status = 'PENDING' THEN 'ACTIVE' ELSE user_status END
                WHERE verification_token = :token 
                AND verification_token_expiry > NOW()";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['token' => $token]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function saveResetToken($userId, $token, $expiry)
    {
        $sql = "UPDATE users SET 
                user_reset_token = :token,
                user_reset_token_expiry = :expiry
                WHERE user_id = :user_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'token' => $token,
                'expiry' => $expiry,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function findByResetToken($token)
    {
        $sql = "SELECT * FROM users 
                WHERE user_reset_token = ? 
                AND user_reset_token_expiry > NOW()
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function clearResetToken($userId)
    {
        $sql = "UPDATE users SET 
                user_reset_token = NULL,
                user_reset_token_expiry = NULL
                WHERE user_id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateSessionToken($userId, $token, $expiry)
    {
        $sql = "UPDATE users SET 
                user_session_token = :token,
                user_session_expiry = :expiry,
                user_last_activity = NOW()
                WHERE user_id = :user_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'token' => $token,
                'expiry' => $expiry,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function clearSessionToken($userId)
    {
        $sql = "UPDATE users SET 
                user_session_token = NULL,
                user_session_expiry = NULL
                WHERE user_id = ?";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateFailedLogins($userId, $attempts, $lockoutUntil = null)
    {
        $sql = "UPDATE users SET 
                user_failed_login_attempts = :attempts,
                user_lockout_until = :lockout
                WHERE user_id = :user_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'attempts' => $attempts,
                'lockout' => $lockoutUntil,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getPasswordHistory($userId, $limit = 5)
    {
        $sql = "SELECT password_hash 
                FROM user_password_history 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    public function updateOAuthInfo($userId, $provider, $oauthUid)
    {
        $sql = "UPDATE users SET 
                user_oauth_provider = :provider,
                user_oauth_uid = :oauth_uid
                WHERE user_id = :user_id";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'provider' => $provider,
                'oauth_uid' => $oauthUid,
                'user_id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function findByOAuth($provider, $oauthUid)
    {
        $sql = "SELECT * FROM users 
                WHERE user_oauth_provider = ? 
                AND user_oauth_uid = ? 
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$provider, $oauthUid]);
        return $stmt->fetch();
    }

    public function hasRole($userId, $role)
    {
        $sql = "SELECT user_roles FROM users WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        $userRoles = $stmt->fetchColumn();

        return ($userRoles & $role) === $role;
    }

    public function addRole($userId, $role)
    {
        $sql = "UPDATE users SET user_roles = user_roles | ? WHERE user_id = ?";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$role, $userId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function removeRole($userId, $role)
    {
        $sql = "UPDATE users SET user_roles = user_roles & ~? WHERE user_id = ?";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$role, $userId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function generateReferralId()
    {
        return 'REF' . date('Y') . rand(1000, 9999);
    }

    private function generateUsername($fname, $lname)
    {
        $base = strtolower(substr($fname, 0, 1) . $lname);
        $username = $base;
        $counter = 1;

        while ($this->usernameExists($username)) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    private function usernameExists($username)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE user_username = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$username]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get user by ID
     */
    public function getUserById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $user['user_roles'] = json_decode($user['user_roles'], true) ?? [];
        }
        return $user;
    }

    public function getUserByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $user['user_roles'] = json_decode($user['user_roles'], true) ?? [];
        }
        return $user;
    }

    public function createUser($email, $password, $firstName, $lastName, $roles)
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (user_email, user_password, user_fname, user_lname, user_roles) VALUES (:email, :password, :firstName, :lastName, :roles)");
        $stmt->execute([
            ':email' => $email,
            ':password' => $password,
            ':firstName' => $firstName,
            ':lastName' => $lastName,
            ':roles' => json_encode($roles)
        ]);
        return $this->pdo->lastInsertId();
    }
    public function getUserDetails($userId)
    {
        try {
            error_log('Welcome to getUserDetails()' . $userId);

            $userId =
                // Get basic user information
                $basicInfo = $this->getUserBasicInfo($userId);

            if (!$basicInfo) {
                error_log("No basic info found for user ID: " . $userId);
                return null;
            }
            error_log($basicInfo['user_fname']);
            // Get additional user information
            $userDetails = [
                'basic_info' => $basicInfo,
                'user_papers' => $this->getUserPapers($userId),
                'user_reviews' => $this->getUserReviews($userId),
                'call_for_papers' => $this->getCallForPapers()
            ];

            return $userDetails;
        } catch (PDOException $e) {
            error_log("Error fetching user details: " . $e->getMessage());
            return null;
        }
    }

    private function getUserBasicInfo($userId)
    {
        try {
            // error_log('Welcome to getUserBasicInfo() - User ID: ' . $userId);

            // Prepare SQL with proper parameter binding
            $sql = "SELECT 
                user_id,
                user_referral_id,
                user_prefixname,
                user_fname,
                user_mname,
                user_lname,
                user_email,
                user_designation,
                user_institution,
                user_status,
                user_roles,
                user_reg_date,
                user_last_login,
                user_countryCode,
                user_mobile,
                user_address_line1,
                user_address_line2,
                user_city,
                user_state,
                user_country,
                user_pin_code,
                user_profile_image,
                user_about_me,
                user_email_verified,
                user_mobile_verified,
                user_username,
                user_status,
                user_last_activity,
                
                -- Contact information
                CONCAT('+', user_countryCode, ' ', user_mobile) as full_phone_number,
                
                -- Full address
                CONCAT(
                    COALESCE(user_address_line1, ''), 
                    CASE WHEN user_address_line2 IS NOT NULL THEN CONCAT(', ', user_address_line2) ELSE '' END,
                    CASE WHEN user_city IS NOT NULL THEN CONCAT(', ', user_city) ELSE '' END,
                    CASE WHEN user_state IS NOT NULL THEN CONCAT(', ', user_state) ELSE '' END,
                    CASE WHEN user_country IS NOT NULL THEN CONCAT(', ', user_country) ELSE '' END,
                    CASE WHEN user_pin_code IS NOT NULL THEN CONCAT(' - ', user_pin_code) ELSE '' END
                ) as full_address,
                
                -- Verification status
                CASE 
                    WHEN user_email_verified = 1 AND user_mobile_verified = 1 THEN 'FULLY_VERIFIED'
                    WHEN user_email_verified = 1 THEN 'EMAIL_VERIFIED'
                    WHEN user_mobile_verified = 1 THEN 'MOBILE_VERIFIED'
                    ELSE 'NOT_VERIFIED'
                END as verification_status
                
            FROM users 
            WHERE user_id = :user_id";

            $stmt = $this->pdo->prepare($sql);

            // Bind parameter explicitly
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

            // Execute and fetch
            if ($stmt->execute()) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                // Add some formatted fields
                if ($result) {
                    // Format dates
                    $result['user_reg_date_formatted'] = date('F j, Y', strtotime($result['user_reg_date']));
                    $result['user_last_login_formatted'] = $result['user_last_login']
                        ? date('F j, Y H:i:s', strtotime($result['user_last_login']))
                        : 'Never';

                    // Format verification status for display
                    $result['email_verified'] = $result['user_email_verified'] == 1;
                    $result['mobile_verified'] = $result['user_mobile_verified'] == 1;

                    // Set default profile image if none exists
                    $result['profile_image_url'] = $result['user_profile_image']
                        ? Helper::config('app.url') . 'uploads/profiles/' . $result['user_profile_image']
                        : Helper::config('app.url') . 'assets/img/default-avatar.png';

                    // Parse roles from bit flags
                    $result['roles_array'] = [];
                    if ($result['user_roles'] & self::ROLE_ADMIN)
                        $result['roles_array'][] = 'Admin';
                    if ($result['user_roles'] & self::ROLE_MODERATOR)
                        $result['roles_array'][] = 'Moderator';
                    if ($result['user_roles'] & self::ROLE_REVIEWER)
                        $result['roles_array'][] = 'Reviewer';
                    if ($result['user_roles'] & self::ROLE_AUTHOR)
                        $result['roles_array'][] = 'Author';
                    if ($result['user_roles'] & self::ROLE_SUBSCRIBER)
                        $result['roles_array'][] = 'Subscriber';
                }

                // Debug log
                // error_log("Query result: " . print_r($result, true));

                return $result;
            } else {
                error_log("Execute failed: " . print_r($stmt->errorInfo(), true));
                return null;
            }

        } catch (PDOException $e) {
            error_log("PDO Error in getUserBasicInfo: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("User ID: " . $userId);
            return null;
        } catch (Exception $e) {
            error_log("General Error in getUserBasicInfo: " . $e->getMessage());
            return null;
        }
    }



    // models/User.php

    private function getUserPapers($userId)
    {
        try {
            $sql = "SELECT 
                p.*,
                ps.status_name,
                j.journal_name,
                CONCAT(u.user_fname, ' ', u.user_lname) as author_name
            FROM papers p
            LEFT JOIN paper_status ps ON p.status_id = ps.status_id
            LEFT JOIN journals j ON p.journal_id = j.journal_id
            LEFT JOIN users u ON p.author_id = u.user_id
            WHERE p.author_id = :userId
            ORDER BY p.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getUserPapers: " . $e->getMessage());
            return [];
        }
    }

    private function getUserReviews($userId)
    {
        try {
            $sql = "SELECT 
                r.*,
                p.paper_title,
                p.paper_abstract,
                ps.status_name as review_status,
                CONCAT(u.user_fname, ' ', u.user_lname) as author_name
            FROM reviews r
            LEFT JOIN papers p ON r.paper_id = p.paper_id
            LEFT JOIN paper_status ps ON r.status_id = ps.status_id
            LEFT JOIN users u ON p.author_id = u.user_id
            WHERE r.reviewer_id = :userId
            ORDER BY r.assigned_date DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getUserReviews: " . $e->getMessage());
            return [];
        }
    }

    public function getCallForPapers()
    {
        try {
            $sql = "SELECT 
                j.*,
                COUNT(p.paper_id) as submission_count
            FROM journals j
            LEFT JOIN papers p ON j.journal_id = p.journal_id
            WHERE j.submission_deadline >= CURRENT_DATE
            GROUP BY j.journal_id
            ORDER BY j.submission_deadline ASC
            LIMIT 5";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getCallForPapers: " . $e->getMessage());
            return [];
        }
    }

    // In User.php model
    public function storeEmailVerificationToken($userId, $token)
    {
        try {
            $stmt = $this->pdo->prepare("
            UPDATE users 
            SET email_verification_token = :token, token_created_at = CURRENT_TIMESTAMP 
            WHERE user_id = :userId
        ");
            return $stmt->execute([
                'userId' => $userId,
                'token' => $token
            ]);
        } catch (PDOException $e) {
            error_log("Error storing email verification token: " . $e->getMessage());
            return false;
        }
    }

    public function verifyEmailToken($token)
    {
        try {
            $stmt = $this->pdo->prepare("
            SELECT user_id FROM users 
            WHERE email_verification_token = :token 
            AND token_created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            LIMIT 1
        ");
            $stmt->execute(['token' => $token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? $result['user_id'] : false;
        } catch (PDOException $e) {
            error_log("Error verifying email token: " . $e->getMessage());
            return false;
        }
    }

    public function markEmailAsVerified($userId)
    {
        try {
            $stmt = $this->pdo->prepare("
            UPDATE users 
            SET is_email_verified = 1, 
                email_verification_token = NULL, 
                token_created_at = NULL 
            WHERE user_id = :userId
        ");
            return $stmt->execute(['userId' => $userId]);
        } catch (PDOException $e) {
            error_log("Error marking email as verified: " . $e->getMessage());
            return false;
        }
    }

    // In User.php model
    public function logActivity($userId, $action, $details = '')
    {
        try {
            $stmt = $this->pdo->prepare("
            INSERT INTO activity_logs (user_id, action, details, created_at)
            VALUES (:userId, :action, :details, CURRENT_TIMESTAMP)
        ");
            return $stmt->execute([
                'userId' => $userId,
                'action' => $action,
                'details' => $details
            ]);
        } catch (PDOException $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }


    public function getPdo()
    {
        return $this->pdo;
    }


}



