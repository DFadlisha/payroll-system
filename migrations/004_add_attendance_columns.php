<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();

    $alterCommands = [
        "ADD COLUMN IF NOT EXISTS late_minutes INTEGER DEFAULT 0",
        "ADD COLUMN IF NOT EXISTS project_hours INTEGER DEFAULT 0",
        "ADD COLUMN IF NOT EXISTS extra_shifts INTEGER DEFAULT 0",
        "ADD COLUMN IF NOT EXISTS ot_sunday_hours NUMERIC(5,2) DEFAULT 0",
        "ADD COLUMN IF NOT EXISTS ot_public_hours NUMERIC(5,2) DEFAULT 0",
        "ADD COLUMN IF NOT EXISTS ot_hours NUMERIC(5,2) DEFAULT 0"
    ];

    foreach ($alterCommands as $cmd) {
        // Postgres syntax: ALTER TABLE attendance ADD COLUMN ...
        // 'IF NOT EXISTS' is supported in recent Postgres.
        $sql = "ALTER TABLE attendance $cmd";
        try {
            $conn->exec($sql);
            echo "Executed: $sql\n";
        } catch (PDOException $e) {
            // Check if error is "column already exists" just in case IF NOT EXISTS fails on older versions
            if (strpos($e->getMessage(), 'cols') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                echo "Column already exists (skipped): $cmd\n";
            } else {
                echo "Error executing $cmd: " . $e->getMessage() . "\n";
            }
        }
    }

    // Also ensuring 'overtime_hours' exists as alias or distinct?
    // The codebase uses 'overtime_hours' in some places and 'ot_hours' in others.
    // 'ot_hours' seems to be the new standard in attendance.php (editable).
    // Let's ensure 'overtime_hours' exists too just in case.
    $conn->exec("ALTER TABLE attendance ADD COLUMN IF NOT EXISTS overtime_hours NUMERIC(5,2) DEFAULT 0");

    echo "Migration 004 completed.\n";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
