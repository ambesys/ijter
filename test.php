<?php
// tree-diagnostic.php - Display project structure with missing and misplaced files summary

// Start with a clean output
ob_start();

// Define root path
$rootPath = __DIR__;

// Define expected files and their locations
$expectedFiles = [
    'bootstrap.php' => 'Root directory',
    'index.php' => 'Root directory',
    '.env' => 'Root directory',
    'config/config.php' => 'Main configuration',
    'config/journal-details.php' => 'Journal details configuration',
    'core/functions.php' => 'Core system functions',
    'core/auth.php' => 'Core authentication functions',
    'includes/functions.php' => 'Helper functions',
    'includes/common_data.php' => 'Common data used across the application'
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
    if ($fileName === '___PREV_TRIAL' || $fileName === 'tree-diagnostic.php') {
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
                    if (!strpos($relativePath, 'public/assets/') && !strpos($relativePath, 'uploads/')) {
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

// Get missing files
$missingFiles = checkMissingFiles($rootPath, $expectedFiles);

// Build the directory tree and collect misplaced files
$misplacedFiles = [];
$directoryTree = buildDirectoryTree($rootPath, '', $missingFiles, $misplacedFiles);

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

// Get the output
$treeHtml = renderTree($directoryTree);
$missingFilesSummary = renderMissingFilesSummary($missingFiles);
$misplacedFilesSummary = renderMisplacedFilesSummary($misplacedFiles);

// Output HTML
echo "<!DOCTYPE html>
<html>
<head>
    <title>IJTER Project Structure</title>
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
        });
    </script>
</head>
<body>
    <h1>IJTER Project Structure</h1>
    
    <div class=\"legend\">
        <h3>Legend</h3>
        <ul>
            <li>✅ - File exists and is readable</li>
            <li>⚠️ - File exists but is not readable (permission issue)</li>
            <li>❌ - File is missing</li>
            <li>📁 - Directory</li>
            <li>📄 - File</li>
        </ul>
    </div>
    
    <div class=\"tabs\">
        <div id=\"tree-view\" class=\"tab\" onclick=\"switchTab('tree-view')\">Directory Tree</div>
        <div id=\"missing-files\" class=\"tab\" onclick=\"switchTab('missing-files')\">Missing Files</div>
        <div id=\"misplaced-files\" class=\"tab\" onclick=\"switchTab('misplaced-files')\">Misplaced Files</div>
    </div>
    
    <div id=\"tree-view-content\" class=\"tab-content\">
        <h2>Directory Structure</h2>
        $treeHtml
    </div>
    
    <div id=\"missing-files-content\" class=\"tab-content\">
        <h2>Missing Files Summary</h2>
        $missingFilesSummary
    </div>
    
    <div id=\"misplaced-files-content\" class=\"tab-content\">
        <h2>Misplaced Files Summary</h2>
        $misplacedFilesSummary
    </div>
    
    <div class=\"system-info\">
        <h3>System Information</h3>
        <p>PHP Version: " . phpversion() . "</p>
        <p>Server Software: " . $_SERVER["SERVER_SOFTWARE"] . "</p>
        <p>Document Root: " . $_SERVER["DOCUMENT_ROOT"] . "</p>
        <p>Current Directory: " . $rootPath . "</p>
    </div>
</body>
</html>";

// Send the output
$output = ob_get_clean();
echo $output;
