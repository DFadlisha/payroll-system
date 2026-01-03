<?php
require_once 'config/database.php';

try {
    $conn = getConnection();
    $stmt = $conn->query("SELECT full_name, role, employment_type, email FROM profiles");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Profiles:\n";
    print_r($users);

    session_start();
    echo "\nCurrent Session:\n";
    print_r($_SESSION);
} catch (Exception $e) {
    echo $e->getMessage();
}
