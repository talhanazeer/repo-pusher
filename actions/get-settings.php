<?php
/**
 * Get settings for frontend
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
    $settings = getSettings();
    
    $settings['github_token_configured'] = !empty($settings['github_token']);
    
    // Never expose the token to frontend
    unset($settings['github_token']);
    
    jsonResponse(true, 'Settings loaded', $settings);
} catch (Exception $e) {
    jsonResponse(false, 'Error loading settings: ' . $e->getMessage());
}
