<?php
/**
 * GitHub Backup Manager - Git Helper Functions
 */

require_once __DIR__ . '/functions.php';

class GitHelper {
    private $projectPath;
    private $protectSecrets = false;
    
    /**
     * Verify that Git is available on the system
     */
    private function verifyGitAvailable() {
        // Check if shell_exec is available
        if (!function_exists('shell_exec') && !function_exists('exec')) {
            throw new Exception('shell_exec and exec functions are disabled in PHP configuration. Please enable at least one of them to use Git operations.');
        }
        
        // Try different ways to find Git
        $gitCommands = [
            'git --version 2>&1',
            '"C:\Program Files\Git\bin\git" --version 2>&1',
            '"C:\Program Files (x86)\Git\bin\git" --version 2>&1',
            '/usr/bin/git --version 2>&1',
            '/usr/local/bin/git --version 2>&1'
        ];
        
        $gitFound = false;
        $gitVersion = '';
        
        foreach ($gitCommands as $cmd) {
            $output = array();
            $returnCode = 0;
            exec($cmd, $output, $returnCode);
            
            if ($returnCode === 0 && preg_match('/^git version/', implode(' ', $output))) {
                $gitFound = true;
                $gitVersion = trim(implode(' ', $output));
                addLog("Git found with command: {$cmd}", 'info');
                break;
            }
        }
        
        if (!$gitFound) {
            throw new Exception('Git is not available on this system. Please install Git and ensure it\'s in your PATH, or update the Git path in the code.');
        }
        
        addLog("Git version: " . $gitVersion, 'info');
    }
    
    /**
     * Configure Git to trust this directory (fixes ownership issues)
     */
    private function configureSafeDirectory() {
        try {
            // Add the directory to Git's safe list
            $cmd = 'git config --global --add safe.directory ' . escapeArg($this->projectPath);
            shell_exec($cmd . ' 2>&1');
            
            addLog("Added safe directory: {$this->projectPath}", 'info');
        } catch (Exception $e) {
            addLog("Failed to configure safe directory: " . $e->getMessage(), 'warning');
        }
    }
    
    public function __construct($projectPath) {
        if (!isValidPath($projectPath)) {
            throw new Exception("Invalid project path: {$projectPath}");
        }
        $this->projectPath = realpath($projectPath);
        $appRoot = defined('APP_ROOT') ? realpath(APP_ROOT) : false;
        $this->protectSecrets = ($appRoot && strcasecmp($this->projectPath, $appRoot) === 0);
        
        // Verify Git is available
        $this->verifyGitAvailable();
        
        // Configure Git to trust this directory
        $this->configureSafeDirectory();
    }
    
    /**
     * Initialize git repository
     */
    public function init() {
        // Use cd approach with proper path handling
        $cmd = 'cd /d "' . $this->projectPath . '" && git init';
        $output = $this->executeCommand($cmd);
        
        addLog("Git initialized in: {$this->projectPath}", 'info');
        return true;
    }
    
    /**
     * Add all files to staging area
     */
    public function addAll() {
        // First, let's list files in the directory to make sure they exist
        $files = scandir($this->projectPath);
        $actualFiles = array_filter($files, function($file) {
            return !in_array($file, ['.', '..']);
        });
        addLog("Files found in directory (including .git): " . implode(', ', $actualFiles), 'info');
        
        // Count total files to verify
        $totalFiles = getDirectoryFileCountRecursive($this->projectPath);
        addLog("Total files (recursive): {$totalFiles}", 'info');
        
        // Clean up stale index.lock files before attempting git operations
        $this->cleanupIndexLock();
        
        // Use git add with --force to ensure all files are added, even if they would be ignored
        // This includes all files in all subdirectories recursively
        $cmd = 'cd /d "' . $this->projectPath . '" && git add -A --force';
        $result = $this->executeCommand($cmd);
        addLog("Git add result: " . $result, 'info');
        
        addLog("Files added to staging area in: {$this->projectPath}", 'info');
        return true;
    }
    
    /**
     * Clean up stale index.lock files
     */
    private function cleanupIndexLock() {
        $lockFile = $this->projectPath . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'index.lock';
        if (file_exists($lockFile)) {
            try {
                // Force delete the lock file
                if (is_file($lockFile)) {
                    @unlink($lockFile);
                    addLog("Cleaned up stale index.lock file", 'info');
                }
            } catch (Exception $e) {
                addLog("Failed to cleanup index.lock: " . $e->getMessage(), 'warning');
            }
        }
    }
    
    /**
     * Commit changes
     */
    public function commit($message = 'Initial backup') {
        $escapedMessage = escapeshellarg($message);
        $cmd = 'cd /d "' . $this->projectPath . '" && git commit -m ' . $escapedMessage;
        
        try {
            $result = $this->executeCommand($cmd);
            addLog("Commit result: " . $result, 'info');
            addLog("Commit created: {$message}", 'info');
            return true;
        } catch (Exception $e) {
            // It's okay if there's nothing to commit
            if (strpos($e->getMessage(), 'nothing to commit') !== false) {
                addLog("Nothing to commit in: {$this->projectPath}", 'info');
                return true;
            }
            throw $e;
        }
    }
    
    /**
     * Check if repository has uncommitted changes
     */
    public function hasChanges() {
        $cmd = 'git -C ' . escapeArg($this->projectPath) . ' status --porcelain';
        $output = shell_exec($cmd);
        return !empty(trim($output));
    }
    
    /**
     * Rename branch to main
     */
    public function renameBranchToMain() {
        $cmd = 'cd /d "' . $this->projectPath . '" && git branch -M main';
        $this->executeCommand($cmd);
        
        addLog("Branch renamed to main", 'info');
        return true;
    }
    
    /**
     * Add remote origin
     */
    public function addRemote($remoteUrl, $remoteName = 'origin') {
        // Check if remote already exists
        if ($this->hasRemote($remoteName)) {
            return $this->updateRemote($remoteUrl, $remoteName);
        }
        
        $escapedUrl = escapeshellarg($remoteUrl);
        $cmd = 'cd /d "' . $this->projectPath . '" && git remote add ' . $remoteName . ' ' . $escapedUrl;
        $this->executeCommand($cmd);
        
        addLog("Remote '{$remoteName}' added: {$remoteUrl}", 'info');
        return true;
    }
    
    /**
     * Update existing remote
     */
    public function updateRemote($remoteUrl, $remoteName = 'origin') {
        $escapedUrl = escapeshellarg($remoteUrl);
        $cmd = 'git -C ' . escapeArg($this->projectPath) . ' remote set-url ' . $remoteName . ' ' . $escapedUrl;
        $this->executeCommand($cmd);
        
        addLog("Remote '{$remoteName}' updated: {$remoteUrl}", 'info');
        return true;
    }
    
    /**
     * Check if remote exists
     */
    public function hasRemote($remoteName = 'origin') {
        $cmd = 'git -C ' . escapeArg($this->projectPath) . ' remote | findstr ' . escapeshellarg($remoteName);
        $output = @shell_exec($cmd);
        return !empty(trim($output ?? ''));
    }
    
    /**
     * Push to remote
     */
    public function push($remoteName = 'origin', $branch = 'main', $force = false) {
        $forceFlag = $force ? ' -f' : '';
        $cmd = 'cd /d "' . $this->projectPath . '" && git push -u' . $forceFlag . ' ' . $remoteName . ' ' . $branch . ' 2>&1';
        
        try {
            $output = $this->executeCommand($cmd);
            addLog("Push result: " . $output, 'info');
            addLog("Pushed to {$remoteName}/{$branch}", 'info');
            return $output;
        } catch (Exception $e) {
            addLog("Push failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Configure git user (if needed)
     */
    public function configureUser($name = 'GitHub Backup Manager', $email = 'backup@github.com') {
        $escapedName = escapeshellarg($name);
        $escapedEmail = escapeshellarg($email);
        
        shell_exec('git config --global user.name ' . $escapedName);
        shell_exec('git config --global user.email ' . $escapedEmail);
        
        addLog("Git user configured: {$name} <{$email}>", 'info');
        return true;
    }
    
    /**
     * Get current status
     */
    public function getStatus() {
        $cmd = 'cd /d "' . $this->projectPath . '" && git status --short';
        $output = shell_exec($cmd);
        return $output !== null ? trim($output) : '';
    }
    
    /**
     * Get current branch
     */
    public function getCurrentBranch() {
        $cmd = 'git -C ' . escapeArg($this->projectPath) . ' rev-parse --abbrev-ref HEAD 2>nul';
        $output = shell_exec($cmd);
        return $output !== null ? trim($output) : '';
    }
    
    /**
     * Ensure the repository is on the main branch
     */
    public function ensureMainBranch() {
        $currentBranch = $this->getCurrentBranch();
        
        if ($currentBranch === '' || $currentBranch === 'HEAD') {
            $cmd = 'cd /d "' . $this->projectPath . '" && git checkout -B main';
            $this->executeCommand($cmd);
            addLog("Checked out new branch main", 'info');
            return 'main';
        }
        
        if ($currentBranch !== 'main') {
            $cmd = 'cd /d "' . $this->projectPath . '" && git branch -M main';
            $this->executeCommand($cmd);
            addLog("Renamed branch {$currentBranch} to main", 'info');
        }
        
        return 'main';
    }
    
    /**
     * Get current remote URL
     */
    public function getRemoteUrl($remoteName = 'origin') {
        $cmd = 'git -C ' . escapeArg($this->projectPath) . ' remote get-url ' . $remoteName . ' 2>&1';
        $output = shell_exec($cmd);
        $output = $output !== null ? trim($output) : '';
        
        if (strpos($output, 'fatal:') !== false) {
            return null;
        }
        
        return $output;
    }
    
    /**
     * Get last commit hash
     */
    public function getLastCommit() {
        $cmd = 'git -C ' . escapeArg($this->projectPath) . ' log -1 --pretty=format:"%H"';
        $output = @shell_exec($cmd);
        return trim($output);
    }
    
    /**
     * Get commit count
     */
    public function getCommitCount() {
        $cmd = 'git -C ' . escapeArg($this->projectPath) . ' rev-list --count HEAD 2>/dev/null';
        $output = @shell_exec($cmd);
        return (int) trim($output) ?: 0;
    }
    
    /**
     * Execute git command safely
     */
    private function executeCommand($cmd) {
        // Ensure the directory is in Git's safe list before executing commands
        $this->configureSafeDirectory();
        
        addLog("Executing git command: {$cmd}", 'debug');
        
        // Try exec instead of shell_exec for better error handling
        $output = array();
        $returnCode = 0;
        exec($cmd . ' 2>&1', $output, $returnCode);
        
        $outputStr = implode("\n", $output);
        
        addLog("Git command return code: {$returnCode}", 'debug');
        addLog("Git command output: " . substr($outputStr, 0, 200) . (strlen($outputStr) > 200 ? '...' : ''), 'debug');
        
        if ($returnCode !== 0) {
            // For some Git commands, warnings don't mean failure
            // Only fail if it's a fatal error or the command completely failed
            $outputStrLower = strtolower($outputStr);
            if (strpos($outputStrLower, 'fatal') !== false || 
                strpos($outputStrLower, 'error') !== false || 
                empty(trim($outputStr))) {
                throw new Exception('Git command failed with return code ' . $returnCode . ': ' . trim($outputStr));
            }
            // If it's just a warning, continue
            addLog("Git command had warnings but continuing: " . trim($outputStr), 'warning');
        }
        
        return trim($outputStr);
    }
    
    /**
     * Perform full backup workflow
     */
    // public function backup($remoteUrl, $isExisting = false) {
    //     try {
    //         // Step 1: Initialize if needed
    //         if (!isGitRepository($this->projectPath)) {
    //             $this->configureUser();
    //             $this->init();
    //         }

    //         // Step 2: Stage all files FIRST (before generating .gitignore)
    //         // Remove any existing .gitignore to ensure all files can be added
    //         $gitignorePath = $this->projectPath . DIRECTORY_SEPARATOR . '.gitignore';
    //         if (file_exists($gitignorePath)) {
    //             unlink($gitignorePath);
    //             addLog("Removed existing .gitignore to allow all files to be backed up", 'info');
    //         }

    //         $this->addAll();

    //         // Step 3: Generate .gitignore if needed (after adding files)
    //         // For backups, we want to include everything, so skip .gitignore generation
    //         // if (getSetting('auto_gitignore')) {
    //         //     generateGitIgnore($this->projectPath);
    //         //     // Re-stage .gitignore file
    //         //     $cmd = 'cd ' . escapeArg($this->projectPath) . ' && git add .gitignore';
    //         //     shell_exec($cmd . ' 2>&1');
    //         // }

    //         // Check if there are files to commit
    //         $status = $this->getStatus();
    //         addLog("Git status after adding files: " . $status, 'info');

    //         $hasChanges = !empty(trim($status));

    //         // For new repositories, we require at least some files
    //         // For existing repositories being synced, we allow no changes (just ensure sync)
    //         if (!$hasChanges && !$isExisting) {
    //             addLog("No files to backup in: {$this->projectPath}", 'warning');
    //             return array(
    //                 'success' => false,
    //                 'message' => 'No files found to backup. The directory may be empty or all files are excluded by .gitignore. Check your auto_gitignore setting or .gitignore file.'
    //             );
    //         }

    //         if (!$hasChanges && $isExisting) {
    //             addLog("No changes to commit, but repository exists - performing sync", 'info');
    //             // Even though there are no changes, we still perform the push to ensure sync
    //             // This handles the case where the local repo might be out of sync
    //         }

    //         // Step 4: Commit (only if there are changes)
    //         if ($hasChanges) {
    //             $message = 'Initial backup [' . date('Y-m-d H:i:s') . ']';
    //             $commitResult = $this->commit($message);

    //             if (!$commitResult) {
    //                 addLog("Commit failed in: {$this->projectPath}", 'warning');
    //                 return array(
    //                     'success' => false,
    //                     'message' => 'Failed to commit changes'
    //                 );
    //             }
    //             addLog("Committed changes", 'info');
    //         } else {
    //             addLog("Skipping commit - no changes to commit", 'info');
    //         }

    //         // Step 5: Ensure main branch
    //         try {
    //             $this->renameBranchToMain();
    //         } catch (Exception $e) {
    //             // Branch might already be main
    //         }

    //         // Step 6: Add/update remote
    //         $this->addRemote($remoteUrl);

    //         // Step 7: Push (force push to overwrite any remote content)
    //         $this->push('origin', 'main', true);

    //         return array(
    //             'success' => true,
    //             'message' => 'Backup completed successfully'
    //         );
    //     } catch (Exception $e) {
    //         addLog('Backup failed: ' . $e->getMessage(), 'error');
    //         throw $e;
    //     }
    // }
    public function backup($remoteUrl, $isExisting = false)
    {
        try {
            $path = $this->projectPath;

            if (!is_dir($path)) {
                return [
                    'success' => false,
                    'message' => 'Project folder not found'
                ];
            }

            // Helper runner with lock cleanup
            $run = function ($cmd) use ($path) {
                // Clean up any stale lock files before running commands
                $lockFile = $path . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'index.lock';
                if (file_exists($lockFile)) {
                    @unlink($lockFile);
                }
                
                $full = 'cd /d "' . $path . '" && ' . $cmd . ' 2>&1';
                $out = shell_exec($full);
                addLog("CMD: " . $full, 'info');
                addLog("OUT: " . $out, 'info');
                return trim((string)$out);
            };

            addLog("Working Dir: " . $path, 'info');
            addLog("Files: " . implode(', ', scandir($path)), 'info');

            $gitFolder = $path . DIRECTORY_SEPARATOR . '.git';
            $isGitRepo = is_dir($gitFolder);
            
            // ==========================================
            // STEP 1: Initialize or Update existing repo
            // ==========================================
            addLog("Step 1: Checking repository status", 'info');
            
            // Detect "resume" mode: local .git has commits but push was never completed
            // (e.g. previous run timed out after commit but before push)
            $localHasCommit = false;
            if ($isGitRepo) {
                $headCheck = shell_exec('cd /d "' . $path . '" && git rev-parse --verify HEAD 2>&1');
                $localHasCommit = (strpos((string)$headCheck, 'fatal') === false && !empty(trim((string)$headCheck)));
            }
            $localHasRemote = false;
            if ($isGitRepo) {
                $remoteCheck = shell_exec('cd /d "' . $path . '" && git remote -v 2>&1');
                $localHasRemote = !empty(trim((string)$remoteCheck));
            }
            
            // Resume mode: local commit exists but remote not yet pushed (previous timeout recovery)
            $isResumeMode = ($isGitRepo && $localHasCommit && !$localHasRemote && !$isExisting);
            
            if ($isResumeMode) {
                addLog("Resume mode detected: local commits exist but no remote push yet. Skipping git init/add, going straight to push.", 'info');
                $this->cleanupIndexLock();
                // Jump directly to branch + remote + push (skip Steps 2-6)
                $run('git branch -M main');
                addLog("Branch set to main", 'info');
                $run('git remote remove origin');
                $run('git remote add origin "' . $remoteUrl . '"');
                addLog("Remote configured", 'info');
                if ($this->protectSecrets) {
                    $this->excludeSecretsFromIndex($run);
                    $secretError = $this->findStagedSecrets($run);
                    if ($secretError) {
                        return ['success' => false, 'message' => $secretError];
                    }
                }
                $currentBranch = trim($run('git branch --show-current'));
                addLog("Current branch before push: {$currentBranch}", 'info');
                $logOutput = trim($run('git log --oneline -1'));
                addLog("Latest commit: {$logOutput}", 'info');
                addLog('Pushing resumed backup to GitHub', 'info');
                $push = $run('git push --force --set-upstream origin main');
                addLog("Push output: " . substr($push, 0, 500), 'info');
                if (stripos($push, 'error') !== false || stripos($push, 'failed') !== false) {
                    addLog("Push failed: {$push}", 'error');
                    return ['success' => false, 'message' => 'Push failed: ' . substr($push, 0, 200)];
                }
                addLog('Resumed backup pushed successfully', 'info');
                return ['success' => true, 'message' => 'Project successfully backed up to GitHub (resumed).'];
            } elseif ($isGitRepo && $isExisting) {
                addLog("Repository exists, updating existing repo", 'info');
                // For existing repos, just clean the index.lock and proceed
                $this->cleanupIndexLock();
            } else {
                // Fresh backup - remove old .git if exists
                if (is_dir($gitFolder)) {
                    shell_exec('rmdir /s /q "' . $gitFolder . '" 2>&1');
                    addLog("Old .git removed - starting fresh backup", 'info');
                }
                $this->configureUser();
                $run('git init');
                addLog("Fresh Git repository initialized", 'info');
            }

            // ==========================================
            // STEP 2: Prepare files (.gitignore)
            // ==========================================
            addLog("Step 2: Preparing files for backup", 'info');
            $gitignore = $path . DIRECTORY_SEPARATOR . '.gitignore';
            if ($this->protectSecrets) {
                $this->writeSecretGitignore();
                addLog("Keeping .gitignore so the GitHub token, logs, and cache are not pushed", 'info');
            } elseif (file_exists($gitignore)) {
                unlink($gitignore);
                addLog(".gitignore removed to include all files", 'info');
            }

            // ==========================================
            // STEP 3: Clean up index.lock before add
            // ==========================================
            addLog("Step 3: Cleaning up any lock files", 'info');
            $this->cleanupIndexLock();
            
            // Small delay to ensure lock is released
            usleep(100000);

            // ==========================================
            // STEP 4: Add All Files with retry
            // ==========================================
            addLog("Step 4: Adding files to Git staging area", 'info');
            $maxRetries = 3;
            $addSuccess = false;
            $addOutput = '';
            
            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                $addCmd = $this->protectSecrets ? 'git add -A' : 'git add -A --force';
                $addOutput = $run($addCmd);
                
                if (strpos($addOutput, 'index.lock') === false && 
                    strpos($addOutput, 'fatal') === false) {
                    $addSuccess = true;
                    addLog("Files added successfully on attempt {$attempt}", 'info');
                    break;
                }
                
                addLog("Retry {$attempt}: index.lock issue detected, cleaning up...", 'warning');
                $this->cleanupIndexLock();
                usleep(200000);
            }
            
            if (!$addSuccess && strpos($addOutput, 'index.lock') !== false) {
                return [
                    'success' => false,
                    'message' => 'Failed to add files - index.lock issue persists. Try again in a moment.'
                ];
            }

            if ($this->protectSecrets) {
                $this->excludeSecretsFromIndex($run);
                $secretError = $this->findStagedSecrets($run);
                if ($secretError) {
                    addLog($secretError, 'error');
                    return [
                        'success' => false,
                        'message' => $secretError
                    ];
                }
            }

            // ==========================================
            // STEP 5: Check for changes
            // ==========================================
            addLog("Step 5: Checking for file changes", 'info');
            $status = $run('git status --short');

            if ($status === '' && !$isExisting) {
                return [
                    'success' => false,
                    'message' => 'No files detected to commit.'
                ];
            }

            // ==========================================
            // STEP 6: Commit (only if there are changes)
            // ==========================================
            addLog("Step 6: Creating commit", 'info');
            // NOTE: Do NOT embed 2>nul here - $run() already appends 2>&1
            $hasHead = trim($run('git rev-parse --verify HEAD'));
            // On a fresh repo with no commits, this returns a non-zero exit code
            // and outputs "fatal: Needed a single revision" - which means no HEAD yet
            $hasNoHead = (strpos($hasHead, 'fatal') !== false || $hasHead === '');
            $msg = 'Auto backup ' . date('Y-m-d H:i:s');

            if (!empty(trim($status))) {
                // There are staged changes - create a regular commit
                $commitOut = $run('git commit -m "' . $msg . '"');
                addLog("Commit result: " . substr($commitOut, 0, 200), 'info');
                addLog("Commit completed", 'info');
            } elseif ($hasNoHead) {
                // No staged changes and no HEAD commit found - create initial empty commit
                addLog("No staged changes and no HEAD commit found - creating initial commit", 'info');
                $commitOut = $run('git commit --allow-empty -m "' . $msg . '"');
                addLog("Initial commit result: " . substr($commitOut, 0, 200), 'info');
            } elseif ($isExisting) {
                addLog("No changes to commit but repo exists - will still push", 'info');
            }

            // ==========================================
            // STEP 7: Ensure main branch and configure Remote
            // ==========================================
            addLog("Step 7: Configuring Git branch and remote", 'info');
            // Rename branch to main (works after first commit exists)
            $branchOut = $run('git branch -M main');
            addLog("Branch renamed to main: " . $branchOut, 'info');
            $run('git remote remove origin');
            $run('git remote add origin "' . $remoteUrl . '"');
            addLog("Remote configured", 'info');

            // ==========================================
            // STEP 8: Verify branch and push
            // ==========================================
            $currentBranch = trim($run('git branch --show-current'));
            addLog("Current branch before push: {$currentBranch}", 'info');
            
            $logOutput = trim($run('git log --oneline -1'));
            addLog("Latest commit: {$logOutput}", 'info');
            
            // ==========================================
            // STEP 9: Force Push main to remote
            // ==========================================
            addLog('Step 9: Pushing to GitHub', 'info');
            $push = $run('git push --force --set-upstream origin main');
            addLog("Push output received: " . substr($push, 0, 500), 'info');

            if (
                stripos($push, 'error') !== false ||
                stripos($push, 'failed') !== false ||
                (stripos($push, 'rejected') !== false && stripos($push, 'pre-receive') !== false)
            ) {
                addLog("Push failed with output: {$push}", 'error');
                return [
                    'success' => false,
                    'message' => 'Push failed: ' . substr($push, 0, 200)
                ];
            }

            addLog('Backup and push completed successfully', 'info');
            return [
                'success' => true,
                'message' => 'Project successfully backed up to GitHub.'
            ];
        } catch (Exception $e) {
            addLog('Backup failed: ' . $e->getMessage(), 'error');

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function writeSecretGitignore() {
        $content = "# Local secrets and runtime data — never push these\n"
            . "/config/settings.json\n"
            . "/logs/\n"
            . "/cache/\n"
            . "*.log\n"
            . ".env\n"
            . ".env.*\n";
        file_put_contents($this->projectPath . DIRECTORY_SEPARATOR . '.gitignore', $content);
    }

    private function excludeSecretsFromIndex($run) {
        $run('git rm -r --cached -f --ignore-unmatch -- "config/settings.json" "logs" "cache"');
        $run('git reset HEAD -- "config/settings.json" "logs" "cache"');
    }

    private function findStagedSecrets($run) {
        $tracked = $run('git ls-files');
        $trackedFiles = preg_split('/\r\n|\r|\n/', (string) $tracked);
        foreach ($trackedFiles as $file) {
            $file = trim($file);
            if ($file === '') {
                continue;
            }
            $normalized = strtolower(str_replace('\\', '/', $file));
            if ($normalized === 'config/settings.json' || strpos($normalized, 'logs/') === 0 || strpos($normalized, 'cache/') === 0) {
                return 'Refusing to push: ' . $file . ' would upload local secrets.';
            }
        }

        $cached = $run('git diff --cached');
        if (preg_match('/ghp_[A-Za-z0-9]{36}|gho_[A-Za-z0-9]{36}|ghu_[A-Za-z0-9]{36}|ghs_[A-Za-z0-9]{36}|github_pat_[A-Za-z0-9]{11}_[A-Za-z0-9]{20,}/', (string) $cached)) {
            return 'Refusing to push: a GitHub token was found in staged files.';
        }

        return null;
    }
}
