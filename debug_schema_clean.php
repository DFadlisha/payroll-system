<?php
require_once 'config/database.php';
$conn = getConnection();
$stmt = $conn->query("SELECT column_name FROM information_schema.columns WHERE table_name = 'profiles' ORDER BY column_name");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Clean Columns:\n" . implode("\n", $cols);
