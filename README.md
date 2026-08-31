# GitHub Backup Manager

A modern, local web-based tool that automatically creates PRIVATE GitHub repositories from your local project folders and pushes the code.

## Features

✅ **Dashboard UI** - Clean, responsive interface with dark mode support
✅ **Folder Scanner** - Automatically detects all projects in your local directory
✅ **Automatic Backup** - Create private GitHub repositories with one click
✅ **Bulk Operations** - Backup all projects at once
✅ **Git Automation** - Handles all git operations (init, commit, push)
✅ **Auto .gitignore** - Generates .gitignore if missing
✅ **Secure Token Storage** - Tokens never exposed in frontend
✅ **Real-time Status** - See project status (Git initialized, linked to GitHub)
✅ **Search & Filter** - Find projects quickly
✅ **Detailed Logs** - Full operation history
✅ **Responsive Design** - Works on desktop, tablet, mobile

## Requirements

- PHP 7.2+
- Git installed and in system PATH
- CURL enabled in PHP
- A GitHub Personal Access Token (PAT)

## Installation

1. **Extract the project** to your WAMP/LAMP htdocs folder:
   ```
   d:\wamp64\www\projects\repo-pusher
   ```

2. **Ensure git is installed** and accessible from command line:
   ```bash
   git --version
   ```

3. **Create GitHub Personal Access Token**:
   - Go to https://github.com/settings/tokens
   - Click "Generate new token" → "Generate new token (classic)"
   - Select scopes: `repo`, `public_repo`
   - Copy the token (you won't see it again!)

## Quick Start

### 1. Access the Application
```
http://localhost/projects/repo-pusher/
```

### 2. Configure Settings
- Go to **Settings** page
- Enter your GitHub username
- Paste your Personal Access Token
- Verify projects folder path
- Click **"Test Connection"** to verify credentials
- Click **"Save Settings"**

### 3. Scan Projects
- Go to **Dashboard**
- Click **"Scan Projects"** button
- All projects in your folder will appear as cards

### 4. Backup Projects
- **Single backup**: Click "Backup" on any project card
- **Bulk backup**: Click "Backup All" button
- Wait for the operation to complete
- Check the notifications for status

## File Structure

```
repo-pusher/
├── config/
│   ├── config.php              # Configuration loader
│   └── settings.json           # User settings (auto-generated)
├── includes/
│   ├── functions.php           # Utility functions
│   ├── github-api.php          # GitHub API wrapper class
│   └── git-helper.php          # Git operations class
├── assets/
│   ├── css/
│   │   └── style.css           # Main stylesheet (dark mode included)
│   └── js/
│       └── app.js              # Frontend JavaScript
├── actions/
│   ├── scan-projects.php       # Project scanning endpoint
│   ├── backup-project.php      # Single project backup
│   ├── backup-all.php          # Bulk backup endpoint
│   ├── save-settings.php       # Settings save endpoint
│   ├── get-settings.php        # Settings load endpoint
│   ├── test-github.php         # GitHub connection test
│   ├── get-project-details.php # Project details endpoint
│   └── clear-data.php          # Clear all data endpoint
├── logs/
│   └── app_YYYY-MM-DD.log     # Daily application logs
├── index.php                   # Dashboard page
├── settings.php                # Settings page
└── README.md                   # This file
```

## Configuration

Settings are stored in `config/settings.json`:

```json
{
    "github_username": "your-username",
    "github_token": "ghp_xxxxxxxxxxxxxxxxxxxx",
    "projects_path": "D:/wamp64/www/projects/",
    "auto_gitignore": true,
    "dark_mode": false
}
```

**Important**: Never commit `settings.json` to version control!

## What Happens During Backup

When you backup a project, the following workflow runs:

1. **GitHub API** creates a private repository
2. **Git** initializes the local folder (if needed)
3. **Auto .gitignore** is generated (if enabled)
4. All files are added and committed
5. Branch is renamed to `main`
6. Remote origin is configured
7. Changes are pushed to GitHub

```bash
git init
git add .
git commit -m "Initial backup [timestamp]"
git branch -M main
git remote add origin https://github.com/username/repo-name.git
git push -u origin main
```

## API Endpoints

All endpoints are in the `actions/` folder and accept JSON requests:

### GET `actions/scan-projects.php`
Scans the configured folder and returns all projects.

**Response:**
```json
{
    "success": true,
    "data": {
        "projects": [...],
        "count": 5
    }
}
```

### POST `actions/backup-project.php`
Backs up a single project.

**Request:**
```json
{
    "path": "D:/wamp64/www/projects/my-project",
    "name": "my-project"
}
```

### POST `actions/backup-all.php`
Backs up all projects (this takes time!)

### POST `actions/save-settings.php`
Saves user settings.

### GET `actions/get-settings.php`
Loads saved settings (token not included).

### POST `actions/test-github.php`
Tests GitHub connection with a token.

### GET `actions/get-project-details.php?path=...`
Gets detailed information about a project.

### POST `actions/clear-data.php`
Clears all settings and logs (dangerous!).

## Security Considerations

✅ **Token Storage**: 
- Tokens are stored locally in `config/settings.json`
- Never sent to external servers
- Never displayed in frontend

✅ **Input Validation**:
- All inputs are sanitized and validated
- Path traversal attacks prevented
- GitHub usernames validated

✅ **Git Operations**:
- All git commands escape arguments properly
- Commands run locally, not on remote server
- Errors are caught and logged

✅ **Repository Privacy**:
- All created repositories are PRIVATE by default
- Only you have access

## Logging

All operations are logged to `logs/app_YYYY-MM-DD.log`:

```
[2024-01-15 14:23:45] [info] Scanned 5 projects
[2024-01-15 14:24:10] [info] Git initialized in: D:/wamp64/www/projects/my-project
[2024-01-15 14:24:15] [info] Pushed to origin/main
[2024-01-15 14:24:20] [info] Backup completed successfully for: my-project
```

View logs in the dashboard (feature can be added).

## Troubleshooting

### "Git not found"
- Ensure Git is installed: `git --version`
- On Windows, Git should be in PATH
- Restart WAMP after installing Git

### "Invalid GitHub token"
- Token must start with `ghp_`, `ghu_`, `ghs_`, or `gho_`
- Create a new token at https://github.com/settings/tokens
- Ensure token has `repo` and `public_repo` scopes

### "Repository already exists"
- The repo name already exists on your GitHub account
- Either:
  - Delete the repo from GitHub and try again
  - Rename the local folder and try again
  - Use "Backup" to update existing repo

### "Permission denied" on git push
- Verify GitHub token has correct permissions
- Test connection in Settings page
- Token should have `repo` scope

### "Invalid projects path"
- Ensure path exists and is accessible
- Use absolute paths, not relative paths
- Windows example: `D:/wamp64/www/projects/`
- Linux example: `/var/www/html/projects/`

## Advanced Usage

### Re-sync Existing Repos

To update an existing GitHub repo with new changes:

1. The app detects existing remote URLs
2. Click "Backup" again on the project
3. It will update the remote and push new changes

### Custom .gitignore

Edit generated `.gitignore` files after first backup. The app won't override them.

### Manual Git Operations

You can also manage repos manually in each project folder:

```bash
cd D:/wamp64/www/projects/my-project
git status
git log
git pull origin main
```

## Performance

- **Scanning 100 projects**: ~2-3 seconds
- **Single backup**: ~5-10 seconds (depends on folder size)
- **Bulk backup (10 projects)**: ~1-2 minutes

## Browser Support

- Chrome/Edge 88+
- Firefox 87+
- Safari 14+
- Mobile browsers (iOS Safari, Chrome Android)

## Dark Mode

- Toggle in Settings page
- Preference is saved in browser localStorage
- Perfect for late-night coding sessions!

## Tips & Tricks

1. **Organize your projects** in a single folder for easier management
2. **Use consistent naming** (lowercase, hyphens) for better compatibility
3. **Test connection** after updating credentials
4. **Check logs** if something goes wrong
5. **Keep tokens secret** - don't share `settings.json`
6. **Regularly backup** - use bulk backup weekly

## Examples

### Backing up a Laravel project:
```
Dashboard → Scan Projects → Locate "my-laravel-app" → Click Backup
GitHub repo created at: github.com/username/my-laravel-app
```

### Backing up multiple Node.js projects:
```
Dashboard → Click "Backup All"
- my-api-server ✓ Backed up
- my-web-app ✓ Backed up
- my-dashboard ✓ Backed up
```

## Common Settings

### GitHub Username
Your GitHub account username (e.g., `octocat`)

### Personal Access Token
Create at https://github.com/settings/tokens with `repo` scope

### Projects Folder
Where your projects are stored (e.g., `D:/wamp64/www/projects/`)

### Auto .gitignore
Automatically generates `.gitignore` with common patterns

### Dark Mode
Toggle light/dark theme

## API Response Format

All API responses follow this format:

```json
{
    "success": true/false,
    "message": "Human-readable message",
    "data": {...},
    "timestamp": "2024-01-15T14:23:45+00:00"
}
```

## Support & Issues

- Check the logs in `logs/` folder
- Test GitHub connection in Settings
- Verify git installation: `git --version`
- Ensure CURL is enabled: `php -m | grep curl`

## License

Free to use for personal and commercial projects.

## Changelog

### v1.0 (Initial Release)
- Dashboard with project list
- Single and bulk backup
- GitHub API integration
- Git automation
- Settings management
- Dark mode
- Full logging
- Mobile responsive

## Future Enhancements

- [ ] Scheduled backups (cron integration)
- [ ] GitHub webhook integration
- [ ] Repository statistics
- [ ] Commit history viewer
- [ ] SSH key support
- [ ] Multiple GitHub accounts
- [ ] Backup recovery/restore
- [ ] Database backup support
- [ ] FTP backup support
- [ ] Email notifications

---

**Made with ❤️ for developers who backup their code**
