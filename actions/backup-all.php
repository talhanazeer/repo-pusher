<?php
/**
 * Backup all projects to GitHub
 *
 * Prefer the dashboard Backup All button, which runs one project at a time
 * via backup-project.php so the browser does not time out. This endpoint
 * remains for API use and skips the Backup Manager folder.
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/github-api.php';
require_once __DIR__ . '/../includes/git-helper.php';

set_time_limit(0);
ini_set('memory_limit', '512M');

try {
    $username = getSetting('github_username');
    $token = getGitHubToken();
    $projectsPath = getSetting('projects_path', DEFAULT_PROJECTS_PATH);

    if (empty($username)) {
        jsonResponse(false, 'GitHub username not configured', ['status' => 'failed']);
    }

    if (empty($token)) {
        jsonResponse(false, 'GitHub token not configured', ['status' => 'failed']);
    }

    if (!isValidPath($projectsPath)) {
        jsonResponse(false, 'Invalid projects path', ['status' => 'failed']);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $visibility = is_array($input) && isset($input['visibility']) ? strtolower(trim($input['visibility'])) : 'private';
    $isPrivate = ($visibility !== 'public');

    $successCount = 0;
    $failedCount = 0;
    $skippedCount = 0;
    $backupResults = array();
    $skipNames = array('node_modules', 'vendor');
    $appRoot = realpath(APP_ROOT);

    addLog('Starting bulk backup of all projects', 'info');

    try {
        $github = new GitHubAPI($token, $username);

        $projects = array();
        $iterator = new DirectoryIterator($projectsPath);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                continue;
            }

            $folderName = $fileInfo->getFilename();

            if ($folderName[0] === '.' || in_array(strtolower($folderName), $skipNames, true)) {
                $skippedCount++;
                continue;
            }

            $folderPath = $fileInfo->getPathname();
            $realFolder = realpath($folderPath);
            if ($appRoot && $realFolder && strcasecmp($realFolder, $appRoot) === 0) {
                $skippedCount++;
                continue;
            }

            $projects[] = array(
                'name' => $folderName,
                'path' => $folderPath
            );
        }

        addLog('Found ' . count($projects) . ' projects to backup', 'info');

        foreach ($projects as $project) {
            try {
                $folderName = $project['name'];
                $folderPath = $project['path'];

                addLog("Processing: {$folderName}", 'info');

                $repoName = preg_replace('/[^a-zA-Z0-9._-]/', '-', $folderName);

                $isExisting = false;
                try {
                    $github->getRepository($repoName);
                    $isExisting = true;
                    addLog("Repository '{$repoName}' already exists, will update", 'info');
                } catch (Exception $e) {
                    $isExisting = false;
                }

                try {
                    $createResult = $github->createRepository(
                        $repoName,
                        "Backup of {$folderName}",
                        $isPrivate
                    );
                    $repoInfo = isset($createResult['repo']) ? $createResult['repo'] : $createResult;
                } catch (Exception $e) {
                    try {
                        $repoInfo = $github->getRepository($repoName);
                    } catch (Exception $e2) {
                        throw new Exception('Failed to create or retrieve repository: ' . $e->getMessage());
                    }
                }

                $git = new GitHelper($folderPath);
                $authenticatedUrl = "https://oauth2:{$token}@github.com/{$username}/{$repoName}.git";
                $backupResult = $git->backup($authenticatedUrl, $isExisting);

                if ($backupResult['success']) {
                    $successCount++;
                    $backupResults[] = array(
                        'project' => $folderName,
                        'status' => 'success',
                        'repo_name' => $repoInfo['name'] ?? $repoName,
                        'repo_url' => $repoInfo['url'] ?? ''
                    );
                    addLog("Successfully backed up: {$folderName}", 'info');
                } else {
                    $failedCount++;
                    $backupResults[] = array(
                        'project' => $folderName,
                        'status' => 'failed',
                        'error' => $backupResult['message']
                    );
                    addLog("Failed to backup {$folderName}: " . $backupResult['message'], 'error');
                }
            } catch (Exception $e) {
                $failedCount++;
                $backupResults[] = array(
                    'project' => $project['name'],
                    'status' => 'failed',
                    'error' => $e->getMessage()
                );
                addLog("Failed to backup {$project['name']}: " . $e->getMessage(), 'error');
            }
        }

        addLog("Bulk backup completed. Success: {$successCount}, Failed: {$failedCount}, Skipped: {$skippedCount}", 'info');

        jsonResponse(true, "Bulk backup completed: {$successCount} succeeded, {$failedCount} failed, {$skippedCount} skipped", array(
            'status' => 'success',
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'skipped_count' => $skippedCount,
            'results' => $backupResults
        ));
    } catch (Exception $e) {
        addLog('Error during bulk backup: ' . $e->getMessage(), 'error');
        jsonResponse(false, 'Error during bulk backup: ' . $e->getMessage(), [
            'status' => 'failed',
            'error' => $e->getMessage(),
            'partial_results' => $backupResults
        ]);
    }
} catch (Exception $e) {
    addLog('Critical error in bulk backup: ' . $e->getMessage(), 'error');
    jsonResponse(false, 'Critical error: ' . $e->getMessage(), ['status' => 'failed']);
}
