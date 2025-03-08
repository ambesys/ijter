<?php

// Don't redefine ROOT_PATH if it's already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Load environment variables if not already loaded
$env = [];
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $env[trim($name)] = trim($value);
        }
    }
}

// Configuration array
$config = [
    'app' => [
        'name' => 'IJTER',
        'url' => $env['APP_URL'] ?? 'http://localhost',
        'env' => $env['APP_ENV'] ?? 'development',
        'debug' => $env['APP_DEBUG'] ?? true,
        'timezone' => 'UTC',
        'upload_dir' => ROOT_PATH . '/uploads',
        'session_lifetime' => 24 * 60 * 60 // Fixed syntax error here
    ],
    'database' => [
        'host' => $env['DB_HOST'] ?? 'localhost',
        'dbname' => $env['DB_NAME'] ?? 'ijter',
        'username' => $env['DB_USER'] ?? 'root',
        'password' => $env['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],
    'mail' => [
        'host' => $env['MAIL_HOST'] ?? 'smtp.example.com',
        'port' => $env['MAIL_PORT'] ?? 587,
        'username' => $env['MAIL_USERNAME'] ?? 'noreply@example.com',
        'password' => $env['MAIL_PASSWORD'] ?? '',
        'encryption' => $env['MAIL_ENCRYPTION'] ?? 'tls',
        'from_address' => $env['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
        'from_name' => $env['MAIL_FROM_NAME'] ?? 'IJTER Journal',
    ],
    'session' => [
        'lifetime' => $env['SESSION_LIFETIME'] ?? 120,
        'secure' => ($env['SESSION_SECURE'] ?? 'false') === 'true',
        'http_only' => true,
    ],
    'payment' => [
        'currency' => $env['PAYMENT_CURRENCY'] ?? 'USD',
        'submission_fee' => $env['SUBMISSION_FEE'] ?? '50.00',
        'publication_fee' => $env['PUBLICATION_FEE'] ?? '100.00',
        'gateway' => $env['PAYMENT_GATEWAY'] ?? 'paypal',
        'gateways' => [
            'stripe' => [
                'public_key' => $env['STRIPE_PUBLIC_KEY'] ?? '',
                'secret_key' => $env['STRIPE_SECRET_KEY'] ?? ''
            ],
            'paypal' => [
                'client_id' => $env['PAYPAL_CLIENT_ID'] ?? '',
                'secret' => $env['PAYPAL_SECRET'] ?? ''
            ]
        ]
    ],

    'uploads' => [
        'papers_dir' => $env['PAPERS_UPLOAD_DIR'] ?? 'uploads/papers',
        'profiles_dir' => $env['PROFILES_UPLOAD_DIR'] ?? 'uploads/profiles',
        'max_file_size' => $env['MAX_UPLOAD_SIZE'] ?? 10485760, // 10MB
        'allowed_extensions' => ['pdf', 'doc', 'docx'],
    ],
    'oauth' => [
        'google_client_id' => $env['GOOGLE_CLIENT_ID'] ?? '',
        'google_client_secret' => $env['GOOGLE_CLIENT_SECRET'] ?? '',
        'facebook_app_id' => $env['FACEBOOK_APP_ID'] ?? '',
        'facebook_app_secret' => $env['FACEBOOK_APP_SECRET'] ?? '',
        'orcid_client_id' => $env['ORCID_CLIENT_ID'] ?? '',
        'orcid_client_secret' => $env['ORCID_CLIENT_SECRET'] ?? '',
    ],
    // Add paper status constants to match database schema
    'paper_statuses' => [
        'DRAFT',
        'SUBMITTED',
        'UNDER_REVIEW',
        'REVISION_REQUESTED',
        'ACCEPTED',
        'REJECTED',
        'PUBLISHED',
        'WITHDRAWN'
    ],
    // Add user roles to match database schema
    'user_roles' => [
        'ADMIN' => 1,
        'MODERATOR' => 2,
        'REVIEWER' => 4,
        'AUTHOR' => 8,
        'SUBSCRIBER' => 16
    ]
];

// Define journal constants
// In config/config.php, replace the constant definitions with:

if (!defined('JOURNAL_NAME')) {
    define('JOURNAL_NAME', $config['app']['name']);
}

if (!defined('JOURNAL_ISSN')) {
    define('JOURNAL_ISSN', $env['JOURNAL_ISSN'] ?? '2347-4289');
}

if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', $config['app']['env']);
}

// Set error reporting based on environment
if ($config['app']['env'] === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Set timezone
date_default_timezone_set($config['app']['timezone']);

// Set session settings before session starts
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', $config['session']['lifetime'] * 60);
    ini_set('session.gc_maxlifetime', $config['session']['lifetime'] * 60);

    session_set_cookie_params(
        $config['session']['lifetime'] * 60,
        '/',
        '',
        $config['session']['secure'],
        $config['session']['http_only']
    );
}

return $config;
