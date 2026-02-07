<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();
    echo "🛠 Migrating Attendance Table...\n";

    $stmts = [
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS clock_out_latitude DECIMAL(10, 8)",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS clock_out_longitude DECIMAL(11, 8)",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS clock_out_photo TEXT",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS gps_location TEXT",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS clock_in_latitude DECIMAL(10, 8)",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS clock_in_longitude DECIMAL(11, 8)",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS clock_in_address TEXT",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS clock_in_photo TEXT",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS ip_address VARCHAR(45)",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS photo_hash VARCHAR(255)",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS is_verified BOOLEAN DEFAULT FALSE",
        "ALTER TABLE attendance ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'active'" // Ensure status exists
    ];

    foreach ($stmts as $sql) {
        $conn->exec($sql);
        echo "Executed: " . substr($sql, 0, 50) . "...\n";
    }

    echo "✅ Migration Complete.\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
