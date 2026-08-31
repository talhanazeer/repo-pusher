<?php
/**
 * Clear scan cache to force refresh
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
require_once __DIR__ . '/../includes/cache-helper.php';

try {
    $projectsPath = getSetting('projects_path', DEFAULT_PROJECTS_PATH);
    
    ScanCache::clear($projectsPath);
    
    addLog('Scan cache cleared', 'info');
    jsonResponse(true, 'Cache cleared successfully');
    
} catch (Exception $e) {
    addLog('Error clearing cache: ' . $e->getMessage(), 'error');
    jsonResponse(false, 'Error clearing cache: ' . $e->getMessage());
}
