<?php
/**
 * Database Connection Test
 * MI-NES Payroll System
 * 
 * Run this file to test your Supabase database connection
 */

echo "<h2>🔌 Database Connection Test</h2>";
echo "<hr>";

// Include database configuration
require_once 'config/database.php';

echo "<p><strong>Connection Details:</strong></p>";
echo "<ul>";
echo "<li>Host: " . DB_HOST . "</li>";
echo "<li>Port: " . DB_PORT . "</li>";
echo "<li>Database: " . DB_NAME . "</li>";
echo "<li>User: " . DB_USER . "</li>";
echo "<li>Password: " . str_repeat("*", strlen(DB_PASS)) . "</li>";
echo "</ul>";
echo "<hr>";

// Test connection
echo "<p><strong>Testing Connection...</strong></p>";

try {
    $startTime = microtime(true);
    $conn = getConnection();
    $endTime = microtime(true);
    $connectionTime = round(($endTime - $startTime) * 1000, 2);
    
    echo "<p style='color: green; font-size: 18px;'>✅ <strong>SUCCESS!</strong> Connected to Supabase successfully!</p>";
    echo "<p>Connection time: " . $connectionTime . " ms</p>";
    
    // Test query - get PostgreSQL version
    $stmt = $conn->query("SELECT version()");
    $version = $stmt->fetch();
    echo "<p><strong>PostgreSQL Version:</strong><br>" . $version['version'] . "</p>";
    
    // Test query - get current timestamp
    $stmt = $conn->query("SELECT NOW() as current_time");
    $time = $stmt->fetch();
    echo "<p><strong>Server Time:</strong> " . $time['current_time'] . "</p>";
    
    // List all tables in public schema
    $stmt = $conn->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll();
    
    echo "<p><strong>Tables in Database:</strong></p>";
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . $table['table_name'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ No tables found. You may need to create the database schema.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-size: 18px;'>❌ <strong>CONNECTION FAILED!</strong></p>";
    echo "<p><strong>Error Message:</strong></p>";
    echo "<pre style='background: #fee; padding: 10px; border: 1px solid #f00;'>" . $e->getMessage() . "</pre>";
    
    echo "<h3>🔧 Troubleshooting Tips:</h3>";
    echo "<ol>";
    echo "<li><strong>Check Password:</strong> Make sure you replaced <code>[YOUR-PASSWORD]</code> with your actual Supabase database password in <code>config/database.php</code></li>";
    echo "<li><strong>Check Host:</strong> Verify the host address is correct in your Supabase dashboard</li>";
    echo "<li><strong>Check Network:</strong> Ensure your internet connection is working</li>";
    echo "<li><strong>Enable pdo_pgsql:</strong> Make sure the PostgreSQL PDO extension is enabled in PHP</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Back to Homepage</a></p>";
?>
