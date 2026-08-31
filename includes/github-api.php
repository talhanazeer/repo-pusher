<?php
/**
 * GitHub Backup Manager - GitHub API Integration
 */

require_once __DIR__ . '/functions.php';

class GitHubAPI {
    private $token;
    private $username;
    private $baseUrl = 'https://api.github.com';
    
    public function __construct($token = null, $username = null) {
        $this->token = $token ?: getGitHubToken();
        $this->username = $username ?: getGitHubUsername();
    }
    
    /**
     * Make API request to GitHub
     */
    private function request($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->token,
            'User-Agent: GitHub-Backup-Manager',
            'Accept: application/vnd.github.v3+json',
            'X-GitHub-Api-Version: 2022-11-28'
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->token,
                'User-Agent: GitHub-Backup-Manager',
                'Accept: application/vnd.github.v3+json',
                'X-GitHub-Api-Version: 2022-11-28',
                'Content-Type: application/json'
            ));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('CURL Error: ' . $error);
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $message = isset($responseData['message']) ? $responseData['message'] : 'Unknown error';
            throw new Exception("GitHub API Error ({$httpCode}): {$message}");
        }
        
        return $responseData;
    }
    
    /**
     * Verify token and get user info
     */
    public function verifyToken() {
        try {
            $response = $this->request('/user');
            return array(
                'valid' => true,
                'login' => $response['login'] ?? null,
                'name' => $response['name'] ?? null,
                'bio' => $response['bio'] ?? null
            );
        } catch (Exception $e) {
            return array(
                'valid' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    /**
     * Check if repository exists
     */
    public function repoExists($repoName) {
        try {
            $this->request("/repos/{$this->username}/{$repoName}");
            return true;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), '404') !== false) {
                return false;
            }
            throw $e;
        }
    }
    
    /**
     * Create a new repository (private by default)
     */
    public function createRepository($repoName, $description = '', $private = true) {
        $repoName = preg_replace('/[^a-zA-Z0-9._-]/', '-', $repoName);
        
        if (strlen($repoName) === 0) {
            throw new Exception('Invalid repository name');
        }
        
        // Check if repo already exists
        if ($this->repoExists($repoName)) {
            addLog("Repository '{$repoName}' already exists, using existing repository", 'info');
            $repo = $this->getRepository($repoName);
            return array(
                'repo' => $repo,
                'is_existing' => true
            );
        }
        
        $data = array(
            'name' => $repoName,
            'description' => $description,
            'private' => $private,
            'auto_init' => false
        );
        
        addLog("Creating GitHub repository '{$repoName}' as " . ($private ? 'private' : 'public'), 'info');
        
        try {
            $response = $this->request('/user/repos', 'POST', $data);
            
            $repo = array(
                'name' => $response['name'],
                'url' => $response['html_url'],
                'clone_url' => $response['clone_url'],
                'ssh_url' => $response['ssh_url'],
                'private' => $response['private']
            );
            
            return array(
                'repo' => $repo,
                'is_existing' => false
            );
        } catch (Exception $e) {
            addLog("Failed to create repo '{$repoName}': " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get repository information
     */
    public function getRepository($repoName) {
        try {
            $response = $this->request("/repos/{$this->username}/{$repoName}");
            
            return array(
                'name' => $response['name'],
                'url' => $response['html_url'],
                'clone_url' => $response['clone_url'],
                'ssh_url' => $response['ssh_url'],
                'private' => $response['private'],
                'size' => $response['size'],
                'created_at' => $response['created_at'],
                'pushed_at' => $response['pushed_at'],
                'updated_at' => $response['updated_at']
            );
        } catch (Exception $e) {
            addLog("Failed to get repo info '{$repoName}': " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Update repository description
     */
    public function updateRepository($repoName, $description) {
        $data = array('description' => $description);
        
        try {
            $this->request("/repos/{$this->username}/{$repoName}", 'PATCH', $data);
            return true;
        } catch (Exception $e) {
            addLog("Failed to update repo '{$repoName}': " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Get list of user repositories
     */
    public function listRepositories($type = 'all', $per_page = 100) {
        try {
            $response = $this->request("/user/repos?type={$type}&per_page={$per_page}");
            
            $repos = array();
            foreach ($response as $repo) {
                $repos[] = array(
                    'name' => $repo['name'],
                    'url' => $repo['html_url'],
                    'private' => $repo['private'],
                    'size' => $repo['size'],
                    'pushed_at' => $repo['pushed_at']
                );
            }
            
            return $repos;
        } catch (Exception $e) {
            addLog("Failed to list repos: " . $e->getMessage(), 'error');
            throw $e;
        }
    }
    
    /**
     * Delete repository
     */
    public function deleteRepository($repoName) {
        try {
            $this->request("/repos/{$this->username}/{$repoName}", 'DELETE');
            return true;
        } catch (Exception $e) {
            addLog("Failed to delete repo '{$repoName}': " . $e->getMessage(), 'error');
            throw $e;
        }
    }
}
