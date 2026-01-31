<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();
    
    // 1. List all companies
    echo "Current Companies in DB:\n";
    $stmt = $conn->query("SELECT id, name FROM companies");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($companies as $c) {
        echo "ID: {$c['id']} | Name: {$c['name']}\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
