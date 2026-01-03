<?php
require_once 'config/database.php';

try {
    $conn = getConnection();
    $stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'profiles'");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in profiles table:\n";
    print_r($columns);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
