<?php
/**
 * ============================================
 * EXPORT REPORT HANDLER
 * ============================================
 * Generates CSV files for reports.
 * ============================================
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// Auth Check
if (!isLoggedIn() || !isHR()) {
    die("Access denied");
}

$type = $_GET['type'] ?? '';
$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');
$conn = getConnection();
$companyId = $_SESSION['company_id'];

// Filename and Data Setup
$filename = "report_export.csv";
$data = [];
$headers = [];

try {
    switch ($type) {
        case 'payroll_summary':
            $filename = "payroll_summary_{$year}_{$month}.csv";
            $headers = [
                'Employee Name',
                'IC Number',
                'Check ID',
                'Department/Role',
                'Employment Type',
                'Month',
                'Year',
                'Basic Salary (RM)',
                'OT Normal (RM)',
                'OT Sunday (RM)',
                'OT Public (RM)',
                'Gross Pay (RM)',
                'EPF Employee (RM)',
                'SOCSO Employee (RM)',
                'EIS Employee (RM)',
                'PCB Tax (RM)',
                'Net Pay (RM)',
                'Payment Status'
            ];

            $stmt = $conn->prepare("
                SELECT p.full_name, p.ic_number, p.role, p.employment_type,
                       pay.month, pay.year,
                       pay.basic_salary, pay.ot_normal, pay.ot_sunday, pay.ot_public,
                       pay.gross_pay,
                       pay.epf_employee, pay.socso_employee, pay.eis_employee, pay.pcb_tax,
                       pay.net_pay, pay.status
                FROM payroll pay
                JOIN profiles p ON pay.user_id = p.id
                WHERE p.company_id = ? AND pay.month = ? AND pay.year = ?
                ORDER BY p.full_name ASC
            ");
            $stmt->execute([$companyId, $month, $year]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'attendance_summary':
            $filename = "attendance_summary_{$year}_{$month}.csv";
            $headers = [
                'Employee Name',
                'Role',
                'Employment Type',
                'Days Present',
                'Days Late',
                'Total Late (Mins)',
                'Total Regular Hours',
                'Total OT Hours'
            ];

            // Aggregated Attendance Query
            $stmt = $conn->prepare("
                SELECT 
                    p.full_name, p.role, p.employment_type,
                    COUNT(a.id) as days_present,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as days_late,
                    SUM(COALESCE(a.late_minutes, 0)) as total_late_minutes,
                    SUM(COALESCE(a.total_hours, 0)) as total_hours,
                    SUM(COALESCE(a.overtime_hours, 0)) as total_ot
                FROM profiles p
                LEFT JOIN attendance a ON p.id = a.user_id 
                    AND EXTRACT(MONTH FROM a.clock_in) = ? 
                    AND EXTRACT(YEAR FROM a.clock_in) = ?
                WHERE p.company_id = ? AND p.is_active = TRUE
                GROUP BY p.id, p.full_name, p.role, p.employment_type
                ORDER BY p.full_name ASC
            ");
            $stmt->execute([$month, $year, $companyId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'employee_list':
            $filename = "employee_list_" . date('Ymd') . ".csv";
            $headers = [
                'Full Name',
                'IC Number',
                'Email',
                'Phone',
                'Role',
                'Employment Type',
                'Position',
                'Joined Date',
                'Basic Salary (RM)',
                'Bank Name',
                'Bank Acc No',
                'Status'
            ];

            $stmt = $conn->prepare("
                SELECT full_name, ic_number, email, phone,
                       role, employment_type, position,
                       joined_date, basic_salary, bank_name, bank_account_number,
                       CASE WHEN is_active THEN 'Active' ELSE 'Inactive' END as status
                FROM profiles
                WHERE company_id = ?
                ORDER BY full_name ASC
            ");
            $stmt->execute([$companyId]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        default:
            die("Invalid report type specified.");
    }

    // Generate CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fputs($output, "\xEF\xBB\xBF");

    // Write Headers
    fputcsv($output, $headers);

    // Write Data
    foreach ($data as $row) {
        // Optional: formatting for numbers if needed, but raw is better for Excel calc
        fputcsv($output, $row);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    die("Error generating report: " . $e->getMessage());
}
