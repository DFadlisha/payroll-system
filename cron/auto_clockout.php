<?php
/**
 * AUTO CLOCK-OUT FORGOTTEN STAFF
 * Run this daily at 11:59 PM
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $conn = getConnection();

    // Find all active attendance from previous days
    $stmt = $conn->prepare("
        SELECT a.id, a.user_id, a.clock_in, p.full_name, p.email
        FROM attendance a
        JOIN profiles p ON a.user_id = p.id
        WHERE DATE(a.clock_in) < CURRENT_DATE 
        AND a.clock_out IS NULL 
        AND a.status = 'active'
    ");
    $stmt->execute();
    $forgotten = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($forgotten as $record) {
        // Auto clock out at 9 hours after clock in
        $clockIn = new DateTime($record['clock_in']);
        $clockOut = clone $clockIn;
        $clockOut->modify('+9 hours');

        $totalHours = 9.0;
        $overtimeHours = 0.0;

        // Update attendance
        $stmt = $conn->prepare("
            UPDATE attendance SET 
                clock_out = ?,
                status = 'completed',
                total_hours = ?,
                overtime_hours = ?,
                is_verified = FALSE,
                verification_notes = 'Auto clocked out by system - Please verify with HR'
            WHERE id = ?
        ");
        $stmt->execute([$clockOut->format('Y-m-d H:i:s'), $totalHours, $overtimeHours, $record['id']]);

        // Send warning email via helper function if available
        if (function_exists('sendEmail')) {
            sendEmail(
                $record['email'],
                '⚠️ Auto Clock-Out - Action Required',
                "
                <p>Hi {$record['full_name']},</p>
                <p>You forgot to clock out on " . date('d/m/Y', strtotime($record['clock_in'])) . ".</p>
                <p>The system has automatically clocked you out at " . $clockOut->format('h:i A') . ".</p>
                <p><strong>Please verify your attendance with HR to avoid salary deductions.</strong></p>
                "
            );
        }
    }

    echo "Auto clock-out completed. Processed: " . count($forgotten) . " records.\n";

} catch (Exception $e) {
    echo "Auto clock-out error: " . $e->getMessage() . "\n";
    error_log("Auto clock-out error: " . $e->getMessage());
}
?>