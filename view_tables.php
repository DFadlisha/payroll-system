<?php
/**
 * Database Table Viewer
 * MI-NES Payroll System
 * 
 * View all tables and their structure in the database
 */

echo "<h2>📊 Database Table Viewer</h2>";
echo "<hr>";

require_once 'config/database.php';

try {
    $conn = getConnection();
    echo "<p style='color: green;'>✅ Connected to database successfully!</p>";
    
    // Get all tables in public schema
    echo "<h3>📋 Tables in Database:</h3>";
    $stmt = $conn->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p style='color: orange;'>⚠️ No tables found in database. You need to create the schema first.</p>";
        echo "<p>Run the SQL schema in your Supabase SQL Editor.</p>";
    } else {
        echo "<p>Found <strong>" . count($tables) . "</strong> table(s):</p>";
        
        // Show each table with its structure
        foreach ($tables as $table) {
            echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 20px;'>";
            echo "<h4 style='margin-top: 0; color: #0d6efd;'>📁 {$table}</h4>";
            
            // Get columns
            $stmt = $conn->prepare("
                SELECT column_name, data_type, is_nullable, column_default
                FROM information_schema.columns 
                WHERE table_schema = 'public' AND table_name = ?
                ORDER BY ordinal_position
            ");
            $stmt->execute([$table]);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table style='width: 100%; border-collapse: collapse; font-size: 14px;'>";
            echo "<thead style='background: #e9ecef;'>";
            echo "<tr>";
            echo "<th style='padding: 8px; text-align: left; border: 1px solid #dee2e6;'>Column</th>";
            echo "<th style='padding: 8px; text-align: left; border: 1px solid #dee2e6;'>Type</th>";
            echo "<th style='padding: 8px; text-align: left; border: 1px solid #dee2e6;'>Nullable</th>";
            echo "<th style='padding: 8px; text-align: left; border: 1px solid #dee2e6;'>Default</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td style='padding: 8px; border: 1px solid #dee2e6;'><strong>{$col['column_name']}</strong></td>";
                echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>{$col['data_type']}</td>";
                echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>{$col['is_nullable']}</td>";
                echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>" . ($col['column_default'] ?? '-') . "</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table>";
            
            // Get row count
            $stmt = $conn->query("SELECT COUNT(*) FROM \"{$table}\"");
            $count = $stmt->fetchColumn();
            echo "<p style='margin-bottom: 0;'><strong>Total rows:</strong> {$count}</p>";
            
            echo "</div>";
        }
        
        // Show sample data from users table if exists
        if (in_array('users', $tables)) {
            echo "<h3>👥 Sample Data from Users Table:</h3>";
            $stmt = $conn->query("SELECT id, full_name, email, role, is_active FROM users LIMIT 10");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($users)) {
                echo "<table style='width: 100%; border-collapse: collapse;'>";
                echo "<thead style='background: #0d6efd; color: white;'>";
                echo "<tr>";
                echo "<th style='padding: 10px; border: 1px solid #dee2e6;'>ID</th>";
                echo "<th style='padding: 10px; border: 1px solid #dee2e6;'>Name</th>";
                echo "<th style='padding: 10px; border: 1px solid #dee2e6;'>Email</th>";
                echo "<th style='padding: 10px; border: 1px solid #dee2e6;'>Role</th>";
                echo "<th style='padding: 10px; border: 1px solid #dee2e6;'>Active</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>{$user['id']}</td>";
                    echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>{$user['full_name']}</td>";
                    echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>{$user['email']}</td>";
                    echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>{$user['role']}</td>";
                    echo "<td style='padding: 8px; border: 1px solid #dee2e6;'>" . ($user['is_active'] ? 'Yes' : 'No') . "</td>";
                    echo "</tr>";
                }
                
                echo "</tbody></table>";
            } else {
                echo "<p>No users found.</p>";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Connection failed!</p>";
    echo "<pre style='background: #fee; padding: 10px;'>" . $e->getMessage() . "</pre>";
}

echo "<hr>";
echo "<h3>🔗 Other Options to View Database:</h3>";
echo "<ol>";
echo "<li><strong>Supabase Dashboard:</strong> Go to <a href='https://supabase.com/dashboard' target='_blank'>supabase.com/dashboard</a> → Your Project → Table Editor</li>";
echo "<li><strong>SQL Editor:</strong> Supabase Dashboard → SQL Editor → Run queries</li>";
echo "</ol>";

echo "<p><a href='index.php'>← Back to Homepage</a> | <a href='test_connection.php'>Test Connection</a></p>";
?>
