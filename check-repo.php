<?php
require 'includes/functions.php';
require 'includes/github-api.php';

// Get token from settings file directly
$settingsFile = 'config/settings.json';
$settings = json_decode(file_get_contents($settingsFile), true);
$token = $settings['github_token'] ?? '';

$api = new GitHubAPI($token, 'talhanazeer');
try {
    $repo = $api->getRepository('CSM');
    echo "Repository: " . $repo['name'] . PHP_EOL;
    echo "Size: " . $repo['size'] . " KB" . PHP_EOL;
    echo "Pushed at: " . $repo['pushed_at'] . PHP_EOL;
    echo "URL: " . $repo['url'] . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
