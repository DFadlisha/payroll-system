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
define('DB_HOST', 'db.aahaznqptohmkdiqpjnx.supabase.co');         // Host from Supabase
define('DB_PORT', '5432');                                         // Port (5432 for direct connection)
define('DB_NAME', 'postgres');                                     // Database name (default: postgres)
define('DB_USER', 'postgres');                                     // User
define('DB_PASS', '[YOUR-PASSWORD]');                              // Password from Supabase - CHANGE TO YOUR ACTUAL PASSWORD

/**
 * Function to connect to Supabase (PostgreSQL)
 * Using PDO for security
 */
function getConnection() {
    try {
        // PostgreSQL connection DSN
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Supabase Connection Error: " . $e->getMessage());
        die("Connection error to Supabase. Please check database settings.");
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
