<?php
/**
 * ============================================
 * ENVIRONMENT CONFIGURATION LOADER
 * ============================================
 * Loads environment variables from .env file
 * ============================================
 */

class Environment
{
    private static $loaded = false;

    /**
     * Load environment variables from .env file
     */
    public static function load($path = null)
    {
        if (self::$loaded) {
            return;
        }

        if ($path === null) {
            $path = __DIR__ . '/../.env';
        }

        if (!file_exists($path)) {
            // In production, throw error. In development, allow fallback to defaults
            if (getenv('APP_ENV') === 'production') {
                throw new Exception('.env file not found. Please create one from .env.example');
            }
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

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

                // Set as environment variable
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }

        self::$loaded = true;
    }

    /**
     * Get environment variable with optional default
     */
    public static function get($key, $default = null)
    {
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return $default;
    }

    /**
     * Get boolean environment variable
     */
    public static function getBool($key, $default = false)
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
    }

    /**
     * Check if we're in development mode
     */
    public static function isDevelopment()
    {
        return self::get('APP_ENV', 'development') === 'development';
    }

    /**
     * Check if we're in production mode
     */
    public static function isProduction()
    {
        return self::get('APP_ENV', 'development') === 'production';
    }

    /**
     * Check if debug mode is enabled
     */
    public static function isDebug()
    {
        return self::getBool('APP_DEBUG', true);
    }
}

// Configure simple file logging
if (Environment::get('APP_ENV') !== 'production') {
    ini_set('log_errors', 1);
    ini_set('display_errors', 0); // Hide errors from the user's screen
    ini_set('error_log', __DIR__ . '/../debug.log'); // Save them to debug.log in the main folder
}

// Auto-load environment on include
Environment::load();
