@echo off
REM GitHub Backup Manager - Quick Reference for Windows
REM This batch file provides quick reference information

echo.
echo ╔════════════════════════════════════════════════════════════════════╗
echo ║          GitHub Backup Manager - Quick Reference (Windows)        ║
echo ╚════════════════════════════════════════════════════════════════════╝
echo.

echo 📁 PROJECT LOCATION:
echo    d:\wamp64\www\projects\repo-pusher\
echo.

echo 🌐 ACCESS URL:
echo    http://localhost/projects/repo-pusher/
echo.

echo ⚙️  SYSTEM REQUIREMENTS:
echo    • PHP 7.2+
echo    • Git 2.0+ (https://git-scm.com/download/win)
echo    • WAMP running (Apache + PHP)
echo    • GitHub account with PAT
echo.

echo 🔧 FIRST-TIME SETUP:
echo    1. Create GitHub PAT: https://github.com/settings/tokens
echo    2. Go to Settings: http://localhost/projects/repo-pusher/settings.php
echo    3. Enter GitHub username
echo    4. Paste Personal Access Token
echo    5. Click "Test Connection"
echo    6. Click "Save Settings"
echo.

echo 🚀 QUICK START:
echo    1. Start WAMP services (Apache green)
echo    2. Open http://localhost/projects/repo-pusher/
echo    3. Configure Settings with GitHub credentials
echo    4. Click "Scan Projects"
echo    5. Click "Backup" on any project
echo.

echo 🔑 CREATING GITHUB PAT:
echo    1. Go to https://github.com/settings/tokens
echo    2. Click "Generate new token" (classic)
echo    3. Name: GitHub Backup Manager
echo    4. Scopes: repo, public_repo
echo    5. Click "Generate token"
echo    6. COPY IMMEDIATELY (only shown once!)
echo.

echo 📚 DOCUMENTATION:
echo    • README.md            - Main guide and features
echo    • INSTALL.md           - Installation steps
echo    • API.md               - API reference
echo    • TROUBLESHOOTING.md   - Problem solving
echo    • PROJECT_SUMMARY.md   - Project overview
echo.

echo 🆘 COMMON ISSUES & SOLUTIONS:
echo.
echo    Git not found:
echo       - Install from https://git-scm.com/download/win
echo       - Restart WAMP after installing
echo.
echo    Token invalid:
echo       - Ensure token starts with ghp_
echo       - Create new PAT if > 90 days old
echo.
echo    Projects not showing:
echo       - Check Settings: projects_path is correct
echo       - Ensure folder exists and is readable
echo.
echo    Backup fails:
echo       - Check logs\ folder for detailed errors
echo       - Verify GitHub token is valid
echo       - Try single project first
echo.
echo    Permission denied:
echo       - Run WAMP as Administrator
echo       - Right-click WAMP tray icon
echo.

echo 🔧 USEFUL COMMANDS (Windows CMD/PowerShell):
echo.
echo    Check Git installed:
echo       git --version
echo.
echo    Check PHP installed:
echo       php --version
echo.
echo    View today's logs:
echo       type d:\wamp64\www\projects\repo-pusher\logs\app_*.log
echo.
echo    Clear old logs:
echo       del d:\wamp64\www\projects\repo-pusher\logs\app_*.log
echo.

echo 📊 PERFORMANCE EXPECTATIONS:
echo    Scan 50 projects:     2-3 seconds
echo    Backup 100MB project: 8-15 seconds
echo    Bulk backup 10 repos: 1-2 minutes
echo.

echo ✅ WHAT'S INCLUDED:
echo    ✓ Modern responsive UI with dark mode
echo    ✓ Project scanning and detection
echo    ✓ GitHub API integration
echo    ✓ Automatic git operations
echo    ✓ Single and bulk backup
echo    ✓ Security and validation
echo    ✓ Comprehensive logging
echo    ✓ Full documentation
echo.

echo ⚠️  IMPORTANT SECURITY NOTES:
echo    • GitHub token stored locally only
echo    • Token never sent to external servers
echo    • All repos created are PRIVATE
echo    • Never share your token!
echo    • Rotate token every 90 days
echo.

echo 🎉 YOU'RE READY!
echo.
echo Next steps:
echo   1. Make sure WAMP is running
echo   2. Visit http://localhost/projects/repo-pusher/
echo   3. Create GitHub PAT: https://github.com/settings/tokens
echo   4. Go to Settings and enter your credentials
echo   5. Click "Test Connection" to verify
echo   6. Save Settings
echo   7. Go to Dashboard and click "Scan Projects"
echo   8. Click "Backup" on any project
echo.

echo 📞 NEED HELP?
echo    • See TROUBLESHOOTING.md for common problems
echo    • Check logs\ folder for detailed errors
echo    • Review API.md for endpoint documentation
echo.

pause
