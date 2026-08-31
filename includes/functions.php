<?php
/**
 * GitHub Backup Manager - Utility Functions
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Sanitize input strings
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate folder path
 */
function isValidPath($path) {
    if (empty($path)) return false;
    
    $realPath = realpath($path);
    return $realPath && is_dir($realPath) && is_readable($realPath);
}

/**
 * Log messages to file
 */
function addLog($message, $type = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = LOG_DIR . '/app_' . date('Y-m-d') . '.log';
    
    $safeMessage = preg_replace('/oauth2:[^@\s]+@/i', 'oauth2:***@', (string) $message);
    $safeMessage = preg_replace('/(github_pat_|ghp_|gho_|ghu_|ghs_)[A-Za-z0-9_]+/', '$1***', $safeMessage);
    
    $logMessage = "[{$timestamp}] [{$type}] {$safeMessage}\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Get recent logs
 */
function getLogs($lines = 50) {
    $logFile = LOG_DIR . '/app_' . date('Y-m-d') . '.log';
    
    if (!file_exists($logFile)) {
        return array();
    }
    
    $allLines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return array_slice($allLines, -$lines);
}

/**
 * Return JSON response
 */
function jsonResponse($success, $message = '', $data = array()) {
    header('Content-Type: application/json');
    
    echo json_encode(array(
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ));
    
    exit;
}

/**
 * Check if folder is a Git repository
 */
function isGitRepository($folderPath) {
    $gitDir = $folderPath . '/.git';
    return is_dir($gitDir);
}
function getFolderSize($path)
{
    $totalSize = 0;

    if (!is_dir($path)) return 0;

    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalSize += $file->getSize();
            }
        }
    } catch (Exception $e) {
        return 0;
    }

    return formatBytes($totalSize);
}
/**
 * Configure Git to trust a directory (fixes ownership issues)
 */
function configureGitSafeDirectory($folderPath) {
    try {
        $realPath = realpath($folderPath);
        if ($realPath) {
            $cmd = 'git config --global --add safe.directory ' . escapeshellarg($realPath);
            shell_exec($cmd . ' 2>&1');
        }
    } catch (Exception $e) {
        // Silently ignore errors - this is a best-effort configuration
    }
}

/**
 * Check if git remote exists (optimized)
 */
function hasGitRemote($folderPath, $remoteName = 'origin') {
    if (!isGitRepository($folderPath)) {
        return false;
    }
    
    // Configure safe directory first
    configureGitSafeDirectory($folderPath);
    
    // Use git config to check for remote (faster than git remote -v)
    $command = "cd " . escapeshellarg($folderPath) . " && git config --get remote.{$remoteName}.url 2>nul";
    $output = shell_exec($command);

    return !empty(trim($output ?? ''));
}

/**
 * Get git remote URL (optimized with timeout)
 */
function getGitRemoteUrl($folderPath, $remoteName = 'origin') {
    if (!isGitRepository($folderPath)) {
        return null;
    }
    
    // Configure safe directory first
    configureGitSafeDirectory($folderPath);
    
    // Use faster git config command with timeout
    $command = 'cd /d ' . escapeshellarg($folderPath) . ' && timeout 5 git config --get remote.' . $remoteName . '.url 2>nul';
    
    // For Windows compatibility
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $command = 'cd /d ' . escapeshellarg($folderPath) . ' && git config --get remote.' . $remoteName . '.url 2>nul';
    }
    
    $output = @shell_exec($command);
    $output = is_string($output) ? trim($output) : '';
    return $output !== '' ? $output : null;
}

/**
 * Get last modified date of a folder
 */
function getLastModifiedDate($folderPath) {
    $time = @filemtime($folderPath);
    return $time ? date('Y-m-d H:i:s', $time) : 'Unknown';
}

/**
 * Get folder size in human-readable format (fast approximation)
 */
function getFolderSizeFast($folderPath) {
    $size = 0;
    $fileCount = 0;
    
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        
        // Limit to first 1000 files for performance
        $maxFiles = 1000;
        foreach ($iterator as $path) {
            if ($path->isFile()) {
                $size += $path->getSize();
                $fileCount++;
                if ($fileCount >= $maxFiles) {
                    break;
                }
            }
        }
        
        // If we hit the limit, indicate it's approximate
        if ($fileCount >= $maxFiles) {
            return '~' . formatBytes($size) . ' (' . $fileCount . '+ files)';
        }
    } catch (Exception $e) {
        return 'Unknown';
    }
    
    return formatBytes($size);
}

/**
 * Format bytes to human-readable
 */
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Generate .gitignore if it doesn't exist
 */
function generateGitIgnore($folderPath) {
    $gitignorePath = $folderPath . '/.gitignore';
    
    if (file_exists($gitignorePath)) {
        return true;
    }
    
    $gitignoreContent = <<<'GITIGNORE'
# Node modules
node_modules/
npm-debug.log
yarn-error.log

# PHP
vendor/
composer.lock
*.log

# IDEs
.vscode/
.idea/
*.sublime-project
*.sublime-workspace

# OS
.DS_Store
Thumbs.db
.env
.env.local

# Build/Dist
dist/
build/
.next/
out/

# Temporary files
*.tmp
*.bak
*.swp
*~

# Python
__pycache__/
*.py[cod]
*$py.class
.venv/
env/

GITIGNORE;

    return file_put_contents($gitignorePath, $gitignoreContent) !== false;
}

/**
 * Validate GitHub username
 */
function isValidGitHubUsername($username) {
    // GitHub username pattern: alphanumeric, hyphens, no consecutive hyphens
    return preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9-]{0,37}[a-zA-Z0-9])?$/', $username);
}

/**
 * Validate GitHub token format (basic check)
 */
function isValidGitHubToken($token) {
    // Classic PAT: ghp_  | Fine-grained PAT: github_pat_  | OAuth/App: ghu_, ghs_, gho_
    return strlen($token) > 20 && (
        strpos($token, 'github_pat_') === 0 ||
        strpos($token, 'ghp_') === 0 ||
        strpos($token, 'ghu_') === 0 ||
        strpos($token, 'ghs_') === 0 ||
        strpos($token, 'gho_') === 0
    );
}

/**
 * Get directory file count (faster version)
 */
function getDirectoryFileCount($path) {
    $count = 0;
    
    try {
        $iterator = new DirectoryIterator($path);
        
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isDot() && $fileInfo->getFilename() !== '.git') {
                $count++;
            }
        }
    } catch (Exception $e) {
        addLog('Error counting files in directory: ' . $e->getMessage(), 'error');
    }
    
    return $count;
}

/**
 * Get total recursive file count in directory and subdirectories
 */
function getDirectoryFileCountRecursive($path, $limit = 100000) {
    $count = 0;
    
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        
        // Skip .git folders
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                // Skip .git directory files
                if (strpos($fileInfo->getPathname(), DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) === false) {
                    $count++;
                    if ($count >= $limit) {
                        break;
                    }
                }
            }
        }
    } catch (Exception $e) {
        addLog('Error counting files recursively: ' . $e->getMessage(), 'error');
    }
    
    return $count;
}

/**
 * Get modified files in a git repository
 */
function getModifiedFiles($folderPath, $limit = 20) {
    if (!isGitRepository($folderPath)) {
        return array(
            'modified' => 0,
            'untracked' => 0,
            'deleted' => 0,
            'files' => array()
        );
    }
    
    try {
        // Configure safe directory first
        configureGitSafeDirectory($folderPath);
        
        // Get git status in porcelain format
        $cmd = 'cd ' . escapeshellarg($folderPath) . ' && git status --porcelain 2>nul';
        $output = shell_exec($cmd);
        
        if (empty($output)) {
            return array(
                'modified' => 0,
                'untracked' => 0,
                'deleted' => 0,
                'files' => array()
            );
        }
        
        $lines = explode("\n", trim($output));
        $stats = array(
            'modified' => 0,
            'untracked' => 0,
            'deleted' => 0,
            'files' => array()
        );
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            $status = substr($line, 0, 2);
            $filename = substr($line, 3);
            
            // Parse status codes
            if ($status === '??') {
                $stats['untracked']++;
                $fileStatus = 'untracked';
            } elseif (strpos($status, 'M') !== false) {
                $stats['modified']++;
                $fileStatus = 'modified';
            } elseif (strpos($status, 'D') !== false) {
                $stats['deleted']++;
                $fileStatus = 'deleted';
            } else {
                continue;
            }
            
            if (count($stats['files']) < $limit) {
                $stats['files'][] = array(
                    'name' => basename($filename),
                    'path' => $filename,
                    'status' => $fileStatus
                );
            }
        }
        
        return $stats;
    } catch (Exception $e) {
        addLog('Error checking modified files: ' . $e->getMessage(), 'error');
        return array(
            'modified' => 0,
            'untracked' => 0,
            'deleted' => 0,
            'files' => array()
        );
    }
}

/**
 * Get directory files (limited)
 */
function getDirectoryFiles($path, $limit = 10) {
    $files = array();
    
    try {
        $iterator = new DirectoryIterator($path);
        
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->getFilename() === '.git') {
                continue;
            }
            
            $files[] = array(
                'name' => $fileInfo->getFilename(),
                'type' => $fileInfo->isDir() ? 'dir' : 'file',
                'size' => $fileInfo->getSize(),
                'modified' => $fileInfo->getMTime()
            );
            
            if (count($files) >= $limit) break;
        }
    } catch (Exception $e) {
        addLog('Error reading directory: ' . $e->getMessage(), 'error');
    }
    
    return $files;
}

/**
 * Escape shell argument safely
 */
function escapeArg($arg) {
    return escapeshellarg($arg);
}
