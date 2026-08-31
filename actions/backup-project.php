<?php
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
require_once __DIR__ . '/../includes/git-helper.php';

// Set longer timeout for large backups (large repos with many files can take 10+ minutes)
set_time_limit(1200); // 20 minutes
ini_set('max_execution_time', 1200);

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['path'])) {
        jsonResponse(false, 'Project path required', ['status' => 'failed']);
    }

    $projectPath = trim($input['path']);

    if (!is_dir($projectPath)) {
        jsonResponse(false, 'Folder does not exist', ['status' => 'failed']);
    }

    if (!isValidPath($projectPath)) {
        jsonResponse(false, 'Invalid project path', ['status' => 'failed']);
    }

    $realProjectPath = realpath($projectPath);
    $appRoot = realpath(APP_ROOT);
    if ($realProjectPath && $appRoot && strcasecmp($realProjectPath, $appRoot) === 0) {
        if (file_exists(CONFIG_FILE)) {
            $settingsSnapshot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'repo-pusher-settings-restore.json';
            @copy(CONFIG_FILE, $settingsSnapshot);
            register_shutdown_function(function () use ($settingsSnapshot) {
                if (!empty($settingsSnapshot) && file_exists($settingsSnapshot)) {
                    @copy($settingsSnapshot, CONFIG_FILE);
                    @unlink($settingsSnapshot);
                }
            });
        }
    }

    // Auto repo name from folder name
    $projectName = basename($projectPath);

    // GitHub-safe repo name
    $projectName = preg_replace('/[^a-zA-Z0-9._-]/', '-', $projectName);

    $username = trim(getSetting('github_username'));
    $token    = trim(getGitHubToken());

    if ($username === '') jsonResponse(false, 'GitHub username not configured', ['status' => 'failed']);
    if ($token === '') jsonResponse(false, 'GitHub token not configured', ['status' => 'failed']);

    addLog("Starting backup for {$projectName}", 'info');

    $github = new GitHubAPI($token, $username);

    $visibility = isset($input['visibility']) ? strtolower(trim($input['visibility'])) : '';
    $isPrivate = ($visibility !== 'public');

    // Check if repo already exists for update capability
    $isExisting = false;
    try {
        $isExisting = $github->repoExists($projectName);
        if ($isExisting) {
            addLog("Repository '{$projectName}' already exists, using existing repository", 'info');
        } else {
            addLog("Repository '{$projectName}' does not exist, will create new one", 'info');
        }
    } catch (Exception $e) {
        jsonResponse(false, 'Failed to check GitHub repository: ' . $e->getMessage(), [
            'status' => 'failed',
            'error' => $e->getMessage()
        ]);
    }

    // New repos: ask the UI unless visibility was already chosen (default private)
    if (!$isExisting && $visibility !== 'private' && $visibility !== 'public') {
        jsonResponse(true, 'Choose whether to create this repository as private or public', [
            'status' => 'needs_visibility',
            'repo_name' => $projectName,
            'default_visibility' => 'private'
        ]);
    }

    try {
        addLog("Creating/accessing repository '{$projectName}' as " . ($isExisting ? 'existing' : ($isPrivate ? 'private' : 'public')), 'info');
        $createResult = $github->createRepository(
            $projectName,
            "Backup of {$projectName}",
            $isPrivate
        );

        $repoInfo   = $createResult['repo'];
        $cloneUrl = $repoInfo['clone_url'];

        // Create authenticated URL for git operations
        $authenticatedUrl = "https://oauth2:{$token}@github.com/{$username}/{$projectName}.git";

        addLog("Repository ready: {$repoInfo['name']} ({$repoInfo['url']})", 'info');
    } catch (Exception $e) {
        addLog("Failed to create/access repository: " . $e->getMessage(), 'error');
        $errorMessage = $e->getMessage();
        if (strpos($errorMessage, '401') !== false || stripos($errorMessage, 'Bad credentials') !== false) {
            $errorMessage = 'GitHub rejected the saved token (401 Bad credentials). Open Settings, paste your PAT, then click Test Connection or Save Settings. Testing alone does not store the token unless you save.';
        }
        jsonResponse(false, 'Failed to create/access GitHub repository: ' . $errorMessage, [
            'status' => 'failed',
            'error' => $e->getMessage()
        ]);
    }

    $git = new GitHelper($projectPath);

    addLog("Starting Git operations for {$projectName}", 'info');

    try {
        $backupResult = $git->backup($authenticatedUrl, $isExisting);

        if (!$backupResult['success']) {
            addLog("Backup failed: " . $backupResult['message'], 'error');
            jsonResponse(false, $backupResult['message'], [
                'status' => 'failed',
                'repo_name' => $repoInfo['name'],
                'error' => $backupResult['message']
            ]);
        }

        addLog("Backup successful for {$projectName}", 'info');
        jsonResponse(true, 'Backup successful', [
            'status' => 'success',
            'repo_name' => $repoInfo['name'],
            'repo_url'  => $repoInfo['url'],
            'private' => isset($repoInfo['private']) ? (bool) $repoInfo['private'] : $isPrivate,
            'message' => $backupResult['message']
        ]);
    } catch (Exception $e) {
        addLog("Git operation failed: " . $e->getMessage(), 'error');
        jsonResponse(false, 'Git operation failed: ' . $e->getMessage(), [
            'status' => 'failed',
            'repo_name' => $repoInfo['name'] ?? $projectName,
            'error' => $e->getMessage()
        ]);
    }
} catch (Exception $e) {
    addLog($e->getMessage(), 'error');
    jsonResponse(false, $e->getMessage(), [
        'status' => 'failed',
        'error' => $e->getMessage()
    ]);
}

