<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();

    $alterCommands = [
        "ADD COLUMN IF NOT EXISTS ic_number TEXT",
        "ADD COLUMN IF NOT EXISTS bank_name TEXT",
        "ADD COLUMN IF NOT EXISTS bank_account_number TEXT",
        "ADD COLUMN IF NOT EXISTS epf_number TEXT",
        "ADD COLUMN IF NOT EXISTS socso_number TEXT",
        "ADD COLUMN IF NOT EXISTS tax_number TEXT",
        "ADD COLUMN IF NOT EXISTS citizenship_status TEXT DEFAULT 'citizen'", // Adding this as it was used in code
        "ADD COLUMN IF NOT EXISTS dependents INTEGER DEFAULT 0"
    ];

    foreach ($alterCommands as $cmd) {
        $sql = "ALTER TABLE profiles $cmd";
        try {
            $conn->exec($sql);
            echo "Executed: $sql\n";
        } catch (PDOException $e) {
            echo "Error/Skipped $cmd: " . $e->getMessage() . "\n";
        }
    }

    echo "Migration 005 completed.\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
