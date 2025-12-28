<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();

    echo "Updating schema...\n";

    // 1. Add columns to attendance table
    // PostgreSQL syntax for adding columns if they don't exist is a bit verbose in pure SQL without a specific function,
    // but we can just try adding them and ignore errors or check information_schema. 
    // Since this is a helper script, we'll execute them one by one.

    $statements = [
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS clock_in_photo TEXT",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS clock_out_photo TEXT",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS is_verified BOOLEAN DEFAULT FALSE",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS verification_notes TEXT"
    ];

    foreach ($statements as $sql) {
        try {
            $conn->exec($sql);
            echo "Executed: $sql\n";
        } catch (PDOException $e) {
            echo "Note: " . $e->getMessage() . "\n";
        }
    }

    echo "Schema update completed.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>