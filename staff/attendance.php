<?php
/**
 * ============================================
 * STAFF ATTENDANCE PAGE
 * ============================================
 * Halaman kehadiran untuk staff.
 * Clock in/out dan lihat rekod kehadiran.
 * ============================================
 */

$pageTitle = 'Kehadiran - MI-NES Payroll';
require_once '../includes/header.php';
requireLogin();

if (isHR()) {
    redirect('../hr/dashboard.php');
}

$userId = $_SESSION['user_id'];
$today = date('Y-m-d');
$message = '';
$messageType = '';

// Process clock in/out
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        $conn = getConnection();
        
        if ($action === 'clock_in') {
            // Check if already clocked in today
            $stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
            $stmt->execute([$userId, $today]);
            
            if ($stmt->fetch()) {
                $message = 'Anda sudah clock in hari ini.';
                $messageType = 'warning';
            } else {
                // Determine if late (after 9:00 AM)
                $currentTime = date('H:i:s');
                $status = strtotime($currentTime) > strtotime('09:00:00') ? 'late' : 'present';
                
                $stmt = $conn->prepare("
                    INSERT INTO attendance (user_id, date, clock_in, status) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $today, $currentTime, $status]);
                
                $message = 'Clock in berjaya pada ' . formatTime($currentTime);
                $messageType = 'success';
            }
        } elseif ($action === 'clock_out') {
            // Update clock out time
            $currentTime = date('H:i:s');
            
            $stmt = $conn->prepare("
                UPDATE attendance SET clock_out = ? 
                WHERE user_id = ? AND date = ? AND clock_out IS NULL
            ");
            $stmt->execute([$currentTime, $userId, $today]);
            
            if ($stmt->rowCount() > 0) {
                $message = 'Clock out berjaya pada ' . formatTime($currentTime);
                $messageType = 'success';
            } else {
                $message = 'Tiada rekod clock in atau sudah clock out.';
                $messageType = 'warning';
            }
        }
    } catch (PDOException $e) {
        error_log("Attendance error: " . $e->getMessage());
        $message = 'Ralat sistem. Sila cuba lagi.';
        $messageType = 'error';
    }
}

// Get today's attendance
try {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
    $stmt->execute([$userId, $today]);
    $todayAttendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get attendance history (current month)
    $currentMonth = date('n');
    $currentYear = date('Y');
    $stmt = $conn->prepare("
        SELECT * FROM attendance 
        WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
        ORDER BY date DESC
    ");
    $stmt->execute([$userId, $currentMonth, $currentYear]);
    $attendanceHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate statistics
    $stats = [
        'present' => 0,
        'late' => 0,
        'absent' => 0,
        'total_hours' => 0
    ];
    
    foreach ($attendanceHistory as $record) {
        if ($record['status'] === 'present') $stats['present']++;
        elseif ($record['status'] === 'late') $stats['late']++;
        elseif ($record['status'] === 'absent') $stats['absent']++;
        
        if ($record['clock_in'] && $record['clock_out']) {
            $in = strtotime($record['clock_in']);
            $out = strtotime($record['clock_out']);
            $stats['total_hours'] += ($out - $in) / 3600;
        }
    }
    
} catch (PDOException $e) {
    error_log("Attendance fetch error: " . $e->getMessage());
    $todayAttendance = null;
    $attendanceHistory = [];
    $stats = ['present' => 0, 'late' => 0, 'absent' => 0, 'total_hours' => 0];
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
            <a href="dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="attendance.php" class="active">
                <i class="bi bi-calendar-check"></i> Kehadiran
            </a>
        </li>
        <li>
            <a href="leaves.php">
                <i class="bi bi-calendar-x"></i> Cuti
            </a>
        </li>
        <li>
            <a href="payslips.php">
                <i class="bi bi-receipt"></i> Slip Gaji
            </a>
        </li>
        <li>
            <a href="profile.php">
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
            <span class="fw-bold">Kehadiran</span>
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
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="bi bi-calendar-check me-2"></i>Kehadiran</h1>
        <p class="text-muted mb-0"><?= getDayName($today) ?>, <?= formatDate($today) ?></p>
    </div>
    
    <!-- Clock In/Out Card -->
    <div class="card mb-4">
        <div class="card-body text-center py-5">
            <h2 class="display-4 mb-3" id="currentTime"><?= date('h:i:s A') ?></h2>
            <p class="text-muted mb-4"><?= getDayName($today) ?>, <?= formatDate($today) ?></p>
            
            <?php if (!$todayAttendance): ?>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="clock_in">
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Clock In
                    </button>
                </form>
                <p class="text-muted mt-3 mb-0">
                    <small>Waktu masuk sebelum 9:00 AM dikira tepat masa</small>
                </p>
            <?php elseif (!$todayAttendance['clock_out']): ?>
                <div class="mb-3">
                    <span class="badge bg-success fs-6 p-2">
                        <i class="bi bi-check-circle me-1"></i>
                        Sudah Clock In: <?= formatTime($todayAttendance['clock_in']) ?>
                    </span>
                </div>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="clock_out">
                    <button type="submit" class="btn btn-danger btn-lg px-5">
                        <i class="bi bi-box-arrow-left me-2"></i>Clock Out
                    </button>
                </form>
            <?php else: ?>
                <div class="text-success mb-3">
                    <i class="bi bi-check-circle" style="font-size: 4rem;"></i>
                </div>
                <h4>Selesai untuk hari ini!</h4>
                <p class="text-muted">
                    Masuk: <?= formatTime($todayAttendance['clock_in']) ?> | 
                    Keluar: <?= formatTime($todayAttendance['clock_out']) ?>
                </p>
                <?php
                $in = strtotime($todayAttendance['clock_in']);
                $out = strtotime($todayAttendance['clock_out']);
                $hours = round(($out - $in) / 3600, 1);
                ?>
                <span class="badge bg-info fs-6">Jumlah: <?= $hours ?> jam</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Stats Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stats-card success">
                <h2><?= $stats['present'] ?></h2>
                <p>Hadir Tepat Masa</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card warning">
                <h2><?= $stats['late'] ?></h2>
                <p>Lewat</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card danger">
                <h2><?= $stats['absent'] ?></h2>
                <p>Tidak Hadir</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h2><?= round($stats['total_hours'], 1) ?></h2>
                <p>Jam Bekerja</p>
            </div>
        </div>
    </div>
    
    <!-- Attendance History -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Rekod Kehadiran - <?= getMonthName($currentMonth) ?> <?= $currentYear ?>
        </div>
        <div class="card-body">
            <?php if (empty($attendanceHistory)): ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                    Tiada rekod kehadiran untuk bulan ini.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tarikh</th>
                                <th>Hari</th>
                                <th>Masuk</th>
                                <th>Keluar</th>
                                <th>Jam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceHistory as $record): 
                                $hours = '-';
                                if ($record['clock_in'] && $record['clock_out']) {
                                    $in = strtotime($record['clock_in']);
                                    $out = strtotime($record['clock_out']);
                                    $hours = round(($out - $in) / 3600, 1) . ' jam';
                                }
                                $statusBadge = [
                                    'present' => ['Hadir', 'bg-success'],
                                    'late' => ['Lewat', 'bg-warning'],
                                    'absent' => ['Tidak Hadir', 'bg-danger'],
                                ];
                                $badge = $statusBadge[$record['status']] ?? ['N/A', 'bg-secondary'];
                            ?>
                                <tr>
                                    <td><?= formatDate($record['date']) ?></td>
                                    <td><?= getDayName($record['date']) ?></td>
                                    <td><?= formatTime($record['clock_in']) ?></td>
                                    <td><?= formatTime($record['clock_out']) ?></td>
                                    <td><?= $hours ?></td>
                                    <td><span class="badge <?= $badge[1] ?>"><?= $badge[0] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Update clock every second
function updateClock() {
    const now = new Date();
    const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    document.getElementById('currentTime').textContent = now.toLocaleTimeString('en-US', options);
}
setInterval(updateClock, 1000);
</script>

<?php require_once '../includes/footer.php'; ?>
