<?php
/**
 * ============================================
 * HR DASHBOARD
 * ============================================
 * Main dashboard for HR users.
 * Display summary of attendance, leaves, and payroll.
 * ============================================
 */

require_once '../includes/header.php';
requireHR();
$pageTitle = 'Dashboard - MI-NES Payroll System';

// Get Statistics (using Supabase schema)
try {
    $conn = getConnection();
    $companyId = $_SESSION['company_id'];

    // 1. Total Employees
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM profiles WHERE company_id = ? AND is_active = TRUE");
    $stmt->execute([$companyId]);
    $totalEmployees = $stmt->fetch()['total'];

    // 2. Attendance Today
    $today = date('Y-m-d');
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM attendance a 
        JOIN profiles p ON a.user_id = p.id 
        WHERE p.company_id = ? AND DATE(a.clock_in) = ?
    ");
    $stmt->execute([$companyId, $today]);
    $todayAttendance = $stmt->fetch()['total'];

    // 3. Pending Leave Requests
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM leaves l 
        JOIN profiles p ON l.user_id = p.id 
        WHERE p.company_id = ? AND l.status = 'pending'
    ");
    $stmt->execute([$companyId]);
    $pendingLeaves = $stmt->fetch()['total'];

    // 4. Pending Payroll (Current Month)
    $currentMonth = date('n');
    $currentYear = date('Y');
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM payroll pay 
        JOIN profiles p ON pay.user_id = p.id 
        WHERE p.company_id = ? AND pay.month = ? AND pay.year = ? AND pay.status != 'paid'
    ");
    $stmt->execute([$companyId, $currentMonth, $currentYear]);
    $unpaidPayroll = $stmt->fetch()['total'];

    // 5. Recent Attendance List
    $stmt = $conn->prepare("
        SELECT p.full_name, p.role, a.clock_in, a.clock_out, a.status, a.clock_in_photo
        FROM attendance a 
        JOIN profiles p ON a.user_id = p.id 
        WHERE p.company_id = ? AND DATE(a.clock_in) = ?
        ORDER BY a.clock_in DESC
        LIMIT 5
    ");
    $stmt->execute([$companyId, $today]);
    $todayAttendanceList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Recent Pending Leaves
    $stmt = $conn->prepare("
        SELECT l.*, p.full_name, p.role 
        FROM leaves l 
        JOIN profiles p ON l.user_id = p.id 
        WHERE p.company_id = ? AND l.status = 'pending'
        ORDER BY l.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$companyId]);
    $recentLeaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalEmployees = $todayAttendance = $pendingLeaves = $unpaidPayroll = 0;
    $todayAttendanceList = $recentLeaves = [];
}
?>

<?php include '../includes/hr_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <?php
    $navTitle = __('nav.dashboard');
    include '../includes/top_navbar.php';
    ?>

    <!-- Flash Messages -->
    <?php displayFlashMessage(); ?>

    <!-- Welcome Header -->
    <div class="mb-4">
        <p class="text-muted mb-1">Overview</p>
        <h2 class="fw-bold">HR Management</h2>
        <div class="d-flex align-items-center mt-2 text-muted">
            <i class="bi bi-calendar3 me-2"></i> <?= date('l, d F Y') ?>
        </div>
    </div>

    <!-- Stats Cards (Pastel Design) -->
    <div class="row g-4 mb-5">
        <!-- Total Employees (Purple) -->
        <div class="col-md-6 col-lg-3">
            <div class="stats-card purple">
                <div class="stats-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <p>Total Employees</p>
                    <h2><?= $totalEmployees ?></h2>
                </div>
            </div>
        </div>

        <!-- Present Today (Green) -->
        <div class="col-md-6 col-lg-3">
            <div class="stats-card green">
                <div class="stats-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div>
                    <p>Present Today</p>
                    <h2><?= $todayAttendance ?></h2>
                </div>
            </div>
        </div>

        <!-- Pending Leaves (Orange) -->
        <div class="col-md-6 col-lg-3">
            <div class="stats-card orange">
                <div class="stats-icon">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </div>
                <div>
                    <p>Leave Requests</p>
                    <h2><?= $pendingLeaves ?></h2>
                </div>
            </div>
        </div>

        <!-- Payroll Pending (Blue) -->
        <div class="col-md-6 col-lg-3">
            <div class="stats-card blue">
                <div class="stats-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <p>Pending Payroll</p>
                    <h2><?= $unpaidPayroll ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Split -->
    <div class="row g-4 mb-5">
        <!-- Recent Attendance -->
        <div class="col-lg-8">
            <div class="card h-100 border-0 shadow-sm">
                <div
                    class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center border-bottom-0 pb-0">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Live
                        Attendance</h5>
                    <a href="attendance.php" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3">View
                        All</a>
                </div>
                <div class="card-body p-0 pt-3">
                    <?php if (empty($todayAttendanceList)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-cup-hot text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">No attendance records today yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead style="background: #F8FAFC;">
                                    <tr>
                                        <th class="ps-4 text-uppercase text-secondary small fw-bold">Employee</th>
                                        <th class="text-uppercase text-secondary small fw-bold">Clock In</th>
                                        <th class="text-uppercase text-secondary small fw-bold">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todayAttendanceList as $att): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar-sm me-3 bg-white border shadow-sm text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width: 40px; height: 40px; font-size: 0.9rem;">
                                                        <?= strtoupper(substr($att['full_name'], 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark">
                                                            <?= htmlspecialchars($att['full_name']) ?>
                                                        </div>
                                                        <small class="text-muted"
                                                            style="font-size: 0.75rem;"><?= ucwords(str_replace('_', ' ', $att['role'])) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= formatTime($att['clock_in']) ?></div>
                                                <?php if ($att['clock_in_photo']): ?>
                                                    <small class="text-success"><i class="bi bi-check-all"></i> Verified</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span
                                                    class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2">Present
                                                    Today</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pending Leaves Side Panel -->
        <div class="col-lg-4">
            <div class="card h-100 border-0 shadow-sm">
                <div
                    class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center border-bottom-0 pb-0">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-inbox me-2 text-warning"></i>Requests</h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning pill"><?= count($recentLeaves) ?>
                        New</span>
                </div>
                <div class="card-body pt-3">
                    <?php if (empty($recentLeaves)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">All caught up!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentLeaves as $leave): ?>
                            <div class="p-4 mb-3 bg-light rounded-4 border-0 hover-lift transition-all">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($leave['full_name']) ?></span>
                                    <small class="text-muted"><?= time_elapsed_string($leave['created_at']) ?></small>
                                </div>
                                <div class="mb-3">
                                    <span class="badge bg-white text-dark shadow-sm border border-light">
                                        <?= getLeaveTypeName($leave['leave_type']) ?>
                                    </span>
                                    <small class="ms-2 text-secondary fw-semibold">
                                        <?= $leave['total_days'] ?> days
                                    </small>
                                </div>
                                <div class="d-grid">
                                    <a href="leaves.php?id=<?= $leave['id'] ?>"
                                        class="btn btn-sm btn-dark rounded-pill fw-bold">
                                        Review Request
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="text-center mt-4">
                            <a href="leaves.php" class="text-decoration-none text-muted fw-bold small">View all requests <i
                                    class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>