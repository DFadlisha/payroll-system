<?php
/**
 * ============================================
 * STAFF LEAVES PAGE
 * ============================================
 * Page for leave applications.
 * - Staff: Annual, Medical, Emergency, Unpaid leave
 * - Intern: Medical Leave and NRL (Need Replacement Leave)
 *   NRL days = internship months (e.g., 3 months = 3 days); Medical default = 14 days
 * ============================================
 */

$pageTitle = 'Leave - MI-NES Payroll';
require_once '../includes/header.php';
requireLogin();

if (isHR()) {
    redirect('../hr/dashboard.php');
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$message = '';
$messageType = '';

// Get user details including employment_type and internship_months (Supabase schema)
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT employment_type, internship_months FROM profiles WHERE id = ?");
    $stmt->execute([$userId]);
    $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    $employmentType = $userDetails['employment_type'] ?? 'permanent';
    $internshipMonths = intval($userDetails['internship_months'] ?? 0);
} catch (PDOException $e) {
    $employmentType = 'permanent';
    $internshipMonths = 0;
}

$isIntern = ($employmentType === 'intern');
if ($isIntern && $internshipMonths <= 0) {
    $internshipMonths = 3; // Default to 3 months if not set
}

// Process leave application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_leave'])) {
    $leaveType = sanitize($_POST['leave_type'] ?? '');
    $startDate = sanitize($_POST['start_date'] ?? '');
    $endDate = sanitize($_POST['end_date'] ?? '');
    $reason = sanitize($_POST['reason'] ?? '');

    // Set valid leave types depending on employment type
    if ($isIntern) {
        // Interns: only Medical and NRL (Need Replacement Leave)
        $validLeaveTypes = ['medical', 'nrl'];
    } else {
        // Staff/Part-time: Annual, Medical, Emergency, Unpaid, Other
        $validLeaveTypes = ['annual', 'medical', 'emergency', 'unpaid', 'other'];
    }

    if (empty($leaveType) || empty($startDate) || empty($endDate)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif (!in_array($leaveType, $validLeaveTypes)) {
        $message = 'Invalid leave type selected.';
        $messageType = 'error';
    } elseif (strtotime($endDate) < strtotime($startDate)) {
        $message = 'End date cannot be before start date.';
        $messageType = 'error';
    } elseif (strtotime($startDate) < strtotime(date('Y-m-d'))) {
        $message = 'Start date cannot be in the past.';
        $messageType = 'error';
    } else {
        // Calculate requested total days
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        // Exclude weekends if you want business days only? 
        // For now, simple diff + 1 (includes weekends)
        $requestedDays = $end->diff($start)->days + 1;

        // Enforce Leave Balance Limits
        $balanceError = false;

        if ($isIntern && $leaveType === 'nrl') {
            $available = $leaveBalance['nrl']['total'] - $leaveBalance['nrl']['used'];
            if ($requestedDays > $available) {
                $message = "Insufficient NRL balance. You have {$available} days remaining (Based on {$internshipMonths} months internship).";
                $balanceError = true;
            }
        } elseif (!$isIntern && $leaveType === 'annual') {
            $available = $leaveBalance['annual']['total'] - $leaveBalance['annual']['used'];
            if ($requestedDays > $available) {
                $message = "Insufficient Annual Leave balance. You have {$available} days remaining.";
                $balanceError = true;
            }
        }

        if ($balanceError) {
            $messageType = 'error';
        } else {
            // Proceed with database insertion
            try {
                $leaveUuid = sprintf(
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

                // Insert into leaves table (Supabase schema)
                $stmt = $conn->prepare("
                INSERT INTO leaves (id, user_id, leave_type, start_date, end_date, days, reason, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
                $stmt->execute([$leaveUuid, $userId, $leaveType, $startDate, $endDate, $totalDays, $reason]);

                $message = 'Leave application submitted successfully. Waiting for HR approval.';
                $messageType = 'success';
                $action = ''; // Reset to list view

            } catch (PDOException $e) {
                error_log("Leave application error: " . $e->getMessage());
                $message = 'System error. Please try again.';
                $messageType = 'error';
            }
        } // End balance check else
    }
}

// Get leave history
try {
    $conn = getConnection();

    $stmt = $conn->prepare("
        SELECT * FROM leaves 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate leave balance based on role
    $currentYear = date('Y');

    if ($isIntern) {
        // Intern: NRL balance = internship months, plus Medical leave entitlement
        $stmt = $conn->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN leave_type = 'nrl' AND status = 'approved' THEN days ELSE 0 END),0) as nrl_used,
                COALESCE(SUM(CASE WHEN leave_type = 'medical' AND status = 'approved' THEN days ELSE 0 END),0) as medical_used
            FROM leaves 
            WHERE user_id = ? AND EXTRACT(YEAR FROM start_date) = ?
        ");
        $stmt->execute([$userId, $currentYear]);
        $used = $stmt->fetch(PDO::FETCH_ASSOC);
        $nrlUsed = $used['nrl_used'] ?? 0;
        $medicalUsed = $used['medical_used'] ?? 0;

        // Medical entitlement for interns: default to 14 days (same as staff)
        $medicalTotal = 14;

        $leaveBalance = [
            'nrl' => ['total' => $internshipMonths, 'used' => $nrlUsed],
            'medical' => ['total' => $medicalTotal, 'used' => $medicalUsed]
        ];
    } else {
        // Staff/Part-time: Annual & Medical leave
        $stmt = $conn->prepare("
            SELECT 
                SUM(CASE WHEN leave_type = 'annual' AND status = 'approved' THEN days ELSE 0 END) as annual_used,
                SUM(CASE WHEN leave_type = 'medical' AND status = 'approved' THEN days ELSE 0 END) as medical_used
            FROM leaves 
            WHERE user_id = ? AND EXTRACT(YEAR FROM start_date) = ?
        ");
        $stmt->execute([$userId, $currentYear]);
        $leaveUsed = $stmt->fetch(PDO::FETCH_ASSOC);

        $leaveBalance = [
            'annual' => ['total' => 14, 'used' => $leaveUsed['annual_used'] ?? 0],
            'medical' => ['total' => 14, 'used' => $leaveUsed['medical_used'] ?? 0]
        ];
    }

} catch (PDOException $e) {
    error_log("Leave fetch error: " . $e->getMessage());
    $leaves = [];
    if ($isIntern) {
        // Fallback include both nrl and medical with defaults
        $leaveBalance = [
            'nrl' => ['total' => $internshipMonths, 'used' => 0],
            'medical' => ['total' => 14, 'used' => 0]
        ];
    } else {
        $leaveBalance = [
            'annual' => ['total' => 14, 'used' => 0],
            'medical' => ['total' => 14, 'used' => 0]
        ];
    }
}
?>

<?php include '../includes/staff_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <?php
    $navTitle = __('nav.leaves');
    include '../includes/top_navbar.php';

    // Router for leaves views
    // $employmentType is already fetched at top of file
    
    if ($isIntern) {
        include 'views/intern/leaves.php';
    } elseif ($employmentType === 'leader') {
        include 'views/leader/leaves.php';
    } elseif ($employmentType === 'part_time') {
        include 'views/part_time/leaves.php';
    } else {
        // Default to permanent view for staff/full_time
        include 'views/permanent/leaves.php';
    }
    ?>
</div>

<script>
    // Auto-calculate end date based on start date
    document.querySelector('input[name="start_date"]')?.addEventListener('change', function () {
        const endDateInput = document.querySelector('input[name="end_date"]');
        if (endDateInput && !endDateInput.value) {
            endDateInput.value = this.value;
        }
        endDateInput.min = this.value;
    });
</script>

<?php require_once '../includes/footer.php'; ?>