<?php
/**
 * Attendance Seeding Script
 */

require_once __DIR__ . '/../config/database.php';
// Need these for calculation logic? Maybe just simple random times.

try {
    $conn = getConnection();
    
    echo "🌱 Seeding Attendance for this month...\n";
    
    // 1. Get all staff users
    $stmt = $conn->query("SELECT id, email, role, employment_type FROM profiles WHERE role != 'hr'");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        die("❌ No staff users found. Run seed_data.php first.\n");
    }
    
    // 2. Generate dates for current month until today
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-d'); // Today
    
    $current = strtotime($start_date);
    $end = strtotime($end_date);
    
    $dates = [];
    while ($current <= $end) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
    
    $count = 0;
    
    foreach ($users as $user) {
        echo "Processing user: {$user['email']} ({$user['employment_type']})\n";
        
        foreach ($dates as $date) {
            $dayOfWeek = date('N', strtotime($date)); // 1 (Mon) - 7 (Sun)
            
            // Skip weekends for most, unless part-time maybe? Let's just standard M-F for now
            if ($dayOfWeek >= 6) continue;
            
            // Random chance to be absent/leave (10%)
            if (rand(1, 100) <= 10) continue;
            
            // Generate random times
            // 8:30 AM to 9:30 AM clock in
            $clockInHour = rand(8, 9);
            $clockInMin = rand(0, 59);
            if ($clockInHour == 9 && $clockInMin > 30) $clockInMin = 30; // Max 9:30
            
            $clockIn = sprintf('%s %02d:%02d:00', $date, $clockInHour, $clockInMin);
            
            // 5:30 PM to 6:30 PM clock out
            $clockOutHour = rand(17, 18);
            $clockOutMin = rand(0, 59);
            if ($clockOutHour == 17 && $clockOutMin < 30) $clockOutMin = 30; // Min 5:30
            
            $clockOut = sprintf('%s %02d:%02d:00', $date, $clockOutHour, $clockOutMin);
            
            // Check if already exists
            $check = $conn->prepare("SELECT id FROM attendance WHERE user_id = ? AND DATE(clock_in) = ?");
            $check->execute([$user['id'], $date]);
            
            if (!$check->fetch()) {
                // Insert
                // Need valid photo hash and path to avoid breaking UI that expects them
                $stmt = $conn->prepare("
                    INSERT INTO attendance 
                    (user_id, clock_in, clock_out, status, gps_location, ip_address, device_info, clock_in_photo, photo_hash) 
                    VALUES (?, ?, ?, 'completed', '3.1412,101.6865', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0)', 'assets/logos/nes.jpg', 'fake_hash_123')
                ");
                $stmt->execute([$user['id'], $clockIn, $clockOut]);
                $count++;
            }
        }
    }
    
    echo "✅ Added $count attendance records.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
