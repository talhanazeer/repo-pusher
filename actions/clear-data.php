<?php
/**
 * Clear all application data
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
    // Delete config file
    if (file_exists(CONFIG_FILE)) {
        unlink(CONFIG_FILE);
    }
    
    // Clear logs
    $logDir = LOG_DIR;
    if (is_dir($logDir)) {
        $files = glob($logDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
    
    jsonResponse(true, 'All data cleared successfully');
} catch (Exception $e) {
    jsonResponse(false, 'Error clearing data: ' . $e->getMessage());
}
