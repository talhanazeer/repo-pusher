<?php
/**
 * Get detailed project status including modified files
 * This endpoint provides detailed information for a project
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

    if (empty($input['path'])) {
        jsonResponse(false, 'Project path required');
    }

    $projectPath = trim($input['path']);

    if (!is_dir($projectPath)) {
        jsonResponse(false, 'Folder does not exist');
    }

    if (!isValidPath($projectPath)) {
        jsonResponse(false, 'Invalid project path');
    }

    $projectName = basename($projectPath);
    $isGit = isGitRepository($projectPath);
    
    $status = array(
        'name' => $projectName,
        'path' => $projectPath,
        'is_git' => $isGit,
        'modified_files' => array(
            'modified' => 0,
            'untracked' => 0,
            'deleted' => 0,
            'files' => array()
        ),
        'file_count' => getDirectoryFileCount($projectPath),
        'size' => 'Calculating...'
    );
    
    if ($isGit) {
        $status['has_remote'] = hasGitRemote($projectPath);
        $status['remote_url'] = $status['has_remote'] ? getGitRemoteUrl($projectPath) : null;
        $status['modified_files'] = getModifiedFiles($projectPath, 50);
    }
    
    jsonResponse(true, 'Project status retrieved', $status);

} catch (Exception $e) {
    addLog('Error getting project status: ' . $e->getMessage(), 'error');
    jsonResponse(false, $e->getMessage());
}
