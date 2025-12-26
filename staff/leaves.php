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
        try {
            $conn = getConnection();
            
            // Calculate total days
            $start = new DateTime($startDate);
            $end = new DateTime($endDate);
            $totalDays = $end->diff($start)->days + 1;
            
            // Generate UUID for leave
            $leaveUuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
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
    ?>
    
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
            <h1><i class="bi bi-calendar-x me-2"></i><?= __('leaves.title') ?></h1>
        </div>
        <?php if ($action !== 'new'): ?>
            <a href="?action=new" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i><?= __('leaves.apply') ?>
            </a>
        <?php else: ?>
            <a href="leaves.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i><?= __('back') ?>
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Leave Balance Cards -->
    <?php if ($isIntern): ?>
        <!-- Intern: show NRL and Medical balances -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">NRL (Need Replacement Leave)</h6>
                                <h3 class="mb-0">
                                    <?= ($leaveBalance['nrl']['total'] - $leaveBalance['nrl']['used']) ?> 
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
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Medical Leave</h6>
                                <h3 class="mb-0">
                                    <?= ($leaveBalance['medical']['total'] - $leaveBalance['medical']['used']) ?> 
                                    <small class="text-muted fs-6">/ <?= $leaveBalance['medical']['total'] ?> days</small>
                                </h3>
                            </div>
                            <div class="text-danger">
                                <i class="bi bi-heart-pulse" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 8px;">
                            <?php $medicalPercent = $leaveBalance['medical']['total'] > 0 ? ($leaveBalance['medical']['used'] / $leaveBalance['medical']['total']) * 100 : 0; ?>
                            <div class="progress-bar bg-danger" style="width: <?= $medicalPercent ?>%"></div>
                        </div>
                        <small class="text-muted">Used: <?= $leaveBalance['medical']['used'] ?> days</small>
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
                                <!-- Intern: NRL and Medical -->
                                <select name="leave_type" class="form-select" required>
                                    <option value="">-- <?= __('select') ?> --</option>
                                    <option value="nrl"><?= __('leaves.nrl') ?></option>
                                    <option value="medical"><?= __('leaves.medical') ?></option>
                                </select>
                                <small class="text-muted d-block">
                                    <?= __('leaves.balance') ?>: <?= ($leaveBalance['nrl']['total'] - $leaveBalance['nrl']['used']) ?> <?= __('leaves.days') ?> (NRL)<br>
                                    <?= __('leaves.balance') ?>: <?= ($leaveBalance['medical']['total'] - $leaveBalance['medical']['used']) ?> <?= __('leaves.days') ?> (<?= __('leaves.medical') ?>)
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
                <i class="bi bi-clock-history me-2"></i><?= __('leaves.my_leaves') ?>
            </div>
            <div class="card-body">
                <?php if (empty($leaves)): ?>
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
                                    <th><?= __('leaves.leave_type') ?></th>
                                    <th><?= __('leaves.start_date') ?>/<?= __('leaves.end_date') ?></th>
                                    <th><?= __('leaves.days') ?></th>
                                    <th><?= __('leaves.reason') ?></th>
                                    <th><?= __('status') ?></th>
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
