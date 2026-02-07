<?php
// Initialize Database Schema
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();
    echo "🛠 Initializing Database Schema...\n";

    // 1. Attendance Table
    echo "- Creating 'attendance' table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS attendance (
        id VARCHAR(36) PRIMARY KEY,
        user_id VARCHAR(36) NOT NULL,
        clock_in TIMESTAMP,
        clock_out TIMESTAMP,
        status VARCHAR(20) DEFAULT 'active',
        clock_in_latitude DECIMAL(10, 8),
        clock_in_longitude DECIMAL(11, 8),
        clock_in_address TEXT,
        clock_in_photo TEXT,
        clock_out_latitude DECIMAL(10, 8),
        clock_out_longitude DECIMAL(11, 8),
        clock_out_photo TEXT,
        gps_location TEXT,
        ip_address VARCHAR(45),
        photo_hash VARCHAR(255),
        device_info TEXT,
        is_verified BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "  ✅ Done.\n";

    // 2. Leaves Table (to fix 'relation leaves does not exist' error)
    echo "- Creating 'leaves' table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS leaves (
        id VARCHAR(36) PRIMARY KEY,
        user_id VARCHAR(36) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        type VARCHAR(50),
        reason TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        rejection_reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "  ✅ Done.\n";

    // 3. Payroll Table (likely needed)
    echo "- Creating 'payroll' table...\n";
    $sql = "CREATE TABLE IF NOT EXISTS payroll (
        id VARCHAR(36) PRIMARY KEY,
        user_id VARCHAR(36) NOT NULL,
        month INT NOT NULL,
        year INT NOT NULL,
        basic_salary DECIMAL(10, 2),
        total_earnings DECIMAL(10, 2),
        total_deductions DECIMAL(10, 2),
        net_pay DECIMAL(10, 2),
        status VARCHAR(20) DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "  ✅ Done.\n";

    echo "\n🎉 Database initialization complete.\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
