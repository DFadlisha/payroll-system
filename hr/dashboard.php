<?php
/**
 * ============================================
 * HR DASHBOARD
 * ============================================
 * Main dashboard for HR users.
 * Display summary of attendance, leaves, and payroll.
 * ============================================
 */

$pageTitle = __('nav.dashboard') . ' - ' . __('app_name');
require_once '../includes/header.php';
requireHR();

// Dapatkan statistik
try {
    $conn = getConnection();
    $companyId = $_SESSION['company_id'];
    
    // Jumlah pekerja aktif
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE company_id = ? AND is_active = 1");
    $stmt->execute([$companyId]);
    $totalEmployees = $stmt->fetch()['total'];
    
    // Kehadiran hari ini
    $today = date('Y-m-d');
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        WHERE u.company_id = ? AND a.date = ?
    ");
    $stmt->execute([$companyId, $today]);
    $todayAttendance = $stmt->fetch()['total'];
    
    // Permohonan cuti menunggu kelulusan
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM leaves l 
        JOIN users u ON l.user_id = u.id 
        WHERE u.company_id = ? AND l.status = 'pending'
    ");
    $stmt->execute([$companyId]);
    $pendingLeaves = $stmt->fetch()['total'];
    
    // Gaji bulan ini belum dibayar
    $currentMonth = date('n');
    $currentYear = date('Y');
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM payroll p 
        JOIN users u ON p.user_id = u.id 
        WHERE u.company_id = ? AND p.month = ? AND p.year = ? AND p.status != 'paid'
    ");
    $stmt->execute([$companyId, $currentMonth, $currentYear]);
    $unpaidPayroll = $stmt->fetch()['total'];
    
    // Senarai kehadiran hari ini
    $stmt = $conn->prepare("
        SELECT u.full_name, a.clock_in, a.clock_out, a.status
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        WHERE u.company_id = ? AND a.date = ?
        ORDER BY a.clock_in DESC
        LIMIT 10
    ");
    $stmt->execute([$companyId, $today]);
    $todayAttendanceList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Senarai permohonan cuti terbaru
    $stmt = $conn->prepare("
        SELECT l.*, u.full_name 
        FROM leaves l 
        JOIN users u ON l.user_id = u.id 
        WHERE u.company_id = ? AND l.status = 'pending'
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
    <div class="top-navbar">
        <div>
            <button class="mobile-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <span class="fw-bold"><?= __('nav.dashboard') ?></span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?= getLanguageSwitcher() ?>
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?>
                </div>
                <div>
                    <div class="fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                    <small class="text-muted"><?= __('roles.hr') ?></small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php displayFlashMessage(); ?>
    
    <!-- Page Header -->
    <div class="page-header">
        <h1><?= __('dashboard.welcome_back') ?>, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>
        <p class="text-muted mb-0">
            <i class="bi bi-calendar me-1"></i>
            <?= getDayName(date('Y-m-d')) ?>, <?= formatDate(date('Y-m-d')) ?>
        </p>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><?= $totalEmployees ?></h2>
                        <p><?= __('dashboard.total_employees') ?></p>
                    </div>
                    <i class="bi bi-people" style="font-size: 2.5rem; opacity: 0.7;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="stats-card success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><?= $todayAttendance ?></h2>
                        <p><?= __('dashboard.present_today') ?></p>
                    </div>
                    <i class="bi bi-calendar-check" style="font-size: 2.5rem; opacity: 0.7;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="stats-card warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><?= $pendingLeaves ?></h2>
                        <p><?= __('dashboard.pending_leaves') ?></p>
                    </div>
                    <i class="bi bi-hourglass-split" style="font-size: 2.5rem; opacity: 0.7;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="stats-card danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><?= $unpaidPayroll ?></h2>
                        <p><?= __('dashboard.monthly_payroll') ?></p>
                    </div>
                    <i class="bi bi-cash" style="font-size: 2.5rem; opacity: 0.7;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Row -->
    <div class="row g-4">
        <!-- Today's Attendance -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar-check me-2"></i><?= __('dashboard.attendance_overview') ?></span>
                    <a href="attendance.php" class="btn btn-sm btn-outline-primary"><?= __('all') ?></a>
                </div>
                <div class="card-body">
                    <?php if (empty($todayAttendanceList)): ?>
                        <p class="text-muted text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                            <?= __('no_data') ?>
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><?= __('employees.name') ?></th>
                                        <th><?= __('attendance.clock_in') ?></th>
                                        <th><?= __('attendance.clock_out') ?></th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todayAttendanceList as $att): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($att['full_name']) ?></td>
                                            <td><?= formatTime($att['clock_in']) ?></td>
                                            <td><?= formatTime($att['clock_out']) ?></td>
                                            <td>
                                                <?php
                                                $statusBadge = [
                                                    'present' => ['Hadir', 'bg-success'],
                                                    'late' => ['Lewat', 'bg-warning'],
                                                    'absent' => ['Tidak Hadir', 'bg-danger'],
                                                ];
                                                $badge = $statusBadge[$att['status']] ?? ['N/A', 'bg-secondary'];
                                                ?>
                                                <span class="badge <?= $badge[1] ?>"><?= $badge[0] ?></span>
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
        
        <!-- Pending Leaves -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar-x me-2"></i>Permohonan Cuti</span>
                    <a href="leaves.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentLeaves)): ?>
                        <p class="text-muted text-center py-4">
                            <i class="bi bi-check-circle" style="font-size: 3rem;"></i><br>
                            Tiada permohonan cuti menunggu.
                        </p>
                    <?php else: ?>
                        <?php foreach ($recentLeaves as $leave): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($leave['full_name']) ?></div>
                                    <small class="text-muted">
                                        <?= getLeaveTypeName($leave['leave_type']) ?> • 
                                        <?= formatDate($leave['start_date']) ?> - <?= formatDate($leave['end_date']) ?>
                                    </small>
                                </div>
                                <a href="leaves.php?action=view&id=<?= $leave['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    Semak
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
