<?php
require_once __DIR__ . '/../config/database.php';

try {
    $conn = getConnection();
    echo "🔍 Starting De-duplication...\n";
    
    // 1. Get all companies
    $stmt = $conn->query("SELECT id, name FROM companies ORDER BY created_at ASC"); // Keep oldest? Assuming created_at exists, else raw order
    
    // If created_at doesn't exist, this might fail, let's just use ID
    try {
         $stmt = $conn->query("SELECT id, name FROM companies ORDER BY id");
    } catch(Exception $e) {
         $stmt = $conn->query("SELECT id, name FROM companies");
    }
    
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $grouped = [];
    foreach ($companies as $c) {
        $normName = strtolower(trim($c['name']));
        if (!isset($grouped[$normName])) {
            $grouped[$normName] = [];
        }
        $grouped[$normName][] = $c;
    }
    
    foreach ($grouped as $name => $list) {
        if (count($list) > 1) {
            echo "Found duplicates for: '{$list[0]['name']}' (" . count($list) . " records)\n";
            
            // Keep the first one
            $master = $list[0];
            $masterId = $master['id'];
            echo " -> Keeping ID: $masterId\n";
            
            // Others to remove
            $removeIds = [];
            for ($i = 1; $i < count($list); $i++) {
                $removeIds[] = $list[$i]['id'];
            }
            
            // 2. Update profiles
            foreach ($removeIds as $badId) {
                echo " -> Moving users from $badId to $masterId...\n";
                $stmt = $conn->prepare("UPDATE profiles SET company_id = ? WHERE company_id = ?");
                $stmt->execute([$masterId, $badId]);
                
                // 3. Delete company
                echo " -> Deleting company ID: $badId\n";
                $stmt = $conn->prepare("DELETE FROM companies WHERE id = ?");
                $stmt->execute([$badId]);
            }
            echo "✅ Fixed duplicates for $name\n";
        } else {
            echo "No duplicates for: {$list[0]['name']}\n";
        }
    }
    
    echo "\n🎉 Cleanup Done.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
