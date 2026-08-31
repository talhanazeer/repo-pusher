<?php
/**
 * Scan projects from the configured folder (Optimized with Caching)
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
    
    if (!isValidPath($projectsPath)) {
        jsonResponse(false, 'Invalid projects path. Please configure it in settings.');
    }
    
    // Check for cache
    $useCache = isset($_GET['nocache']) ? false : true;
    $cachedProjects = null;
    
    if ($useCache) {
        $cachedProjects = ScanCache::get($projectsPath);
        if ($cachedProjects !== null) {
            addLog('Returning cached scan results', 'info');
            jsonResponse(true, 'Projects scanned successfully (cached)', array(
                'projects' => $cachedProjects,
                'count' => count($cachedProjects),
                'cached' => true
            ));
        }
    }
    
    $projects = array();
    
    try {
        $iterator = new DirectoryIterator($projectsPath);
        
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                continue;
            }
            
            $folderName = $fileInfo->getFilename();
            $folderPath = $fileInfo->getPathname();
            
            // Skip hidden folders and common non-project folders
            if ($folderName[0] === '.' || in_array($folderName, ['node_modules', 'vendor', '.git'])) {
                continue;
            }
            
            try {
                $isGit = isGitRepository($folderPath);
                
                // Only check git remote details if it's actually a git repository
                $hasRemote = false;
                $remoteUrl = null;
                $modifiedFiles = array(
                    'modified' => 0,
                    'untracked' => 0,
                    'deleted' => 0,
                    'files' => array()
                );
                
                if ($isGit) {
                    $hasRemote = hasGitRemote($folderPath);
                    $remoteUrl = $hasRemote ? getGitRemoteUrl($folderPath) : null;
                    // Get modified files status (optimized version)
                    $modifiedFiles = getModifiedFiles($folderPath, 10); // Limit to 10 files for speed
                }
                
                $lastModified = getLastModifiedDate($folderPath);
                
                // Skip expensive folder size calculation for performance
                // Will be calculated on-demand when needed
                $size = 'Calculating...';
                
                // Get file count only (much faster than full file listing)
                $fileCount = getDirectoryFileCount($folderPath);
                
                $projects[] = array(
                    'name' => $folderName,
                    'path' => $folderPath, // Keep original path separators
                    'is_git' => $isGit,
                    'has_remote' => $hasRemote,
                    'remote_url' => $remoteUrl,
                    'modified' => $lastModified,
                    'size' => $size,
                    'file_count' => $fileCount,
                    'modified_files' => $modifiedFiles
                );
            } catch (Exception $e) {
                addLog("Error scanning folder {$folderName}: " . $e->getMessage(), 'error');
                continue;
            }
        }
        
        // Sort by name
        usort($projects, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        // Cache the results
        ScanCache::set($projectsPath, $projects);
        
        addLog('Scanned ' . count($projects) . ' projects', 'info');
        
        jsonResponse(true, 'Projects scanned successfully', array(
            'projects' => $projects,
            'count' => count($projects),
            'cached' => false
        ));
    } catch (Exception $e) {
        jsonResponse(false, 'Error scanning projects: ' . $e->getMessage());
    }
} catch (Exception $e) {
    jsonResponse(false, 'Error: ' . $e->getMessage());
}


