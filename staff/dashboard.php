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

    // Get Profile Data
    $stmt = $conn->prepare("SELECT * FROM profiles WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check Today's Attendance
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(clock_in) = ?");
    $stmt->execute([$userId, $today]);
    $todayAttendance = $stmt->fetch(PDO::FETCH_ASSOC);

    // Attendance Stats for Current Month
    $currentMonth = date('n');
    $currentYear = date('Y');
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            COALESCE(SUM(total_hours), 0) as total_hours,
            COALESCE(SUM(overtime_hours), 0) as overtime_hours
        FROM attendance 
        WHERE user_id = ? AND EXTRACT(MONTH FROM clock_in) = ? AND EXTRACT(YEAR FROM clock_in) = ?
    ");
    $stmt->execute([$userId, $currentMonth, $currentYear]);
    $attendanceStats = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate derived stats
    if ($attendanceStats) {
        $attendanceStats['attendance_percentage'] = ($attendanceStats['total_days'] > 0)
            ? round(($attendanceStats['present'] / $attendanceStats['total_days']) * 100)
            : 0;
        $attendanceStats['absent'] = 0; // Placeholder logic
    } else {
        $attendanceStats = [
            'total_days' => 0,
            'present' => 0,
            'active' => 0,
            'total_hours' => 0,
            'overtime_hours' => 0,
            'attendance_percentage' => 0,
            'absent' => 0
        ];
    }

    // Recent Leaves
    $stmt = $conn->prepare("
        SELECT * FROM leaves 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $recentLeaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Latest Payslip
    $stmt = $conn->prepare("
        SELECT * FROM payroll 
        WHERE user_id = ? AND status = 'paid'
        ORDER BY year DESC, month DESC 
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $latestPayslip = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Staff Dashboard error: " . $e->getMessage());
    $user = null;
    $todayAttendance = null;
    $attendanceStats = ['total_days' => 0, 'present' => 0, 'active' => 0, 'total_hours' => 0, 'overtime_hours' => 0];
    $recentLeaves = [];
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
    <div class="row mb-5 align-items-center">
        <div class="col-md-8">
            <h1 class="fw-bold text-dark mb-1">Welcome back, <?= htmlspecialchars($user['full_name']) ?>! 👋</h1>
            <p class="text-muted mb-0">Here's what's happening with your attendance today.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex align-items-center bg-white px-4 py-2 rounded-pill shadow-sm">
                <i class="bi bi-calendar-event text-primary me-2"></i>
                <span class="fw-bold text-dark"><?= date('l, d M Y') ?></span>
            </div>
        </div>
    </div>

    <!-- Clock In/Out Hero Card -->
    <div class="card border-0 text-white overflow-hidden position-relative mb-5"
        style="background: linear-gradient(135deg, #0F172A 0%, #334155 100%); min-height: 200px; box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 10px 10px -5px rgba(15, 23, 42, 0.04);">
        <!-- Decorative Shapes -->
        <div class="position-absolute end-0 top-0 p-5 mt-n5 me-n5 rounded-circle bg-white opacity-10"
            style="width: 300px; height: 300px; opacity: 0.05;"></div>
        <div class="position-absolute start-0 bottom-0 p-5 mb-n5 ms-n5 rounded-circle bg-white opacity-05"
            style="width: 200px; height: 200px; opacity: 0.05;"></div>

        <div class="card-body p-4 p-lg-5 position-relative">
            <div class="row align-items-center">
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <?php if ($todayAttendance): ?>
                        <span class="badge bg-white text-dark mb-3 px-3 py-2 rounded-pill fw-bold">
                            <i class="bi bi-geo-alt-fill text-success me-1"></i> Checked In
                        </span>
                        <h2 class="display-6 fw-bold mb-2 text-white">Clocked in at
                            <?= formatTime($todayAttendance['clock_in']) ?>
                        </h2>
                        <?php if (!$todayAttendance['clock_out']): ?>
                            <p class="text-white-50 fs-5 mb-0">Have a productive day! Don't forget to clock out.</p>
                        <?php else: ?>
                            <p class="text-emerald-300 fs-5 mb-0" style="color: #6EE7B7;">You've completed your shift. Great
                                job!</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill fw-bold">
                            <i class="bi bi-hourglass-split me-1"></i> Not Started
                        </span>
                        <h2 class="display-6 fw-bold mb-2 text-white">Start your work day</h2>
                        <p class="text-white-50 fs-5 mb-0">Ready to clock in?</p>
                    <?php endif; ?>
                </div>

                <div class="col-lg-5 text-lg-end">
                    <?php if (!$todayAttendance): ?>
                        <form method="POST" action="attendance.php">
                            <input type="hidden" name="action" value="clock_in">
                            <button type="submit"
                                class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold text-dark shadow-lg hover-scale">
                                <i class="bi bi-fingerprint me-2 text-primary"></i> Clock In Now
                            </button>
                        </form>
                    <?php elseif (!$todayAttendance['clock_out']): ?>
                        <form method="POST" action="attendance.php">
                            <input type="hidden" name="action" value="clock_out">
                            <button type="submit"
                                class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold hover-scale">
                                <i class="bi bi-box-arrow-right me-2"></i> Clock Out
                            </button>
                        </form>
                    <?php else: ?>
                        <div
                            class="bg-white bg-opacity-10 p-4 rounded-4 text-center backdrop-blur border border-white border-opacity-10">
                            <h3 class="fw-bold mb-1 text-white"><i
                                    class="bi bi-check-circle-fill text-success me-2"></i>Completed</h3>
                            <p class="mb-0 text-white-50">Shift Summary Available</p>
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
    <div class="row g-4 mt-2">
        <!-- Recent Activity -->
        <div class="col-lg-8">
            <div class="card h-100 border-0 shadow-sm">
                <div
                    class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom-0 pb-0">
                    <h5 class="fw-bold mb-0 text-dark">Recent Leaves</h5>
                    <a href="leaves.php" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3">View
                        All</a>
                </div>
                <div class="card-body pt-3">
                    <?php if (empty($recentLeaves)): ?>
                        <div class="text-center py-5">
                            <div class="bg-light rounded-circle d-inline-flex p-4 mb-3">
                                <i class="bi bi-brightness-high text-muted fs-1"></i>
                            </div>
                            <h6 class="fw-bold text-dark">No leave history</h6>
                            <p class="text-muted small">You haven't requested any time off recently.</p>
                            <a href="leaves.php?action=new" class="btn btn-sm btn-outline-primary rounded-pill mt-2">Apply
                                Now</a>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($recentLeaves as $leave):
                                $badge = getLeaveStatusBadge($leave['status']);
                                $leaveIcon = match ($leave['leave_type']) {
                                    'medical' => 'bi-bandaid',
                                    'annual' => 'bi-airplane',
                                    'emergency' => 'bi-exclamation-triangle',
                                    default => 'bi-calendar-check'
                                };
                                ?>
                                <div
                                    class="p-3 rounded-4 bg-light border-0 d-flex align-items-center justify-content-between transition-hover">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="bg-white p-2 rounded-circle shadow-sm text-primary d-flex align-items-center justify-content-center"
                                            style="width: 45px; height: 45px;">
                                            <i class="bi <?= $leaveIcon ?> fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0"><?= getLeaveTypeName($leave['leave_type']) ?>
                                            </h6>
                                            <small class="text-muted"><?= formatDate($leave['start_date']) ?> -
                                                <?= formatDate($leave['end_date']) ?></small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span
                                            class="badge <?= $badge['class'] ?> rounded-pill mb-1"><?= $badge['name'] ?></span>
                                        <div class="small fw-bold text-dark"><?= $leave['total_days'] ?> Days</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <h5 class="fw-bold mb-3 ms-1 text-dark">Quick Access</h5>
            <div class="row g-3">
                <div class="col-6">
                    <a href="leaves.php?action=new" class="text-decoration-none">
                        <div class="card h-100 hover-lift border-0 shadow-sm text-center p-4">
                            <div class="rounded-circle d-inline-flex p-3 mb-3"
                                style="background:var(--card-purple); color:var(--card-purple-text);">
                                <i class="bi bi-calendar-plus fs-4"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Apply Leave</h6>
                            <small class="text-muted d-block">Time off</small>
                        </div>
                    </a>
                </div>
                <div class="col-6">
                    <a href="attendance.php" class="text-decoration-none">
                        <div class="card h-100 hover-lift border-0 shadow-sm text-center p-4">
                            <div class="rounded-circle d-inline-flex p-3 mb-3"
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
                        <div class="card h-100 hover-lift border-0 shadow-sm text-center p-4">
                            <div class="rounded-circle d-inline-flex p-3 mb-3"
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
                        <div class="card h-100 hover-lift border-0 shadow-sm text-center p-4">
                            <div class="rounded-circle d-inline-flex p-3 mb-3"
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