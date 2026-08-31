// GitHub Backup Manager API Documentation

/**
 * All API endpoints return JSON responses with this format:
 * {
 *     "success": boolean,
 *     "message": "Human readable message",
 *     "data": {...},
 *     "timestamp": "ISO 8601 timestamp"
 * }
 */

// ============================================
// SCANNING & PROJECT DISCOVERY
// ============================================

/**
 * GET /actions/scan-projects.php
 * 
 * Scans the configured projects folder and returns all project information.
 * 
 * Response:
 * {
 *     "success": true,
 *     "message": "Projects scanned successfully",
 *     "data": {
 *         "projects": [
 *             {
 *                 "name": "my-project",
 *                 "path": "D:/wamp64/www/projects/my-project",
 *                 "is_git": true,
 *                 "has_remote": false,
 *                 "remote_url": null,
 *                 "modified": "2024-01-15 14:30:00",
 *                 "size": "2.5 MB",
 *                 "file_count": 45
 *             }
 *         ],
 *         "count": 1
 *     }
 * }
 */
endpoint.GET('/actions/scan-projects.php');


// ============================================
// SETTINGS MANAGEMENT
// ============================================

/**
 * GET /actions/get-settings.php
 * 
 * Loads current settings from config/settings.json.
 * NOTE: GitHub token is NOT returned for security.
 * 
 * Response:
 * {
 *     "success": true,
 *     "message": "Settings loaded",
 *     "data": {
 *         "github_username": "octocat",
 *         "projects_path": "D:/wamp64/www/projects/",
 *         "auto_gitignore": true,
 *         "dark_mode": false
 *     }
 * }
 */
endpoint.GET('/actions/get-settings.php');


/**
 * POST /actions/save-settings.php
 * 
 * Saves user settings to config/settings.json.
 * Only non-empty fields are updated.
 * 
 * Request body:
 * {
 *     "github_username": "octocat",
 *     "github_token": "ghp_xxxxxxxxxxxx",
 *     "projects_path": "D:/wamp64/www/projects/",
 *     "auto_gitignore": true,
 *     "dark_mode": false
 * }
 * 
 * Response:
 * {
 *     "success": true,
 *     "message": "Settings saved successfully",
 *     "data": {}
 * }
 * 
 * Errors:
 * - "Invalid GitHub username format"
 * - "Invalid GitHub token format"
 * - "Invalid projects path"
 */
endpoint.POST('/actions/save-settings.php', requestBody);


/**
 * POST /actions/test-github.php
 * 
 * Tests GitHub API connection with provided token.
 * Verifies token is valid and has required permissions.
 * 
 * Request body:
 * {
 *     "token": "ghp_xxxxxxxxxxxx"
 * }
 * 
 * Success Response:
 * {
 *     "success": true,
 *     "message": "Connection successful",
 *     "data": {
 *         "user": {
 *             "login": "octocat",
 *             "name": "The Octocat",
 *             "bio": "There once was..."
 *         }
 *     }
 * }
 * 
 * Error Response:
 * {
 *     "success": false,
 *     "message": "Invalid credentials: ...",
 *     "data": {}
 * }
 */
endpoint.POST('/actions/test-github.php', { token: string });


// ============================================
// PROJECT BACKUP & OPERATIONS
// ============================================

/**
 * GET /actions/get-project-details.php?path=...
 * 
 * Gets detailed information about a specific project.
 * 
 * Query Parameters:
 * - path (string, required): Full path to project folder
 * 
 * Response:
 * {
 *     "success": true,
 *     "message": "Project details loaded",
 *     "data": {
 *         "name": "my-project",
 *         "path": "D:/wamp64/www/projects/my-project",
 *         "size": "2.5 MB",
 *         "modified": "2024-01-15 14:30:00",
 *         "is_git": true,
 *         "remote_url": "https://github.com/octocat/my-project.git"
 *     }
 * }
 */
endpoint.GET('/actions/get-project-details.php?path=' + encodeURIComponent(projectPath));


/**
 * POST /actions/backup-project.php
 * 
 * Backs up a single project to GitHub.
 * Creates private GitHub repo and pushes code.
 * 
 * Request body:
 * {
 *     "path": "D:/wamp64/www/projects/my-project",
 *     "name": "my-project"
 * }
 * 
 * Success Response:
 * {
 *     "success": true,
 *     "message": "Project backed up successfully!",
 *     "data": {
 *         "repo_name": "my-project",
 *         "repo_url": "https://github.com/octocat/my-project",
 *         "repo_clone_url": "https://github.com/octocat/my-project.git"
 *     }
 * }
 * 
 * Error Responses:
 * {
 *     "success": false,
 *     "message": "Repository 'my-project' already exists on GitHub"
 * }
 * 
 * Process:
 * 1. Creates private repo on GitHub
 * 2. Initializes git (if needed)
 * 3. Generates .gitignore (if enabled)
 * 4. Stages all files (git add .)
 * 5. Creates commit (git commit)
 * 6. Renames branch to main
 * 7. Configures remote origin
 * 8. Pushes to GitHub (git push)
 */
endpoint.POST('/actions/backup-project.php', {
    path: string,
    name: string
});


/**
 * POST /actions/backup-all.php
 * 
 * Backs up ALL projects from the configured folder.
 * Loops through projects and backs each one up.
 * Takes time - be patient!
 * 
 * Response:
 * {
 *     "success": true,
 *     "message": "Bulk backup completed: 5 succeeded, 1 failed",
 *     "data": {
 *         "success_count": 5,
 *         "failed_count": 1,
 *         "results": [
 *             {
 *                 "project": "my-project",
 *                 "status": "success",
 *                 "repo_url": "https://github.com/octocat/my-project"
 *             },
 *             {
 *                 "project": "existing-repo",
 *                 "status": "skipped",
 *                 "message": "Repository already exists"
 *             }
 *         ]
 *     }
 * }
 * 
 * Result statuses:
 * - "success": Project backed up successfully
 * - "skipped": Repository already exists
 * - "failed": Backup failed (see error message)
 */
endpoint.POST('/actions/backup-all.php');


// ============================================
// UTILITY & MAINTENANCE
// ============================================

/**
 * POST /actions/clear-data.php
 * 
 * Clears all application data.
 * WARNING: This is destructive and cannot be undone!
 * - Deletes config/settings.json
 * - Deletes all log files
 * - Does NOT delete actual projects or GitHub repos
 * 
 * Response:
 * {
 *     "success": true,
 *     "message": "All data cleared successfully",
 *     "data": {}
 * }
 * 
 * After calling this, user must reconfigure in Settings.
 */
endpoint.POST('/actions/clear-data.php');


// ============================================
// ERROR HANDLING
// ============================================

/**
 * All errors follow this format:
 * 
 * {
 *     "success": false,
 *     "message": "Error description",
 *     "data": {},
 *     "timestamp": "2024-01-15T14:30:00+00:00"
 * }
 * 
 * Common errors:
 * - "Invalid GitHub token" - Token format incorrect
 * - "Invalid project path" - Path doesn't exist or not readable
 * - "GitHub API Error (403)" - Token lacks permissions
 * - "GitHub API Error (404)" - Repository not found
 * - "Git error: fatal:" - Git command failed
 */


// ============================================
// CLIENT-SIDE USAGE EXAMPLES
// ============================================

/**
 * Example 1: Scan projects
 */
async function scanProjects() {
    const response = await fetch('actions/scan-projects.php');
    const data = await response.json();
    
    if (data.success) {
        console.log('Found projects:', data.data.projects);
    } else {
        console.error('Scan failed:', data.message);
    }
}


/**
 * Example 2: Backup a single project
 */
async function backupProject(path, name) {
    const response = await fetch('actions/backup-project.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            path: path,
            name: name
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        console.log('Backup complete:', data.data.repo_url);
    } else {
        console.error('Backup failed:', data.message);
    }
}


/**
 * Example 3: Save settings
 */
async function saveSettings(settings) {
    const response = await fetch('actions/save-settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(settings)
    });
    
    const data = await response.json();
    
    if (data.success) {
        console.log('Settings saved');
    } else {
        console.error('Settings save failed:', data.message);
    }
}


/**
 * Example 4: Test GitHub connection
 */
async function testConnection(token) {
    const response = await fetch('actions/test-github.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            token: token
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        console.log('Connected as:', data.data.user.login);
    } else {
        console.error('Connection failed:', data.message);
    }
}


// ============================================
// RATE LIMITING & BEST PRACTICES
// ============================================

/**
 * GitHub API Rate Limits (for authenticated requests):
 * - 5,000 requests per hour per token
 * - Most app operations use 1-3 requests
 * 
 * Best practices:
 * 1. Use bulk operations when possible
 * 2. Don't spam API calls in a loop
 * 3. Handle rate limiting gracefully
 * 4. Cache results when appropriate
 * 5. Monitor rate limit headers
 */


// ============================================
// RESPONSE TIMES
// ============================================

/**
 * Typical response times:
 * 
 * Scan 50 projects: 2-3 seconds
 * Backup single (100MB): 8-15 seconds
 * Backup all (10 projects): 1-2 minutes
 * Test connection: <1 second
 * Get settings: <0.1 seconds
 * Save settings: <0.1 seconds
 * 
 * Times depend on:
 * - Internet speed
 * - GitHub API response time
 * - Project size
 * - Disk speed
 * - System load
 */
