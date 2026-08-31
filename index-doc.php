<?php
/**
 * GitHub Backup Manager - Project Index
 * Complete file listing and documentation map
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub Backup Manager - Project Index</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        .header p {
            color: #666;
            font-size: 1.1em;
        }
        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.5em;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .file-list {
            list-style: none;
        }
        .file-list li {
            padding: 10px;
            margin: 5px 0;
            background: #f5f5f5;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }
        .file-list code {
            background: #efefef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
        }
        .description {
            color: #666;
            font-size: 0.95em;
            margin-top: 5px;
        }
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .quick-link {
            display: block;
            padding: 15px;
            background: #f9f9f9;
            border: 2px solid #667eea;
            border-radius: 6px;
            text-decoration: none;
            color: #667eea;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s;
        }
        .quick-link:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        .status-badge {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .feature-box {
            padding: 15px;
            background: #f0f4ff;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }
        .feature-box h4 {
            color: #667eea;
            margin-bottom: 8px;
        }
        .footer {
            text-align: center;
            color: white;
            padding: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🚀 GitHub Backup Manager</h1>
            <p>Complete Project Index & Documentation Map</p>
        </div>

        <!-- Quick Start -->
        <div class="section">
            <h2>⚡ Quick Start</h2>
            <div class="quick-links">
                <a href="index.php" class="quick-link">📊 Dashboard</a>
                <a href="settings.php" class="quick-link">⚙️ Settings</a>
                <a href="README.md" class="quick-link">📖 README</a>
                <a href="INSTALL.md" class="quick-link">📥 Install</a>
                <a href="API.md" class="quick-link">🔌 API Docs</a>
                <a href="TROUBLESHOOTING.md" class="quick-link">🆘 Help</a>
            </div>
        </div>

        <!-- Features -->
        <div class="section">
            <h2>✨ Key Features</h2>
            <div class="features">
                <div class="feature-box">
                    <h4>📁 Project Scanning</h4>
                    <p>Automatically detect all projects in your folder with Git status</p>
                </div>
                <div class="feature-box">
                    <h4>☁️ GitHub Backup</h4>
                    <p>Create private repos and push code with one click</p>
                </div>
                <div class="feature-box">
                    <h4>🔄 Bulk Operations</h4>
                    <p>Backup all projects at once with progress tracking</p>
                </div>
                <div class="feature-box">
                    <h4>🔐 Secure</h4>
                    <p>Token stored locally, never exposed, full validation</p>
                </div>
                <div class="feature-box">
                    <h4>🌙 Dark Mode</h4>
                    <p>Beautiful light and dark themes with responsive design</p>
                </div>
                <div class="feature-box">
                    <h4>📝 Logging</h4>
                    <p>Complete operation logs with timestamps and error details</p>
                </div>
            </div>
        </div>

        <!-- Application Files -->
        <div class="section">
            <h2>📁 Application Files <span class="status-badge">Ready</span></h2>
            <ul class="file-list">
                <li>
                    <code>index.php</code>
                    <div class="description">Main dashboard page with project list and controls</div>
                </li>
                <li>
                    <code>settings.php</code>
                    <div class="description">Settings page for GitHub credentials and preferences</div>
                </li>
                <li>
                    <code>.htaccess</code>
                    <div class="description">Apache security configuration and headers</div>
                </li>
            </ul>
        </div>

        <!-- Configuration -->
        <div class="section">
            <h2>⚙️ Configuration Files</h2>
            <ul class="file-list">
                <li>
                    <code>config/config.php</code>
                    <div class="description">Settings loader and configuration constants</div>
                </li>
                <li>
                    <code>config/settings.json</code>
                    <div class="description">User settings (auto-generated on first save)</div>
                </li>
            </ul>
        </div>

        <!-- Includes/Libraries -->
        <div class="section">
            <h2>📚 PHP Libraries & Classes</h2>
            <ul class="file-list">
                <li>
                    <code>includes/functions.php</code>
                    <div class="description">Utility functions: sanitization, validation, logging, helpers</div>
                </li>
                <li>
                    <code>includes/github-api.php</code>
                    <div class="description">GitHubAPI class: Create repos, verify tokens, manage repositories</div>
                </li>
                <li>
                    <code>includes/git-helper.php</code>
                    <div class="description">GitHelper class: Initialize repos, commit, push, backup workflow</div>
                </li>
            </ul>
        </div>

        <!-- Frontend Assets -->
        <div class="section">
            <h2>🎨 Frontend Assets</h2>
            <ul class="file-list">
                <li>
                    <code>assets/css/style.css</code>
                    <div class="description">Main stylesheet with CSS variables for dark mode, responsive design</div>
                </li>
                <li>
                    <code>assets/js/app.js</code>
                    <div class="description">Frontend JavaScript: DOM manipulation, API calls, UI interactions</div>
                </li>
            </ul>
        </div>

        <!-- API Endpoints -->
        <div class="section">
            <h2>🔌 API Endpoints</h2>
            <ul class="file-list">
                <li>
                    <code>actions/scan-projects.php</code>
                    <div class="description">GET: Scan and list all projects from configured folder</div>
                </li>
                <li>
                    <code>actions/backup-project.php</code>
                    <div class="description">POST: Backup a single project to GitHub</div>
                </li>
                <li>
                    <code>actions/backup-all.php</code>
                    <div class="description">POST: Bulk backup all projects at once</div>
                </li>
                <li>
                    <code>actions/save-settings.php</code>
                    <div class="description">POST: Save user settings to config file</div>
                </li>
                <li>
                    <code>actions/get-settings.php</code>
                    <div class="description">GET: Load user settings (token not included)</div>
                </li>
                <li>
                    <code>actions/test-github.php</code>
                    <div class="description">POST: Test GitHub connection with token</div>
                </li>
                <li>
                    <code>actions/get-project-details.php</code>
                    <div class="description">GET: Get detailed information about a project</div>
                </li>
                <li>
                    <code>actions/clear-data.php</code>
                    <div class="description">POST: Clear all application data (dangerous!)</div>
                </li>
            </ul>
        </div>

        <!-- Documentation -->
        <div class="section">
            <h2>📖 Documentation Files</h2>
            <ul class="file-list">
                <li>
                    <code>README.md</code>
                    <div class="description">Main documentation with features, usage guide, and tips</div>
                </li>
                <li>
                    <code>INSTALL.md</code>
                    <div class="description">Step-by-step installation and configuration guide</div>
                </li>
                <li>
                    <code>API.md</code>
                    <div class="description">Complete API reference with examples and error handling</div>
                </li>
                <li>
                    <code>TROUBLESHOOTING.md</code>
                    <div class="description">Problem solving guide with common issues and solutions</div>
                </li>
                <li>
                    <code>CONFIG_REFERENCE.md</code>
                    <div class="description">Configuration options and environment settings</div>
                </li>
                <li>
                    <code>PROJECT_SUMMARY.md</code>
                    <div class="description">Complete project overview and summary</div>
                </li>
                <li>
                    <code>QUICK_START.bat</code>
                    <div class="description">Quick reference for Windows (batch file)</div>
                </li>
                <li>
                    <code>QUICK_START.sh</code>
                    <div class="description">Quick reference for Linux/Mac (bash script)</div>
                </li>
            </ul>
        </div>

        <!-- Logs -->
        <div class="section">
            <h2>📝 Logging</h2>
            <ul class="file-list">
                <li>
                    <code>logs/</code>
                    <div class="description">Daily application logs (app_YYYY-MM-DD.log)</div>
                </li>
            </ul>
        </div>

        <!-- Getting Started -->
        <div class="section">
            <h2>🚀 Getting Started</h2>
            <ol style="padding-left: 20px; line-height: 1.8;">
                <li>Create GitHub Personal Access Token: <a href="https://github.com/settings/tokens" target="_blank">https://github.com/settings/tokens</a></li>
                <li>Visit <a href="settings.php">Settings Page</a></li>
                <li>Enter your GitHub username</li>
                <li>Paste your Personal Access Token</li>
                <li>Click "Test Connection"</li>
                <li>Click "Save Settings"</li>
                <li>Go to <a href="index.php">Dashboard</a></li>
                <li>Click "Scan Projects"</li>
                <li>Click "Backup" on any project</li>
                <li>Watch your code sync to GitHub!</li>
            </ol>
        </div>

        <!-- Project Stats -->
        <div class="section">
            <h2>📊 Project Statistics</h2>
            <ul class="file-list">
                <li><strong>Total Files:</strong> 25+</li>
                <li><strong>Lines of Code:</strong> 4000+</li>
                <li><strong>PHP Files:</strong> 13 (well-commented)</li>
                <li><strong>CSS Lines:</strong> 1000+ (with dark mode)</li>
                <li><strong>JavaScript:</strong> 400+ (vanilla, no dependencies)</li>
                <li><strong>Documentation Pages:</strong> 8</li>
                <li><strong>API Endpoints:</strong> 8</li>
                <li><strong>Helper Classes:</strong> 3 (GitHub API, Git Helper)</li>
            </ul>
        </div>

        <!-- Technology Stack -->
        <div class="section">
            <h2>🛠️ Technology Stack</h2>
            <div class="features">
                <div class="feature-box">
                    <h4>Backend</h4>
                    <p>PHP 7.2+, JSON storage, Shell execution for Git</p>
                </div>
                <div class="feature-box">
                    <h4>Frontend</h4>
                    <p>HTML5, CSS3, Vanilla JavaScript (no dependencies)</p>
                </div>
                <div class="feature-box">
                    <h4>APIs</h4>
                    <p>GitHub API v3, Git CLI, Fetch API</p>
                </div>
                <div class="feature-box">
                    <h4>Security</h4>
                    <p>Input validation, sanitization, HTTPS headers</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>✨ GitHub Backup Manager - Built with ❤️ for developers</p>
            <p>© 2024 | All Rights Reserved | Free to use</p>
        </div>
    </div>
</body>
</html>
