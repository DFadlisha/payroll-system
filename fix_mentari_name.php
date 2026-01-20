<?php
require_once 'config/database.php';

try {
    $conn = getConnection();
    
    echo "Updating Mentari Company name...\n";
    
    $stmt = $conn->prepare("UPDATE companies SET name = 'MENTARI INFINITI SDN BHD' WHERE name LIKE '%MENTARI%'");
    $stmt->execute();
    
    echo "Mentari company name updated to MENTARI INFINITI SDN BHD.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
