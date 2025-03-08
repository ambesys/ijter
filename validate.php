<?php
// validate.php

echo "Validating configuration and file structure...\n";

// Load bootstrap
$app = require_once __DIR__ . '/bootstrap.php';

// Check configuration
echo "Configuration loaded: " . (isset($app['config']) ? "YES" : "NO") . "\n";
echo "Environment: " . $app['config']['app']['environment'] . "\n";
echo "Site URL: " . $app['config']['app']['url'] . "\n";

// Check database connection
echo "Database connection: ";
try {
    $app['pdo']->query("SELECT 1");
    echo "SUCCESS\n";
} catch (PDOException $e) {
    echo "FAILED - " . $e->getMessage() . "\n";
}

// Check required directories
$directories = [
    $app['config']['paths']['uploads'],
    $app['config']['paths']['papers'],
    $app['config']['paths']['revisions'],
    $app['config']['paths']['payments'],
    $app['config']['paths']['profiles'],
    $app['config']['paths']['reviewer_applications']
];

echo "Checking directories:\n";
foreach ($directories as $dir) {
    echo "- $dir: " . (file_exists($dir) ? "EXISTS" : "MISSING") . "\n";
}

// Check required files
$required_files = [
    '/config/config.php',
    '/config/journal-details.php',
    '/core/functions.php',
    '/core/auth.php',
    '/includes/functions.php',
    '/includes/common_data.php',
    '/models/User.php',
    '/models/Paper.php',
    '/models/Review.php',
    '/models/Payment.php',
    '/models/Notification.php',
    '/models/Activity.php',
    '/models/JournalDetails.php',
    '/models/Issue.php',
    '/controllers/UserController.php',
    '/controllers/PaperController.php',
    '/controllers/AdminController.php',
    '/controllers/AuthController.php',
    '/controllers/SearchController.php'
];

echo "Checking required files:\n";
foreach ($required_files as $file) {
    $full_path = $app['config']['paths']['root'] . $file;
    echo "- $file: " . (file_exists($full_path) ? "EXISTS" : "MISSING") . "\n";
}

echo "Validation complete.\n";
