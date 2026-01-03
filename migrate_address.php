<?php
require_once 'config/database.php';
try {
    $conn = getConnection();
    // Add address column if not exists
    $conn->exec("ALTER TABLE profiles ADD COLUMN IF NOT EXISTS address TEXT");
    echo "Added address column.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
