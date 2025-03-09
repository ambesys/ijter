<?php
// tree-diagnostic.php - Display project structure with missing and misplaced files summary

// Start with a clean output
ob_start();

// Define root path
$rootPath = __DIR__;

// Define expected files and their locations based on folder-structure-new
$expectedFiles = [
    // Root directory files
    'bootstrap.php' => 'Root directory - Application initialization',
    'index.php' => 'Root directory - Main entry point',
    '.env' => 'Root directory - Environment-specific variables',
    '.htaccess' => 'Root directory - Security and URL rewriting',
    'README.md' => 'Root directory - Project documentation',
    
    // Config files
    'config/config.php' => 'Main configuration file',
    'config/journal-details.php' => 'Journal details configuration',
    
    // Core files
    'core/functions.php' => 'Core system functions',
    'core/auth.php' => 'Core authentication functions',
    'core/database.php' => 'Database connection handler',
    
    // Models
    'models/User.php' => 'User model',
    'models/Paper.php' => 'Paper model',
    'models/Journal.php' => 'Journal model',
    'models/Payment.php' => 'Payment model',
    'models/Review.php' => 'Review model',
    'models/Activity.php' => 'Activity model',
    'models/Notification.php' => 'Notification model',
    
    // Controllers
    'controllers/AuthController.php' => 'Authentication controller',
    'controllers/UserController.php' => 'User management controller',
    'controllers/PaperController.php' => 'Paper management controller',
    'controllers/JournalController.php' => 'Journal management controller',
    'controllers/PaymentController.php' => 'Payment processing controller',
    
    // Public directory structure
    'public/index.php' => 'Public homepage',
    'public/call-for-papers.php' => 'Call for papers page',
    'public/search.php' => 'Search functionality',
    'public/editorial_board.php' => 'Editorial board page',
    'public/contact_us.php' => 'Contact page',
    'public/research_areas.php' => 'Research areas page',
    'public/publication_guidelines.php' => 'Publication guidelines',
    'public/sample_paper_format.php' => 'Sample paper format',
    'public/processing_charges.php' => 'Processing charges information',
    'public/hardcopy_doi_charges.php' => 'Hardcopy and DOI charges',
    'public/check_paper_status.php' => 'Check paper status',
    'public/approval_license.php' => 'Approval license information',
    
    // Public user directory
    'public/user/dashboard.php' => 'User dashboard',
    'public/user/submit-paper.php' => 'Submit paper form',
    'public/user/papers.php' => 'User papers list',
    'public/user/payment_status.php' => 'Payment status',
    'public/user/document_submission.php' => 'Document submission',
    'public/user/review_status.php' => 'Review status',
    'public/user/publication_status.php' => 'Publication status',
    'public/user/doi_request.php' => 'DOI request',
    'public/user/hardcopy_request.php' => 'Hardcopy request',
    'public/user/download_paper.php' => 'Download paper',
    'public/user/download_confirmation_letter.php' => 'Download confirmation letter',
    'public/user/download_ecertificate.php' => 'Download e-certificate',
    'public/user/download_journal_pages.php' => 'Download journal pages',
    'public/user/review.php' => 'Review page',
    'public/user/navbar.php' => 'User navbar',
    'public/user/menu.php' => 'User menu',
    
    // Admin directory structure
    'admin/dashboard.php' => 'Admin dashboard',
    'admin/manage-users.php' => 'User management',
    'admin/manage-papers.php' => 'Paper management',
    'admin/journal-settings.php' => 'Journal settings',
    'admin/navbar.php' => 'Admin navbar',
    'admin/menu.php' => 'Admin menu',
    'admin/manage_reviews.php' => 'Manage reviews',
    'admin/manage_payments.php' => 'Manage payments',
    'admin/seo_settings.php' => 'SEO settings',
    'admin/update_issue_details.php' => 'Update issue details',
    'admin/control_menu_items.php' => 'Control menu items',
    'admin/journal_information.php' => 'Journal information',
    
    // Database
    'db/users.sql' => 'Users table schema',
    'db/papers.sql' => 'Papers table schema',
    'db/reviews.sql' => 'Reviews table schema',
    'db/payments.sql' => 'Payments table schema',
    'db/journal_details.sql' => 'Journal details schema',
    'db/activity_logs.sql' => 'Activity logs schema'
];

// Define required directories based on folder-structure-new
$requiredDirectories = [
    'public',
    'public/user',
    'public/assets',
    'public/includes',
    'admin',
    'config',
    'core',
    'models',
    'controllers',
    'views',
    'db',
    'documents'
];

// Define file type patterns for detecting misplaced files
$filePatterns = [
    'controller' => '/^[A-Z][a-zA-Z0-9]*Controller\.php$/',
    'model' => '/^[A-Z][a-zA-Z0-9]*\.php$/',
    'view' => '/^[a-z0-9_-]+\.(php|html)$/',
    'config' => '/^[a-z0-9_-]+\.php$/',
    'include' => '/^[a-z0-9_-]+\.php$/',
    'asset' => '/\.(css|js|jpg|jpeg|png|gif|svg|pdf)$/'
];

// Function to check if a file exists and is readable
function checkFile($path) {
    if (file_exists($path)) {
        if (is_readable($path)) {
            return "✅";
        } else {
            return "⚠️";
        }
    } else {
        return "❌";
    }
}

// Function to get file status description
function getFileStatus($status) {
    switch ($status) {
        case "✅": return "exists";
        case "⚠️": return "not readable";
        case "❌": return "missing";
        default: return "";
    }
}

// Function to analyze a file and suggest proper location
function analyzeFile($path, $relativePath) {
    global $filePatterns;
    
    $fileName = basename($path);
    $fileContent = @file_get_contents($path);
    $fileType = pathinfo($path, PATHINFO_EXTENSION);
    
    $suggestedLocation = null;
    $reason = null;
    
    // Skip certain files
    if ($fileName === '___PREV_TRIAL' || $fileName === 'tree-diagnostic.php' || $fileName === 'test.php') {
        return null;
    }
    
    // Check file patterns
    foreach ($filePatterns as $type => $pattern) {
        if (preg_match($pattern, $fileName)) {
            switch ($type) {
                case 'controller':
                    if (!strpos($relativePath, 'controllers/')) {
                        $suggestedLocation = 'controllers/';
                        $reason = "File appears to be a controller";
                    }
                    break;
                case 'model':
                    if (!strpos($relativePath, 'models/') && !preg_match('/Controller\.php$/', $fileName)) {
                        $suggestedLocation = 'models/';
                        $reason = "File appears to be a model";
                    }
                    break;
                case 'view':
                    if (!strpos($relativePath, 'views/') && !strpos($relativePath, 'public/') && 
                        !strpos($relativePath, 'admin/') && !strpos($relativePath, 'includes/')) {
                        $suggestedLocation = 'views/';
                        $reason = "File appears to be a view template";
                    }
                    break;
                case 'config':
                    if (!strpos($relativePath, 'config/') && strpos($fileContent, 'config')) {
                        $suggestedLocation = 'config/';
                        $reason = "File appears to contain configuration";
                    }
                    break;
                case 'include':
                    if (!strpos($relativePath, 'includes/') && strpos($fileContent, 'include') && 
                        !strpos($relativePath, 'controllers/') && !strpos($relativePath, 'models/')) {
                        $suggestedLocation = 'includes/';
                        $reason = "File appears to be an include file";
                    }
                    break;
                case 'asset':
                    if (!strpos($relativePath, 'public/assets/') && !strpos($relativePath, 'assets/')) {
                        $suggestedLocation = 'public/assets/';
                        $reason = "File appears to be an asset";
                    }
                    break;
            }
        }
    }
    
    // Check content for specific patterns
    if ($fileType === 'php') {
        // Check for authentication related code
        if (strpos($fileContent, 'login') !== false || 
            strpos($fileContent, 'register') !== false || 
            strpos($fileContent, 'password') !== false) {
            if (!strpos($relativePath, 'auth/') && !strpos($relativePath, 'core/auth.php')) {
                $suggestedLocation = 'auth/';
                $reason = "File contains authentication related code";
            }
        }
        
        // Check for database models
        if ((strpos($fileContent, 'SELECT') !== false || 
             strpos($fileContent, 'INSERT') !== false || 
             strpos($fileContent, 'UPDATE') !== false || 
             strpos($fileContent, 'DELETE') !== false) && 
            !strpos($relativePath, 'models/')) {
            $suggestedLocation = 'models/';
            $reason = "File contains database queries";
        }
        
        // Check for core functions
        if (strpos($fileContent, 'function') !== false && 
            count(explode('function', $fileContent)) > 3 && 
            !strpos($relativePath, 'core/') && 
            !strpos($relativePath, 'includes/')) {
            $suggestedLocation = 'core/';
            $reason = "File contains multiple functions";
        }
    }
    
    return [
        'path' => $path,
        'relativePath' => $relativePath,
        'fileName' => $fileName,
        'fileType' => $fileType,
        'suggestedLocation' => $suggestedLocation,
        'reason' => $reason
    ];
}

// Function to recursively scan directory and build tree
function buildDirectoryTree($dir, $baseDir = '', &$missingFiles = [], &$misplacedFiles = []) {
    global $expectedFiles, $rootPath;
    $result = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === '___PREV_TRIAL') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        $relativePath = $baseDir ? $baseDir . '/' . $file : $file;
        
        if (is_dir($path)) {
            $children = buildDirectoryTree($path, $relativePath, $missingFiles, $misplacedFiles);
            $result[$file] = [
                'type' => 'directory',
                'path' => $relativePath,
                'children' => $children
            ];
        } else {
            $status = checkFile($path);
            $result[$file] = [
                'type' => 'file',
                'path' => $relativePath,
                'status' => $status
            ];
            
            // Check if this is a potentially misplaced file
            $analysis = analyzeFile($path, $relativePath);
            if ($analysis && $analysis['suggestedLocation']) {
                $misplacedFiles[] = $analysis;
            }
        }
    }
    
    return $result;
}

// Check for missing expected files
function checkMissingFiles($rootPath, $expectedFiles) {
    $missingFiles = [];
    
    foreach ($expectedFiles as $file => $description) {
        $path = $rootPath . '/' . $file;
        if (!file_exists($path)) {
            $missingFiles[] = [
                'file' => $file,
                'description' => $description
            ];
        }
    }
    
    return $missingFiles;
}

// Function to check if a directory is required
function isRequiredDirectory($path, $requiredDirectories) {
    foreach ($requiredDirectories as $dir) {
        if ($path === $dir || strpos($path, $dir . '/') === 0) {
            return true;
        }
    }
    return false;
}

// Function to check if a file is required
function isRequiredFile($path, $expectedFiles) {
    return isset($expectedFiles[$path]);
}

// Function to analyze all files and identify unnecessary ones
// Function to analyze all files and identify unnecessary ones
function findUnnecessaryFiles($rootPath, $expectedFiles, $requiredDirectories) {
    $unnecessaryFiles = [];
    $unnecessaryDirs = [];
    $unnecessaryTree = [];
    
    // Define paths to always ignore
    $ignoredPaths = [
        '.git',
        '.vscode',
        '.DS_Store',
        'node_modules',
        'vendor'
    ];
    
    // Helper function to check if a path should be ignored
    $shouldIgnore = function($path) use ($ignoredPaths) {
        // Check if the path itself is in the ignore list
        if (in_array(basename($path), $ignoredPaths)) {
            return true;
        }
        
        // Check if the path is within an ignored directory
        foreach ($ignoredPaths as $ignoredPath) {
            if (strpos($path, $ignoredPath . '/') === 0) {
                return true;
            }
        }
        
        return false;
    };
    
    // Helper function to recursively scan directories
    $scanDir = function($dir, $relativePath = '') use (&$scanDir, &$unnecessaryFiles, &$unnecessaryDirs, &$unnecessaryTree, $expectedFiles, $requiredDirectories, $rootPath, $shouldIgnore) {
        $items = scandir($dir);
        $currentDirNode = [];
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === '___PREV_TRIAL') {
                continue;
            }
            
            $path = $dir . '/' . $item;
            $relPath = $relativePath ? $relativePath . '/' . $item : $item;
            
            // Skip ignored paths
            if ($shouldIgnore($relPath)) {
                continue;
            }
            
            if (is_dir($path)) {
                // Check if directory is required
                $isRequired = isRequiredDirectory($relPath, $requiredDirectories);
                $dirType = 'required';
                $reason = 'Required directory';
                
                if (!$isRequired) {
                    // Special cases - some directories might be necessary even if not explicitly listed
                    if ($item === 'uploads' || $item === 'cache' || $item === 'logs') {
                        // These are common necessary directories
                        $dirType = 'optional';
                        $reason = 'Common necessary directory';
                        $unnecessaryDirs[] = [
                            'path' => $relPath,
                            'type' => 'optional',
                            'reason' => 'Common necessary directory'
                        ];
                    } else {
                        $dirType = 'unnecessary';
                        $reason = 'Not in required directory structure';
                        $unnecessaryDirs[] = [
                            'path' => $relPath,
                            'type' => 'unnecessary',
                            'reason' => 'Not in required directory structure'
                        ];
                    }
                }
                
                // Recursively scan subdirectories
                $childrenResult = $scanDir($path, $relPath);
                
                // Only add to tree if it's unnecessary or has unnecessary children
                if ($dirType !== 'required' || !empty($childrenResult)) {
                    $currentDirNode[$item] = [
                        'type' => 'directory',
                        'status' => $dirType,
                        'reason' => $reason,
                        'children' => $childrenResult
                    ];
                }
            } else {
                // Skip .DS_Store files
                if ($item === '.DS_Store') {
                    continue;
                }
                
                // Check if file is required
                $isRequired = isRequiredFile($relPath, $expectedFiles);
                $fileType = 'required';
                $reason = 'Required file';
                
                if (!$isRequired) {
                    // Special cases for common necessary files
                    $fileName = basename($relPath);
                    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                    
                    if ($fileName === '.gitignore' || $fileName === '.env.example' || 
                        $fileName === 'composer.json' || $fileName === 'package.json' ||
                        $fileName === 'LICENSE') {
                        $fileType = 'optional';
                        $reason = 'Development/configuration file';
                        $unnecessaryFiles[] = [
                            'path' => $relPath,
                            'type' => 'optional',
                            'reason' => 'Development/configuration file'
                        ];
                    } 
                    // Check for asset files which are typically needed
                    else if (in_array($ext, ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'pdf']) && 
                             (strpos($relPath, 'assets/') !== false || strpos($relPath, 'public/') !== false)) {
                        $fileType = 'asset';
                        $reason = 'Asset file (likely needed)';
                        $unnecessaryFiles[] = [
                            'path' => $relPath,
                            'type' => 'asset',
                            'reason' => 'Asset file (likely needed)'
                        ];
                    }
                    // Check for PHP files that might be part of the application
                    else if ($ext === 'php') {
                        // If it's in a required directory, it might be needed
                        $dirName = dirname($relPath);
                        if (isRequiredDirectory($dirName, $requiredDirectories)) {
                            $fileType = 'possible';
                            $reason = 'PHP file in required directory (might be needed)';
                            $unnecessaryFiles[] = [
                                'path' => $relPath,
                                'type' => 'possible',
                                'reason' => 'PHP file in required directory (might be needed)'
                            ];
                        } else {
                            $fileType = 'unnecessary';
                            $reason = 'PHP file not in required structure';
                            $unnecessaryFiles[] = [
                                'path' => $relPath,
                                'type' => 'unnecessary',
                                'reason' => 'PHP file not in required structure'
                            ];
                        }
                    } 
                    // Other files
                    else {
                        $fileType = 'unnecessary';
                        $reason = 'Not in required file list';
                        $unnecessaryFiles[] = [
                            'path' => $relPath,
                            'type' => 'unnecessary',
                            'reason' => 'Not in required file list'
                        ];
                    }
                    
                    // Add to tree if not required
                    if ($fileType !== 'required') {
                        $currentDirNode[$item] = [
                            'type' => 'file',
                            'status' => $fileType,
                            'reason' => $reason
                        ];
                    }
                }
            }
        }
        
        return $currentDirNode;
    };
    
    $unnecessaryTree = $scanDir($rootPath);
    
    return [
        'files' => $unnecessaryFiles,
        'directories' => $unnecessaryDirs,
        'tree' => $unnecessaryTree
    ];
}


// Get missing files
$missingFiles = checkMissingFiles($rootPath, $expectedFiles);

// Build the directory tree and collect misplaced files
$misplacedFiles = [];
$directoryTree = buildDirectoryTree($rootPath, '', $missingFiles, $misplacedFiles);

// Find unnecessary files and directories
$unnecessaryItems = findUnnecessaryFiles($rootPath, $expectedFiles, $requiredDirectories);
$unnecessaryFiles = $unnecessaryItems['files'];
$unnecessaryDirs = $unnecessaryItems['directories'];
$unnecessaryTree = $unnecessaryItems['tree'];

// Function to render the tree as HTML
function renderTree($tree, $level = 0) {
    $html = '<ul class="tree-view' . ($level === 0 ? ' root' : '') . '">';
    
    foreach ($tree as $name => $item) {
        $indent = str_repeat('  ', $level);
        $isDir = $item['type'] === 'directory';
        $icon = $isDir ? '📁' : '📄';
        $status = isset($item['status']) ? $item['status'] : '';
        $statusText = getFileStatus($status);
        
        $html .= '<li>';
        
        if ($isDir) {
            $html .= '<details' . ($level < 2 ? ' open' : '') . '>';
            $html .= '<summary class="directory">' . $icon . ' ' . htmlspecialchars($name) . '</summary>';
            $html .= renderTree($item['children'], $level + 1);
            $html .= '</details>';
        } else {
            $html .= '<span class="file">' . $icon . ' ' . htmlspecialchars($name) . ' <span class="status">' . $status . ' (' . $statusText . ')</span></span>';
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    return $html;
}

// Function to render the unnecessary files tree
function renderUnnecessaryTree($tree, $level = 0) {
    if (empty($tree)) {
        return '<p class="success">✅ No unnecessary files detected.</p>';
    }
    
    $html = '<ul class="tree-view' . ($level === 0 ? ' root' : '') . '">';
    
    foreach ($tree as $name => $item) {
        $indent = str_repeat('  ', $level);
        $isDir = $item['type'] === 'directory';
        $icon = $isDir ? '📁' : '📄';
        $status = isset($item['status']) ? $item['status'] : '';
        
        // Set CSS class based on status
        $statusClass = '';
        switch ($status) {
            case 'unnecessary':
                $statusClass = 'remove';
                break;
            case 'optional':
                $statusClass = 'keep-dev';
                break;
            case 'asset':
                $statusClass = 'keep';
                break;
            case 'possible':
                $statusClass = 'review';
                break;
        }
        
        $html .= '<li>';
        
        if ($isDir) {
            $html .= '<details' . ($level < 2 ? ' open' : '') . '>';
            $html .= '<summary class="directory ' . $statusClass . '">' . $icon . ' ' . htmlspecialchars($name) . 
                     ' <span class="status-badge ' . $statusClass . '">' . $status . '</span>' .
                     ' <span class="reason">(' . $item['reason'] . ')</span></summary>';
            
            if (!empty($item['children'])) {
                $html .= renderUnnecessaryTree($item['children'], $level + 1);
            } else {
                $html .= '<ul class="tree-view"><li><em>Empty directory</em></li></ul>';
            }
            
            $html .= '</details>';
        } else {
            $html .= '<span class="file ' . $statusClass . '">' . $icon . ' ' . htmlspecialchars($name) . 
                     ' <span class="status-badge ' . $statusClass . '">' . $status . '</span>' .
                     ' <span class="reason">(' . $item['reason'] . ')</span></span>';
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    return $html;
}

// Render missing files summary
function renderMissingFilesSummary($missingFiles) {
    if (empty($missingFiles)) {
        return '<p class="success">✅ All expected files are present.</p>';
    }
    
    $html = '<div class="summary-section">';
    $html .= '<h3>Missing Files</h3>';
    $html .= '<table>';
    $html .= '<tr><th>File Path</th><th>Description</th></tr>';
    
    foreach ($missingFiles as $file) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($file['file']) . '</td>';
        $html .= '<td>' . htmlspecialchars($file['description']) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    $html .= '</div>';
    
    return $html;
}

// Render misplaced files summary
function renderMisplacedFilesSummary($misplacedFiles) {
    if (empty($misplacedFiles)) {
        return '<p class="success">✅ No potentially misplaced files detected.</p>';
    }
    
    $html = '<div class="summary-section">';
    $html .= '<h3>Potentially Misplaced Files</h3>';
    $html .= '<table>';
    $html .= '<tr><th>File</th><th>Current Location</th><th>Suggested Location</th><th>Reason</th></tr>';
    
    foreach ($misplacedFiles as $file) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($file['fileName']) . '</td>';
        $html .= '<td>' . htmlspecialchars(dirname($file['relativePath'])) . '</td>';
        $html .= '<td>' . htmlspecialchars($file['suggestedLocation']) . '</td>';
        $html .= '<td>' . htmlspecialchars($file['reason']) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    $html .= '</div>';
    
    return $html;
}

// Render unnecessary files summary
function renderUnnecessaryFilesSummary($unnecessaryFiles, $unnecessaryDirs) {
    $html = '<div class="summary-section">';
    
    // Unnecessary files
    $html .= '<h3>Unnecessary Files</h3>';
    
    if (empty($unnecessaryFiles)) {
        $html .= '<p class="success">✅ No unnecessary files detected.</p>';
    } else {
        $html .= '<table>';
        $html .= '<tr><th>File Path</th><th>Type</th><th>Recommendation</th><th>Reason</th></tr>';
        
        foreach ($unnecessaryFiles as $file) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($file['path']) . '</td>';
            $html .= '<td>' . htmlspecialchars($file['type']) . '</td>';
            
            // Recommendation based on type
            $recommendation = '';
            switch ($file['type']) {
                case 'unnecessary':
                    $recommendation = 'Safe to remove';
                    $rowClass = 'remove';
                    break;
                case 'optional':
                    $recommendation = 'Keep for development';
                    $rowClass = 'keep-dev';
                    break;
                case 'asset':
                    $recommendation = 'Keep if used in frontend';
                    $rowClass = 'keep';
                    break;
                case 'possible':
                    $recommendation = 'Review before removing';
                    $rowClass = 'review';
                    break;
                default:
                    $recommendation = 'Unknown';
                    $rowClass = '';
            }
            
            $html .= '<td class="' . $rowClass . '">' . $recommendation . '</td>';
            $html .= '<td>' . htmlspecialchars($file['reason']) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
    }
    
    // Unnecessary directories
    $html .= '<h3>Unnecessary Directories</h3>';
    
    if (empty($unnecessaryDirs)) {
        $html .= '<p class="success">✅ No unnecessary directories detected.</p>';
    } else {
        $html .= '<table>';
        $html .= '<tr><th>Directory Path</th><th>Type</th><th>Recommendation</th><th>Reason</th></tr>';
        
        foreach ($unnecessaryDirs as $dir) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($dir['path']) . '</td>';
            $html .= '<td>' . htmlspecialchars($dir['type']) . '</td>';
            
            // Recommendation based on type
            $recommendation = '';
            switch ($dir['type']) {
                case 'unnecessary':
                    $recommendation = 'Safe to remove';
                    $rowClass = 'remove';
                    break;
                case 'optional':
                    $recommendation = 'Keep for development';
                    $rowClass = 'keep-dev';
                    break;
                default:
                    $recommendation = 'Unknown';
                    $rowClass = '';
            }
            
            $html .= '<td class="' . $rowClass . '">' . $recommendation . '</td>';
            $html .= '<td>' . htmlspecialchars($dir['reason']) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
    }
    
    $html .= '</div>';
    
    return $html;
}

// Get the output
$treeHtml = renderTree($directoryTree);
$missingFilesSummary = renderMissingFilesSummary($missingFiles);
$misplacedFilesSummary = renderMisplacedFilesSummary($misplacedFiles);
$unnecessaryItemsSummary = renderUnnecessaryFilesSummary($unnecessaryFiles, $unnecessaryDirs);
$unnecessaryTreeHtml = renderUnnecessaryTree($unnecessaryTree);

// Output HTML
echo "<!DOCTYPE html>
<html>
<head>
    <title>Project Structure Analysis</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
            color: #333;
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            color: #2c3e50;
            margin-top: 30px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .tree-view {
            list-style-type: none;
            padding-left: 20px;
        }
        .tree-view.root {
            padding-left: 0;
        }
        .tree-view li {
            margin: 5px 0;
        }
        .directory {
            cursor: pointer;
            font-weight: bold;
            color: #2c3e50;
        }
        .file {
            color: #34495e;
        }
        .status {
            margin-left: 5px;
        }
        details {
            margin-bottom: 5px;
        }
        summary {
            padding: 5px;
            border-radius: 4px;
                       background-color: #f8f9fa;
        }
        summary:hover {
            background-color: #e9ecef;
        }
        .status {
            font-weight: normal;
            font-size: 0.9em;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.8em;
            margin-left: 5px;
        }
        .legend {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #3498db;
        }
        .legend h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .legend ul {
            list-style-type: none;
            padding-left: 0;
        }
        .legend li {
            margin: 5px 0;
        }
        .system-info {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border-left: 4px solid #2ecc71;
        }
        .system-info h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .summary-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff3cd;
            border-radius: 4px;
            border-left: 4px solid #ffc107;
        }
        .summary-section h3 {
            margin-top: 0;
            color: #856404;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: rgba(0,0,0,0.05);
        }
        tr:hover {
            background-color: rgba(0,0,0,0.02);
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border-radius: 4px;
            padding: 10px;
            border-left: 4px solid #28a745;
        }
        .tabs {
            display: flex;
            margin-top: 20px;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
            margin-right: 5px;
        }
        .tab.active {
            background-color: white;
            border-bottom: 2px solid white;
            font-weight: bold;
        }
        .tab-content {
            display: none;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 4px 4px 4px;
        }
        .tab-content.active {
            display: block;
        }
        .remove {
            background-color: #ffebee;
            color: #c62828;
        }
        .keep {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .keep-dev {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        .review {
            background-color: #fff8e1;
            color: #f57f17;
        }
        .reason {
            font-size: 0.85em;
            color: #666;
            font-style: italic;
        }
        .actions {
            margin-top: 20px;
            padding: 15px;
            background-color: #e8f5e9;
            border-radius: 4px;
            border-left: 4px solid #2e7d32;
        }
        .actions h3 {
            margin-top: 0;
            color: #2e7d32;
        }
        .actions pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        .file-count {
            margin-left: 10px;
            font-size: 0.9em;
            color: #666;
        }
    </style>
    <script>
        function switchTab(tabId) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => {
                content.classList.remove('active');
            });
            
            // Deactivate all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Activate selected tab and content
            document.getElementById(tabId).classList.add('active');
            document.getElementById(tabId + '-content').classList.add('active');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Activate first tab by default
            switchTab('tree-view');
            
            // Add event listeners to all details elements to toggle children
            const details = document.querySelectorAll('details');
            details.forEach(detail => {
                detail.addEventListener('toggle', function() {
                    if (this.open) {
                        // Close all other details at the same level
                        const parent = this.parentNode;
                        const siblings = parent.querySelectorAll(':scope > details');
                        siblings.forEach(sibling => {
                            if (sibling !== this && sibling.open) {
                                // sibling.open = false;
                            }
                        });
                    }
                });
            });
        });
    </script>
</head>
<body>
    <h1>Project Structure Analysis</h1>
    
    <div class=\"legend\">
        <h3>Legend</h3>
        <ul>
            <li>✅ - File exists and is readable</li>
            <li>⚠️ - File exists but is not readable (permission issue)</li>
            <li>❌ - File is missing</li>
            <li>📁 - Directory</li>
            <li>📄 - File</li>
            <li><span class=\"status-badge remove\">unnecessary</span> - Safe to remove</li>
            <li><span class=\"status-badge keep-dev\">optional</span> - Keep for development</li>
            <li><span class=\"status-badge keep\">asset</span> - Likely needed asset file</li>
            <li><span class=\"status-badge review\">possible</span> - Review before removing</li>
        </ul>
    </div>
    
    <div class=\"tabs\">
        <div id=\"tree-view\" class=\"tab\" onclick=\"switchTab('tree-view')\">Directory Tree</div>
        <div id=\"unnecessary-tree\" class=\"tab\" onclick=\"switchTab('unnecessary-tree')\">Unnecessary Files Tree</div>
        <div id=\"missing-files\" class=\"tab\" onclick=\"switchTab('missing-files')\">Missing Files</div>
        <div id=\"misplaced-files\" class=\"tab\" onclick=\"switchTab('misplaced-files')\">Misplaced Files</div>
        <div id=\"unnecessary-files\" class=\"tab\" onclick=\"switchTab('unnecessary-files')\">Unnecessary Files List</div>
    </div>
    
    <div id=\"tree-view-content\" class=\"tab-content\">
        <h2>Directory Structure</h2>
        $treeHtml
    </div>
    
    <div id=\"unnecessary-tree-content\" class=\"tab-content\">
        <h2>Unnecessary Files Tree Structure <span class=\"file-count\">(Expand/collapse to explore)</span></h2>
        <p>This tree shows only unnecessary files and directories that can potentially be removed.</p>
        $unnecessaryTreeHtml
    </div>
    
    <div id=\"missing-files-content\" class=\"tab-content\">
        <h2>Missing Files Summary</h2>
        $missingFilesSummary
    </div>
    
    <div id=\"misplaced-files-content\" class=\"tab-content\">
        <h2>Misplaced Files Summary</h2>
        $misplacedFilesSummary
    </div>
    
    <div id=\"unnecessary-files-content\" class=\"tab-content\">
        <h2>Unnecessary Files Summary</h2>
        $unnecessaryItemsSummary
    </div>
    
    <div class=\"actions\">
        <h3>Cleanup Actions</h3>
        <p>Here's a shell script you can use to remove unnecessary files (use with caution):</p>
        <pre>";

// Generate improved cleanup script
$cleanupScript = "#!/bin/bash\n\n";
$cleanupScript .= "# WARNING: This script will delete files. Use with caution!\n";
$cleanupScript .= "# It's recommended to backup your project before running this script.\n\n";

// Define paths to always ignore
$cleanupScript .= "# Define paths to always ignore\n";
$cleanupScript .= "IGNORED_PATHS=(\n";
$cleanupScript .= "  \".git\"\n";
$cleanupScript .= "  \".vscode\"\n";
$cleanupScript .= "  \".DS_Store\"\n";
$cleanupScript .= "  \"node_modules\"\n";
$cleanupScript .= "  \"vendor\"\n";
$cleanupScript .= ")\n\n";

// First, check if .gitignore exists and parse it
$cleanupScript .= "# Respecting .gitignore patterns\n";
$cleanupScript .= "GITIGNORE_PATTERNS=()\n\n";
$cleanupScript .= "if [ -f \".gitignore\" ]; then\n";
$cleanupScript .= "  echo \"Found .gitignore file, will respect ignored patterns\"\n";
$cleanupScript .= "  while IFS= read -r line || [[ -n \"$line\" ]]; do\n";
$cleanupScript .= "    # Skip comments and empty lines\n";
$cleanupScript .= "    if [[ ! $line =~ ^# && -n $line ]]; then\n";
$cleanupScript .= "      GITIGNORE_PATTERNS+=(\"$line\")\n";
$cleanupScript .= "    fi\n";
$cleanupScript .= "  done < \".gitignore\"\n";
$cleanupScript .= "fi\n\n";

$cleanupScript .= "# Function to check if a path should be ignored\n";
$cleanupScript .= "should_ignore() {\n";
$cleanupScript .= "  local path=\"$1\"\n";
$cleanupScript .= "  local basename=\"$(basename \"$path\")\"\n\n";
$cleanupScript .= "  # Check if path is in the always-ignore list\n";
$cleanupScript .= "  for ignored in \"\${IGNORED_PATHS[@]}\"; do\n";
$cleanupScript .= "    if [[ \"$basename\" == \"$ignored\" || \"$path\" == \"$ignored\"* ]]; then\n";
$cleanupScript .= "      return 0 # True, should ignore\n";
$cleanupScript .= "    fi\n";
$cleanupScript .= "  done\n\n";
$cleanupScript .= "  # Check if path matches any gitignore pattern\n";
$cleanupScript .= "  for pattern in \"\${GITIGNORE_PATTERNS[@]}\"; do\n";
$cleanupScript .= "    # Handle pattern with wildcards\n";
$cleanupScript .= "    if [[ \"$path\" == $pattern || \"$path\" == */$pattern || \"$path\" == $pattern/* || \"$path\" == */$pattern/* ]]; then\n";
$cleanupScript .= "      return 0 # True, should ignore\n";
$cleanupScript .= "    fi\n";
$cleanupScript .= "  done\n\n";
$cleanupScript .= "  return 1 # False, don't ignore\n";
$cleanupScript .= "}\n\n";

// Group unnecessary directories by parent path to handle entire folders
$dirsByPath = [];
foreach ($unnecessaryDirs as $dir) {
    if ($dir['type'] === 'unnecessary') {
        $path = $dir['path'];
        $parts = explode('/', $path);
        
        // Skip if this is a top-level directory
        if (count($parts) === 1) {
            $dirsByPath[$path] = true;
            continue;
        }
        
        // Check if any parent directory is already marked for deletion
        $parentPath = '';
        $shouldAdd = true;
        for ($i = 0; $i < count($parts) - 1; $i++) {
            $parentPath = $parentPath ? $parentPath . '/' . $parts[$i] : $parts[$i];
            if (isset($dirsByPath[$parentPath])) {
                $shouldAdd = false;
                break;
            }
        }
        
        if ($shouldAdd) {
            $dirsByPath[$path] = true;
        }
    }
}

// Add directories to remove (most efficient way - remove parent directories first)
$cleanupScript .= "# Unnecessary directories to remove\n";
$cleanupScript .= "echo \"Removing unnecessary directories...\"\n";
foreach (array_keys($dirsByPath) as $dir) {
    $cleanupScript .= "if [ -d \"$dir\" ]; then\n";
    $cleanupScript .= "  if ! should_ignore \"$dir\"; then\n";
    $cleanupScript .= "    echo \"Removing directory: $dir\"\n";
    $cleanupScript .= "    rm -rf \"$dir\"\n";
    $cleanupScript .= "  else\n";
    $cleanupScript .= "    echo \"Skipping ignored directory: $dir\"\n";
    $cleanupScript .= "  fi\n";
    $cleanupScript .= "fi\n";
}

// Add individual files to remove (only if their parent directory wasn't already removed)
$cleanupScript .= "\n# Unnecessary individual files to remove\n";
$cleanupScript .= "echo \"Removing unnecessary files...\"\n";
foreach ($unnecessaryFiles as $file) {
    if ($file['type'] === 'unnecessary') {
        $path = $file['path'];
        $dir = dirname($path);
        
        // Skip if parent directory is already being removed
        if ($dir !== '.' && isset($dirsByPath[$dir])) {
            continue;
        }
        
        // Check if any parent directory is being removed
        $parts = explode('/', $dir);
        $parentPath = '';
        $shouldSkip = false;
        for ($i = 0; $i < count($parts); $i++) {
            $parentPath = $parentPath ? $parentPath . '/' . $parts[$i] : $parts[$i];
            if (isset($dirsByPath[$parentPath])) {
                $shouldSkip = true;
                break;
            }
        }
        
        if (!$shouldSkip) {
            $cleanupScript .= "if [ -f \"$path\" ]; then\n";
            $cleanupScript .= "  if ! should_ignore \"$path\"; then\n";
            $cleanupScript .= "    echo \"Removing file: $path\"\n";
            $cleanupScript .= "    rm \"$path\"\n";
            $cleanupScript .= "  else\n";
            $cleanupScript .= "    echo \"Skipping ignored file: $path\"\n";
            $cleanupScript .= "  fi\n";
            $cleanupScript .= "fi\n";
        }
    }
}

// Add cleanup for .DS_Store files
$cleanupScript .= "\n# Cleanup .DS_Store files\n";
$cleanupScript .= "echo \"Cleaning up .DS_Store files...\"\n";
$cleanupScript .= "find . -name \".DS_Store\" -type f -delete\n";

$cleanupScript .= "\necho \"Cleanup complete!\"\n";

echo htmlspecialchars($cleanupScript);



echo "</pre>
    </div>
    
    <div class=\"system-info\">
        <h3>System Information</h3>
        <p>PHP Version: " . phpversion() . "</p>
        <p>Server Software: " . $_SERVER["SERVER_SOFTWARE"] . "</p>
        <p>Document Root: " . $_SERVER["DOCUMENT_ROOT"] . "</p>
        <p>Current Directory: " . $rootPath . "</p>
        <p>Total Files: " . count($unnecessaryFiles) + count($missingFiles) . "</p>
        <p>Unnecessary Files: " . count($unnecessaryFiles) . "</p>
        <p>Missing Files: " . count($missingFiles) . "</p>
    </div>
</body>
</html>";

// Send the output
$output = ob_get_clean();
echo $output;

