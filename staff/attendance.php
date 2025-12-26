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

// Process clock in/out (Supabase attendance schema)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $address = $_POST['address'] ?? null;
    
    try {
        $conn = getConnection();
        
        if ($action === 'clock_in') {
            // Check if already clocked in today (active status)
            $stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(clock_in) = ? AND status = 'active'");
            $stmt->execute([$userId, $today]);
            
            if ($stmt->fetch()) {
                $message = 'You have already clocked in today.';
                $messageType = 'warning';
            } else {
                // Generate UUID for attendance
                $attendanceUuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                );
                
                $stmt = $conn->prepare("
                    INSERT INTO attendance (id, user_id, clock_in, status, clock_in_latitude, clock_in_longitude, clock_in_address) 
                    VALUES (?, ?, NOW(), 'active', ?, ?, ?)
                ");
                $stmt->execute([$attendanceUuid, $userId, $latitude, $longitude, $address]);
                
                $message = 'Clock in successful at ' . date('h:i A');
                $messageType = 'success';
            }
        } elseif ($action === 'clock_out') {
            // Update clock out time and calculate hours
            $stmt = $conn->prepare("
                SELECT id, clock_in FROM attendance 
                WHERE user_id = ? AND DATE(clock_in) = ? AND status = 'active' AND clock_out IS NULL
            ");
            $stmt->execute([$userId, $today]);
            $activeRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($activeRecord) {
                // Calculate total hours
                $clockIn = new DateTime($activeRecord['clock_in']);
                $clockOut = new DateTime();
                $interval = $clockIn->diff($clockOut);
                $totalHours = $interval->h + ($interval->i / 60);
                
                // Calculate overtime (over 8 hours)
                $overtimeHours = max(0, $totalHours - 8);
                
                $stmt = $conn->prepare("
                    UPDATE attendance SET 
                        clock_out = NOW(), 
                        status = 'completed',
                        total_hours = ?,
                        overtime_hours = ?,
                        clock_out_latitude = ?,
                        clock_out_longitude = ?,
                        clock_out_address = ?
                    WHERE id = ?
                ");
                $stmt->execute([$totalHours, $overtimeHours, $latitude, $longitude, $address, $activeRecord['id']]);
                
                $message = 'Clock out successful at ' . date('h:i A') . '. Total hours: ' . number_format($totalHours, 2);
                $messageType = 'success';
            } else {
                $message = 'No active clock in record found.';
                $messageType = 'warning';
            }
        }
    } catch (PDOException $e) {
        error_log("Attendance error: " . $e->getMessage());
        $message = 'System error. Please try again.';
        $messageType = 'error';
    }
}

// Get today's attendance (Supabase schema)
try {
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM attendance WHERE user_id = ? AND DATE(clock_in) = ? ORDER BY clock_in DESC LIMIT 1");
    $stmt->execute([$userId, $today]);
    $todayAttendance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get attendance history (current month)
    $currentMonth = date('n');
    $currentYear = date('Y');
    $stmt = $conn->prepare("
        SELECT * FROM attendance 
        WHERE user_id = ? AND EXTRACT(MONTH FROM clock_in) = ? AND EXTRACT(YEAR FROM clock_in) = ?
        ORDER BY clock_in DESC
    ");
    $stmt->execute([$userId, $currentMonth, $currentYear]);
    $attendanceHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate statistics
    $stats = [
        'present' => 0,
        'late' => 0,
        'absent' => 0,
        'completed' => 0,
        'active' => 0,
        'total_hours' => 0
    ];
    
    foreach ($attendanceHistory as $record) {
        if ($record['status'] === 'completed') $stats['completed']++;
        elseif ($record['status'] === 'active') $stats['active']++;
        
        // Determine if present or late based on clock-in time
        if ($record['clock_in']) {
            $clockInTime = date('H:i:s', strtotime($record['clock_in']));
            if ($clockInTime <= '09:00:00') {
                $stats['present']++;
            } else {
                $stats['late']++;
            }
        }
        
        if ($record['clock_in'] && $record['clock_out']) {
            $in = strtotime($record['clock_in']);
            $out = strtotime($record['clock_out']);
            $stats['total_hours'] += ($out - $in) / 3600;
        }
    }
    
    // Calculate absent days (working days - present days)
    $totalWorkingDays = date('j'); // Current day of month
    $totalAttended = $stats['present'] + $stats['late'];
    $stats['absent'] = max(0, $totalWorkingDays - $totalAttended);
    
} catch (PDOException $e) {
    error_log("Attendance fetch error: " . $e->getMessage());
    $todayAttendance = null;
    $attendanceHistory = [];
    $stats = ['present' => 0, 'late' => 0, 'absent' => 0, 'total_hours' => 0];
}
?>

<?php include '../includes/staff_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <?php 
    $navTitle = __('nav.attendance');
    include '../includes/top_navbar.php'; 
    ?>
    
    <!-- Flash Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="bi bi-calendar-check me-2"></i><?= __('nav.attendance') ?></h1>
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
                    <small>Clock-ins before 9:00 AM count as on-time</small>
                </p>
            <?php elseif (!$todayAttendance['clock_out']): ?>
                <div class="mb-3">
                    <span class="badge bg-success fs-6 p-2">
                        <i class="bi bi-check-circle me-1"></i>
                        Clocked In: <?= formatTime($todayAttendance['clock_in']) ?>
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
                <h4>Completed for today!</h4>
                <p class="text-muted">
                    In: <?= formatTime($todayAttendance['clock_in']) ?> | 
                    Out: <?= formatTime($todayAttendance['clock_out']) ?>
                </p>
                <?php
                $in = strtotime($todayAttendance['clock_in']);
                $out = strtotime($todayAttendance['clock_out']);
                $hours = round(($out - $in) / 3600, 1);
                ?>
                <span class="badge bg-info fs-6">Total: <?= $hours ?> hours</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Stats Row -->
    <div class="row g-4 mb-4">
                <div class="col-md-3">
            <div class="stats-card success">
                <h2><?= $stats['present'] ?></h2>
                <p>On-time</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card warning">
                <h2><?= $stats['late'] ?></h2>
                <p>Late</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card danger">
                <h2><?= $stats['absent'] ?></h2>
                <p>Absent</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h2><?= round($stats['total_hours'], 1) ?></h2>
                <p>Hours Worked</p>
            </div>
        </div>
    </div>
    
    <!-- Attendance History -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Attendance Records - <?= getMonthName($currentMonth) ?> <?= $currentYear ?>
        </div>
        <div class="card-body">
            <?php if (empty($attendanceHistory)): ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                    <?= __('no_data') ?>
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= __('date') ?></th>
                                <th>Day</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Hours</th>
                                <th><?= __('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceHistory as $record): 
                                $hours = '-';
                                if ($record['clock_in'] && $record['clock_out']) {
                                    $in = strtotime($record['clock_in']);
                                    $out = strtotime($record['clock_out']);
                                    $hours = round(($out - $in) / 3600, 1) . ' hours';
                                }
                                $statusBadge = [
                                    'present' => ['Present', 'bg-success'],
                                    'late' => ['Late', 'bg-warning'],
                                    'absent' => ['Absent', 'bg-danger'],
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
