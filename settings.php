<?php

/**
 * GitHub Backup Manager - Settings Page
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$settings = getSettings();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Backup Manager - Settings</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="layout">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="sidebar-logo">
                <a href="index.php">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.375 3.375 0 0 0-.975-2.438 3.75 3.75 0 0 1 .75-5.062 3.545 3.545 0 0 1 1.225-.182c1.082.256 2.04.974 2.748 2.166.71-1.192 1.666-1.91 2.748-2.166 1.062-.207 2.312.075 3.225.975.438.488.6 1.25.69 2c.08.887.06 2.05.01 3.28-.06 1.5.3 2.25-.56 3.25-1.125 1.5-3.225 1.5-5 1.5-1.5 0-3.125 0-5 1.5"></path>
                    </svg>
                    <span>GitHub Backup</span>
                </a>
            </div>

            <div class="sidebar-nav">
                <a href="index.php" class="nav-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12a9 9 0 1 0 18 0A9 9 0 0 0 3 12z"></path>
                        <polyline points="12 7 12 12 16 14"></polyline>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="settings.php" class="nav-item active">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="1"></circle>
                        <path d="M12 1v6m0 6v4"></path>
                        <path d="M4.22 4.22l4.24 4.24m3.08 3.08l4.24 4.24"></path>
                        <path d="M1 12h6m6 0h4"></path>
                        <path d="M4.22 19.78l4.24-4.24m3.08-3.08l4.24-4.24"></path>
                        <path d="M12 17v6"></path>
                        <path d="M19.78 19.78l-4.24-4.24"></path>
                        <path d="M23 12h-6"></path>
                        <path d="M19.78 4.22l-4.24 4.24"></path>
                    </svg>
                    <span>Settings</span>
                </a>
            </div>

            <div class="sidebar-footer">
                <button id="darkModeToggle" class="nav-item" style="width: calc(100% + 32px); margin: 0 -16px; border-radius: 0;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                    <span>Dark Mode</span>
                </button>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <div class="topbar-left">
                    <h2>Settings</h2>
                </div>
            </div>

            <!-- Page Content -->
            <div class="page-content">
                <!-- GitHub Credentials Section -->
                <div class="card mb-2">
                    <div class="card-header">
                        <h3 class="card-title">GitHub Configuration</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="github_username">GitHub Username</label>
                            <input
                                type="text"
                                id="github_username"
                                placeholder="e.g., your-github-username"
                                autocomplete="off"
                                name="github_username">
                            <small style="color: var(--color-text-light);">Your GitHub account username</small>
                        </div>

                        <div class="form-group">
                            <label for="github_token">Personal Access Token (PAT)</label>
                            <input
                                type="password"
                                id="github_token"
                                placeholder="ghp_... or github_pat_..."
                                autocomplete="off"
                                name="github_token">
                            <small id="github_token_hint" style="color: var(--color-text-light);">
                                Classic PAT (recommended):
                                <a href="https://github.com/settings/tokens/new" target="_blank">
                                    github.com/settings/tokens/new
                                </a>
                                with the <strong>repo</strong> scope.
                                Click <strong>Test Connection</strong> after pasting — that now saves the token.
                                Never share this token.
                            </small>
                        </div>

                        <div style="display: flex; gap: 12px; margin-top: 20px;">
                            <button id="testConnBtn" class="btn btn-secondary" onclick="Settings.testConnection()">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                Test Connection
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Backup Configuration Section -->
                <div class="card mb-2">
                    <div class="card-header">
                        <h3 class="card-title">Backup Configuration</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="projects_path">Projects Folder Path</label>
                            <input
                                type="text"
                                id="projects_path"
                                placeholder="D:/wamp64/www/projects/"
                                value="<?php echo htmlspecialchars(DEFAULT_PROJECTS_PATH); ?>">
                            <small style="color: var(--color-text-light);">Path to scan for projects. Must be an absolute path.</small>
                        </div>

                        <div class="checkbox-group mt-2">
                            <label class="checkbox-item">
                                <input type="checkbox" id="auto_gitignore">
                                <span>Auto-generate .gitignore if missing</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Application Settings Section -->
                <div class="card mb-2">
                    <div class="card-header">
                        <h3 class="card-title">Application Settings</h3>
                    </div>
                    <div class="card-body">
                        <div class="checkbox-group">
                            <label class="checkbox-item">
                                <input type="checkbox" id="dark_mode" onchange="Settings.toggleDarkModePreference()">
                                <span>Dark Mode</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div style="display: flex; gap: 12px; margin-bottom: 20px;">
                    <button id="saveSettingsBtn" class="btn btn-primary" onclick="Settings.saveSettings()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Save Settings
                    </button>
                    <button class="btn btn-secondary" onclick="Settings.loadSettings()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 4v6h6"></path>
                            <path d="M23 20v-6h-6"></path>
                            <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path>
                        </svg>
                        Reset
                    </button>
                </div>

                <!-- About Section -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">About</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>GitHub Backup Manager</strong> v1.0</p>
                        <p>A local web-based tool to automatically create private GitHub repositories and backup your projects.</p>

                        <h4 style="margin-top: 16px; margin-bottom: 8px;">Quick Guide:</h4>
                        <ol style="color: var(--color-text-secondary); line-height: 1.8;">
                            <li>Configure your GitHub credentials above</li>
                            <li>Go to Dashboard and click "Scan Projects"</li>
                            <li>Click "Backup" on any project to create a private repo</li>
                            <li>Or use "Backup All" for bulk backup</li>
                        </ol>

                        <h4 style="margin-top: 16px; margin-bottom: 8px;">Security:</h4>
                        <ul style="color: var(--color-text-secondary); line-height: 1.8;">
                            <li>Your token is stored locally and never sent to external servers</li>
                            <li>All backups are PRIVATE repositories</li>
                            <li>Git operations happen locally before pushing to GitHub</li>
                        </ul>

                        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--color-border);">
                            <p style="font-size: var(--font-size-xs); color: var(--color-text-light);">
                                Built with PHP, JavaScript, and the GitHub API
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="card" style="border-color: var(--color-danger); margin-top: 24px;">
                    <div class="card-header" style="border-bottom-color: var(--color-danger);">
                        <h3 class="card-title" style="color: var(--color-danger);">Danger Zone</h3>
                    </div>
                    <div class="card-body">
                        <p style="color: var(--color-text-secondary); margin-bottom: 16px;">
                            This action cannot be undone. It will permanently delete all stored settings and logs.
                        </p>
                        <button class="btn btn-danger" onclick="Settings.clearAllData()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 4 21 4 23 6 20 20a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2L3 6"></polyline>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                <line x1="19" y1="4" x2="19" y2="6"></line>
                                <line x1="5" y1="4" x2="5" y2="6"></line>
                            </svg>
                            Clear All Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            Settings.loadSettings();
        });

        Settings.toggleDarkModePreference = function() {
            const isDarkMode = document.getElementById('dark_mode').checked;
            if (isDarkMode) {
                document.documentElement.classList.add('dark-mode');
            } else {
                document.documentElement.classList.remove('dark-mode');
            }
        };
    </script>
</body>

</html>