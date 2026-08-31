<?php
/**
 * GitHub Backup Manager - Dashboard
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$settings = getSettings();
$isConfigured = !empty($settings['github_username']) && !empty($settings['github_token']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Backup Manager - Dashboard</title>
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
                <a href="index.php" class="nav-item active">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12a9 9 0 1 0 18 0A9 9 0 0 0 3 12z"></path>
                        <polyline points="12 7 12 12 16 14"></polyline>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="settings.php" class="nav-item">
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
                    <h2>Dashboard</h2>
                </div>
                <div class="topbar-right">
                    <?php if ($isConfigured): ?>
                        <span class="status-indicator">
                            <span class="indicator-dot active"></span>
                            <span>Connected</span>
                        </span>
                    <?php else: ?>
                        <span class="status-indicator">
                            <span class="indicator-dot inactive"></span>
                            <span>Not Configured</span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Page Content -->
            <div class="page-content">
                <?php if (!$isConfigured): ?>
                    <div class="alert alert-warning">
                        <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3.05h16.94a2 2 0 0 0 1.71-3.05L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <div>
                            <strong>Not Configured!</strong> Please <a href="settings.php">configure your GitHub credentials</a> to get started.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Header with Actions -->
                <div class="flex justify-between items-center mb-3">
                    <div>
                        <h1>Your Projects</h1>
                        <p style="color: var(--color-text-secondary);">Scan and backup your local projects to GitHub</p>
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <input 
                            type="text" 
                            id="projectSearch" 
                            placeholder="Search projects..." 
                            style="width: 250px; max-width: 100%;"
                            onkeyup="Dashboard.filterProjects(this.value)"
                        >
                        <button id="scanBtn" class="btn btn-primary" onclick="Dashboard.loadProjects()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                            </svg>
                            Scan Projects
                        </button>
                        <button id="backupAllBtn" class="btn btn-success" onclick="Dashboard.backupAll()" <?php echo !$isConfigured ? 'disabled' : ''; ?>>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Backup All
                        </button>
                    </div>
                </div>

                <div id="bulkBackupProgress" class="card mb-3" style="display: none;">
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <strong id="bulkBackupStatus">Preparing bulk backup…</strong>
                            <button type="button" id="bulkBackupCancelBtn" class="btn btn-secondary btn-small" onclick="Dashboard.cancelBackupAll()">
                                Stop
                            </button>
                        </div>
                        <div class="progress-container" style="margin: 12px 0 8px;">
                            <div class="progress-bar">
                                <div id="bulkBackupFill" class="progress-fill">0%</div>
                            </div>
                        </div>
                        <p id="bulkBackupCounts" style="color: var(--color-text-secondary); margin: 0;"></p>
                    </div>
                </div>

                <!-- Projects Grid -->
                <div id="projectsContainer" class="projects-grid">
                    <div class="card" style="grid-column: 1/-1;">
                        <p class="text-center text-muted">
                            Click "Scan Projects" to load your projects...
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Repo visibility choice -->
    <div id="visibilityModal" class="modal">
        <div class="modal-content" style="max-width: 480px;">
            <div class="modal-header">
                <h3 class="modal-title">Repository visibility</h3>
                <button type="button" class="modal-close" onclick="Dashboard.finishVisibilityChoice(null)">&times;</button>
            </div>
            <div class="modal-body">
                <p id="visibilityModalText" style="margin: 0 0 12px; color: var(--color-text-secondary); line-height: 1.5;">
                    This repository does not exist on GitHub yet. Create it as private or public?
                </p>
                <p style="margin: 0; font-size: var(--font-size-sm); color: var(--color-text-light);">
                    Private is the default. Public repos can be seen by anyone on GitHub.
                </p>
            </div>
            <div class="modal-footer" style="flex-wrap: wrap;">
                <button type="button" class="btn btn-secondary" onclick="Dashboard.finishVisibilityChoice(null)">Cancel</button>
                <button type="button" class="btn btn-secondary" onclick="Dashboard.finishVisibilityChoice('public')">Public</button>
                <button type="button" class="btn btn-primary" onclick="Dashboard.finishVisibilityChoice('private')">Private (default)</button>
            </div>
        </div>
    </div>

    <!-- Project Details Modal -->
    <div id="projectDetailsModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title">Project Details</h3>
                <button class="modal-close" data-close-modal>&times;</button>
            </div>
            <div class="modal-body">
                <div id="projectDetailsContent">
                    <div class="spinner"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js?v=<?php echo time(); ?>"></script>
    <script>
        // Auto-load projects on page load if configured
        document.addEventListener('DOMContentLoaded', () => {
            <?php if ($isConfigured): ?>
                Dashboard.loadProjects();
            <?php endif; ?>
        });
    </script>
</body>
</html>
