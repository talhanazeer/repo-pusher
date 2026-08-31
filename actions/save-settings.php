<?php
/**
 * Save settings from frontend
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
    
    if (!$input) {
        jsonResponse(false, 'Invalid input');
    }
    
    $settings = getSettings();
    
    // Sanitize and validate inputs
    $username = isset($input['github_username']) ? sanitizeInput($input['github_username']) : '';
    $token = isset($input['github_token']) ? trim($input['github_token']) : '';
    $projectsPath = isset($input['projects_path']) ? sanitizeInput($input['projects_path']) : '';
    
    // Validate GitHub username
    if (!empty($username) && !isValidGitHubUsername($username)) {
        jsonResponse(false, 'Invalid GitHub username format');
    }
    
    // Validate token if provided
    if (!empty($token) && !isValidGitHubToken($token)) {
        jsonResponse(false, 'Invalid GitHub token format. Must start with github_pat_, ghp_, ghu_, ghs_, or gho_');
    }
    
    // Validate projects path
    if (!empty($projectsPath) && !isValidPath($projectsPath)) {
        jsonResponse(false, 'Invalid projects path. Must be an existing directory.');
    }
    
    // Update settings (keep existing token if not provided)
    if (!empty($username)) {
        $settings['github_username'] = $username;
    }
    
    if (!empty($token)) {
        $settings['github_token'] = $token;
    }
    
    if (!empty($projectsPath)) {
        $settings['projects_path'] = $projectsPath;
    }
    
    $settings['auto_gitignore'] = isset($input['auto_gitignore']) ? (bool)$input['auto_gitignore'] : true;
    $settings['dark_mode'] = isset($input['dark_mode']) ? (bool)$input['dark_mode'] : false;
    
    saveSettings($settings);
    
    addLog('Settings updated', 'info');
    
    jsonResponse(true, 'Settings saved successfully');
} catch (Exception $e) {
    addLog('Error saving settings: ' . $e->getMessage(), 'error');
    jsonResponse(false, 'Error: ' . $e->getMessage());
}
