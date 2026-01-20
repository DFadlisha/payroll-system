<?php
require_once 'config/database.php';
$conn = getConnection();
$stmt = $conn->query("SELECT name FROM companies");
foreach($stmt->fetchAll() as $row) {
    echo $row['name'] . "\n";
}
