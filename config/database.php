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

// Using Session Mode Pooler (IPv4 reachable from your network)
define('DB_HOST', 'aws-1-ap-southeast-1.pooler.supabase.com');
define('DB_PORT', '5432');                                       
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres.aahaznqptohmkdiqpjnx');
define('DB_PASS', 'itkoqLjr1QTLuFqg');

/**
 * Function to connect to Supabase (PostgreSQL)
 * Using PDO for security
 */
function getConnection() {
    try {
        // PostgreSQL connection DSN with SSL requirement
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";sslmode=require";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 10,  // 10 second timeout
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
