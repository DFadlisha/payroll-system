<?php
/**
 * Import Employees from CSV
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
requireHR();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $companyId = $_SESSION['company_id'];
    $file = $_FILES['csv_file'];

    // Check file type
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        // Fallback: If user uploaded xlsx, we can't process it natively easily.
        // Alert user to save as CSV.
        echo "<script>alert('Please upload a CSV file. Excel (.xlsx) files should be Saved As CSV first.'); window.location.href='employees.php';</script>";
        exit;
    }

    $handle = fopen($file['tmp_name'], "r");
    if ($handle === FALSE) {
        echo "<script>alert('Error reading file.'); window.location.href='employees.php';</script>";
        exit;
    }

    $conn = getConnection();
    $successCount = 0;
    $errorCount = 0;
    $errors = [];

    // Header Mapping: We expect specific columns
    // Expected: Name, Email, Role, Basic Salary, Phone, IC
    $headers = fgetcsv($handle);
    // TODO: Verify headers or auto-map?
    // Let's assume fixed order for simplicity OR try to find index.
    // Fixed Order: Full Name, Email, Role, IC Number, Phone, Basic Salary

    while (($data = fgetcsv($handle)) !== FALSE) {
        // Skip empty rows
        if (empty($data[0]))
            continue;

        try {
            // Map data (Assuming Template Order)
            // 0: Full Name
            // 1: Email
            // 2: Role (staff, leader, intern, etc)
            // 3: IC Number
            // 4: Phone
            // 5: Basic Salary

            $fullName = sanitize($data[0] ?? '');
            $email = sanitize($data[1] ?? '');
            $role = strtolower(sanitize($data[2] ?? 'staff'));
            $icNumber = sanitize($data[3] ?? '');
            $phone = sanitize($data[4] ?? '');
            $salary = floatval(preg_replace('/[^0-9.]/', '', $data[5] ?? '0')); // remove currency symbols

            if (empty($email) || empty($fullName)) {
                $errorCount++;
                continue;
            }

            // Check if email exists
            $stmt = $conn->prepare("SELECT id FROM profiles WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errorCount++; // Duplicate
                $errors[] = "$email (Duplicate)";
                continue;
            }

            // Determine Employment Type from Role or Default
            $empType = 'permanent';
            if ($role === 'intern')
                $empType = 'intern';
            if ($role === 'part_time' || $role === 'part-time') {
                $role = 'part_time';
                $empType = 'part-time';
            }
            if ($role === 'leader')
                $empType = 'leader';

            // Password
            $rawPass = !empty($icNumber) ? $icNumber : 'Pass1234';
            $hashedPass = password_hash($rawPass, PASSWORD_DEFAULT);

            // UUID
            $uuid = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff)
            );

            // Insert
            $stmt = $conn->prepare("
                INSERT INTO profiles 
                (id, company_id, email, full_name, password, role, employment_type, ic_number, phone, basic_salary, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, true)
            ");
            $stmt->execute([
                $uuid,
                $companyId,
                $email,
                $fullName,
                $hashedPass,
                $role,
                $empType,
                $icNumber,
                $phone,
                $salary
            ]);

            $successCount++;

        } catch (Exception $e) {
            $errorCount++;
            $errors[] = "$email (Error: " . $e->getMessage() . ")";
        }
    }
    fclose($handle);

    $msg = "Imported $successCount employees successfully.";
    if ($errorCount > 0) {
        $msg .= " Failed: $errorCount.";
    }

    // Redirect with message
    header("Location: employees.php?msg=" . urlencode($msg));
    exit;

} else {
    // If accessed directly
    header("Location: employees.php");
    exit;
}
?>