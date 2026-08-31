<?php
/**
 * Get detailed project information
 */

// Enable CORS for all requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400'); // 24 hours

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $path = isset($_GET['path']) ? $_GET['path'] : null;
    
    if (!$path || !isValidPath($path)) {
        jsonResponse(false, 'Invalid project path');
    }
    
    $name = basename($path);
    $isGit = isGitRepository($path);
    $remoteUrl = hasGitRemote($path) ? getGitRemoteUrl($path) : null;
    $modified = getLastModifiedDate($path);
    $size = getFolderSize($path);
    
    jsonResponse(true, 'Project details loaded', array(
        'name' => $name,
        'path' => $path, // Keep original path separators
        'size' => $size,
        'modified' => $modified,
        'is_git' => $isGit,
        'remote_url' => $remoteUrl
    ));
} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage());
}
