# API Reference - New Endpoints and Features

## New Endpoints Added

### 1. Get Project Status
**Endpoint:** `actions/get-project-status.php`
**Method:** POST
**Purpose:** Get detailed project information including modified files

**Request:**
```json
{
    "path": "/path/to/project"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Project status retrieved",
    "data": {
        "name": "MyProject",
        "path": "/path/to/project",
        "is_git": true,
        "has_remote": true,
        "remote_url": "https://github.com/user/repo.git",
        "file_count": 245,
        "size": "Calculating...",
        "modified_files": {
            "modified": 5,
            "untracked": 3,
            "deleted": 1,
            "files": [
                {"name": "config.php", "path": "src/config.php", "status": "modified"},
                {"name": "main.js", "path": "assets/js/main.js", "status": "untracked"},
                {"name": "old-file.txt", "path": "docs/old-file.txt", "status": "deleted"}
            ]
        }
    }
}
```

---

### 2. Clear Cache
**Endpoint:** `actions/clear-cache.php`
**Method:** GET or POST
**Purpose:** Clear the project scan cache to force fresh scan

**Response:**
```json
{
    "success": true,
    "message": "Cache cleared successfully"
}
```

---

## Enhanced Endpoints

### Scan Projects (Enhanced)
**Endpoint:** `actions/scan-projects.php`
**Method:** GET
**Query Parameters:**
- `nocache` (optional): Set to 1 to bypass cache

**Response Changes:**
```json
{
    "success": true,
    "message": "Projects scanned successfully",
    "data": {
        "projects": [
            {
                "name": "MyProject",
                "path": "/path/to/project",
                "is_git": true,
                "has_remote": true,
                "remote_url": "https://github.com/user/repo.git",
                "modified": "2026-04-23 13:30:20",
                "size": "Calculating...",
                "file_count": 245,
                "modified_files": {
                    "modified": 2,
                    "untracked": 1,
                    "deleted": 0,
                    "files": [
                        {"name": "config.php", "path": "src/config.php", "status": "modified"},
                        {"name": "test.js", "path": "test.js", "status": "untracked"}
                    ]
                }
            }
        ],
        "count": 1,
        "cached": false
    }
}
```

---

### Backup Project (Enhanced)
**Endpoint:** `actions/backup-project.php`
**Method:** POST
**Purpose:** Backup a single project to GitHub

**Request:**
```json
{
    "path": "/path/to/project",
    "name": "ProjectName"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "Backup successful",
    "data": {
        "status": "success",
        "repo_name": "ProjectName",
        "repo_url": "https://github.com/user/ProjectName",
        "message": "Project successfully backed up to GitHub."
    }
}
```

**Response (Failure):**
```json
{
    "success": false,
    "message": "Backup failed",
    "data": {
        "status": "failed",
        "error": "Unable to add files - index.lock issue persists"
    }
}
```

---

### Backup All Projects (Enhanced)
**Endpoint:** `actions/backup-all.php`
**Method:** POST
**Purpose:** Backup all projects in the configured folder

**Response:**
```json
{
    "success": true,
    "message": "Bulk backup completed: 5 succeeded, 1 failed, 2 skipped",
    "data": {
        "status": "success",
        "success_count": 5,
        "failed_count": 1,
        "skipped_count": 2,
        "results": [
            {
                "project": "ProjectA",
                "status": "success",
                "repo_name": "ProjectA",
                "repo_url": "https://github.com/user/ProjectA"
            },
            {
                "project": "ProjectB",
                "status": "failed",
                "error": "Network timeout"
            },
            {
                "project": "ProjectC",
                "status": "skipped"
            }
        ]
    }
}
```

---

## New PHP Functions

### cache-helper.php

#### ScanCache Class
```php
// Get cached results (returns null if expired or not found)
$projects = ScanCache::get($projectsPath);

// Save scan results to cache
ScanCache::set($projectsPath, $projects);

// Clear cache for specific path
ScanCache::clear($projectsPath);

// Clear all caches
ScanCache::clearAll();

// Get cache key for a path
$key = ScanCache::getCacheKey($projectsPath);
```

#### GitOptimizer Class
```php
// Execute git command with timeout
$output = GitOptimizer::executeOptimized($projectPath, 'git status', 30);

// Execute multiple commands efficiently
$results = GitOptimizer::batchCommands($projectPath, [
    'status' => 'git status --short',
    'count' => 'git rev-list --count HEAD'
]);
```

---

### functions.php - New Functions

#### getModifiedFiles()
```php
$modifiedFiles = getModifiedFiles($folderPath, $limit = 20);
// Returns:
// [
//     'modified' => int,
//     'untracked' => int,
//     'deleted' => int,
//     'files' => [
//         ['name' => 'file.txt', 'path' => 'src/file.txt', 'status' => 'modified'],
//         ...
//     ]
// ]
```

#### getDirectoryFileCountRecursive()
```php
$totalFiles = getDirectoryFileCountRecursive($path, $limit = 100000);
// Returns total file count including all subdirectories
// Skips .git folder automatically
// Returns count up to limit for performance
```

#### getGitRemoteUrl() - Enhanced
```php
$url = getGitRemoteUrl($folderPath, $remoteName = 'origin');
// Now optimized with timeout handling
// More reliable on slow systems
```

---

## JavaScript API Updates

### App.api() - Enhanced
```javascript
// Works with all endpoints
const response = await App.api('actions/get-project-status.php', {
    method: 'POST',
    body: { path: projectPath }
});
```

### Dashboard Methods

#### refreshProjects()
```javascript
// Clears cache and loads fresh project list
Dashboard.refreshProjects();
```

#### loadProjects() - Enhanced
```javascript
// Now shows "(cached)" indicator in success message
Dashboard.loadProjects();
```

#### backupProject() - Enhanced
```javascript
// Now shows status indicators:
// ✓ for success
// ✗ for failure
// Shows repo name on success
Dashboard.backupProject(path, name);
```

---

## Response Status Indicators

All API responses now include a `status` field:

- `success` - Operation completed successfully
- `failed` - Operation failed
- `warning` - Operation completed with warnings
- `skipped` - Operation was skipped (for bulk operations)

---

## Cache System Details

### Cache Files Location
```
/cache/
├── <hash1>.cache  (project scan results)
├── <hash2>.cache  (another project)
└── ...
```

### Cache Duration
- **Default:** 5 minutes
- **Configurable in:** `includes/cache-helper.php` line 11

### Clearing Cache
**Methods:**
1. Visit: `actions/clear-cache.php`
2. Click "Refresh" button in UI
3. Use query param: `?nocache=1` on scan
4. Call: `ScanCache::clearAll()`

---

## Error Handling

### Common Errors and Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| `Unable to create index.lock` | Stale lock file | Auto-cleanup + retry (implemented) |
| `Repository already exists` | Repo on GitHub | Auto-detected, updates instead |
| `No files detected` | Empty folder | Expected behavior |
| `Invalid project path` | Bad path config | Check settings |
| `GitHub token not configured` | Missing credentials | Configure in settings |

---

## Performance Characteristics

### Scan Operations
- **Cold scan (first time):** 8-15 seconds (depends on project count)
- **Warm scan (from cache):** 0.2-0.5 seconds
- **Cache duration:** 5 minutes
- **Max projects scanned:** No limit
- **Max files per project:** No limit (all included)

### Backup Operations
- **Single project:** 5-30 seconds (depends on size and network)
- **Bulk backup:** 1-10 minutes (depends on count and sizes)
- **Retry attempts:** Up to 3 with backoff
- **Timeout:** No limit (can be configured)

---

## Database/Storage

### Cache Storage
- **Type:** File-based JSON
- **Location:** `/cache/` directory
- **Format:** JSON
- **Size:** Minimal (only scan metadata)

### Logs
- **Location:** `/logs/app_YYYY-MM-DD.log`
- **Format:** Plain text with timestamps
- **Rotation:** Daily

---

## Version Information

- **API Version:** 1.1
- **Compatibility:** PHP 7.4+
- **Git:** 2.0+
- **Browser:** Modern browsers (ES6 support)

---

## Migration Guide

### From Previous Version

**No migration needed!** Changes are backward compatible.

**To use new features:**
1. Update your code to check for `modified_files` in scan results
2. Use new `get-project-status.php` endpoint for detailed info
3. Enable caching by using scan endpoint normally (automatic)
4. Use `?nocache=1` to force refresh

---

## Support & Debugging

### Enable Debug Logging
Check `/logs/` directory for detailed operation logs

### Check Cache Status
Look for `cached: true/false` in API response

### Force Cache Clear
```javascript
// In browser console
fetch('actions/clear-cache.php').then(r => r.json()).then(d => console.log(d));
```

### Monitor Performance
Watch for `CMD:` and `OUT:` lines in logs for git execution details

---

## FAQ

**Q: Why is my scan returning cached results?**
A: Cache is valid for 5 minutes. Add `?nocache=1` to force refresh or click the Refresh button.

**Q: Can I change the cache duration?**
A: Yes, edit `includes/cache-helper.php` line 11: `private static $cacheDuration = 300;`

**Q: Will old repositories be updated or replaced?**
A: They will be updated (push only new changes), not replaced.

**Q: How do I see all modified files?**
A: Use the project Details view which shows up to 50 modified files.

**Q: What if backup fails due to index.lock?**
A: It will automatically retry up to 3 times with cleanup between attempts.

---

*Last Updated: April 23, 2026*
