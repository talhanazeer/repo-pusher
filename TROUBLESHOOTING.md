# GitHub Backup Manager - Troubleshooting Guide

## Common Problems & Solutions

### 1. "GitHub Backup Manager" Page Not Loading

**Symptoms**: Blank page or 404 error

**Solutions**:
```bash
# Check if folder exists
dir d:\wamp64\www\projects\repo-pusher

# Check Apache is running
# Restart Apache from WAMP control panel

# Check if PHP is working
# Create test.php with: <?php phpinfo(); ?>
# Visit http://localhost/projects/repo-pusher/test.php

# Clear browser cache (Ctrl+Shift+Delete)
```

---

### 2. "Cannot access settings"

**Symptoms**: Settings page is blank or loading indefinitely

**Solutions**:
```bash
# Check config folder permissions
# Windows: Right-click config → Properties → Security → Edit → Your User → Full Control

# Check logs for errors
type logs\app_*.log

# Verify JSON config file is readable
# Try manually creating config/settings.json with:
{
    "github_username": "",
    "github_token": "",
    "projects_path": "D:/wamp64/www/projects/",
    "auto_gitignore": true,
    "dark_mode": false
}
```

---

### 3. "Git not found" or "Git is not installed"

**Symptoms**: Backup fails with git error

**Windows Solutions**:
```bash
# Check git is installed
git --version

# If not found, install from: https://git-scm.com/download/win

# Ensure git is in PATH
# After installing, restart WAMP

# Or add manually to PATH:
# Control Panel → System → Environment Variables
# Add: C:\Program Files\Git\cmd

# Verify with:
echo %PATH%
```

**Linux/Mac Solutions**:
```bash
# Install Git
sudo apt-get install git  # Ubuntu/Debian
brew install git         # macOS

# Verify
which git
git --version
```

---

### 4. "Invalid GitHub Token"

**Symptoms**: Test Connection fails, backup shows token error

**Causes & Solutions**:
```bash
# Token expired - create a new one at:
# https://github.com/settings/tokens

# Token invalid format - must start with:
# ghp_  (classic PAT)
# ghu_  (user-to-server)
# ghs_  (server-to-server)
# gho_  (OAuth)

# Insufficient permissions - token must have:
# - repo (full control of private repos)
# - public_repo (access to public repos)

# Copy token immediately after creating it
# GitHub won't show it again!
```

**How to create correct token**:
1. Go to https://github.com/settings/tokens
2. Click "Generate new token" → "Generate new token (classic)"
3. Name: "GitHub Backup Manager"
4. Expiration: 90 days (or 365 days)
5. Scopes: Check `repo` and `public_repo`
6. Click "Generate token"
7. **COPY immediately** (only shown once!)
8. Paste into Settings → GitHub Token
9. Click "Test Connection"

---

### 5. "Repository already exists"

**Symptoms**: Backup fails saying repo already exists

**Solutions**:
```bash
# Option 1: Delete repo from GitHub and try again
# GitHub.com → Your Repo → Settings → Danger Zone → Delete

# Option 2: Rename local folder
# Folder: D:/wamp64/www/projects/my-project-1
# Scan again and backup

# Option 3: Verify repo on GitHub
# github.com/username/my-project
# If it exists but code is old, manually push new code:
cd D:\path\to\project
git remote set-url origin https://github.com/username/repo.git
git branch -M main
git push -u origin main
```

---

### 6. "Projects not appearing after scan"

**Symptoms**: Click Scan Projects but no folders appear

**Debug Steps**:
```bash
# Check configured path in Settings
# Should be: D:/wamp64/www/projects/ or similar

# Verify folder exists and is accessible
dir D:\wamp64\www\projects\

# Check folder permissions
# Right-click → Properties → Security → Your User has Read permission

# Check logs for errors
type logs\app_*.log | findstr error

# Make sure projects path ends with / or \

# Make sure folder actually contains project folders
# Not just files at root level
```

**Common Issues**:
- Path has wrong case sensitivity
- Path uses backslashes instead of forward slashes
- Folder doesn't exist
- User lacks read permission
- Folder is mapped network drive (may be slow)

---

### 7. "Permission denied" errors

**Windows**:
```bash
# Run WAMP as Administrator
# Right-click WAMP icon → Run as Administrator

# Or fix folder permissions:
# Right-click folder → Properties → Security → Edit
# Select your user → Check "Full Control"

# Or use command line:
icacls "D:\wamp64\www\projects" /grant Users:(OI)(CI)F /T
```

**Linux**:
```bash
# Check folder ownership
ls -la /var/www/html/projects/repo-pusher/

# Fix ownership
sudo chown -R www-data:www-data /var/www/html/projects/repo-pusher/

# Fix permissions
chmod -R 755 /var/www/html/projects/repo-pusher/
chmod -R 777 /var/www/html/projects/repo-pusher/config/
chmod -R 777 /var/www/html/projects/repo-pusher/logs/
```

---

### 8. "Backup fails silently"

**Symptoms**: Click backup, nothing happens, no error message

**Debugging**:
```bash
# 1. Check browser console for errors
# Press F12 → Console tab → Look for red errors

# 2. Check server logs
type logs\app_*.log

# 3. Check PHP error log
# Find in WAMP php.ini directory

# 4. Test git manually
cd D:\wamp64\www\projects\my-project
git status

# 5. Check if git is initialized
dir D:\wamp64\www\projects\my-project\.git

# 6. Try smaller project first
# Sometimes large projects timeout
```

---

### 9. "GitHub API Error (403)"

**Symptoms**: "Access forbidden" or "API rate limit exceeded"

**Solutions**:
```bash
# Check token permissions
# Must have 'repo' and 'public_repo' scopes
# Go to: https://github.com/settings/tokens

# Check rate limiting (5000/hour)
# Wait an hour or create new token

# Check if organization has restrictions
# Only personal repos work by default

# Verify token is not expired
# Create new token if > 90 days old
```

---

### 10. "CURL Error" or "Connection failed"

**Symptoms**: Can't connect to GitHub, network error

**Solutions**:
```bash
# Check CURL is enabled in PHP
php -r "echo curl_version()['version'];"

# If not found, enable in php.ini:
# Find php.ini in WAMP installation
# Uncomment: extension=curl
# Restart Apache

# Check internet connection
ping github.com

# Check firewall
# Allow outbound HTTPS (port 443)

# Check behind proxy?
# Configure in git:
git config --global http.proxy [proxy-address]:[port]
git config --global https.proxy [proxy-address]:[port]

# Check GitHub status
# https://www.githubstatus.com/
```

---

### 11. "Max execution time exceeded"

**Symptoms**: Bulk backup times out, backup fails midway

**Solutions**:
```bash
# Increase PHP timeout in php.ini (usually in WAMP folder)
max_execution_time = 600    # 10 minutes (was 300)

# Or increase in .htaccess (might not work)
php_value max_execution_time 600

# Restart Apache after changing php.ini

# Or reduce what you're backing up
# Break into smaller batches
# Don't backup 1000 projects at once!
```

---

### 12. "Dark Mode not working"

**Symptoms**: Dark mode toggle doesn't work

**Solutions**:
```bash
# Clear browser cache (Ctrl+Shift+Delete)
# Then refresh page (F5)

# Check browser console for errors (F12)

# Check localStorage is enabled
# Incognito/Private mode disables localStorage
# Use normal browsing mode

# Check browser compatibility
# Chrome 4+, Firefox 3.5+, Safari 4+, Edge 12+
```

---

### 13. "Search/Filter not working"

**Symptoms**: Search box doesn't filter projects

**Solutions**:
```bash
# Make sure projects are loaded first
# Click "Scan Projects" if none appear

# Check browser console (F12) for errors

# Try clearing browser cache

# Refresh page and try again
```

---

### 14. "Settings won't save"

**Symptoms**: Save Settings button does nothing or fails

**Solutions**:
```bash
# Check folder permissions
# config/ folder must be writable
# Windows: Right-click → Properties → Security → Full Control

# Check if config/settings.json exists
# Create it manually if missing (see solution #2)

# Check browser console for errors (F12)

# Check logs for save errors
type logs\app_*.log | findstr "Error saving"

# Check disk space
# Might be full!

# Try clearing old logs
del logs\app_*.log
```

---

### 15. "Can't see logs"

**Symptoms**: Logs folder empty or old logs missing

**Solutions**:
```bash
# Logs are created daily with timestamps
# Check: logs/app_2024-01-15.log

# Check today's log
type logs\app_*.log

# Logs are only created when app runs
# Click buttons to generate activity

# Check log retention
# Default is 30 days, old logs auto-delete

# Check folder permissions
# logs/ folder needs write access

# Check disk space
# Logs can't be written if disk is full
```

---

## Diagnostic Commands

Run these to help debug:

```bash
# Check PHP version
php --version

# Check Git
git --version

# Check CURL
php -r "echo 'CURL: ' . (extension_loaded('curl') ? 'enabled' : 'disabled') . PHP_EOL;"

# Check file permissions
icacls D:\wamp64\www\projects\repo-pusher

# Check if ports are open
netstat -an | findstr :80
netstat -an | findstr :443

# Test local connection
curl http://localhost/projects/repo-pusher/

# Check Apache status (Windows)
tasklist | findstr apache

# Check logs
dir /s logs\
```

---

## Still Not Working?

1. **Collect information**:
   - Copy error message exactly
   - Screenshot the problem
   - Save logs from logs/ folder
   - Note your system (Windows/Mac/Linux)
   - Note PHP and Git versions

2. **Check documentation**:
   - README.md - General usage
   - INSTALL.md - Installation
   - API.md - API reference

3. **Review logs**:
   - Check `logs/app_*.log` for details
   - Search for "error" or "failed"
   - Note timestamps of failures

4. **Test manually**:
   - Test Git directly: `git --version`
   - Test PHP: Create test.php with `<?php echo "Hello"; ?>`
   - Test GitHub: Try creating repo manually on GitHub.com

5. **Try minimal reproduction**:
   - Create one small test folder
   - Try backing up just that folder
   - See if error is consistent

6. **Clear cache**:
   - Clear browser cache (Ctrl+Shift+Delete)
   - Clear PHP opcache if using
   - Restart Apache
   - Restart browser

---

## Performance Issues

### Slow Scanning
```bash
# Normal: 50 projects in 2-3 seconds
# If slower:
# - Check disk speed (slow HDD vs SSD)
# - Check for large files (scans all)
# - Close other programs using disk
# - Check antivirus is not scanning
```

### Slow Backup
```bash
# Normal: 100MB in 10-15 seconds
# If slower:
# - Check internet speed
# - Check GitHub status
# - Reduce project size
# - Try one project at a time
```

### High Memory Usage
```bash
# Increase PHP memory limit in php.ini:
memory_limit = 512M
```

---

## Getting Help

When reporting issues, include:
- [ ] Error message (exact text)
- [ ] Steps to reproduce
- [ ] Logs from `logs/` folder
- [ ] PHP version (`php --version`)
- [ ] Git version (`git --version`)
- [ ] Operating system
- [ ] Browser (Chrome, Firefox, Safari, Edge)
- [ ] Screenshots if visual issue

---

Good luck backing up your projects! 🚀
