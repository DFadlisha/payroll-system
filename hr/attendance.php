<?php
/**
 * ============================================
 * HR ATTENDANCE PAGE
 * ============================================
 * Page to view and manage employee attendance.
 * Includes OT hours and Project hours entry.
 * ============================================
 */

$pageTitle = __('nav.attendance') . ' - ' . __('app_name');
require_once '../includes/header.php';
requireHR();

$companyId = $_SESSION['company_id'];
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$message = '';
$messageType = '';

// Process OT/Project hours update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_hours'])) {
    try {
        $conn = getConnection();
        $attendanceId = intval($_POST['attendance_id']);
        $otHours = floatval($_POST['ot_hours'] ?? 0);
        $otSundayHours = floatval($_POST['ot_sunday_hours'] ?? 0);
        $otPublicHours = floatval($_POST['ot_public_hours'] ?? 0);
        $projectHours = floatval($_POST['project_hours'] ?? 0);
        $extraShifts = intval($_POST['extra_shifts'] ?? 0);
        $lateMinutes = intval($_POST['late_minutes'] ?? 0);
        
        $stmt = $conn->prepare("
            UPDATE attendance SET 
                ot_hours = ?, ot_sunday_hours = ?, ot_public_hours = ?,
                project_hours = ?, extra_shifts = ?, late_minutes = ?
            WHERE id = ?
        ");
        $stmt->execute([$otHours, $otSundayHours, $otPublicHours, $projectHours, $extraShifts, $lateMinutes, $attendanceId]);
        
        $message = __('attendance.updated_success');
        $messageType = 'success';
    } catch (PDOException $e) {
        error_log("Update hours error: " . $e->getMessage());
        $message = __('errors.system_error');
        $messageType = 'error';
    }
}

try {
    $conn = getConnection();
    
    // Get all employees with attendance for selected date
    $stmt = $conn->prepare("
        SELECT u.id, u.full_name, u.role,
               a.id as attendance_id, a.clock_in, a.clock_out, a.status, a.notes,
               a.ot_hours, a.ot_sunday_hours, a.ot_public_hours, 
               a.project_hours, a.extra_shifts, a.late_minutes
        FROM users u
        LEFT JOIN attendance a ON u.id = a.user_id AND a.date = ?
        WHERE u.company_id = ? AND u.is_active = 1 AND u.role IN ('staff', 'part_time', 'intern')
        ORDER BY u.full_name
    ");
    $stmt->execute([$selectedDate, $companyId]);
    $attendanceList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get summary
    $present = 0;
    $late = 0;
    $absent = 0;
    
    foreach ($attendanceList as $att) {
        if ($att['status'] === 'present') $present++;
        elseif ($att['status'] === 'late') $late++;
        else $absent++;
    }
    
} catch (PDOException $e) {
    error_log("Attendance fetch error: " . $e->getMessage());
    $attendanceList = [];
    $present = $late = $absent = 0;
}
?>

<?php include '../includes/hr_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div>
            <button class="mobile-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <span class="fw-bold"><?= __('attendance.title') ?></span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?= getLanguageSwitcher() ?>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
                <div>
                    <div class="fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                    <small class="text-muted"><?= __('roles.hr') ?></small>
                </div>
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
        <h1><i class="bi bi-calendar-check me-2"></i>Employee Attendance</h1>
    </div>
    
    <!-- Date Selector -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row align-items-center">
                <div class="col-auto">
                    <label class="form-label mb-0">Select Date:</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="date" class="form-control" 
                           value="<?= $selectedDate ?>" onchange="this.form.submit()">
                </div>
                <div class="col">
                    <strong><?= getDayName($selectedDate) ?>, <?= formatDate($selectedDate) ?></strong>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stats-card success">
                <h2><?= $present ?></h2>
                <p><?= __('attendance.present') ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card warning">
                <h2><?= $late ?></h2>
                <p><?= __('attendance.late') ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card danger">
                <h2><?= $absent ?></h2>
                <p><?= __('attendance.absent') ?></p>
            </div>
        </div>
    </div>
    
    <!-- Rate Info Card -->
    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-white">
            <i class="bi bi-info-circle me-2"></i><?= __('attendance.rates_info') ?>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <strong><?= __('attendance.rate_ot_normal') ?>:</strong><br>
                    <small class="text-muted">RM 10.00/hr</small>
                </div>
                <div class="col-md-2">
                    <strong><?= __('attendance.rate_ot_sunday') ?>:</strong><br>
                    <small class="text-muted">RM 12.50/hr</small>
                </div>
                <div class="col-md-2">
                    <strong><?= __('attendance.rate_ot_public') ?>:</strong><br>
                    <small class="text-muted">RM 20.00/hr</small>
                </div>
                <div class="col-md-2">
                    <strong><?= __('attendance.rate_project') ?>:</strong><br>
                    <small class="text-muted">RM 15.00/project</small>
                </div>
                <div class="col-md-2">
                    <strong><?= __('attendance.rate_shift') ?>:</strong><br>
                    <small class="text-muted">RM 10.00/shift</small>
                </div>
                <div class="col-md-2">
                    <strong><?= __('attendance.rate_late') ?>:</strong><br>
                    <small class="text-muted">RM 1.00/min</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Attendance Table -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table me-2"></i><?= __('nav.attendance') ?></div>
        <div class="card-body">
            <?php if (empty($attendanceList)): ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                    No attendance data.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>In/Out</th>
                                <th>Status</th>
                                <th title="Normal OT Hours">OT</th>
                                <th title="Sunday OT Hours">Sun</th>
                                <th title="Public Holiday OT">PH</th>
                                <th title="Project Count">Proj</th>
                                <th title="Extra Shifts">Shift</th>
                                <th title="Late Minutes">Late</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceList as $att): 
                                $hours = '-';
                                if ($att['clock_in'] && $att['clock_out']) {
                                    $in = strtotime($att['clock_in']);
                                    $out = strtotime($att['clock_out']);
                                    $hours = round(($out - $in) / 3600, 1) . 'h';
                                }
                                $statusBadge = [
                                    'present' => ['Present', 'bg-success'],
                                    'late' => ['Late', 'bg-warning'],
                                ];
                                $badge = $statusBadge[$att['status']] ?? ['Absent', 'bg-danger'];
                                
                                $roleLabels = [
                                    'staff' => ['Staff', 'bg-primary'],
                                    'part_time' => ['P/T', 'bg-info'],
                                    'intern' => ['Intern', 'bg-secondary'],
                                ];
                                $roleLabel = $roleLabels[$att['role']] ?? ['Staff', 'bg-primary'];
                            ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($att['full_name']) ?></strong></td>
                                    <td><span class="badge <?= $roleLabel[1] ?>"><?= $roleLabel[0] ?></span></td>
                                    <td>
                                        <small>
                                            <?= $att['clock_in'] ? formatTime($att['clock_in']) : '-' ?>
                                            / <?= $att['clock_out'] ? formatTime($att['clock_out']) : '-' ?>
                                        </small>
                                    </td>
                                    <td><span class="badge <?= $badge[1] ?>"><?= $badge[0] ?></span></td>
                                    <?php if ($att['attendance_id']): ?>
                                        <form method="POST" class="d-flex align-items-center">
                                            <input type="hidden" name="update_hours" value="1">
                                            <input type="hidden" name="attendance_id" value="<?= $att['attendance_id'] ?>">
                                            <td>
                                                <input type="number" name="ot_hours" class="form-control form-control-sm" 
                                                       style="width: 55px;" step="0.5" min="0" max="24"
                                                       title="Normal OT Hours" placeholder="OT"
                                                       value="<?= $att['ot_hours'] ?? 0 ?>">
                                            </td>
                                            <td>
                                                <input type="number" name="ot_sunday_hours" class="form-control form-control-sm" 
                                                       style="width: 55px;" step="0.5" min="0" max="24"
                                                       title="Sunday OT Hours" placeholder="Sun"
                                                       value="<?= $att['ot_sunday_hours'] ?? 0 ?>">
                                            </td>
                                            <td>
                                                <input type="number" name="ot_public_hours" class="form-control form-control-sm" 
                                                       style="width: 55px;" step="0.5" min="0" max="24"
                                                       title="Public Holiday OT" placeholder="PH"
                                                       value="<?= $att['ot_public_hours'] ?? 0 ?>">
                                            </td>
                                            <td>
                                                <input type="number" name="project_hours" class="form-control form-control-sm" 
                                                       style="width: 55px;" step="1" min="0" max="50"
                                                       title="Project Count" placeholder="Proj"
                                                       value="<?= $att['project_hours'] ?? 0 ?>">
                                            </td>
                                            <td>
                                                <input type="number" name="extra_shifts" class="form-control form-control-sm" 
                                                       style="width: 55px;" step="1" min="0" max="10"
                                                       title="Extra Shifts" placeholder="Shift"
                                                       value="<?= $att['extra_shifts'] ?? 0 ?>">
                                            </td>
                                            <td>
                                                <input type="number" name="late_minutes" class="form-control form-control-sm" 
                                                       style="width: 55px;" step="1" min="0" max="480"
                                                       title="Late Minutes" placeholder="Late"
                                                       value="<?= $att['late_minutes'] ?? 0 ?>">
                                            </td>
                                            <td>
                                                <button type="submit" class="btn btn-sm btn-primary" title="Save">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </td>
                                        </form>
                                    <?php else: ?>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td>-</td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
