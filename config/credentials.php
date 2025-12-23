<?php
/**
 * Environment Configuration Loader
 * Loads database credentials from .env file
 * This keeps sensitive data out of version control
 */

/**
 * Load environment variables from .env file
 * Parses KEY=VALUE format and sets environment variables
 * @param string $envFile Path to .env file
 * @return bool Success status
 */
function loadEnv($envFile = __DIR__ . '/../.env') {
    if (!file_exists($envFile)) {
        error_log("Warning: .env file not found at: " . $envFile);
        return false;
    }
    
    // Check file permissions (should be readable only by owner)
    $perms = fileperms($envFile);
    if (($perms & 0777) > 0600) {
        error_log("Warning: .env file has insecure permissions. Run: chmod 600 .env");
    }
    
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set as environment variable (only if not already set)
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
    
    return true;
}

/**
 * Get environment variable with fallback
 * @param string $key Variable name
 * @param mixed $default Default value if not found
 * @return mixed
 */
function env($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

// Load environment variables
loadEnv();

// Define constants from environment variables
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', ''));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', ''));

// Validate that credentials are loaded
if (empty(DB_USER) || empty(DB_NAME)) {
    error_log("Error: Database credentials not properly loaded from .env file");
}
?>
