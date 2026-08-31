<?php
/**
 * Performance Optimization - Caching and Speed Improvements
 */

/**
 * Simple file-based cache for scan results
 */
class ScanCache {
    private static $cacheDir = __DIR__ . '/../cache';
    private static $cacheDuration = 300; // 5 minutes
    
    public static function init() {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    public static function getCacheKey($projectsPath) {
        return md5('scan_' . $projectsPath);
    }
    
    public static function get($projectsPath) {
        self::init();
        $cacheFile = self::$cacheDir . '/' . self::getCacheKey($projectsPath) . '.cache';
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($cacheFile), true);
        
        // Check if cache is expired
        if ($data['timestamp'] + self::$cacheDuration < time()) {
            @unlink($cacheFile);
            return null;
        }
        
        return $data['projects'] ?? null;
    }
    
    public static function set($projectsPath, $projects) {
        self::init();
        $cacheFile = self::$cacheDir . '/' . self::getCacheKey($projectsPath) . '.cache';
        
        $data = [
            'timestamp' => time(),
            'projects' => $projects
        ];
        
        file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    public static function clear($projectsPath) {
        self::init();
        $cacheFile = self::$cacheDir . '/' . self::getCacheKey($projectsPath) . '.cache';
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }
    
    public static function clearAll() {
        self::init();
        $files = glob(self::$cacheDir . '/*.cache');
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}

/**
 * Git command optimization - batch operations
 */
class GitOptimizer {
    
    /**
     * Execute multiple git commands efficiently
     */
    public static function batchCommands($projectPath, $commands) {
        $results = array();
        
        foreach ($commands as $label => $cmd) {
            $results[$label] = self::executeOptimized($projectPath, $cmd);
        }
        
        return $results;
    }
    
    /**
     * Execute git command with timeout
     */
    public static function executeOptimized($projectPath, $cmd, $timeout = 30) {
        $full = 'cd /d "' . $projectPath . '" && ' . $cmd . ' 2>&1';
        
        $proc = proc_open(
            $full,
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            null
        );
        
        if (!is_resource($proc)) {
            return null;
        }
        
        $output = '';
        $timeout_ms = $timeout * 1000000;
        
        stream_set_blocking($pipes[1], false);
        
        $start = time();
        while (($line = fgets($pipes[1])) !== false && (time() - $start) < $timeout) {
            $output .= $line;
            usleep(1000); // Small delay to reduce CPU usage
        }
        
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
        
        return trim($output);
    }
}

// Initialize cache
ScanCache::init();
