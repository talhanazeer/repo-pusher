<?php
/**
 * Get folder size on-demand (for performance)
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
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['path'])) {
        jsonResponse(false, 'Path required');
    }

    $folderPath = $input['path'];

    if (!isValidPath($folderPath)) {
        jsonResponse(false, 'Invalid path');
    }

    if (!is_dir($folderPath)) {
        jsonResponse(false, 'Path is not a directory');
    }

    $size = getFolderSizeFast($folderPath);

    jsonResponse(true, 'Size calculated', array('size' => $size));
} catch (Exception $e) {
    jsonResponse(false, 'Error calculating size: ' . $e->getMessage());
}
?>