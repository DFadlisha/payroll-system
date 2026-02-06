<?php
/**
 * ============================================
 * STAFF DASHBOARD
 * ============================================
 * Main dashboard for Staff users.
 * Display attendance, leave balance, and payroll summary.
 * ============================================
 */

$pageTitle = 'Dashboard - MI-NES Payroll';
require_once '../includes/header.php';
requireLogin();

// If HR, redirect to HR dashboard
if (isHR()) {
    redirect('../hr/dashboard.php');
}

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    $conn = getConnection();
    $currentMonth = date('n');
    $currentYear = date('Y');

    // Consolidated Query: Profile, Today's Attendance, Monthly Stats, and Latest Payslip
    // This reduces 4 network round-trips to 1, significantly improving performance for Cloud DBs.
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            (SELECT clock_in FROM attendance WHERE user_id = p.id AND DATE(clock_in) = ? LIMIT 1) as today_clock_in,
            (SELECT clock_out FROM attendance WHERE user_id = p.id AND DATE(clock_in) = ? LIMIT 1) as today_clock_out,
            (SELECT status FROM attendance WHERE user_id = p.id AND DATE(clock_in) = ? LIMIT 1) as today_status,
            (SELECT COUNT(*) FROM attendance  WHERE user_id = p.id AND EXTRACT(MONTH FROM clock_in) = ? AND EXTRACT(YEAR FROM clock_in) = ?) as total_days,
            (SELECT COUNT(*) FROM attendance WHERE user_id = p.id AND status = 'completed' AND EXTRACT(MONTH FROM clock_in) = ? AND EXTRACT(YEAR FROM clock_in) = ?) as present,
            (SELECT COUNT(*) FROM attendance WHERE user_id = p.id AND status = 'active' AND EXTRACT(MONTH FROM clock_in) = ? AND EXTRACT(YEAR FROM clock_in) = ?) as active,
            (SELECT COALESCE(SUM(total_hours), 0) FROM attendance WHERE user_id = p.id AND EXTRACT(MONTH FROM clock_in) = ? AND EXTRACT(YEAR FROM clock_in) = ?) as total_hours,
            (SELECT COALESCE(SUM(overtime_hours), 0) FROM attendance WHERE user_id = p.id AND EXTRACT(MONTH FROM clock_in) = ? AND EXTRACT(YEAR FROM clock_in) = ?) as overtime_hours,
            (SELECT net_pay FROM payroll WHERE user_id = p.id AND status = 'paid' ORDER BY year DESC, month DESC LIMIT 1) as latest_net_pay,
            (SELECT month FROM payroll WHERE user_id = p.id AND status = 'paid' ORDER BY year DESC, month DESC LIMIT 1) as latest_month,
            (SELECT year FROM payroll WHERE user_id = p.id AND status = 'paid' ORDER BY year DESC, month DESC LIMIT 1) as latest_year
        FROM profiles p
        WHERE p.id = ?
    ");
    
    $stmt->execute([
        $today, $today, $today,               // Attendance Check
        $currentMonth, $currentYear,          // total_days
        $currentMonth, $currentYear,          // present
        $currentMonth, $currentYear,          // active
        $currentMonth, $currentYear,          // total_hours
        $currentMonth, $currentYear,          // overtime_hours
        $userId                               // profiles.id
    ]);
    
    $dashboardData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dashboardData) {
        $user = $dashboardData;
        
        // Reconstruct expected variables for the UI
        $todayAttendance = $dashboardData['today_clock_in'] ? [
            'clock_in' => $dashboardData['today_clock_in'],
            'clock_out' => $dashboardData['today_clock_out'],
            'status' => $dashboardData['today_status']
        ] : null;
        
        $attendanceStats = [
            'total_days' => $dashboardData['total_days'],
            'present' => $dashboardData['present'],
            'active' => $dashboardData['active'],
            'total_hours' => $dashboardData['total_hours'],
            'overtime_hours' => $dashboardData['overtime_hours'],
            'attendance_percentage' => ($dashboardData['total_days'] > 0) 
                ? round(($dashboardData['present'] / $dashboardData['total_days']) * 100) 
                : 0,
            'absent' => 0
        ];
        
        $latestPayslip = $dashboardData['latest_net_pay'] ? [
            'net_pay' => $dashboardData['latest_net_pay'],
            'month' => $dashboardData['latest_month'],
            'year' => $dashboardData['latest_year']
        ] : null;
    }



} catch (PDOException $e) {
    error_log("Staff Dashboard error: " . $e->getMessage());
    $user = null;
    $todayAttendance = null;
    $attendanceStats = ['total_days' => 0, 'present' => 0, 'active' => 0, 'total_hours' => 0, 'overtime_hours' => 0, 'attendance_percentage' => 0];

    $latestPayslip = null;
}
?>

<?php include '../includes/staff_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <?php
    $navTitle = __('nav.dashboard');
    include '../includes/top_navbar.php';
    ?>

    <!-- Flash Messages -->
    <?php displayFlashMessage(); ?>

    <!-- Welcome Section -->
    <div class="row mb-5 align-items-center animate-fade-in">
        <div class="col-md-8">
            <h1 class="fw-bold text-dark mb-1">Welcome back, <?= htmlspecialchars($user['full_name']) ?>! 👋</h1>
            <p class="text-muted mb-0">Here's what's happening with your attendance today.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex align-items-center glass-card px-4 py-2 border-0">
                <i class="bi bi-calendar-event text-primary me-2"></i>
                <span class="fw-bold text-dark"><?= date('l, d M Y') ?></span>
            </div>
        </div>
    </div>

    <!-- Clock In/Out Hero Card -->
    <div class="card border-0 overflow-hidden position-relative mb-5 animate-fade-in shadow-sm"
        style="background: #ffffff; min-height: 220px; border-radius: 24px;">
        <!-- Decorative Shapes (Subtle Gray) -->
        <div class="position-absolute end-0 top-0 p-5 mt-n5 me-n5 rounded-circle bg-light opacity-50"
            style="width: 300px; height: 300px;"></div>

        <div class="card-body p-4 p-lg-5 position-relative">
            <div class="row align-items-center">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <?php if ($todayAttendance): ?>
                        <span class="badge bg-success-soft text-success mb-3 px-3 py-2 rounded-pill fw-bold border border-success border-opacity-10">
                            <i class="bi bi-patch-check-fill me-1"></i> Verified Arrival
                        </span>
                        <h2 class="display-6 fw-bold mb-2 text-dark">Clocked in at
                            <span class="text-primary"><?= formatTime($todayAttendance['clock_in']) ?></span>
                        </h2>
                        <?php if (!$todayAttendance['clock_out']): ?>
                            <p class="text-muted fs-5 mb-0">Have a productive day! Don't forget to clock out.</p>
                        <?php else: ?>
                            <p class="fs-5 mb-0 text-success">You've completed your shift. Great job!</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge bg-warning-soft text-warning mb-3 px-3 py-2 rounded-pill fw-bold border border-warning border-opacity-10">
                            <i class="bi bi-hourglass-split me-1"></i> Not Started
                        </span>
                        <h2 class="display-6 fw-bold mb-2 text-dark">Start your work day</h2>
                        <p class="text-muted fs-5 mb-0 opacity-75">Ready to clock in?</p>
                    <?php endif; ?>
                </div>

                <div class="col-lg-5 text-lg-end">
                    <?php if (!$todayAttendance): ?>
                        <a href="attendance.php" 
                           class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg hover-scale">
                            <i class="bi bi-fingerprint me-2"></i> Clock In Now
                        </a>
                    <?php elseif (!$todayAttendance['clock_out']): ?>
                        <a href="attendance.php" 
                           class="btn btn-outline-primary btn-lg rounded-pill px-5 py-3 fw-bold hover-scale">
                            <i class="bi bi-box-arrow-right me-2"></i> Clock Out
                        </a>
                    <?php else: ?>
                        <div class="bg-light p-4 rounded-4 text-center border">
                            <h3 class="fw-bold mb-1 text-success"><i class="bi bi-check-circle-fill me-2"></i>Completed</h3>
                            <p class="mb-0 text-muted">Shift Summary Available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <?php
    $role = $user['role'];
    $type = $user['employment_type'];

    if ($role === 'intern' || $type === 'intern') {
        include 'views/intern/dashboard.php';
    } elseif ($role === 'leader' || $type === 'leader') {
        include 'views/leader/dashboard.php';
    } elseif ($role === 'part_time' || $type === 'part_time') {
        include 'views/part_time/dashboard.php';
    } else {
        include 'views/permanent/dashboard.php';
    }
    ?>

    <!-- Content Split -->
    <div class="row g-4 mt-2 animate-fade-in">
        <!-- Recent Activity -->


        <!-- Quick Actions -->
        <div class="col-lg-4">
            <h5 class="fw-bold mb-3 ms-1 text-dark">Quick Access</h5>
            <div class="row g-3">

                <div class="col-6">
                    <a href="attendance.php" class="text-decoration-none">
                        <div class="card glass-card h-100 hover-lift border-0 text-center p-4">
                            <div class="rounded-circle d-inline-flex p-3 mb-3 mx-auto"
                                style="background:var(--card-green); color:var(--card-green-text);">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">My History</h6>
                            <small class="text-muted d-block">Attendance</small>
                        </div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="payslips.php" class="text-decoration-none">
                        <div class="card glass-card h-100 hover-lift border-0 text-center p-4">
                            <div class="rounded-circle d-inline-flex p-3 mb-3 mx-auto"
                                style="background:var(--card-blue); color:var(--card-blue-text);">
                                <i class="bi bi-file-earmark-text fs-4"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Payslips</h6>
                            <small class="text-muted d-block">View Salary</small>
                        </div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="profile.php" class="text-decoration-none">
                        <div class="card glass-card h-100 hover-lift border-0 text-center p-4">
                            <div class="rounded-circle d-inline-flex p-3 mb-3 mx-auto"
                                style="background:var(--card-orange); color:var(--card-orange-text);">
                                <i class="bi bi-person-gear fs-4"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Profile</h6>
                            <small class="text-muted d-block">Settings</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>
</div>