<?php
require 'config/config.php';
require 'includes/functions.php';
require 'includes/github-api.php';
require 'includes/git-helper.php';

$projectPath = 'D:\\wamp64\\www\\projects\\CSM';
$git = new GitHelper($projectPath);
$result = $git->backup('https://github.com/talhanazeer/CSM.git', false);
echo json_encode($result, JSON_PRETTY_PRINT);
?>
