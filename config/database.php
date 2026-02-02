<?php
/**
 * Database Configuration - SUPABASE (PostgreSQL)
 * MI-NES Payroll System
 * 
 * HOW TO USE:
 * 1. Go to Supabase Dashboard > Settings > Database
 * 2. Copy connection details and paste below
 * 3. Use Direct Connection (port 5432) or Transaction Pooler (port 6543)
 */

// ===========================================
// SUPABASE DATABASE SETTINGS
// Change according to your Supabase project
// ===========================================

// How to get details:
// Supabase Dashboard > Settings > Database > Connection parameters

// Use Environment class to get settings (initialized in includes/functions.php or manually)
if (!class_exists('Environment')) {
    require_once __DIR__ . '/environment.php';
}

// Using Direct Connection (Port 5432) - Most reliable for this setup
define('DB_HOST', Environment::get('DB_HOST', ''));
define('DB_PORT', Environment::get('DB_PORT', '5432'));
define('DB_NAME', Environment::get('DB_NAME', 'postgres'));
define('DB_USER', Environment::get('DB_USER', 'postgres'));
define('DB_PASS', Environment::get('DB_PASS', ''));

/**
 * Function to connect to Supabase (PostgreSQL)
 * Using PDO for security
 */
function getConnection() {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // PostgreSQL connection DSN with SSL requirement
        // Added keepalives=1 for better stability with Supabase
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require;keepalives=1";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true, // Required for Supabase Transaction Pooler
            PDO::ATTR_TIMEOUT => 10,  // 10 second timeout
            PDO::ATTR_PERSISTENT => true, // Use persistent connections
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Supabase Connection Error: " . $e->getMessage());
        
        // Show detailed error for debugging (remove in production)
        $errorMsg = "Connection error to Supabase.<br><br>";
        $errorMsg .= "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
        $errorMsg .= "<strong>Check these settings in config/database.php:</strong><br>";
        $errorMsg .= "- DB_HOST: " . DB_HOST . "<br>";
        $errorMsg .= "- DB_PORT: " . DB_PORT . "<br>";
        $errorMsg .= "- DB_NAME: " . DB_NAME . "<br>";
        $errorMsg .= "- DB_USER: " . DB_USER . "<br>";
        $errorMsg .= "- DB_PASS: " . (DB_PASS === '[YOUR-PASSWORD]' ? '<span style=\"color:red\">NOT SET - Please update with your Supabase password!</span>' : '******') . "<br><br>";
        $errorMsg .= "<strong>To get your password:</strong><br>";
        $errorMsg .= "1. Go to Supabase Dashboard<br>";
        $errorMsg .= "2. Click Settings → Database<br>";
        $errorMsg .= "3. Copy the Database Password<br>";
        $errorMsg .= "4. Update DB_PASS in config/database.php";
        
        die($errorMsg);
    }
}

// Test connection (uncomment to test)
// try {
//     $conn = getConnection();
//     echo "Supabase connected successfully!";
// } catch (Exception $e) {
//     echo "Connection failed: " . $e->getMessage();
// }
?>
