<?php
// bootstrap.php

// Define constants
define('ROOT_PATH', dirname(__FILE__));
define('APP_PATH', ROOT_PATH . '/app');

// Load configuration first
$config = require_once ROOT_PATH . '/config/config.php';

// Set error reporting based on config
if ($config['app']['debug']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// Verify required directories
$requiredDirs = [
    ROOT_PATH . '/uploads',
    ROOT_PATH . '/models',
    ROOT_PATH . '/controllers'
];



// Verify required files
// Verify required files
$requiredFiles = [
    ROOT_PATH . '/config/config.php',
    ROOT_PATH . '/core/functions.php',
    ROOT_PATH . '/core/auth.php',
    ROOT_PATH . '/core/Router.php',
    ROOT_PATH . '/core/AuthMiddleware.php',  // Add this
    ROOT_PATH . '/controllers/AdminController.php',
    ROOT_PATH . '/controllers/ReviewController.php',  // Add this
    ROOT_PATH . '/controllers/ModerationController.php',  // Add this
    ROOT_PATH . '/core/AuthMiddleware.php',
];


foreach ($requiredFiles as $file) {
    if (!file_exists($file)) {
        die("Required file missing: $file");
    }
}

// Load core files
require_once ROOT_PATH . '/core/functions.php';
require_once ROOT_PATH . '/core/auth.php';
require_once ROOT_PATH . '/core/Router.php';
require_once ROOT_PATH . '/core/Mailer.php';
require_once ROOT_PATH . '/core/RoleMiddleware.php';
require_once ROOT_PATH . '/core/Helpers.php';
require_once ROOT_PATH . '/core/Validator.php';
require_once ROOT_PATH . '/core/Cache.php';
require_once ROOT_PATH . '/core/Mailer.php';


// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize database connection
try {
    $pdo = new PDO(
        "mysql:host=" . $config['database']['host'] .
        ";dbname=" . $config['database']['dbname'] .
        ";charset=" . $config['database']['charset'],
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['options']
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please check the error log.");
}

// Load models
$modelFiles = glob(ROOT_PATH . '/models/*.php');
foreach ($modelFiles as $modelFile) {
    require_once $modelFile;
}

// Load controllers
$controllerFiles = glob(ROOT_PATH . '/controllers/*.php');
foreach ($controllerFiles as $controllerFile) {
    require_once $controllerFile;
}

// Initialize application
$app = [
    'pdo' => $pdo,
    'config' => $config
];

// Initialize controllers
$controllers = [
    'auth' => new AuthController($pdo),
    'journal' => new JournalController($pdo),
    'paper' => new PaperController($pdo),
    'payment' => new PaymentController($pdo),
    'user' => new UserController($pdo),
    'admin' => new AdminController($pdo),
    'review' => new ReviewController($pdo),
    'moderation' => new ModerationController($pdo) // Add this if you need moderation features

];

return $app;
