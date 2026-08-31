<?php
/**
 * Test GitHub connection
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
require_once __DIR__ . '/../includes/github-api.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['token']) || trim($input['token']) === '') {
        jsonResponse(false, 'Token required');
    }
    
    $token = trim($input['token']);
    
    // Validate token format
    if (!isValidGitHubToken($token)) {
        jsonResponse(false, 'Invalid token format');
    }
    
    $username = isset($input['username']) ? sanitizeInput($input['username']) : getSetting('github_username');
    if (empty($username)) {
        jsonResponse(false, 'GitHub username not configured');
    }
    
    try {
        $github = new GitHubAPI($token, $username);
        $result = $github->verifyToken();
        
        if ($result['valid']) {
            $settings = getSettings();
            $settings['github_token'] = $token;
            if (!empty($result['login'])) {
                $settings['github_username'] = $result['login'];
            } elseif (!empty($username)) {
                $settings['github_username'] = $username;
            }
            saveSettings($settings);
            addLog('GitHub connection test successful; credentials saved', 'info');
            jsonResponse(true, 'Connection successful', array(
                'user' => $result,
                'saved' => true
            ));
        } else {
            addLog('GitHub connection test failed', 'error');
            jsonResponse(false, 'Invalid credentials: ' . $result['error']);
        }
    } catch (Exception $e) {
        addLog('GitHub connection error: ' . $e->getMessage(), 'error');
        jsonResponse(false, 'Connection error: ' . $e->getMessage());
    }
} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage());
}
