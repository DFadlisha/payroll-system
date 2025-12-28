<?php
require_once 'config/database.php';

try {
    $conn = getConnection();
    $conn->exec("ALTER TABLE companies ADD COLUMN IF NOT EXISTS address TEXT");
    $conn->exec("ALTER TABLE companies ADD COLUMN IF NOT EXISTS phone TEXT");
    $conn->exec("ALTER TABLE companies ADD COLUMN IF NOT EXISTS email TEXT");
    $conn->exec("ALTER TABLE companies ADD COLUMN IF NOT EXISTS website TEXT");
    $conn->exec("ALTER TABLE companies ADD COLUMN IF NOT EXISTS registration_number TEXT");

    // Update NES address
    $nessql = "UPDATE companies SET address = 'NO 23-1, JALAN PNBBU 5,BUKIT BARU, 75150 MELAKA', registration_number = '1545048-W' WHERE name ILIKE '%NES%'";
    $conn->exec($nessql);

    // Update Mentari address
    $misql = "UPDATE companies SET address = 'NO 16A, JALAN CEMPAKA 1, TAMAN SERI CEMPAKA, 75400, PERINGGIT, MELAKA', registration_number = '654321-B' WHERE name ILIKE '%Mentari%'";
    $conn->exec($misql);

    echo "Migration 006 completed.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
