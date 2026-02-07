<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();
    echo "🛠 Migrating Attendance Table (Phase 2)...\n";

    $stmts = [
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
    ];

    foreach ($stmts as $sql) {
        $conn->exec($sql);
        echo "Executed: $sql\n";
    }

    echo "✅ Migration Phase 2 Complete.\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
