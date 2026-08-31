<?php
/**
 * GitHub Backup Manager - Diagnostics
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>GitHub Backup Manager - Diagnostics</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
        .section { margin: 20px 0; border: 1px solid #ccc; padding: 10px; }
    </style>
</head>
<body>
    <h1>GitHub Backup Manager - System Diagnostics</h1>";

// Check PHP version
echo "<div class='section'><h2>PHP Version</h2>";
if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
    echo "<p class='success'>✓ PHP " . PHP_VERSION . " (OK - 7.2+)</p>";
} else {
    echo "<p class='error'>✗ PHP " . PHP_VERSION . " (Need 7.2+)</p>";
}
echo "</div>";

// Check required extensions
echo "<div class='section'><h2>Required Extensions</h2>";
$extensions = ['curl', 'json', 'SPL'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✓ $ext loaded</p>";
    } else {
        echo "<p class='error'>✗ $ext NOT loaded</p>";
    }
}
echo "</div>";

// Check folder permissions
echo "<div class='section'><h2>Folder Permissions</h2>";
$folders = [
    'config' => __DIR__ . '/config',
    'logs' => __DIR__ . '/logs',
    'includes' => __DIR__ . '/includes',
    'assets' => __DIR__ . '/assets'
];

foreach ($folders as $name => $path) {
    if (is_dir($path)) {
        if (is_readable($path)) {
            echo "<p class='success'>✓ $name/ - readable</p>";
        } else {
            echo "<p class='error'>✗ $name/ - NOT readable</p>";
        }
        if (is_writable($path)) {
            echo "<p class='success'>✓ $name/ - writable</p>";
        } else {
            echo "<p class='error'>✗ $name/ - NOT writable</p>";
        }
    } else {
        echo "<p class='error'>✗ $name/ - DOES NOT EXIST</p>";
    }
}
echo "</div>";

// Check critical files
echo "<div class='section'><h2>Critical Files</h2>";
$files = [
    'config/config.php' => __DIR__ . '/config/config.php',
    'includes/functions.php' => __DIR__ . '/includes/functions.php',
    'includes/github-api.php' => __DIR__ . '/includes/github-api.php',
    'includes/git-helper.php' => __DIR__ . '/includes/git-helper.php'
];

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        echo "<p class='success'>✓ $name exists</p>";
    } else {
        echo "<p class='error'>✗ $name MISSING</p>";
    }
}
echo "</div>";

// Check config file
echo "<div class='section'><h2>Configuration File</h2>";
$configFile = __DIR__ . '/config/settings.json';
if (file_exists($configFile)) {
    echo "<p class='success'>✓ settings.json exists</p>";
    $content = file_get_contents($configFile);
    $config = json_decode($content, true);
    if ($config) {
        echo "<p class='success'>✓ settings.json is valid JSON</p>";
        echo "<pre>";
        echo "GitHub Username: " . htmlspecialchars($config['github_username'] ?? '') . "\n";
        echo "Token configured: " . (!empty($config['github_token']) ? "YES" : "NO") . "\n";
        echo "Projects path: " . htmlspecialchars($config['projects_path'] ?? '') . "\n";
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ settings.json is INVALID JSON</p>";
    }
} else {
    echo "<p class='error'>✗ settings.json MISSING - creating default...</p>";
    $default = array(
        'github_username' => '',
        'github_token' => '',
        'projects_path' => 'D:/wamp64/www/projects/',
        'auto_gitignore' => true,
        'dark_mode' => false
    );
    file_put_contents($configFile, json_encode($default, JSON_PRETTY_PRINT));
    echo "<p class='success'>✓ Created default settings.json</p>";
}
echo "</div>";

// Try to load functions
echo "<div class='section'><h2>Loading Application Files</h2>";
try {
    require_once __DIR__ . '/config/config.php';
    echo "<p class='success'>✓ config/config.php loaded</p>";
    
    require_once __DIR__ . '/includes/functions.php';
    echo "<p class='success'>✓ includes/functions.php loaded</p>";
    
    $settings = getSettings();
    echo "<p class='success'>✓ getSettings() function works</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
echo "</div>";

// System info
echo "<div class='section'><h2>System Information</h2>";
echo "<pre>";
echo "Server: " . htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . htmlspecialchars($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Request URI: " . htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Unknown') . "\n";
echo "Script Name: " . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "\n";
echo "PHP User: " . htmlspecialchars(get_current_user()) . "\n";
echo "</pre>";
echo "</div>";

echo "<div class='section'><h2>Next Steps</h2>";
if (file_exists(__DIR__ . '/config/settings.json')) {
    $config = json_decode(file_get_contents(__DIR__ . '/config/settings.json'), true);
    if (empty($config['github_token'])) {
        echo "<p class='error'>⚠️ GitHub token is empty!</p>";
        echo "<p>1. Go to <a href='settings.php'>Settings Page</a></p>";
        echo "<p>2. Create GitHub PAT at: <a href='https://github.com/settings/tokens' target='_blank'>https://github.com/settings/tokens</a></p>";
        echo "<p>3. Enter token in Settings</p>";
        echo "<p>4. Click Test Connection</p>";
    } else {
        echo "<p class='success'>✓ Token is configured</p>";
        echo "<p><a href='index.php'>Go to Dashboard</a></p>";
    }
} else {
    echo "<p class='error'>⚠️ settings.json missing!</p>";
    echo "<p>Refresh this page to create it.</p>";
}
echo "</div>";

echo "</body></html>";
?>
