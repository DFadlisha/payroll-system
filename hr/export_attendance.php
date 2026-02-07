<?php
/**
 * ============================================
 * EXPORT ATTENDANCE TO EXCEL (CSV)
 * ============================================
 * Generates Excel-compatible CSV files for attendance records.
 * Supports single date or date range exports.
 * ============================================
 */

require_once '../includes/header.php';
requireHR();

$conn = getConnection();
$companyId = $_SESSION['company_id'];

// Get export parameters
$exportType = $_GET['export_type'] ?? 'single_date';
$date = $_GET['date'] ?? date('Y-m-d');
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Filename and Data Setup
$filename = "attendance_export.csv";
$data = [];
$headers = [];

try {
    if ($exportType === 'single_date') {
        // Single Date Export
        $filename = "attendance_" . str_replace('-', '', $date) . ".csv";
        $headers = [
            'Employee Name',
            'IC Number',
            'Role',
            'Employment Type',
            'Date',
            'Clock In',
            'Clock Out',
            'Location',
            'GPS Location',
            'Total Hours',
            'OT Normal (Hours)',
            'OT Sunday (Hours)',
            'OT Public Holiday (Hours)',
            'Project Hours',
            'Extra Shifts',
            'Late Minutes',
            'Status'
        ];

        $stmt = $conn->prepare("
            SELECT 
                p.full_name,
                p.ic_number,
                p.role,
                p.employment_type,
                DATE(a.clock_in) as attendance_date,
                a.clock_in,
                a.clock_out,
                l.name as location_name,
                a.gps_location,
                a.total_hours,
                COALESCE(a.ot_hours, 0) as ot_hours,
                COALESCE(a.ot_sunday_hours, 0) as ot_sunday_hours,
                COALESCE(a.ot_public_hours, 0) as ot_public_hours,
                COALESCE(a.project_hours, 0) as project_hours,
                COALESCE(a.extra_shifts, 0) as extra_shifts,
                COALESCE(a.late_minutes, 0) as late_minutes,
                a.status
            FROM attendance a
            JOIN profiles p ON a.user_id = p.id
            LEFT JOIN work_locations l ON a.location_id = l.id
            WHERE p.company_id = ? AND DATE(a.clock_in) = ?
            ORDER BY p.full_name ASC, a.clock_in ASC
        ");
        $stmt->execute([$companyId, $date]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // Date Range Export
        $filename = "attendance_" . str_replace('-', '', $startDate) . "_to_" . str_replace('-', '', $endDate) . ".csv";
        $headers = [
            'Employee Name',
            'IC Number',
            'Role',
            'Employment Type',
            'Date',
            'Clock In',
            'Clock Out',
            'Location',
            'GPS Location',
            'Total Hours',
            'OT Normal (Hours)',
            'OT Sunday (Hours)',
            'OT Public Holiday (Hours)',
            'Project Hours',
            'Extra Shifts',
            'Late Minutes',
            'Status'
        ];

        $stmt = $conn->prepare("
            SELECT 
                p.full_name,
                p.ic_number,
                p.role,
                p.employment_type,
                DATE(a.clock_in) as attendance_date,
                a.clock_in,
                a.clock_out,
                l.name as location_name,
                a.gps_location,
                a.total_hours,
                COALESCE(a.ot_hours, 0) as ot_hours,
                COALESCE(a.ot_sunday_hours, 0) as ot_sunday_hours,
                COALESCE(a.ot_public_hours, 0) as ot_public_hours,
                COALESCE(a.project_hours, 0) as project_hours,
                COALESCE(a.extra_shifts, 0) as extra_shifts,
                COALESCE(a.late_minutes, 0) as late_minutes,
                a.status
            FROM attendance a
            JOIN profiles p ON a.user_id = p.id
            LEFT JOIN work_locations l ON a.location_id = l.id
            WHERE p.company_id = ? 
                AND DATE(a.clock_in) BETWEEN ? AND ?
            ORDER BY p.full_name ASC, a.clock_in ASC
        ");
        $stmt->execute([$companyId, $startDate, $endDate]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Generate CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fputs($output, "\xEF\xBB\xBF");

    // Write Headers
    fputcsv($output, $headers);

    // Write Data
    foreach ($data as $row) {
        // Format the data for better Excel compatibility
        $formattedRow = [
            $row['full_name'],
            $row['ic_number'],
            ucfirst($row['role']),
            ucfirst($row['employment_type']),
            $row['attendance_date'],
            $row['clock_in'] ? date('Y-m-d H:i:s', strtotime($row['clock_in'])) : '',
            $row['clock_out'] ? date('Y-m-d H:i:s', strtotime($row['clock_out'])) : '',
            $row['location_name'] ?? '',
            $row['gps_location'] ?? '',
            $row['total_hours'] ?? '0',
            $row['ot_hours'],
            $row['ot_sunday_hours'],
            $row['ot_public_hours'],
            $row['project_hours'],
            $row['extra_shifts'],
            $row['late_minutes'],
            ucfirst($row['status'])
        ];
        fputcsv($output, $formattedRow);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    error_log("Attendance export error: " . $e->getMessage());
    die("Error generating attendance export: " . $e->getMessage());
}
