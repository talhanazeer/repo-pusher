# GitHub Backup Manager - Setup & Installation Guide

## System Requirements

### Minimum
- PHP 7.2+
- 50 MB free disk space
- Modern web browser

### Recommended
- PHP 8.0+
- Git 2.25+
- 200 MB free disk space
- Chrome, Firefox, Safari, or Edge

## Pre-Installation Checklist

- [ ] PHP installed and working
- [ ] Git installed (`git --version`)
- [ ] WAMP/LAMP/XAMPP running
- [ ] GitHub account created
- [ ] GitHub Personal Access Token created

## Step-by-Step Installation

### 1. Create GitHub Personal Access Token

1. Go to https://github.com/settings/tokens
2. Click "Generate new token" → "Generate new token (classic)"
3. Set token name: `GitHub Backup Manager`
4. Expiration: 90 days (or as you prefer)
5. Select scopes:
   - ✓ `repo` (Full control of private repositories)
   - ✓ `public_repo` (Access to public repositories)
6. Scroll down, click "Generate token"
7. **COPY THE TOKEN** (you won't see it again!)
8. Save it safely

### 2. Download/Clone the Project

Option A: Direct copy
```bash
# Navigate to WAMP htdocs
cd d:\wamp64\www\projects

# Copy the repo-pusher folder here
```

Option B: Git clone
```bash
cd d:\wamp64\www\projects
git clone <repository-url> repo-pusher
```

### 3. Set Folder Permissions (Windows)

Right-click `repo-pusher` folder:
- Properties → Security → Edit
- Select your user → Full Control
- Click Apply → OK

### 4. Start WAMP

1. Start WAMP services (MySQL, Apache)
2. Ensure Apache is running (green icon)

### 5. Access the Application

1. Open browser: `http://localhost/projects/repo-pusher/`
2. You should see the GitHub Backup Manager dashboard

### 6. Configure Settings

1. Click **Settings** in sidebar
2. Enter:
   - **GitHub Username**: `your-github-username`
   - **Personal Access Token**: Paste the token from step 1
   - **Projects Folder**: `D:/wamp64/www/projects/`
3. Ensure **Auto-generate .gitignore** is checked
4. Click **Test Connection** - should show your GitHub user
5. Click **Save Settings**

### 7. First Backup

1. Go to **Dashboard**
2. Click **Scan Projects** - all folders will appear
3. Find a test project
4. Click **Backup** - select yes when asked
5. Wait for completion
6. Check GitHub account - new private repo should exist!

## Verification

After installation, verify everything works:

```bash
# Check PHP version
php --version
# Should be 7.2+

# Check Git
git --version
# Should be 2.0+

# Test CURL (in PHP)
php -r "echo curl_version()['version'];"
# Should output version number
```

## Troubleshooting Installation

### "Fatal error: Class not found"
- Check PHP version is 7.2+
- Verify file paths in includes/

### "Git not found"
Windows:
```bash
# Add Git to PATH manually
setx PATH "%PATH%;C:\Program Files\Git\cmd"
# Restart WAMP after this
```

Linux/Mac:
```bash
# Install Git
brew install git  # macOS
sudo apt-get install git  # Ubuntu/Debian
```

### "Connection refused" (localhost not working)
- Start WAMP/LAMP services
- Check Apache is running
- Try http://127.0.0.1 instead of localhost

### "Permission denied" when accessing folder
- Windows: Right-click folder → Properties → Security
- Linux: `chmod 755 repo-pusher`
- macOS: Get Info → Sharing & Permissions

### CURL not enabled
Edit `php.ini`:
```ini
extension=curl
; Uncomment the line above if it's commented
```
Restart Apache after editing.

## Configuration Details

### config/settings.json (Auto-created)

```json
{
    "github_username": "octocat",
    "github_token": "ghp_...",
    "projects_path": "D:/wamp64/www/projects/",
    "auto_gitignore": true,
    "dark_mode": false
}
```

### Log Files

Logs are created automatically in `logs/`:
- `app_2024-01-15.log`
- `app_2024-01-16.log`
- etc (one per day)

### First Backup Workflow

When you click "Backup" on a project:

1. **API Call**: JavaScript sends project info to backend
2. **Validation**: Backend verifies credentials and path
3. **GitHub Repo Creation**: API creates private repo
4. **Git Init**: Initializes git in project folder
5. **Git Add**: Stages all files
6. **Git Commit**: Creates initial commit
7. **Git Push**: Pushes to GitHub
8. **Complete**: Shows success message

This entire process happens automatically!

## Performance Optimization

### For Large Projects (>1GB)
- Close other applications to free RAM
- Increase PHP memory limit in php.ini:
  ```ini
  memory_limit = 512M
  ```
- Increase Git pack.window:
  ```bash
  git config --global pack.window 1
  ```

### For Many Projects (>100)
- Scan takes longer, this is normal
- Don't close the page during scan
- Use "Backup All" during off-hours

## Security Hardening

### 1. Protect settings.json
```bash
# Windows: Use NTFS permissions
# Linux: chmod 600 config/settings.json
# macOS: chmod 600 config/settings.json
```

### 2. Change token periodically
- Go to GitHub settings monthly
- Regenerate token
- Update in app Settings
- Delete old token

### 3. Keep app updated
- Check for updates regularly
- Update PHP and Git when available

### 4. Monitor logs
- Check logs for errors
- Review unusual activity

## Network Considerations

### Firewall
- Ensure outbound HTTPS (port 443) is allowed
- GitHub API uses HTTPS

### Proxy
If behind corporate proxy:
1. Configure Git:
   ```bash
   git config --global http.proxy [proxy-address]:[proxy-port]
   git config --global https.proxy [proxy-address]:[proxy-port]
   ```
2. Configure PHP/CURL in php.ini:
   ```ini
   curl.cainfo = path/to/cacert.pem
   ```

## Backup Strategy

After installation, implement this strategy:

1. **Weekly scans**: Keep project list updated
2. **Regular backups**: Use "Backup All" weekly
3. **Monitor logs**: Check for errors
4. **Update credentials**: Rotate GitHub token monthly
5. **Test recovery**: Occasionally clone from GitHub to verify

## Storage

Where backups are stored:
- **Local**: Git is initialized in each project folder
- **Remote**: All backups pushed to GitHub as private repos
- **Logs**: Application logs stored locally in `logs/`

## Next Steps After Installation

1. ✓ Configure GitHub credentials
2. ✓ Test connection in Settings
3. ✓ Backup one test project
4. ✓ Verify it appears on GitHub
5. ✓ Do a bulk backup of all projects
6. ✓ Check application logs
7. ✓ Set up a backup schedule

## Getting Help

If you encounter issues:

1. **Check Logs**: `logs/app_*.log`
2. **Test Connection**: Settings → Test Connection button
3. **Verify Git**: Run `git --version` in terminal
4. **Verify PHP**: Run `php --version` in terminal
5. **Check Permissions**: Ensure folder has read/write access
6. **Browser Console**: Press F12 and check for JavaScript errors

## Common Setup Issues & Solutions

| Issue | Solution |
|-------|----------|
| "Settings page blank" | Clear browser cache (Ctrl+Shift+Delete) |
| "Can't see projects" | Check projects_path in settings |
| "Backup fails silently" | Check logs for errors |
| "Token not saving" | Ensure config/ folder is writable |
| "Dark mode not working" | Check browser supports localStorage |

## Performance Benchmarks

Typical operation times on modern hardware:

- Dashboard load: <1 second
- Scan 50 projects: 2-3 seconds
- Single backup (100MB): 8-15 seconds
- Bulk backup (10 projects): 1-2 minutes

## Uninstall

To remove GitHub Backup Manager:

1. Delete the `repo-pusher` folder from htdocs
2. Or export repos from GitHub if needed
3. Settings are stored locally, no cleanup needed elsewhere

---

**Installation Complete!** 

Start backing up your projects to GitHub with one click. Check the main README.md for usage guide.
