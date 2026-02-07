<?php
/**
 * ============================================
 * ADD ADVANCE COLUMN TO PAYROLL TABLE
 * ============================================
 * Adds 'advance' column to track salary advances
 * that should be deducted from the payslip.
 * ============================================
 */

require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();
    echo "🔧 Adding 'advance' column to payroll table...\n";

    // Check if column exists
    $stmt = $conn->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'payroll' 
        AND column_name = 'advance'
    ");
    
    if ($stmt->rowCount() == 0) {
        // Column doesn't exist, add it
        $sql = "ALTER TABLE payroll ADD COLUMN advance DECIMAL(10, 2) DEFAULT 0.00";
        $conn->exec($sql);
        echo "  ✅ Column 'advance' added successfully.\n";
    } else {
        echo "  ℹ️  Column 'advance' already exists.\n";
    }

    echo "\n🎉 Migration complete.\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
