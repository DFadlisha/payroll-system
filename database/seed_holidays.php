<?php
/**
 * Public Holidays Seeder (Improved)
 * Fetches Malaysian holidays from Nager.Date API or falls back to a comprehensive list.
 */

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../config/database.php';
}

function fetchAndSeedHolidays($year) {
    if (!function_exists('getConnection')) {
        require_once __DIR__ . '/../config/database.php';
    }
    $conn = getConnection();
    
    // Check if we already have holidays for this year to avoid spamming API
    $stmt = $conn->prepare("SELECT COUNT(*) FROM public_holidays WHERE EXTRACT(YEAR FROM holiday_date) = ?");
    $stmt->execute([$year]);
    if ($stmt->fetchColumn() > 5) { // If we have more than 5, assume seeded
        return;
    }
    
    // API URL
    $url = "https://date.nager.at/api/v3/PublicHolidays/{$year}/MY";
    
    $jsonData = null;
    
    // Try using curl first (more robust)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix for local dev SSL issues
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Reduced from 15s to prevent long delays
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); // Max 3s to establish connection
        curl_setopt($ch, CURLOPT_USERAGENT, 'MI-NES-Payroll/1.0');
        $jsonData = curl_exec($ch);
        // curl_close() is deprecated in PHP 8.0+ - cURL handle is auto-closed when $ch goes out of scope
    }
    
    // Fallback to file_get_contents if curl failed or not available
    if (!$jsonData) {
        $context = stream_context_create([
            'http' => ['timeout' => 5, 'header' => "User-Agent: MI-NES-Payroll/1.0\r\n"],
            'ssl' => ["verify_peer"=>false, "verify_peer_name"=>false]
        ]);
        $jsonData = @file_get_contents($url, false, $context);
    }
    
    $holidays = json_decode($jsonData ?? '', true);
    
    // Comprehensive Fallback if API fails (Hardcoded for 2025/2026 common dates)
    if (empty($holidays) || !is_array($holidays)) {
        $holidays = [];
        
        // Fixed Date Holidays
        $fixedHolidays = [
            '01-01' => "New Year's Day",
            '02-01' => "Federal Territory Day",
            '04-15' => "Declaration of Malacca as a Historical City",
            '05-01' => "Labour Day",
            '08-31' => "National Day",
            '09-16' => "Malaysia Day",
            '12-25' => "Christmas Day",
        ];
        
        foreach ($fixedHolidays as $date => $name) {
            $counties = null;
            if ($date == '02-01') $counties = ['KL', 'LBN', 'PJY'];
            if ($date == '04-15') $counties = ['MLK'];
            
            $holidays[] = ['date' => "$year-$date", 'localName' => $name, 'counties' => $counties];
        }
        
        // Variable Date Estimations (Better than nothing if offline)
        if ($year == 2026) {
             $holidays[] = ['date' => '2026-02-17', 'localName' => 'Chinese New Year'];
             $holidays[] = ['date' => '2026-02-18', 'localName' => 'Chinese New Year (Day 2)'];
             $holidays[] = ['date' => '2026-03-20', 'localName' => 'Hari Raya Aidilfitri'];
             $holidays[] = ['date' => '2026-03-21', 'localName' => 'Hari Raya Aidilfitri (Day 2)'];
             $holidays[] = ['date' => '2026-05-27', 'localName' => 'Hari Raya Haji']; // Approx
             $holidays[] = ['date' => '2026-11-08', 'localName' => 'Deepavali']; // Approx
             $holidays[] = ['date' => '2026-05-31', 'localName' => 'Wesak Day']; // Approx
        } elseif ($year == 2025) {
             $holidays[] = ['date' => '2025-01-29', 'localName' => 'Chinese New Year'];
             $holidays[] = ['date' => '2025-01-30', 'localName' => 'Chinese New Year (Day 2)'];
             $holidays[] = ['date' => '2025-03-31', 'localName' => 'Hari Raya Aidilfitri'];
             $holidays[] = ['date' => '2025-04-01', 'localName' => 'Hari Raya Aidilfitri (Day 2)'];
        }
    }
    
    foreach ($holidays as $h) {
        $date = $h['date'];
        $name = $h['localName'] ?? $h['name'];
        if (empty($name)) continue;
        
        $type = 'national';
        $stateCode = null;
        
        if (!empty($h['counties']) && is_array($h['counties'])) {
            $type = 'state-specific';
            $stateCode = implode(',', array_slice($h['counties'], 0, 5));
        }
        
        // Prepare statement (Upsert logic: Insert if not exists)
        $stmt = $conn->prepare("SELECT id FROM public_holidays WHERE holiday_date = ? AND holiday_name = ?");
        $stmt->execute([$date, $name]);
        
        if (!$stmt->fetch()) {
            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            
            $stmtInsert = $conn->prepare("
                INSERT INTO public_holidays (id, holiday_date, holiday_name, holiday_type, state_code, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, TRUE, NOW())
            ");
            $stmtInsert->execute([$uuid, $date, $name, $type, $stateCode]);
        }
    }
}
?>
