<?php
// core/functions.php

/**
 * Log user activity
 * 
 * @param PDO $pdo Database connection
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $description Description of the action
 * @param array $data Additional data (optional)
 * @return bool Success status
 */
function logActivity($pdo, $userId, $action, $description, $data = [])
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (activity_user_id, activity_action, activity_description, activity_data, activity_ip, activity_created_at)
            VALUES (:user_id, :action, :description, :data, :ip_address, :created_at)
        ");

        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'data' => !empty($data) ? json_encode($data) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return true;
    } catch (PDOException $e) {
        // Log error but don't stop execution
        error_log('Activity logging error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Format date and time
 * 
 * @param string $datetime Date and time string
 * @param string $format Format string
 * @return string Formatted date and time
 */
function formatDateTime($datetime, $format = 'M j, Y g:i A')
{
    if (empty($datetime)) {
        return '';
    }

    $date = new DateTime($datetime);
    return $date->format($format);
}

/**
 * Format date
 * 
 * @param string $date Date string
 * @param string $format Format string
 * @return string Formatted date
 */
function formatDate($date, $format = 'M j, Y')
{
    if (empty($date)) {
        return '';
    }

    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}

/**
 * Generate a random string
 * 
 * @param int $length Length of the string
 * @return string Random string
 */
function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

/**
 * Sanitize input
 * 
 * @param string $input Input string
 * @return string Sanitized string
 */
function sanitize($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format currency
 * 
 * @param float $amount Amount
 * @param string $currency Currency code
 * @return string Formatted currency
 */
function formatCurrency($amount, $currency = 'USD')
{
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'INR' => '₹'
    ];

    $symbol = $symbols[$currency] ?? $currency . ' ';

    return $symbol . number_format($amount, 2);
}

/**
 * Get file extension
 * 
 * @param string $filename Filename
 * @return string File extension
 */
function getFileExtension($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file extension is allowed
 * 
 * @param string $filename Filename
 * @param array $allowedExtensions Allowed extensions
 * @return bool True if allowed, false otherwise
 */
function isAllowedExtension($filename, $allowedExtensions = [])
{
    if (empty($allowedExtensions)) {
        $allowedExtensions = config('uploads.allowed_extensions', ['pdf', 'doc', 'docx']);
    }

    $extension = getFileExtension($filename);
    return in_array($extension, $allowedExtensions);
}

/**
 * Generate a unique filename
 * 
 * @param string $originalFilename Original filename
 * @return string Unique filename
 */
function generateUniqueFilename($originalFilename)
{
    $extension = getFileExtension($originalFilename);
    return uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Get paper status label with appropriate color class
 * 
 * @param string $status Status
 * @return array Status label and class
 */
function getPaperStatusInfo($status)
{
    $statusInfo = [
        'DRAFT' => ['label' => 'Draft', 'class' => 'bg-secondary'],
        'SUBMITTED' => ['label' => 'Submitted', 'class' => 'bg-info'],
        'UNDER_REVIEW' => ['label' => 'Under Review', 'class' => 'bg-warning'],
        'REVISION_REQUESTED' => ['label' => 'Revision Requested', 'class' => 'bg-warning'],
        'ACCEPTED' => ['label' => 'Accepted', 'class' => 'bg-success'],
        'REJECTED' => ['label' => 'Rejected', 'class' => 'bg-danger'],
        'PUBLISHED' => ['label' => 'Published', 'class' => 'bg-primary'],
        'WITHDRAWN' => ['label' => 'Withdrawn', 'class' => 'bg-secondary']
    ];

    return $statusInfo[$status] ?? ['label' => ucfirst(str_replace('_', ' ', $status)), 'class' => 'bg-secondary'];
}

/**
 * Get payment status label with appropriate color class
 * 
 * @param string $status Status
 * @return array Status label and class
 */
function getPaymentStatusInfo($status)
{
    $statusInfo = [
        'PENDING' => ['label' => 'Pending', 'class' => 'bg-warning'],
        'COMPLETED' => ['label' => 'Completed', 'class' => 'bg-success'],
        'FAILED' => ['label' => 'Failed', 'class' => 'bg-danger'],
        'WAIVED' => ['label' => 'Waived', 'class' => 'bg-info']
    ];

    return $statusInfo[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-secondary'];
}

/**
 * Truncate text to a specific length
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $append String to append if truncated
 * @return string Truncated text
 */
function truncateText($text, $length = 100, $append = '...')
{
    if (strlen($text) <= $length) {
        return $text;
    }

    $text = substr($text, 0, $length);
    $text = substr($text, 0, strrpos($text, ' '));

    return $text . $append;
}

/**
 * Check if a string is a valid JSON
 * 
 * @param string $string String to check
 * @return bool True if valid JSON, false otherwise
 */
function isValidJson($string)
{
    if (!is_string($string) || empty($string)) {
        return false;
    }

    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Get current URL
 * 
 * @return string Current URL
 */
function getCurrentUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];

    return $protocol . '://' . $host . $uri;
}

/**
 * Check if current URL matches a specific pattern
 * 
 * @param string $pattern URL pattern
 * @return bool True if matches, false otherwise
 */
function isCurrentUrl($pattern)
{
    $currentUrl = $_SERVER['REQUEST_URI'];
    return strpos($currentUrl, $pattern) !== false;
}

/**
 * Get active class if current URL matches a specific pattern
 * 
 * @param string $pattern URL pattern
 * @param string $class Class to return if active
 * @return string Class if active, empty string otherwise
 */
function activeClass($pattern, $class = 'active')
{
    return isCurrentUrl($pattern) ? $class : '';
}

/**
 * Redirect to a specific URL
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/**
 * Set flash message
 * 
 * @param string $type Message type (success, error, warning, info)
 * @param string $message Message content
 * @return void
 */
function setFlashMessage($type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message,
        'timestamp' => time()
    ];
}

/**
 * Get flash message
 * 
 * @return array|null Flash message or null if none
 */
function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }

    return null;
}

/**
 * Display flash message
 * 
 * @return string HTML for flash message
 */
function displayFlashMessage()
{
    $message = getFlashMessage();

    if (!$message) {
        return '';
    }

    $type = $message['type'];
    $content = $message['message'];

    $alertClass = 'alert-info';

    switch ($type) {
        case 'success':
            $alertClass = 'alert-success';
            break;
        case 'error':
            $alertClass = 'alert-danger';
            break;
        case 'warning':
            $alertClass = 'alert-warning';
            break;
    }

    return '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
                ' . $content . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Generate a DOI (Digital Object Identifier) for a paper
 * 
 * @param string $prefix Journal DOI prefix
 * @param string $paperId Paper ID
 * @return string DOI
 */
function generateDOI($prefix, $paperId)
{
    return $prefix . '/' . $paperId;
}

/**
 * Format a citation in APA style
 * 
 * @param array $paper Paper data
 * @param array $authors Author details
 * @return string Formatted citation
 */
function formatAPACitation($paper, $authors)
{
    $authorCitation = '';

    if (isset($authors['main_author'])) {
        $mainAuthor = $authors['main_author'];
        $authorName = $mainAuthor['user_lname'];

        if (!empty($mainAuthor['user_fname'])) {
            $authorName .= ', ' . substr($mainAuthor['user_fname'], 0, 1) . '.';

            if (!empty($mainAuthor['user_mname'])) {
                $authorName .= ' ' . substr($mainAuthor['user_mname'], 0, 1) . '.';
            }
        }

        $authorCitation = $authorName;

        // Add co-authors if available
        if (!empty($authors['co_authors'])) {
            $coAuthors = $authors['co_authors'];

            if (count($coAuthors) === 1) {
                $coAuthor = reset($coAuthors);
                $coAuthorName = $coAuthor['user_lname'];

                if (!empty($coAuthor['user_fname'])) {
                    $coAuthorName .= ', ' . substr($coAuthor['user_fname'], 0, 1) . '.';

                    if (!empty($coAuthor['user_mname'])) {
                        $coAuthorName .= ' ' . substr($coAuthor['user_mname'], 0, 1) . '.';
                    }
                }

                $authorCitation .= ' & ' . $coAuthorName;
            } else {
                $authorCitation .= ' et al.';
            }
        }
    } else {
        $authorCitation = 'Unknown Author';
    }

    $year = date('Y', strtotime($paper['paper_publication_date'] ?? $paper['paper_submission_date']));
    $title = $paper['paper_title'];
    $journalName = config('journal.name', 'Research Journal');
    $volume = $paper['paper_volume'] ?? '';
    $issue = $paper['paper_issue'] ?? '';
    $pages = $paper['paper_pages'] ?? '';
    $doi = $paper['paper_doi'] ?? '';

    $citation = $authorCitation . ' (' . $year . '). ' . $title . '. ';
    $citation .= '<em>' . $journalName . '</em>';

    if ($volume) {
        $citation .= ', ' . $volume;
        if ($issue) {
            $citation .= '(' . $issue . ')';
        }
    }

    if ($pages) {
        $citation .= ', ' . $pages;
    }

    if ($doi) {
        $citation .= '. https://doi.org/' . $doi;
    }

    return $citation;
}

/**
 * Format a citation in MLA style
 * 
 * @param array $paper Paper data
 * @param array $authors Author details
 * @return string Formatted citation
 */
function formatMLACitation($paper, $authors)
{
    $authorCitation = '';

    if (isset($authors['main_author'])) {
        $mainAuthor = $authors['main_author'];
        $authorName = $mainAuthor['user_lname'] . ', ' . $mainAuthor['user_fname'];

        if (!empty($mainAuthor['user_mname'])) {
            $authorName .= ' ' . $mainAuthor['user_mname'];
        }

        $authorCitation = $authorName;

        // Add co-authors if available
        if (!empty($authors['co_authors'])) {
            $coAuthors = $authors['co_authors'];

            if (count($coAuthors) === 1) {
                $coAuthor = reset($coAuthors);
                $coAuthorName = $coAuthor['user_fname'] . ' ';

                if (!empty($coAuthor['user_mname'])) {
                    $coAuthorName .= $coAuthor['user_mname'] . ' ';
                }

                $coAuthorName .= $coAuthor['user_lname'];

                $authorCitation .= ', and ' . $coAuthorName;
            } else {
                $authorCitation .= ', et al.';
            }
        }
    } else {
        $authorCitation = 'Unknown Author';
    }

    $title = '"' . $paper['paper_title'] . '."';
    $journalName = '<em>' . config('journal.name', 'Research Journal') . '</em>';
    $volume = $paper['paper_volume'] ?? '';
    $issue = $paper['paper_issue'] ?? '';
    $year = date('Y', strtotime($paper['paper_publication_date'] ?? $paper['paper_submission_date']));
    $pages = $paper['paper_pages'] ?? '';
    $doi = $paper['paper_doi'] ?? '';

    $citation = $authorCitation . '. ' . $title . ' ' . $journalName;

    if ($volume) {
        $citation .= ', vol. ' . $volume;
        if ($issue) {
            $citation .= ', no. ' . $issue;
        }
    }

    $citation .= ', ' . $year;

    if ($pages) {
        $citation .= ', pp. ' . $pages;
    }

    if ($doi) {
        $citation .= '. DOI: ' . $doi;
    }

    return $citation;
}

/**
 * Calculate review score based on individual criteria scores
 * 
 * @param array $scores Array of criteria scores
 * @return float Average score
 */
function calculateReviewScore($scores)
{
    if (empty($scores)) {
        return 0;
    }

    return array_sum($scores) / count($scores);
}

/**
 * Get review decision recommendation based on score
 * 
 * @param float $score Review score
 * @return string Decision recommendation
 */
function getReviewDecision($score)
{
    if ($score >= 4.5) {
        return 'ACCEPT';
    } elseif ($score >= 3.5) {
        return 'MINOR_REVISION';
    } elseif ($score >= 2.5) {
        return 'MAJOR_REVISION';
    } else {
        return 'REJECT';
    }
}

/**
 * Generate a paper ID with prefix
 * 
 * @param string $prefix Journal prefix
 * @return string Paper ID
 */
function generatePaperId($prefix = 'RJ')
{
    $year = date('Y');
    $month = date('m');
    $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

    return $prefix . $year . $month . $random;
}

/**
 * Check if a deadline is approaching (within 3 days)
 * 
 * @param string $deadline Deadline date
 * @return bool True if approaching, false otherwise
 */
function isDeadlineApproaching($deadline)
{
    $deadlineDate = new DateTime($deadline);
    $now = new DateTime();
    $diff = $now->diff($deadlineDate);

    return $diff->days <= 3 && $diff->invert === 0;
}

/**
 * Check if a deadline has passed
 * 
 * @param string $deadline Deadline date
 * @return bool True if passed, false otherwise
 */
function isDeadlinePassed($deadline)
{
    $deadlineDate = new DateTime($deadline);
    $now = new DateTime();

    return $now > $deadlineDate;
}

/**
 * Get time elapsed since a date
 * 
 * @param string $datetime Date and time
 * @return string Time elapsed
 */
function timeElapsedString($datetime)
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    }
    if ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    }
    if ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    }
    if ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    }
    if ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }

    return 'just now';
}

/**
 * Get user full name
 * 
 * @param array $user User data
 * @return string Full name
 */
function getUserFullName($user)
{
    $name = '';

    if (!empty($user['user_prefixname'])) {
        $name .= $user['user_prefixname'] . ' ';
    }

    $name .= $user['user_fname'];

    if (!empty($user['user_mname'])) {
        $name .= ' ' . $user['user_mname'];
    }

    $name .= ' ' . $user['user_lname'];

    return $name;
}

/**
 * Get user initials
 * 
 * @param array $user User data
 * @return string Initials
 */
function getUserInitials($user)
{
    $initials = substr($user['user_fname'], 0, 1);

    if (!empty($user['user_lname'])) {
        $initials .= substr($user['user_lname'], 0, 1);
    }

    return strtoupper($initials);
}

/**
 * Format paper authors for display
 * 
 * @param array $authors Author details
 * @return string Formatted authors
 */
function formatPaperAuthors($authors)
{
    if (empty($authors)) {
        return 'Unknown Author';
    }

    $formattedAuthors = [];

    if (isset($authors['main_author'])) {
        $formattedAuthors[] = getUserFullName($authors['main_author']);
    }

    if (!empty($authors['co_authors'])) {
        foreach ($authors['co_authors'] as $coAuthor) {
            $formattedAuthors[] = getUserFullName($coAuthor);
        }
    }

    if (count($formattedAuthors) === 1) {
        return $formattedAuthors[0];
    } elseif (count($formattedAuthors) === 2) {
        return $formattedAuthors[0] . ' and ' . $formattedAuthors[1];
    } else {
        $last = array_pop($formattedAuthors);
        return implode(', ', $formattedAuthors) . ', and ' . $last;
    }
}

/**
 * Get config value
 * 
 * @param string $key Config key
 * @param mixed $default Default value
 * @return mixed Config value
 */
function config($key, $default = null)
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
 * Load model
 * 
 * @param string $name Model name
 * @return object Model instance
 */
function model($name)
{
    global $app;

    $modelName = ucfirst($name);
    $modelFile = ROOT_PATH . '/models/' . $modelName . '.php';

    if (!file_exists($modelFile)) {
        throw new Exception("Model {$modelName} not found");
    }

    require_once $modelFile;

    return new $modelName($app['pdo']);
}

/**
 * Render a view file
 * 
 * @param string $view View file path
 * @param array $data Data to pass to the view
 * @param string $layout Layout file to use
 * @return void
 */
function render($view, $data = [], $layout = 'main')
{
    // Extract data to make variables available in the view
    extract($data);

    // Start output buffering
    ob_start();

    // Include the view file
    $viewPath = ROOT_PATH . '/views/' . $view . '.php';

    if (file_exists($viewPath)) {
        include $viewPath;
    } else {
        throw new Exception("View file not found: {$view}");
    }

    // Get the content of the view
    $content = ob_get_clean();

    // Determine which layout to use
    if ($layout === false) {
        // No layout, just output the view content
        echo $content;
    } else {
        // Include the layout file
        $layoutPath = ROOT_PATH . '/views/layouts/' . $layout . '.php';

        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            throw new Exception("Layout file not found: {$layout}");
        }
    }

}
