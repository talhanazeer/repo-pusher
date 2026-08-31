/**
 * GitHub Backup Manager - Main Application JavaScript
 */

const App = {
    apiBaseUrl: '/',
    
    /**
     * Initialize the application
     */
    init() {
        console.log('Initializing GitHub Backup Manager...');
        this.setupEventListeners();
        this.loadDarkModePreference();
        this.initToasts();
    },
    
    /**
     * Setup global event listeners
     */
    setupEventListeners() {
        // Dark mode toggle
        const darkModeBtn = document.getElementById('darkModeToggle');
        if (darkModeBtn) {
            darkModeBtn.addEventListener('click', () => this.toggleDarkMode());
        }
        
        // Modal close buttons
        document.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const modal = e.target.closest('.modal');
                if (modal) {
                    this.closeModal(modal);
                }
            });
        });
        
        // Close modal when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                if (e.target.id === 'visibilityModal') {
                    Dashboard.finishVisibilityChoice(null);
                } else {
                    this.closeModal(e.target);
                }
            }
        });
    },
    
    /**
     * Toggle dark mode
     */
    toggleDarkMode() {
        const isDark = document.documentElement.classList.toggle('dark-mode');
        localStorage.setItem('darkMode', isDark);
        this.showToast('Dark mode ' + (isDark ? 'enabled' : 'disabled'));
    },
    
    /**
     * Load dark mode preference
     */
    loadDarkModePreference() {
        const isDark = localStorage.getItem('darkMode') === 'true';
        if (isDark) {
            document.documentElement.classList.add('dark-mode');
        }
    },
    
    /**
     * Make API request
     */
    async api(endpoint, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        };
        
        const finalOptions = { ...defaultOptions, ...options };
        
        if (options.body && typeof options.body === 'object') {
            finalOptions.body = JSON.stringify(options.body);
        }
        
        try {
            const response = await fetch(endpoint, finalOptions);
            
            if (!response.ok) {
                throw new Error(`API Error: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            }
            
            return await response.text();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    /**
     * Show toast notification
     */
    showToast(message, type = 'info', duration = 3000) {
        const toastContainer = document.getElementById('toastContainer') || this.createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `alert alert-${type}`;
        toast.innerHTML = `
            <span>${this.escapeHtml(message)}</span>
            <button class="alert-close" onclick="this.parentElement.remove();">&times;</button>
        `;
        
        toastContainer.appendChild(toast);
        
        if (duration > 0) {
            setTimeout(() => toast.remove(), duration);
        }
        
        return toast;
    },
    
    /**
     * Create toast container
     */
    createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(container);
        return container;
    },
    
    /**
     * Initialize toasts container
     */
    initToasts() {
        this.createToastContainer();
    },
    
    /**
     * Open modal dialog
     */
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
        }
    },
    
    /**
     * Close modal dialog
     */
    closeModal(modal) {
        if (typeof modal === 'string') {
            modal = document.getElementById(modal);
        }
        if (modal) {
            modal.classList.remove('active');
        }
    },
    
    /**
     * Show loading spinner on button
     */
    setButtonLoading(buttonId, isLoading = true) {
        const btn = document.getElementById(buttonId);
        if (!btn) return;
        
        if (isLoading) {
            btn.classList.add('btn-loading');
            btn.disabled = true;
        } else {
            btn.classList.remove('btn-loading');
            btn.disabled = false;
        }
    },
    
    /**
     * Escape HTML special characters
     */
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    },
    
    /**
     * Format date
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    },
    
    /**
     * Format file size
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },
    
    /**
     * Clear all alerts
     */
    clearAlerts() {
        document.querySelectorAll('.alert').forEach(alert => alert.remove());
    },
    
    /**
     * Show error message
     */
    showError(message) {
        this.showToast(message, 'danger', 5000);
    },
    
    /**
     * Show success message
     */
    showSuccess(message) {
        this.showToast(message, 'success', 3000);
    },
    
    /**
     * Show warning message
     */
    showWarning(message) {
        this.showToast(message, 'warning', 4000);
    }
};

/**
 * Dashboard specific functions
 */
const Dashboard = {
    projects: [],
    actionsBound: false,
    bulkCancelled: false,
    bulkAbortController: null,
    skipFolders: ['node_modules', 'vendor'],
    visibilityChoiceResolver: null,
    
    /**
     * Load projects list
     */
    async loadProjects() {
        try {
            App.setButtonLoading('scanBtn', true);
            const response = await App.api('actions/scan-projects.php');
            
            if (response.success) {
                this.renderProjects(response.data.projects || []);
                const cacheInfo = response.data.cached ? ' (cached)' : '';
                App.showSuccess('Projects loaded successfully' + cacheInfo);
            } else {
                App.showError(response.message || 'Failed to load projects');
            }
        } catch (error) {
            App.showError('Error loading projects: ' + error.message);
        } finally {
            App.setButtonLoading('scanBtn', false);
        }
    },
    
    /**
     * Refresh projects (force refresh cache)
     */
    async refreshProjects() {
        try {
            App.setButtonLoading('scanBtn', true);
            // Clear cache first
            await App.api('actions/clear-cache.php');
            // Then load fresh
            const response = await App.api('actions/scan-projects.php?nocache=1');
            
            if (response.success) {
                this.renderProjects(response.data.projects || []);
                App.showSuccess('Projects refreshed (cache cleared)');
            } else {
                App.showError(response.message || 'Failed to refresh projects');
            }
        } catch (error) {
            App.showError('Error refreshing projects: ' + error.message);
        } finally {
            App.setButtonLoading('scanBtn', false);
        }
    },
    
    /**
     * Render projects grid
     */
    renderProjects(projects) {
        const container = document.getElementById('projectsContainer');
        if (!container) return;
        
        this.projects = Array.isArray(projects) ? projects : [];
        
        if (this.projects.length === 0) {
            container.innerHTML = `
                <div class="card" style="grid-column: 1/-1;">
                    <p class="text-center text-muted">No projects found in the selected folder.</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = this.projects.map(project => this.renderProjectCard(project)).join('');
        
        this.setupProjectActions();
    },
    
    /**
     * Setup event delegation for project actions
     */
    setupProjectActions() {
        const container = document.getElementById('projectsContainer');
        if (!container || this.actionsBound) return;
        
        this.actionsBound = true;
        container.addEventListener('click', (e) => {
            const button = e.target.closest('button[data-action]');
            if (!button) return;
            
            const action = button.getAttribute('data-action');
            const path = decodeURIComponent(atob(button.getAttribute('data-path')));
            const name = decodeURIComponent(atob(button.getAttribute('data-name') || ''));
            
            if (action === 'backup') {
                this.backupProject(path, name);
            } else if (action === 'details') {
                this.viewProjectDetails(path);
            }
        });
    },
    
    /**
     * Render single project card
     */
    renderProjectCard(project) {
        const gitStatus = project.is_git ? 'Git Initialized' : 'Not a Git Repo';
        const gitClass = project.is_git ? 'status-git' : 'status-pending';
        
        const githubStatus = project.has_remote ? 'Linked to GitHub' : 'Not Linked';
        const githubClass = project.has_remote ? 'status-github' : 'status-pending';
        
        // Calculate modified files count
        const modifiedFiles = project.modified_files || {};
        const totalChanges = (modifiedFiles.modified || 0) + (modifiedFiles.untracked || 0) + (modifiedFiles.deleted || 0);
        const changesHtml = totalChanges > 0 ? `<span class="project-status status-warning" title="${modifiedFiles.modified || 0} modified, ${modifiedFiles.untracked || 0} untracked, ${modifiedFiles.deleted || 0} deleted">Changes: ${totalChanges}</span>` : '';
        
        return `
            <div class="project-card">
                <div class="project-card-header">
                    <div class="project-name">${App.escapeHtml(project.name)}</div>
                </div>
                
                <div class="project-path" title="${App.escapeHtml(project.path.replace(/\\/g, '/'))}">
                    ${App.escapeHtml(project.path.replace(/\\/g, '/'))}
                </div>
                
                <div class="project-meta">
                    <div class="project-meta-item">
                        <span>Size:</span>
                        <strong class="folder-size" data-path="${btoa(encodeURIComponent(project.path))}" onclick="App.loadFolderSize(this)">
                            ${project.size}
                        </strong>
                    </div>
                    <div class="project-meta-item">
                        <span>Modified:</span>
                        <strong>${project.modified}</strong>
                    </div>
                    <div class="project-meta-item">
                        <span>Files:</span>
                        <strong>${project.file_count}</strong>
                    </div>
                </div>
                
                <div style="margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap;">
                    <span class="project-status ${gitClass}">${gitStatus}</span>
                    <span class="project-status ${githubClass}">${githubStatus}</span>
                    ${changesHtml}
                </div>
                
                <div class="project-actions">
                    <button class="btn btn-primary btn-small" data-action="backup" data-path="${btoa(encodeURIComponent(project.path))}" data-name="${btoa(encodeURIComponent(project.name))}">
                        Backup
                    </button>
                    <button class="btn btn-secondary btn-small" data-action="details" data-path="${btoa(encodeURIComponent(project.path))}">
                        Details
                    </button>
                </div>
            </div>
        `;
    },
    
    /**
     * Run one project backup (no confirm). Used by single Backup and Backup All.
     */
    async runSingleBackup(projectPath, projectName, visibility) {
        const controller = new AbortController();
        this.bulkAbortController = controller;
        const timeoutId = setTimeout(() => controller.abort(), 300000);
        
        try {
            const body = { path: projectPath, name: projectName };
            if (visibility === 'private' || visibility === 'public') {
                body.visibility = visibility;
            }
            
            const response = await App.api('actions/backup-project.php', {
                method: 'POST',
                body: body,
                signal: controller.signal
            });
            
            if (response.success && response.data && response.data.status === 'success') {
                return {
                    success: true,
                    repo: response.data.repo_name || projectName,
                    private: response.data.private !== false
                };
            }
            
            if (response.success && response.data && response.data.status === 'needs_visibility') {
                return {
                    success: false,
                    needsVisibility: true,
                    repo: response.data.repo_name || projectName
                };
            }
            
            return {
                success: false,
                error: (response.data && (response.data.error || response.data.message)) || response.message || 'Unknown error'
            };
        } catch (error) {
            if (error.name === 'AbortError') {
                return { success: false, error: this.bulkCancelled ? 'Cancelled' : 'Timed out after 5 minutes' };
            }
            return { success: false, error: error.message || 'Unknown error' };
        } finally {
            clearTimeout(timeoutId);
            if (this.bulkAbortController === controller) {
                this.bulkAbortController = null;
            }
        }
    },
    
    chooseRepoVisibility(message) {
        return new Promise((resolve) => {
            if (typeof this.visibilityChoiceResolver === 'function') {
                this.visibilityChoiceResolver(null);
            }
            
            const text = document.getElementById('visibilityModalText');
            if (text && message) {
                text.textContent = message;
            }
            
            this.visibilityChoiceResolver = resolve;
            App.openModal('visibilityModal');
        });
    },
    
    finishVisibilityChoice(visibility) {
        App.closeModal('visibilityModal');
        const resolve = this.visibilityChoiceResolver;
        this.visibilityChoiceResolver = null;
        if (typeof resolve === 'function') {
            resolve(visibility);
        }
    },
    
    /**
     * Backup single project
     */
    async backupProject(projectPath, projectName) {
        try {
            App.showToast(`Checking GitHub for "${projectName}"...`, 'info', 0);
            
            let result = await this.runSingleBackup(projectPath, projectName);
            
            if (result.needsVisibility) {
                const visibility = await this.chooseRepoVisibility(
                    `"${projectName}" does not exist on GitHub yet. Create it as a private repository (recommended) or public?`
                );
                
                if (!visibility) {
                    App.showWarning('Backup cancelled.');
                    return;
                }
                
                App.showToast(`Starting ${visibility} backup for "${projectName}"...`, 'info', 0);
                result = await this.runSingleBackup(projectPath, projectName, visibility);
            }
            
            if (result.success) {
                const visLabel = result.private ? 'private' : 'public';
                App.showSuccess(`"${projectName}" backed up as a ${visLabel} repo: ${result.repo}`);
                setTimeout(() => Dashboard.loadProjects(), 1500);
            } else {
                App.showError(`Backup failed: ${result.error}`);
            }
        } catch (error) {
            App.showError('Error during backup: ' + (error.message || 'Unknown error'));
            console.error('Backup error:', error);
        }
    },
    
    getBackupQueue() {
        const skip = new Set(this.skipFolders.map(name => name.toLowerCase()));
        let queue = (this.projects || []).filter(project => {
            const name = (project.name || '').trim();
            if (!name || name[0] === '.') return false;
            if (skip.has(name.toLowerCase())) return false;
            return true;
        });
        
        const search = (document.getElementById('projectSearch')?.value || '').trim().toLowerCase();
        if (search) {
            queue = queue.filter(project =>
                (project.name || '').toLowerCase().includes(search) ||
                (project.path || '').toLowerCase().includes(search)
            );
        }
        
        return queue;
    },
    
    showBulkProgress(visible) {
        const panel = document.getElementById('bulkBackupProgress');
        if (panel) {
            panel.style.display = visible ? '' : 'none';
        }
    },
    
    updateBulkProgress(current, total, projectName, successCount, failedCount, lastError) {
        const percent = total ? Math.round((current / total) * 100) : 0;
        const fill = document.getElementById('bulkBackupFill');
        const status = document.getElementById('bulkBackupStatus');
        const counts = document.getElementById('bulkBackupCounts');
        
        if (fill) {
            fill.style.width = percent + '%';
            fill.textContent = percent + '%';
        }
        if (status) {
            status.textContent = current < total
                ? `Backing up ${current + 1} of ${total}: ${projectName}`
                : `Finished ${total} project${total === 1 ? '' : 's'}`;
        }
        if (counts) {
            const extra = lastError && !lastError.success ? ` Last error: ${lastError.error}` : '';
            counts.textContent = `Succeeded: ${successCount}  ·  Failed: ${failedCount}${extra}`;
        }
    },
    
    cancelBackupAll() {
        this.bulkCancelled = true;
        if (this.bulkAbortController) {
            this.bulkAbortController.abort();
        }
        const status = document.getElementById('bulkBackupStatus');
        if (status) {
            status.textContent = 'Stopping after the current project…';
        }
    },
    
    /**
     * Backup all (or currently filtered) projects, one at a time
     */
    async backupAll() {
        if (this.bulkAbortController) {
            App.showWarning('A bulk backup is already running.');
            return;
        }
        
        if (!this.projects || this.projects.length === 0) {
            await this.loadProjects();
        }
        
        const queue = this.getBackupQueue();
        if (queue.length === 0) {
            App.showError('No projects to backup. Scan projects first.');
            return;
        }
        
        const filtered = (document.getElementById('projectSearch')?.value || '').trim();
        const scope = filtered ? `the ${queue.length} filtered project(s)` : `all ${queue.length} projects`;
        
        const visibility = await this.chooseRepoVisibility(
            `Backup ${scope} to GitHub? New repositories will be created as private by default. Choose Public only if you want new repos visible to everyone. Existing repos keep their current visibility.`
        );
        if (!visibility) {
            App.showWarning('Backup All cancelled.');
            return;
        }
        
        this.bulkCancelled = false;
        this.showBulkProgress(true);
        App.setButtonLoading('backupAllBtn', true);
        
        let successCount = 0;
        let failedCount = 0;
        let lastResult = null;
        
        try {
            for (let i = 0; i < queue.length; i++) {
                if (this.bulkCancelled) {
                    break;
                }
                
                const project = queue[i];
                this.updateBulkProgress(i, queue.length, project.name, successCount, failedCount);
                
                lastResult = await this.runSingleBackup(project.path, project.name, visibility);
                if (lastResult.success) {
                    successCount++;
                } else {
                    failedCount++;
                }
                this.updateBulkProgress(i + 1, queue.length, project.name, successCount, failedCount, lastResult);
            }
            
            const stopped = this.bulkCancelled ? 'Stopped. ' : '';
            if (failedCount === 0 && !this.bulkCancelled) {
                App.showSuccess(`${stopped}Backup All finished: ${successCount} succeeded.`);
            } else {
                App.showWarning(`${stopped}Backup All finished: ${successCount} succeeded, ${failedCount} failed.`);
            }
            
            if (successCount > 0) {
                setTimeout(() => Dashboard.loadProjects(), 1500);
            }
        } catch (error) {
            App.showError('Error during bulk backup: ' + error.message);
        } finally {
            this.bulkCancelled = false;
            App.setButtonLoading('backupAllBtn', false);
        }
    },
    
    /**
     * View project details
     */
    viewProjectDetails(projectPath) {
        App.openModal('projectDetailsModal');
        // Load details via AJAX
        this.loadProjectDetails(projectPath);
    },
    
    /**
     * Load project details
     */
    async loadProjectDetails(projectPath) {
        try {
            const response = await App.api('actions/get-project-status.php', {
                method: 'POST',
                body: { path: projectPath }
            });
            
            if (response.success) {
                const details = response.data;
                const modFiles = details.modified_files || {};
                
                let modifiedFilesHtml = '';
                if (modFiles.files && modFiles.files.length > 0) {
                    modifiedFilesHtml = `
                        <div class="form-group">
                            <label>Modified Files (${modFiles.modified || 0} Modified, ${modFiles.untracked || 0} Untracked, ${modFiles.deleted || 0} Deleted)</label>
                            <div style="max-height: 200px; overflow-y: auto; border: 1px solid var(--color-border); border-radius: 4px; padding: 8px; background: var(--color-bg-secondary);">
                                ${modFiles.files.map(file => `
                                    <div style="padding: 4px 0; border-bottom: 1px solid var(--color-border-light); display: flex; align-items: center; gap: 8px; font-size: 12px;">
                                        <span style="
                                            width: 20px;
                                            height: 20px;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            border-radius: 3px;
                                            font-weight: bold;
                                            color: white;
                                            background: ${file.status === 'modified' ? '#ff9800' : file.status === 'untracked' ? '#2196f3' : '#f44336'};
                                        ">${file.status === 'modified' ? 'M' : file.status === 'untracked' ? '?' : 'D'}</span>
                                        <span style="word-break: break-all; font-family: monospace;">${App.escapeHtml(file.name)}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
                
                const detailsContent = document.getElementById('projectDetailsContent');
                
                if (detailsContent) {
                    detailsContent.innerHTML = `
                        <div class="form-group">
                            <label>Name</label>
                            <p>${App.escapeHtml(details.name)}</p>
                        </div>
                        <div class="form-group">
                            <label>Path</label>
                            <p style="word-break: break-all; font-family: monospace; font-size: 12px;">${App.escapeHtml(details.path)}</p>
                        </div>
                        <div class="form-group">
                            <label>Files</label>
                            <p>${details.file_count}</p>
                        </div>
                        <div class="form-group">
                            <label>Size</label>
                            <p>${details.size}</p>
                        </div>
                        <div class="form-group">
                            <label>Git Repository</label>
                            <p>${details.is_git ? '<span style="color: green;">✓ Yes</span>' : '<span style="color: red;">✗ No</span>'}</p>
                        </div>
                        ${details.is_git ? `
                        <div class="form-group">
                            <label>GitHub Remote</label>
                            <p>${details.remote_url ? `<code style="background: var(--color-bg-secondary); padding: 4px 8px; border-radius: 3px;">${App.escapeHtml(details.remote_url)}</code>` : 'Not configured'}</p>
                        </div>
                        ` : ''}
                        ${modifiedFilesHtml}
                    `;
                }
            }
        } catch (error) {
            App.showError('Error loading project details: ' + error.message);
        }
    },
    
    /**
     * Load folder size on-demand
     */
    async loadFolderSize(element) {
        const path = decodeURIComponent(atob(element.dataset.path));
        const originalText = element.textContent;
        
        // Show loading state
        element.textContent = 'Calculating...';
        element.style.opacity = '0.6';
        
        try {
            const response = await App.api('actions/get-folder-size.php', {
                method: 'POST',
                body: JSON.stringify({ path: path })
            });
            
            if (response.success) {
                element.textContent = response.data.size;
                element.onclick = null; // Remove click handler after loading
                element.style.cursor = 'default';
            } else {
                element.textContent = 'Error';
                App.showError('Failed to calculate folder size');
            }
        } catch (error) {
            element.textContent = originalText;
            App.showError('Error calculating folder size: ' + error.message);
        } finally {
            element.style.opacity = '1';
        }
    },
    
    /**
     * Search/filter projects
     */
    filterProjects(searchTerm) {
        const cards = document.querySelectorAll('.project-card');
        const term = searchTerm.toLowerCase();
        
        cards.forEach(card => {
            const name = card.querySelector('.project-name').textContent.toLowerCase();
            const path = card.querySelector('.project-path').textContent.toLowerCase();
            
            if (name.includes(term) || path.includes(term)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }
};

/**
 * Settings page functions
 */
const Settings = {
    /**
     * Load current settings
     */
    async loadSettings() {
        try {
            const response = await App.api('actions/get-settings.php');
            
            if (response.success) {
                const settings = response.data;
                document.getElementById('github_username').value = settings.github_username || '';
                document.getElementById('projects_path').value = settings.projects_path || '';
                document.getElementById('auto_gitignore').checked = settings.auto_gitignore !== false;
                document.getElementById('dark_mode').checked = settings.dark_mode === true;

                const tokenHint = document.getElementById('github_token_hint');
                const tokenInput = document.getElementById('github_token');
                if (tokenHint) {
                    tokenHint.textContent = settings.github_token_configured
                        ? 'A token is already saved. Paste a new PAT here and click Test Connection to replace it.'
                        : 'Paste your PAT, then click Test Connection (this now saves it) or Save Settings.';
                }
                if (tokenInput && settings.github_token_configured) {
                    tokenInput.placeholder = 'Token saved — paste a new one to replace it';
                }
            }
        } catch (error) {
            App.showError('Error loading settings: ' + error.message);
        }
    },
    
    /**
     * Save settings
     */
    async saveSettings() {
        try {
            App.setButtonLoading('saveSettingsBtn', true);
            
            const settings = {
                github_username: document.getElementById('github_username').value,
                github_token: document.getElementById('github_token').value,
                projects_path: document.getElementById('projects_path').value,
                auto_gitignore: document.getElementById('auto_gitignore').checked,
                dark_mode: document.getElementById('dark_mode').checked
            };
            
            const response = await App.api('actions/save-settings.php', {
                method: 'POST',
                body: settings
            });
            
            if (response.success) {
                App.showSuccess('Settings saved successfully!');
            } else {
                App.showError(response.message || 'Failed to save settings');
            }
        } catch (error) {
            App.showError('Error saving settings: ' + error.message);
        } finally {
            App.setButtonLoading('saveSettingsBtn', false);
        }
    },
    
    /**
     * Test GitHub connection
     */
    async testConnection() {
        try {
            App.setButtonLoading('testConnBtn', true);
            
            const token = document.getElementById('github_token').value.trim();
            const username = document.getElementById('github_username').value.trim();
            if (!token) {
                App.showError('Please enter a GitHub token');
                return;
            }
            if (!username) {
                App.showError('Please enter your GitHub username');
                return;
            }
            
            const response = await App.api('actions/test-github.php', {
                method: 'POST',
                body: { token: token, username: username }
            });
            
            if (response.success) {
                const user = response.data.user;
                if (user.login) {
                    document.getElementById('github_username').value = user.login;
                }
                document.getElementById('github_token').value = '';
                App.showSuccess(`Connected as ${App.escapeHtml(user.login)}. Token saved — you can backup now.`);
            } else {
                App.showError(response.message || 'Connection failed');
            }
        } catch (error) {
            App.showError('Error testing connection: ' + error.message);
        } finally {
            App.setButtonLoading('testConnBtn', false);
        }
    },
    
    /**
     * Clear all data
     */
    async clearAllData() {
        if (!confirm('This will clear all settings and logs. Are you sure?')) {
            return;
        }
        
        try {
            const response = await App.api('actions/clear-data.php', {
                method: 'POST'
            });
            
            if (response.success) {
                App.showSuccess('All data cleared. Please refresh the page.');
                setTimeout(() => location.reload(), 2000);
            } else {
                App.showError(response.message || 'Failed to clear data');
            }
        } catch (error) {
            App.showError('Error clearing data: ' + error.message);
        }
    }
};

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    App.init();
});
