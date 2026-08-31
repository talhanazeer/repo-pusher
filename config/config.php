<?php
/**
 * GitHub Backup Manager - Configuration File
 * Stores user settings and paths
 */

defined('APP_ROOT') or define('APP_ROOT', dirname(__DIR__));
defined('CONFIG_FILE') or define('CONFIG_FILE', APP_ROOT . '/config/settings.json');
defined('LOG_DIR') or define('LOG_DIR', APP_ROOT . '/logs');
defined('DEFAULT_PROJECTS_PATH') or define('DEFAULT_PROJECTS_PATH', 'D:/wamp64/www/projects/');

/**
 * Load settings from JSON file
 */
function getSettings() {
    if (!file_exists(CONFIG_FILE)) {
        return array(
            'github_username' => '',
            'github_token' => '',
            'projects_path' => DEFAULT_PROJECTS_PATH,
            'auto_gitignore' => true,
            'dark_mode' => false
        );
    }
    
    $json = file_get_contents(CONFIG_FILE);
    $settings = json_decode($json, true);
    
    return $settings ?: array();
}

/**
 * Save settings to JSON file
 */
function saveSettings($settings) {
    $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    if (file_put_contents(CONFIG_FILE, $json) === false) {
        throw new Exception('Failed to save settings');
    }
    
    return true;
}

/**
 * Get a specific setting
 */
function getSetting($key, $default = null) {
    $settings = getSettings();
    return isset($settings[$key]) ? $settings[$key] : $default;
}

/**
 * Ensure log directory exists
 */
if (!is_dir(LOG_DIR)) {
    @mkdir(LOG_DIR, 0755, true);
}

/**
 * Initialize GitHub token (never expose in frontend)
 */
function getGitHubToken() {
    $token = trim((string) getSetting('github_token'));
    
    if ($token === '') {
        throw new Exception('GitHub token not configured. Please set it in settings.');
    }
    
    return $token;
}

/**
 * Initialize GitHub username
 */
function getGitHubUsername() {
    $username = getSetting('github_username');
    
    if (empty($username)) {
        throw new Exception('GitHub username not configured. Please set it in settings.');
    }
    
    return $username;
}
