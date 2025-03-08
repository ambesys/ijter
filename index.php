<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/bootstrap.php';

// Initialize Router
$router = new Router();

// Path handling
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($scriptName);

// Remove base path from URI if it exists
if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Normalize the path
$path = '/' . trim($uri, '/');

// Handle captcha
// At the top of index.php, after path handling but before routes
if ($path === '/captcha') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    header('Content-Type: image/png');
    require_once __DIR__ . '/core/captcha.php';
    exit();
}


// Public routes
$router->get('/', [$controllers['journal'], 'home']);
$router->get('/about', [$controllers['journal'], 'about']);
$router->get('/editorial-board', [$controllers['journal'], 'editorialBoard']);
$router->get('/call-for-papers', [$controllers['journal'], 'callForPapers']);
$router->get('/contact', [$controllers['journal'], 'contact']);
$router->post('/contact', [$controllers['journal'], 'submitContact']);

// Guidelines routes
$router->get('/guidelines/author', [$controllers['journal'], 'guidelines']);
$router->get('/guidelines/reviewer', [$controllers['journal'], 'guidelines']);
$router->get('/guidelines/ethics', [$controllers['journal'], 'guidelines']);

// Archives routes
$router->get('/archives', [$controllers['journal'], 'archives']);
$router->get('/archives/volume/:volume/issue/:issue', [$controllers['journal'], 'issueDetail']);

// Auth routes (flattened from /auth prefix)
$router->get('/login', [$controllers['auth'], 'loginForm']);
$router->post('/login', [$controllers['auth'], 'login']);
$router->get('/register', [$controllers['auth'], 'registerForm']);
$router->post('/register', [$controllers['auth'], 'register']);
$router->get('/logout', [$controllers['auth'], 'logout']);
$router->get('/forgot-password', [$controllers['auth'], 'forgotPasswordForm']);
$router->post('/forgot-password', [$controllers['auth'], 'forgotPassword']);
$router->get('/reset-password/:token', [$controllers['auth'], 'resetPasswordForm']);
$router->post('/reset-password', [$controllers['auth'], 'resetPassword']);

// Admin routes
$router->get('/admin', [$controllers['admin'], 'dashboard']);
// Add other admin routes here as needed

// User routes (including papers and reviews)
$router->get('/user', [$controllers['auth'], 'dashboard']);
$router->get('/user/dashboard', [$controllers['auth'], 'dashboard']);
$router->get('/user/profile', [$controllers['user'], 'profile']);
$router->post('/user/profile', [$controllers['user'], 'updateProfile']);
$router->get('/user/papers', [$controllers['user'], 'papers']);
$router->get('/user/reviews', [$controllers['user'], 'reviews']);

// Paper routes (moved under user for authenticated actions)
$router->get('/user/paper/submit', [$controllers['paper'], 'submitForm']);
$router->post('/user/paper/submit', [$controllers['paper'], 'submit']);
$router->get('/user/paper/:id', [$controllers['paper'], 'view']);
$router->get('/user/paper/:id/edit', [$controllers['paper'], 'editForm']);
$router->post('/user/paper/:id/edit', [$controllers['paper'], 'edit']);
$router->post('/user/paper/:id/delete', [$controllers['paper'], 'delete']);

// Public paper view (if needed)
$router->get('/paper/:id', [$controllers['paper'], 'view']);

// Dispatch the request
$router->dispatch($_SERVER['REQUEST_METHOD'], $path);
