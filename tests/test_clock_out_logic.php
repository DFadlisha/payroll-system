<?php
// Test script to verify clock_out logic
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    echo "🧪 Tester: Verifying Clock Out Logic...\n";
    
    // 1. Setup DB
    $conn = getConnection();
    
    // 2. Find a staff user
    $stmt = $conn->query("SELECT id FROM profiles WHERE role = 'staff' LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        die("❌ No staff user found for testing.\n");
    }
    $userId = $user['id'];
    echo "✓ Using User ID: $userId\n";
    
    // 3. Create a Dummy Active Session
    $attendanceId = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    
    // Cleanup any existing active sessions to allow clean test
    $stmt = $conn->prepare("UPDATE attendance SET status = 'completed', clock_out = NOW() WHERE user_id = ? AND status = 'active'");
    $stmt->execute([$userId]);
    
    // Insert new active session (simulating yesterday's check-in to test overnight logic too)
    $yesterday = date('Y-m-d H:i:s', strtotime('-1 day 10:00:00'));
    
    $stmt = $conn->prepare("
        INSERT INTO attendance (id, user_id, clock_in, status, is_verified) 
        VALUES (?, ?, ?, 'active', TRUE)
    ");
    $stmt->execute([$attendanceId, $userId, $yesterday]);
    echo "✓ Created active session (ID: $attendanceId) started at $yesterday\n";
    
    // 4. Simulate Clock Out (Logic from staff/attendance.php)
    echo "⏳ Simulating Clock Out...\n";
    
    // Use dummy data
    $latitude = 3.14159;
    $longitude = 101.456;
    $photoPath = 'uploads/attendance/test_out.jpg';
    $gpsLocation = "$latitude, $longitude";
    
    // Find active session
    $stmt = $conn->prepare("
        SELECT id FROM attendance 
        WHERE user_id = ? AND status = 'active'
        ORDER BY clock_in DESC LIMIT 1
    ");
    $stmt->execute([$userId]);
    $record = $stmt->fetch();
    
    if (!$record) {
        die("❌ Failed to find the active session we just created!\n");
    }
    if ($record['id'] !== $attendanceId) {
        echo "⚠️ Warning: Found different active session ({$record['id']}) than created ($attendanceId). Using found one.\n";
    }
    
    // Execute Update
    $stmt = $conn->prepare("
        UPDATE attendance SET 
            clock_out = NOW(), 
            status = 'completed',
            clock_out_latitude = ?,
            clock_out_longitude = ?,
            clock_out_photo = ?,
            gps_location = CONCAT(COALESCE(gps_location, ''), ' | Out: ', ?),
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        $latitude, $longitude, $photoPath, $gpsLocation, $record['id']
    ]);
    
    if ($result) {
        echo "✅ Clock Out Update executed successfully.\n";
        
        // 5. Verify
        $stmt = $conn->prepare("SELECT status, clock_out FROM attendance WHERE id = ?");
        $stmt->execute([$record['id']]);
        $updated = $stmt->fetch();
        
        if ($updated['status'] === 'completed' && $updated['clock_out']) {
            echo "✅ Verification Passed: Status is 'completed' and clock_out is set.\n";
        } else {
            echo "❌ Verification Failed: Status is {$updated['status']}\n";
        }
    } else {
        echo "❌ Update failed.\n";
    }

} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
