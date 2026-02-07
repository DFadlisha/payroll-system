<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();
    $stmt = $conn->prepare("
        SELECT column_name, data_type 
        FROM information_schema.columns 
        WHERE table_name = 'attendance'
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columns in 'attendance':\n";
    foreach ($columns as $col) {
        echo "- {$col['column_name']} ({$col['data_type']})\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
