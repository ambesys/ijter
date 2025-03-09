<?php
// core/Helpers.php

class Helper
{

    private $pdo;
    const USER_CACHE_DURATION = 3600; // 1 hour in seconds

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    /**
     * Load and render a view with layout
     */
    public static function view($name, $data = [])
    {
        // Extract data to make variables available in view
        extract($data);



        // Don't cache authentication pages
        $skipCache = in_array($name, [
            'auth/login',
            'auth/register',
            'auth/forgot-password',
            'auth/reset-password'
        ]);

        if (!$skipCache) {
            // Generate cache key based on view name and user role (if logged in)
            $userId = self::getCurrentUserId();
            $cacheKey = "view_{$name}_" . ($userId ? "user_{$userId}" : 'guest');

            // Try to get from cache first
            $cachedContent = Cache::get($cacheKey);
            if ($cachedContent !== null) {
                return $cachedContent;
            }
        }

        // Start output buffering for the view content
        ob_start();

        // print_r($name);
        // Include the view file
        $viewFile = ROOT_PATH . '/views/' . $name . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View {$name} not found");
        }
        include $viewFile;

        // Get the view content
        $pageContent = ob_get_clean();

        // Start output buffering for the layout
        ob_start();

        // Determine the layout file based on the URL
        $currentUrl = $_SERVER['REQUEST_URI'];
        if (strpos($currentUrl, '/admin') !== false) {
            $layoutFile = ROOT_PATH . '/views/users/user-layout.php';
        } else if (strpos($currentUrl, '/user') !== false) {
            $layoutFile = ROOT_PATH . '/views/users/user-layout.php';
        } else {
            $layoutFile = ROOT_PATH . '/views/layouts/main.php';
        }

        if (!file_exists($layoutFile)) {
            throw new Exception("Layout file not found");
        }

        // Make sure $content is available in the layout
        $content = $pageContent;

        include $layoutFile;

        // Return the complete rendered page
        return ob_get_clean();
    }

    function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Generate CSRF token field
     */
    public static function csrfField()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken()
    {
        if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user is logged in and return user information
     */
    public static function auth()
    {
        if (isset($_SESSION['user_id'])) {
            return [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'status' => $_SESSION['user_status'],
                'roles' => $_SESSION['user_roles'] ?? [],
                'fname' => $_SESSION['user_fname']
            ];
        }
        return null;
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get configuration value
     */
    public static function config($key, $default = null)
    {
        global $config;

        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Generate active class for navigation
     */
    public static function activeClass($path, $class = 'active')
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = dirname($_SERVER['SCRIPT_NAME']);

        // Remove base path from current path
        if ($basePath !== '/') {
            $currentPath = str_replace($basePath, '', $currentPath);
        }

        return $currentPath === $path ? $class : '';
    }

    /**
     * Get old input value
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function old($key, $default = '')
    {
        return isset($_SESSION['old'][$key]) ? $_SESSION['old'][$key] : $default;
    }

    public static function displayFlashMessage()
    {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return '<div class="alert alert-' . $message['type'] . '">' . $message['message'] . '</div>';
        }
        return '';
    }

    
    public static function getUserDetails($userId = null) {
        try {
            if (!$userId) {
                $userId = self::getCurrentUserId();
            }
    
            if (!$userId) {
                return null;
            }
    
            $userModel = new User($GLOBALS['pdo']);
            return $userModel->getUserDetails($userId);
    
        } catch (Exception $e) {
            error_log("Helper getUserDetails error: " . $e->getMessage());
            return null;
        }
    }

    // Cache management methods
    private static function getCache($key) {
        if (isset($_SESSION['cache'][$key])) {
            $cached = $_SESSION['cache'][$key];
            if ($cached['expires'] > time()) {
                return $cached['data'];
            }
            // Remove expired cache
            unset($_SESSION['cache'][$key]);
        }
        return null;
    }

    private static function setCache($key, $data, $duration) {
        $_SESSION['cache'][$key] = [
            'data' => $data,
            'expires' => time() + $duration
        ];
    }

    public static function clearCache($key = null) {
        if ($key === null) {
            // Clear all cache
            $_SESSION['cache'] = [];
        } else {
            // Clear specific cache key
            unset($_SESSION['cache'][$key]);
        }
    }

    // Add method to clear user's cache specifically
    public static function clearUserCache($userId = null) {
        if (!$userId) {
            $userId = self::getCurrentUserId();
        }
        self::clearCache("user_details_{$userId}");
    }

    public static function refreshUserDetails()
    {
        $userId = self::getCurrentUserId();
        if ($userId) {
            Cache::clear("user_details_{$userId}");
        }
    }

    // core/Helper.php

    public static function logActivity($userId, $action, $details = '', $ipAddress = null)
    {
        try {
            // Get PDO instance from User model since it's already handling database connections
            $userModel = new User($GLOBALS['pdo']); // Using global PDO instance
            $pdo = $userModel->getPdo(); // Add this getter method to User class

            // Get activity type ID
            $stmt = $pdo->prepare("SELECT activity_type_id FROM activity_types WHERE activity_type_name = ?");
            $stmt->execute([$action]);
            $activityTypeId = $stmt->fetchColumn();

            if (!$activityTypeId) {
                // Insert new activity type if it doesn't exist
                $stmt = $pdo->prepare("INSERT INTO activity_types (activity_type_name) VALUES (?)");
                $stmt->execute([$action]);
                $activityTypeId = $pdo->lastInsertId();
            }

            // Insert activity log
            $sql = "INSERT INTO activity_logs (
            user_id, 
            activity_type_id, 
            details, 
            ip_address, 
            created_at
        ) VALUES (?, ?, ?, ?, NOW())";

            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                $userId,
                $activityTypeId,
                $details,
                $ipAddress ?? $_SERVER['REMOTE_ADDR']
            ]);

        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
            return false;
        }
    }

    // Add this to core/Helpers.php

public static function getCountryCodes()
{
    return [
        '1' => 'USA/Canada',
        '44' => 'UK',
        '91' => 'India',
        '61' => 'Australia',
        '64' => 'New Zealand',
        '86' => 'China',
        '81' => 'Japan',
        '82' => 'South Korea',
        '65' => 'Singapore',
        '60' => 'Malaysia',
        '49' => 'Germany',
        '33' => 'France',
        '39' => 'Italy',
        '34' => 'Spain',
        '31' => 'Netherlands',
        '46' => 'Sweden',
        '47' => 'Norway',
        '45' => 'Denmark',
        '41' => 'Switzerland',
        '43' => 'Austria',
        '32' => 'Belgium',
        '351' => 'Portugal',
        '353' => 'Ireland',
        '358' => 'Finland',
        '48' => 'Poland',
        '420' => 'Czech Republic',
        '36' => 'Hungary',
        '30' => 'Greece',
        '7' => 'Russia',
        '380' => 'Ukraine',
        '971' => 'UAE',
        '966' => 'Saudi Arabia',
        '20' => 'Egypt',
        '27' => 'South Africa',
        '234' => 'Nigeria',
        '254' => 'Kenya',
        '55' => 'Brazil',
        '52' => 'Mexico',
        '54' => 'Argentina',
        '56' => 'Chile',
        '57' => 'Colombia',
        '51' => 'Peru',
        '58' => 'Venezuela'
    ];
}

public static function getCountries()
{
    return [
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'IN' => 'India',
        'AU' => 'Australia',
        'NZ' => 'New Zealand',
        'CN' => 'China',
        'JP' => 'Japan',
        'KR' => 'South Korea',
        'SG' => 'Singapore',
        'MY' => 'Malaysia',
        'DE' => 'Germany',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'NL' => 'Netherlands',
        'SE' => 'Sweden',
        'NO' => 'Norway',
        'DK' => 'Denmark',
        'CH' => 'Switzerland',
        'AT' => 'Austria',
        'BE' => 'Belgium',
        'PT' => 'Portugal',
        'IE' => 'Ireland',
        'FI' => 'Finland',
        'PL' => 'Poland',
        'CZ' => 'Czech Republic',
        'HU' => 'Hungary',
        'GR' => 'Greece',
        'RU' => 'Russia',
        'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates',
        'SA' => 'Saudi Arabia',
        'EG' => 'Egypt',
        'ZA' => 'South Africa',
        'NG' => 'Nigeria',
        'KE' => 'Kenya',
        'BR' => 'Brazil',
        'MX' => 'Mexico',
        'AR' => 'Argentina',
        'CL' => 'Chile',
        'CO' => 'Colombia',
        'PE' => 'Peru',
        'VE' => 'Venezuela'
    ];
}


    public static function setFlash($type, $message) {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][] = [
            'type' => $type,
            'message' => $message
        ];
    }

    public static function displayFlash() {
        if (!isset($_SESSION['flash'])) {
            return;
        }

        foreach ($_SESSION['flash'] as $flash) {
            echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show" role="alert">
                    ' . $flash['message'] . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }

        // Clear flash messages after displaying
        unset($_SESSION['flash']);
    }
}

