/**
 * GitHub Backup Manager - Configuration Reference
 * 
 * This file documents all configuration options and their defaults.
 */

// ============================================
// APPLICATION CONFIGURATION
// ============================================

// Application root directory
define('APP_ROOT', dirname(__DIR__));

// Configuration file location
define('CONFIG_FILE', APP_ROOT . '/config/settings.json');

// Logs directory
define('LOG_DIR', APP_ROOT . '/logs');

// Default projects scanning path
define('DEFAULT_PROJECTS_PATH', 'D:/wamp64/www/projects/');


// ============================================
// GITHUB API CONFIGURATION
// ============================================

// GitHub API base URL (should not change)
const GITHUB_API_BASE = 'https://api.github.com';

// GitHub API version header
const GITHUB_API_VERSION = '2022-11-28';

// API request timeout (seconds)
const GITHUB_API_TIMEOUT = 30;

// Rate limit check (requests per hour)
const GITHUB_RATE_LIMIT = 5000;


// ============================================
// GIT CONFIGURATION
// ============================================

// Default git author name
const GIT_AUTHOR_NAME = 'GitHub Backup Manager';

// Default git author email
const GIT_AUTHOR_EMAIL = 'backup@github.com';

// Default branch name
const GIT_DEFAULT_BRANCH = 'main';

// Default remote name
const GIT_REMOTE_NAME = 'origin';

// Enable auto-commit on backup
const GIT_AUTO_COMMIT = true;

// Auto-generate .gitignore on init
const GIT_AUTO_GITIGNORE = true;


// ============================================
// SECURITY CONFIGURATION
// ============================================

// Sanitize all inputs
const SANITIZE_INPUTS = true;

// Validate file paths
const VALIDATE_PATHS = true;

// CSRF token validation
const VALIDATE_CSRF = true;

// Session timeout (minutes)
const SESSION_TIMEOUT = 30;

// Maximum token age (days)
const MAX_TOKEN_AGE = 90;


// ============================================
// FOLDER SCANNING CONFIGURATION
// ============================================

// Minimum folder size to include (0 = no minimum)
const MIN_FOLDER_SIZE = 0;

// Exclude folders matching these patterns
const EXCLUDE_PATTERNS = [
    'node_modules',
    'vendor',
    '.git',
    '.github',
    '__pycache__',
    '.venv',
    'venv',
    'dist',
    'build',
    '.next',
    '.nuxt',
    'coverage',
    '.pytest_cache'
];

// Maximum files to scan (0 = unlimited)
const MAX_FILES_PER_FOLDER = 0;

// Follow symlinks
const FOLLOW_SYMLINKS = false;


// ============================================
// LOGGING CONFIGURATION
// ============================================

// Log level: 'debug', 'info', 'warning', 'error'
const LOG_LEVEL = 'info';

// Log file retention (days)
const LOG_RETENTION_DAYS = 30;

// Maximum log file size (MB)
const MAX_LOG_SIZE = 10;

// Log format: 'json', 'text'
const LOG_FORMAT = 'text';


// ============================================
// PERFORMANCE CONFIGURATION
// ============================================

// Enable caching
const ENABLE_CACHE = true;

// Cache TTL (seconds)
const CACHE_TTL = 300;

// Batch operation size
const BATCH_SIZE = 10;

// Maximum concurrent operations
const MAX_CONCURRENT_OPS = 3;


// ============================================
// DATABASE/STORAGE CONFIGURATION
// ============================================

// Settings storage location
const SETTINGS_FILE = CONFIG_FILE;

// Settings file format: 'json', 'php'
const SETTINGS_FORMAT = 'json';

// Auto-backup settings
const AUTO_BACKUP_SETTINGS = true;

// Settings backup count
const SETTINGS_BACKUP_COUNT = 3;


// ============================================
// USER INTERFACE CONFIGURATION
// ============================================

// Default theme: 'light', 'dark', 'auto'
const DEFAULT_THEME = 'light';

// Items per page in lists
const ITEMS_PER_PAGE = 50;

// Auto-refresh dashboard (milliseconds, 0 = disabled)
const AUTO_REFRESH_INTERVAL = 0;

// Show debug information
const DEBUG_MODE = false;

// Enable dark mode toggle
const ENABLE_DARK_MODE = true;


// ============================================
// BACKUP CONFIGURATION
// ============================================

// Auto-commit interval (minutes, 0 = disabled)
const AUTO_COMMIT_INTERVAL = 0;

// Include hidden files
const INCLUDE_HIDDEN_FILES = false;

// Create separate backup branch
const SEPARATE_BACKUP_BRANCH = false;

// Backup branch prefix
const BACKUP_BRANCH_PREFIX = 'backup-';

// Maximum backup retries
const MAX_BACKUP_RETRIES = 3;

// Retry delay (seconds)
const RETRY_DELAY = 5;


// ============================================
// NOTIFICATION CONFIGURATION
// ============================================

// Enable email notifications
const ENABLE_EMAIL = false;

// Email from address
const EMAIL_FROM = 'noreply@backup-manager.local';

// Notify on backup failure
const NOTIFY_ON_FAILURE = true;

// Notify on backup success
const NOTIFY_ON_SUCCESS = false;


// ============================================
// ENVIRONMENT-SPECIFIC SETTINGS
// ============================================

// Current environment: 'development', 'staging', 'production'
const ENVIRONMENT = 'development';

// Show errors to user
const SHOW_ERRORS = ENVIRONMENT !== 'production';

// Log to file
const LOG_TO_FILE = true;

// Log to console (development only)
const LOG_TO_CONSOLE = ENVIRONMENT === 'development';


// ============================================
// SIZE LIMITS
// ============================================

// Maximum project size to backup (MB, 0 = unlimited)
const MAX_PROJECT_SIZE = 0;

// Maximum total backup size per day (GB, 0 = unlimited)
const MAX_DAILY_BACKUP_SIZE = 0;

// Maximum filename length
const MAX_FILENAME_LENGTH = 255;


// ============================================
// VALIDATION RULES
// ============================================

// Valid GitHub username pattern
const GITHUB_USERNAME_PATTERN = '^[a-zA-Z0-9]([a-zA-Z0-9-]{0,37}[a-zA-Z0-9])?$';

// Valid GitHub token pattern
const GITHUB_TOKEN_PATTERN = '^(ghp_|ghu_|ghs_|gho_)[a-zA-Z0-9_]*$';

// Minimum token length
const MIN_TOKEN_LENGTH = 20;

// Maximum token length
const MAX_TOKEN_LENGTH = 255;


// ============================================
// GIT OPERATIONS
// ============================================

// Use SSH for git operations (requires SSH key configured)
const USE_SSH = false;

// SSH key path
const SSH_KEY_PATH = '';

// Git command timeout (seconds)
const GIT_TIMEOUT = 300;

// Show git output in logs
const LOG_GIT_OUTPUT = true;


// ============================================
// DEBUG & DEVELOPMENT
// ============================================

// Display SQL queries
const DEBUG_SQL = false;

// Display HTTP requests
const DEBUG_HTTP = false;

// Verbose logging
const VERBOSE_LOG = false;

// Dump variables on error
const DEBUG_DUMP_VARS = false;
