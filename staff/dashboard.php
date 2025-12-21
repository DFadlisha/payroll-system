<?php
/**
 * ============================================
 * STAFF DASHBOARD
 * ============================================
 * Dashboard utama untuk Staff.
 * Papar kehadiran, baki cuti, dan gaji.
 * ============================================
 */

$pageTitle = 'Dashboard - MI-NES Payroll';
require_once '../includes/header.php';
requireLogin();

// Jika HR, redirect ke HR dashboard
if (isHR()) {
    redirect('../hr/dashboard.php');
}

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    $conn = getConnection();
    
    // Dapatkan maklumat profil
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Semak kehadiran hari ini
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
    $stmt->execute([$userId, $today]);
    $todayAttendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Statistik kehadiran bulan ini
    $currentMonth = date('n');
    $currentYear = date('Y');
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_days,
            SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late,
            SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent
        FROM attendance 
        WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
    ");
    $stmt->execute([$userId, $currentMonth, $currentYear]);
    $attendanceStats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Permohonan cuti terkini
    $stmt = $conn->prepare("
        SELECT * FROM leaves 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $recentLeaves = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Gaji bulan lepas (atau terbaru)
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
    $attendanceStats = ['total_days' => 0, 'present' => 0, 'late' => 0, 'absent' => 0];
    $recentLeaves = [];
    $latestPayslip = null;
}
?>

<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-header">
        <h3><i class="bi bi-building me-2"></i>MI-NES</h3>
        <small>Payroll System</small>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="attendance.php" class="<?= $currentPage === 'attendance' ? 'active' : '' ?>">
                <i class="bi bi-calendar-check"></i> Kehadiran
            </a>
        </li>
        <li>
            <a href="leaves.php" class="<?= $currentPage === 'leaves' ? 'active' : '' ?>">
                <i class="bi bi-calendar-x"></i> Cuti
            </a>
        </li>
        <li>
            <a href="payslips.php" class="<?= $currentPage === 'payslips' ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i> Slip Gaji
            </a>
        </li>
        <li>
            <a href="profile.php" class="<?= $currentPage === 'profile' ? 'active' : '' ?>">
                <i class="bi bi-person"></i> Profil
            </a>
        </li>
        <li class="mt-auto" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 20px;">
            <a href="../auth/logout.php">
                <i class="bi bi-box-arrow-left"></i> Log Keluar
            </a>
        </li>
    </ul>
</nav>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div>
            <button class="mobile-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <span class="fw-bold">Dashboard</span>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?>
            </div>
            <div>
                <div class="fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <small class="text-muted">Staff</small>
            </div>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php displayFlashMessage(); ?>
    
    <!-- Page Header -->
    <div class="page-header">
        <h1>Selamat Datang!</h1>
        <p class="text-muted mb-0">
            <i class="bi bi-calendar me-1"></i>
            <?= getDayName($today) ?>, <?= formatDate($today) ?>
        </p>
    </div>
    
    <!-- Clock In/Out Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-2">
                        <i class="bi bi-clock me-2"></i>Kehadiran Hari Ini
                    </h5>
                    <?php if ($todayAttendance): ?>
                        <p class="mb-0">
                            <span class="badge bg-success me-2">Sudah Clock In</span>
                            Masuk: <strong><?= formatTime($todayAttendance['clock_in']) ?></strong>
                            <?php if ($todayAttendance['clock_out']): ?>
                                | Keluar: <strong><?= formatTime($todayAttendance['clock_out']) ?></strong>
                            <?php endif; ?>
                        </p>
                    <?php else: ?>
                        <p class="mb-0 text-muted">Anda belum clock in hari ini.</p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <?php if (!$todayAttendance): ?>
                        <form method="POST" action="attendance.php" class="d-inline">
                            <input type="hidden" name="action" value="clock_in">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Clock In
                            </button>
                        </form>
                    <?php elseif (!$todayAttendance['clock_out']): ?>
                        <form method="POST" action="attendance.php" class="d-inline">
                            <input type="hidden" name="action" value="clock_out">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="bi bi-box-arrow-left me-2"></i>Clock Out
                            </button>
                        </form>
                    <?php else: ?>
                        <span class="badge bg-secondary fs-6 p-2">
                            <i class="bi bi-check-circle me-1"></i>Selesai untuk hari ini
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stats-card success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><?= $attendanceStats['present'] ?? 0 ?></h2>
                        <p>Hari Hadir</p>
                    </div>
                    <i class="bi bi-calendar-check" style="font-size: 2rem; opacity: 0.7;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="stats-card warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><?= $attendanceStats['late'] ?? 0 ?></h2>
                        <p>Hari Lewat</p>
                    </div>
                    <i class="bi bi-clock-history" style="font-size: 2rem; opacity: 0.7;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="stats-card danger">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><?= $attendanceStats['absent'] ?? 0 ?></h2>
                        <p>Hari Tidak Hadir</p>
                    </div>
                    <i class="bi bi-calendar-x" style="font-size: 2rem; opacity: 0.7;"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><?= $latestPayslip ? formatMoney($latestPayslip['net_salary']) : '-' ?></h2>
                        <p>Gaji Terakhir</p>
                    </div>
                    <i class="bi bi-cash-stack" style="font-size: 2rem; opacity: 0.7;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Row -->
    <div class="row g-4">
        <!-- Recent Leaves -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar-x me-2"></i>Permohonan Cuti Terbaru</span>
                    <a href="leaves.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentLeaves)): ?>
                        <p class="text-muted text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                            Tiada permohonan cuti.
                        </p>
                    <?php else: ?>
                        <?php foreach ($recentLeaves as $leave): 
                            $badge = getLeaveStatusBadge($leave['status']);
                        ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <div class="fw-bold"><?= getLeaveTypeName($leave['leave_type']) ?></div>
                                    <small class="text-muted">
                                        <?= formatDate($leave['start_date']) ?> - <?= formatDate($leave['end_date']) ?>
                                        (<?= $leave['total_days'] ?> hari)
                                    </small>
                                </div>
                                <span class="badge <?= $badge['class'] ?>"><?= $badge['name'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightning me-2"></i>Tindakan Pantas
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="leaves.php?action=new" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-plus-circle d-block mb-2" style="font-size: 1.5rem;"></i>
                                Mohon Cuti
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="attendance.php" class="btn btn-outline-success w-100 py-3">
                                <i class="bi bi-calendar-check d-block mb-2" style="font-size: 1.5rem;"></i>
                                Rekod Kehadiran
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="payslips.php" class="btn btn-outline-info w-100 py-3">
                                <i class="bi bi-receipt d-block mb-2" style="font-size: 1.5rem;"></i>
                                Slip Gaji
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="profile.php" class="btn btn-outline-secondary w-100 py-3">
                                <i class="bi bi-person d-block mb-2" style="font-size: 1.5rem;"></i>
                                Kemaskini Profil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
