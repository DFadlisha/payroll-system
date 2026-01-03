<?php
/**
 * ============================================
 * STAFF PAYSLIPS PAGE
 * ============================================
 * Halaman untuk lihat slip gaji.
 * ============================================
 */

$pageTitle = 'Payslips - MI-NES Payroll';
require_once '../includes/header.php';
requireLogin();

if (isHR()) {
    redirect('../hr/dashboard.php');
}

$userId = $_SESSION['user_id'];
$viewId = $_GET['id'] ?? null;

try {
    $conn = getConnection();

    // Get payslip history (Supabase schema - status: draft, finalized, paid)
    $stmt = $conn->prepare("
        SELECT * FROM payroll 
        WHERE user_id = ? AND status IN ('finalized', 'paid')
        ORDER BY year DESC, month DESC
    ");
    $stmt->execute([$userId]);
    $payslips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If viewing specific payslip
    $currentPayslip = null;
    if ($viewId) {
        $stmt = $conn->prepare("
            SELECT p.*, pr.full_name, pr.epf_number, pr.socso_number, pr.employment_type, pr.basic_salary as profile_salary
            FROM payroll p
            JOIN profiles pr ON p.user_id = pr.id
            WHERE p.id = ? AND p.user_id = ?
        ");
        $stmt->execute([$viewId, $userId]);
        $currentPayslip = $stmt->fetch(PDO::FETCH_ASSOC);

        // Normalize keys to match view expectations
        if ($currentPayslip) {
            $currentPayslip['gross_salary'] = $currentPayslip['gross_pay'] ?? 0;
            $currentPayslip['net_salary'] = $currentPayslip['net_pay'] ?? 0;
            $currentPayslip['overtime_pay'] = ($currentPayslip['ot_normal'] ?? 0) + ($currentPayslip['ot_sunday'] ?? 0) + ($currentPayslip['ot_public'] ?? 0);
            $currentPayslip['overtime_hours'] = ($currentPayslip['ot_normal_hours'] ?? 0) + ($currentPayslip['ot_sunday_hours'] ?? 0) + ($currentPayslip['ot_public_hours'] ?? 0);
            $currentPayslip['allowances'] = $currentPayslip['allowances'] ?? 0; // Column might not exist
            $currentPayslip['bonus'] = $currentPayslip['bonus'] ?? 0;
            $currentPayslip['other_deductions'] = $currentPayslip['other_deductions'] ?? 0;
            $currentPayslip['total_deductions'] = ($currentPayslip['epf_employee'] ?? 0) + ($currentPayslip['socso_employee'] ?? 0) + ($currentPayslip['eis_employee'] ?? 0) + ($currentPayslip['pcb_tax'] ?? 0) + $currentPayslip['other_deductions'];
        }
    }

} catch (PDOException $e) {
    error_log("Payslip fetch error: " . $e->getMessage());
    $payslips = [];
    $currentPayslip = null;
}
?>

<?php include '../includes/staff_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <?php
    $navTitle = __('nav.payslips');
    include '../includes/top_navbar.php';

    // Router for payslips view
    // $user is available from staff_sidebar.php
    $role = strtolower($user['role'] ?? '');
    $type = strtolower($user['employment_type'] ?? '');

    // Normalize types (e.g. 'part-time' to 'part_time' to match folder names)
    if ($type === 'part-time')
        $type = 'part_time';
    if ($role === 'part-time')
        $role = 'part_time';

    if ($role === 'intern' || $type === 'intern') {
        include 'views/intern/payslips.php';
    } elseif ($type === 'leader' || $role === 'leader') {
        include 'views/leader/payslips.php';
    } elseif ($type === 'part_time' || $role === 'part_time') {
        include 'views/part_time/payslips.php';
    } else {
        include 'views/permanent/payslips.php';
    }
    ?>

    <style>
        @media print {

            .sidebar,
            .top-navbar,
            .page-header,
            .d-print-none {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
            }
        }
    </style>

    <?php require_once '../includes/footer.php'; ?>