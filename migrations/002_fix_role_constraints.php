<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();

    // 1. Fix Role Constraint
    // Drop existing check constraint
    $conn->exec("ALTER TABLE profiles DROP CONSTRAINT IF EXISTS profiles_role_check");
    // Add new check constraint with all roles
    $conn->exec("ALTER TABLE profiles ADD CONSTRAINT profiles_role_check CHECK (role IN ('staff', 'hr', 'leader', 'part_time', 'intern'))");
    echo "Fixed profiles_role_check.<br>";

    // 2. Fix Employment Type Constraint
    // Drop existing
    $conn->exec("ALTER TABLE profiles DROP CONSTRAINT IF EXISTS profiles_employment_type_check");
    // Add new
    $conn->exec("ALTER TABLE profiles ADD CONSTRAINT profiles_employment_type_check CHECK (employment_type IN ('permanent', 'contract', 'part-time', 'intern', 'leader'))");
    echo "Fixed profiles_employment_type_check.<br>";

    // 3. Add internship_months column if it doesn't exist
    // Check if column exists
    $stmt = $conn->prepare("SELECT column_name FROM information_schema.columns WHERE table_name='profiles' AND column_name='internship_months'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $conn->exec("ALTER TABLE profiles ADD COLUMN internship_months INTEGER DEFAULT 0");
        echo "Added internship_months column.<br>";
    } else {
        echo "internship_months column already exists.<br>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
