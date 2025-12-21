<?php
/**
 * ============================================
 * STAFF LEAVES PAGE
 * ============================================
 * Page for leave applications.
 * - Staff: Annual, Medical, Emergency, Unpaid leave
 * - Intern: NRL (Need Replacement Leave) only
 *   NRL days = internship months (e.g., 3 months = 3 days)
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

// Get user details including role and internship months
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT role, internship_months FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);
    $userRole = $userDetails['role'] ?? 'staff';
    $internshipMonths = $userDetails['internship_months'] ?? 0;
} catch (PDOException $e) {
    $userRole = 'staff';
    $internshipMonths = 0;
}

$isIntern = ($userRole === 'intern');

// Process leave application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_leave'])) {
    $leaveType = sanitize($_POST['leave_type'] ?? '');
    $startDate = sanitize($_POST['start_date'] ?? '');
    $endDate = sanitize($_POST['end_date'] ?? '');
    $reason = sanitize($_POST['reason'] ?? '');
    
    // Validate
    if (empty($leaveType) || empty($startDate) || empty($endDate)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif (strtotime($endDate) < strtotime($startDate)) {
        $message = 'End date cannot be before start date.';
        $messageType = 'error';
    } elseif (strtotime($startDate) < strtotime(date('Y-m-d'))) {
        $message = 'Start date cannot be in the past.';
        $messageType = 'error';
    } else {
        try {
            $conn = getConnection();
            
            // Calculate total days
            $start = new DateTime($startDate);
            $end = new DateTime($endDate);
            $totalDays = $end->diff($start)->days + 1;
            
            // For intern, check NRL balance
            if ($isIntern) {
                $currentYear = date('Y');
                $stmt = $conn->prepare("
                    SELECT COALESCE(SUM(total_days), 0) as nrl_used
                    FROM leaves 
                    WHERE user_id = ? AND leave_type = 'nrl' AND status IN ('approved', 'pending') AND YEAR(start_date) = ?
                ");
                $stmt->execute([$userId, $currentYear]);
                $nrlUsed = $stmt->fetch()['nrl_used'] ?? 0;
                $nrlBalance = $internshipMonths - $nrlUsed;
                
                if ($totalDays > $nrlBalance) {
                    $message = "Insufficient NRL balance. You have {$nrlBalance} day(s) remaining.";
                    $messageType = 'error';
                }
            }
            
            if (empty($message)) {
                $stmt = $conn->prepare("
                    INSERT INTO leaves (user_id, leave_type, start_date, end_date, total_days, reason)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $leaveType, $startDate, $endDate, $totalDays, $reason]);
                
                $message = 'Leave application submitted successfully. Waiting for HR approval.';
                $messageType = 'success';
                $action = ''; // Reset to list view
            }
            
        } catch (PDOException $e) {
            error_log("Leave application error: " . $e->getMessage());
            $message = 'System error. Please try again.';
            $messageType = 'error';
        }
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
        // Intern: NRL balance = internship months
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(total_days), 0) as nrl_used
            FROM leaves 
            WHERE user_id = ? AND leave_type = 'nrl' AND status = 'approved' AND YEAR(start_date) = ?
        ");
        $stmt->execute([$userId, $currentYear]);
        $nrlUsed = $stmt->fetch()['nrl_used'] ?? 0;
        
        $leaveBalance = [
            'nrl' => ['total' => $internshipMonths, 'used' => $nrlUsed]
        ];
    } else {
        // Staff/Part-time: Annual & Medical leave
        $stmt = $conn->prepare("
            SELECT 
                SUM(CASE WHEN leave_type = 'annual' AND status = 'approved' THEN total_days ELSE 0 END) as annual_used,
                SUM(CASE WHEN leave_type = 'medical' AND status = 'approved' THEN total_days ELSE 0 END) as medical_used
            FROM leaves 
            WHERE user_id = ? AND YEAR(start_date) = ?
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
        $leaveBalance = ['nrl' => ['total' => $internshipMonths, 'used' => 0]];
    } else {
        $leaveBalance = [
            'annual' => ['total' => 14, 'used' => 0],
            'medical' => ['total' => 14, 'used' => 0]
        ];
    }
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
            <a href="attendance.php">
                <i class="bi bi-calendar-check"></i> Kehadiran
            </a>
        </li>
        <li>
            <a href="leaves.php" class="active">
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
            <span class="fw-bold">Permohonan Cuti</span>
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
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-calendar-x me-2"></i>Permohonan Cuti</h1>
        </div>
        <?php if ($action !== 'new'): ?>
            <a href="?action=new" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Mohon Cuti
            </a>
        <?php else: ?>
            <a href="leaves.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Leave Balance Cards -->
    <?php if ($isIntern): ?>
        <!-- Intern: NRL Balance Only -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">NRL (Need Replacement Leave)</h6>
                                <h3 class="mb-0">
                                    <?= $leaveBalance['nrl']['total'] - $leaveBalance['nrl']['used'] ?> 
                                    <small class="text-muted fs-6">/ <?= $leaveBalance['nrl']['total'] ?> days</small>
                                </h3>
                            </div>
                            <div class="text-info">
                                <i class="bi bi-calendar2-check" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 8px;">
                            <?php $nrlPercent = $leaveBalance['nrl']['total'] > 0 ? ($leaveBalance['nrl']['used'] / $leaveBalance['nrl']['total']) * 100 : 0; ?>
                            <div class="progress-bar bg-info" style="width: <?= $nrlPercent ?>%"></div>
                        </div>
                        <small class="text-muted">Used: <?= $leaveBalance['nrl']['used'] ?> days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="text-muted mb-2"><i class="bi bi-info-circle me-1"></i>NRL Information</h6>
                        <p class="mb-1">Your internship duration: <strong><?= $internshipMonths ?> months</strong></p>
                        <p class="mb-0 small text-muted">NRL entitlement = 1 day per month of internship</p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Staff/Part-time: Annual & Medical Leave -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Annual Leave</h6>
                                <h3 class="mb-0">
                                    <?= $leaveBalance['annual']['total'] - $leaveBalance['annual']['used'] ?> 
                                    <small class="text-muted fs-6">/ <?= $leaveBalance['annual']['total'] ?> days</small>
                                </h3>
                            </div>
                            <div class="text-primary">
                                <i class="bi bi-calendar-check" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 8px;">
                            <?php $annualPercent = ($leaveBalance['annual']['used'] / $leaveBalance['annual']['total']) * 100; ?>
                            <div class="progress-bar bg-primary" style="width: <?= $annualPercent ?>%"></div>
                        </div>
                        <small class="text-muted">Used: <?= $leaveBalance['annual']['used'] ?> days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Medical Leave</h6>
                                <h3 class="mb-0">
                                    <?= $leaveBalance['medical']['total'] - $leaveBalance['medical']['used'] ?> 
                                    <small class="text-muted fs-6">/ <?= $leaveBalance['medical']['total'] ?> days</small>
                                </h3>
                            </div>
                            <div class="text-danger">
                                <i class="bi bi-heart-pulse" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 8px;">
                            <?php $medicalPercent = ($leaveBalance['medical']['used'] / $leaveBalance['medical']['total']) * 100; ?>
                            <div class="progress-bar bg-danger" style="width: <?= $medicalPercent ?>%"></div>
                        </div>
                        <small class="text-muted">Used: <?= $leaveBalance['medical']['used'] ?> days</small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if ($action === 'new'): ?>
        <!-- Leave Application Form -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-plus-circle me-2"></i>Leave Application Form
            </div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                            <?php if ($isIntern): ?>
                                <!-- Intern: NRL only -->
                                <select name="leave_type" class="form-select" required>
                                    <option value="">-- Select Leave Type --</option>
                                    <option value="nrl">NRL (Need Replacement Leave)</option>
                                </select>
                                <small class="text-muted">
                                    Balance: <?= $leaveBalance['nrl']['total'] - $leaveBalance['nrl']['used'] ?> days remaining
                                </small>
                            <?php else: ?>
                                <!-- Staff/Part-time: Multiple leave types -->
                                <select name="leave_type" class="form-select" required>
                                    <option value="">-- Select Leave Type --</option>
                                    <option value="annual">Annual Leave</option>
                                    <option value="medical">Medical Leave</option>
                                    <option value="emergency">Emergency Leave</option>
                                    <option value="unpaid">Unpaid Leave</option>
                                    <option value="other">Other</option>
                                </select>
                            <?php endif; ?>
                            <div class="invalid-feedback">Please select leave type.</div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" class="form-control" 
                                   min="<?= date('Y-m-d') ?>" required>
                            <div class="invalid-feedback">Please select start date.</div>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" class="form-control" 
                                   min="<?= date('Y-m-d') ?>" required>
                            <div class="invalid-feedback">Please select end date.</div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" class="form-control" rows="3" 
                                      placeholder="State reason for leave application..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <hr>
                            <button type="submit" name="submit_leave" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Submit Application
                            </button>
                            <a href="leaves.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Leave History -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i>Sejarah Permohonan
            </div>
            <div class="card-body">
                <?php if (empty($leaves)): ?>
                    <p class="text-muted text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                        Tiada permohonan cuti.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tarikh Mohon</th>
                                    <th>Jenis</th>
                                    <th>Tempoh</th>
                                    <th>Hari</th>
                                    <th>Sebab</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leaves as $leave): 
                                    $badge = getLeaveStatusBadge($leave['status']);
                                ?>
                                    <tr>
                                        <td><?= formatDate($leave['created_at']) ?></td>
                                        <td><?= getLeaveTypeName($leave['leave_type']) ?></td>
                                        <td>
                                            <?= formatDate($leave['start_date']) ?> - 
                                            <?= formatDate($leave['end_date']) ?>
                                        </td>
                                        <td><?= $leave['total_days'] ?></td>
                                        <td><?= htmlspecialchars($leave['reason'] ?: '-') ?></td>
                                        <td>
                                            <span class="badge <?= $badge['class'] ?>"><?= $badge['name'] ?></span>
                                            <?php if ($leave['status'] === 'rejected' && $leave['rejection_reason']): ?>
                                                <br><small class="text-danger"><?= htmlspecialchars($leave['rejection_reason']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Auto-calculate end date based on start date
document.querySelector('input[name="start_date"]')?.addEventListener('change', function() {
    const endDateInput = document.querySelector('input[name="end_date"]');
    if (endDateInput && !endDateInput.value) {
        endDateInput.value = this.value;
    }
    endDateInput.min = this.value;
});
</script>

<?php require_once '../includes/footer.php'; ?>
