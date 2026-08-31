<?php
/**
 * DEBUG VERSION - Shows actual errors
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if files exist
$configFile = __DIR__ . '/config/config.php';
$functionsFile = __DIR__ . '/includes/functions.php';

if (!file_exists($configFile)) {
    die("ERROR: config.php not found at $configFile");
}

if (!file_exists($functionsFile)) {
    die("ERROR: functions.php not found at $functionsFile");
}

require_once $configFile;
require_once $functionsFile;

$settings = getSettings();
$isConfigured = !empty($settings['github_username']) && !empty($settings['github_token']);

// If you see this, PHP is working correctly
echo "✅ PHP is working correctly!<br>";
echo "Config file loaded successfully<br>";
echo "GitHub Username: " . htmlspecialchars($settings['github_username']) . "<br>";
echo "Token configured: " . (!empty($settings['github_token']) ? "YES" : "NO") . "<br>";
echo "<br><a href='index.php?debug=0'>Back to Dashboard</a>";
exit;
?>
