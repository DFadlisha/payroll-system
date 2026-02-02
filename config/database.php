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

// 1. Define the Primary Host (Use Environment variables)
define('DB_HOST', Environment::get('DB_HOST', 'db.aahaznqptohmkdiqpjnx.supabase.co'));
define('DB_PORT', Environment::get('DB_PORT', '6543')); // Default to 6543 for cloud
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
        // Build DSN - Increased timeout for local development
        $sslMode = Environment::isDevelopment() ? 'prefer' : 'require';
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode={$sslMode};connect_timeout=30";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::ATTR_TIMEOUT => 30, // Increased for local dev
            PDO::ATTR_PERSISTENT => false, 
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Supabase Connection Error: " . $e->getMessage());
        
        // Show detailed error for debugging (remove in production)
        $errorMsg = "Connection error to Supabase.<br><br>";
        $errorMsg .= "<strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
        
        if (strpos($e->getMessage(), 'authentication failed') !== false) {
            $errorMsg .= "<div style='color:red; background:#fee; padding:15px; border-radius:8px; border:1px solid #fcc; margin-bottom:15px;'>";
            $errorMsg .= "<strong>⚠️ Authentication Failed:</strong> Your database password in .env might be wrong.<br>";
            $errorMsg .= "Note: Your Supabase <strong>Login</strong> password is often different from your <strong>Database</strong> password.</div>";
        }

        $errorMsg .= "<strong>Check these settings in config/database.php:</strong><br>";
        $errorMsg .= "- DB_HOST: " . DB_HOST . " (via Pooler)<br>";
        $errorMsg .= "- DB_PORT: " . DB_PORT . "<br>";
        $errorMsg .= "- DB_NAME: " . DB_NAME . "<br>";
        $errorMsg .= "- DB_USER: " . DB_USER . "<br>";
        $errorMsg .= "- DB_PASS: " . (DB_PASS === '[YOUR-PASSWORD]' ? '<span style="color:red">NOT SET</span>' : '******') . "<br><br>";
        $errorMsg .= "<strong>To fix your password:</strong><br>";
        $errorMsg .= "1. Go to your <a href='https://supabase.com/dashboard/project/aahaznqptohmkdiqpjnx/settings/database' target='_blank'>Supabase Database Settings</a><br>";
        $errorMsg .= "2. Click <strong>'Reset database password'</strong> to set a new one.<br>";
        $errorMsg .= "3. Open your <strong>.env</strong> file and update <code>DB_PASS</code>.<br>";
        $errorMsg .= "4. Refresh this page.";
        
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
