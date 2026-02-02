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

// DATABASE CONFIGURATION START
// -------------------------------------------------------------------------

// 1. Define the Primary Host (Direct Connection)
$primaryHost = 'db.aahaznqptohmkdiqpjnx.supabase.co';

// 2. Force IPv4 Resolution
// Render prefers IPv6, which fails for Supabase Direct Connections.
// We use gethostbyname() to manually grab the IPv4 address (A Record).
$resolvedIP = gethostbyname($primaryHost);

if ($resolvedIP !== $primaryHost && filter_var($resolvedIP, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    // We found a valid IPv4 address! Use it to bypass IPv6 issues.
    define('DB_HOST', $resolvedIP);
} else {
    // Fallback to the hostname
    define('DB_HOST', $primaryHost);
}

define('DB_PORT', '5432');
define('DB_NAME', Environment::get('DB_NAME', 'postgres'));
define('DB_USER', 'postgres'); // Direct uses simple 'postgres' user
define('DB_PASS', 'ZGdRerSZQfxtoHKs'); // Hardcoded new password to override Render env settings temporarily

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
        // We do NOT use persistent connections anymore to avoid pooler conflicts
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";
        
        // Re-define options here to ensure scope access
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_PERSISTENT => false, 
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
