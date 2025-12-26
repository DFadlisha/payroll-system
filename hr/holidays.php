<?php
/**
 * ============================================
 * PUBLIC HOLIDAYS MANAGEMENT
 * ============================================
 * Manage Malaysian public holidays for OT calculation
 * ============================================
 */

$pageTitle = 'Public Holidays - MI-NES Payroll';
require_once '../includes/header.php';
requireLogin();

if (!isHR()) {
    redirect('../staff/dashboard.php');
}

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        $conn = getConnection();
        
        if ($action === 'add') {
            $holidayDate = $_POST['holiday_date'] ?? '';
            $holidayName = sanitize($_POST['holiday_name'] ?? '');
            $holidayType = $_POST['holiday_type'] ?? 'national';
            $stateCode = sanitize($_POST['state_code'] ?? null);
            
            if (empty($holidayDate) || empty($holidayName)) {
                throw new Exception('Holiday date and name are required.');
            }
            
            $holidayUuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            
            $stmt = $conn->prepare("
                INSERT INTO public_holidays (id, holiday_date, holiday_name, holiday_type, state_code)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$holidayUuid, $holidayDate, $holidayName, $holidayType, $stateCode]);
            
            $message = 'Public holiday added successfully.';
            $messageType = 'success';
            
        } elseif ($action === 'delete') {
            $holidayId = $_POST['holiday_id'] ?? '';
            
            $stmt = $conn->prepare("DELETE FROM public_holidays WHERE id = ?");
            $stmt->execute([$holidayId]);
            
            $message = 'Public holiday deleted successfully.';
            $messageType = 'success';
            
        } elseif ($action === 'toggle') {
            $holidayId = $_POST['holiday_id'] ?? '';
            
            $stmt = $conn->prepare("
                UPDATE public_holidays 
                SET is_active = NOT is_active, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$holidayId]);
            
            $message = 'Public holiday status updated.';
            $messageType = 'success';
        }
        
    } catch (Exception $e) {
        error_log("Public Holidays error: " . $e->getMessage());
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Get public holidays
try {
    $conn = getConnection();
    $year = $_GET['year'] ?? date('Y');
    
    $stmt = $conn->prepare("
        SELECT * FROM public_holidays
        WHERE EXTRACT(YEAR FROM holiday_date) = ?
        ORDER BY holiday_date ASC
    ");
    $stmt->execute([$year]);
    $holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Public Holidays fetch error: " . $e->getMessage());
    $holidays = [];
}
?>

<?php include '../includes/hr_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <?php 
    $navTitle = 'Public Holidays';
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
            <h1><i class="bi bi-calendar-event me-2"></i>Public Holidays</h1>
            <p class="text-muted mb-0">Manage Malaysian public holidays for overtime calculations</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
            <i class="bi bi-plus-circle me-2"></i>Add Holiday
        </button>
    </div>
    
    <!-- Year Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Filter by Year</label>
                    <select name="year" class="form-select" onchange="this.form.submit()">
                        <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                            <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-9 text-end">
                    <span class="badge bg-info"><?= count($holidays) ?> holidays in <?= $year ?></span>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Holidays List -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($holidays)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No public holidays found for <?= $year ?></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Holiday Name</th>
                                <th>Type</th>
                                <th>State</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($holidays as $holiday): ?>
                                <tr class="<?= !$holiday['is_active'] ? 'text-muted' : '' ?>">
                                    <td><?= formatDate($holiday['holiday_date']) ?></td>
                                    <td><?= getDayName($holiday['holiday_date']) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($holiday['holiday_name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= ucfirst($holiday['holiday_type']) ?></span>
                                    </td>
                                    <td><?= $holiday['state_code'] ? htmlspecialchars($holiday['state_code']) : '-' ?></td>
                                    <td>
                                        <?php if ($holiday['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Toggle status?')">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="holiday_id" value="<?= $holiday['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-warning" title="Toggle Status">
                                                <i class="bi bi-toggle-on"></i>
                                            </button>
                                        </form>
                                        
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this holiday?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="holiday_id" value="<?= $holiday['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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

<!-- Add Holiday Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Public Holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Holiday Date <span class="text-danger">*</span></label>
                        <input type="date" name="holiday_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Holiday Name <span class="text-danger">*</span></label>
                        <input type="text" name="holiday_name" class="form-control" 
                               placeholder="e.g., Hari Raya Aidilfitri" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="holiday_type" class="form-select">
                            <option value="national">National</option>
                            <option value="state-specific">State-Specific</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">State Code (if state-specific)</label>
                        <input type="text" name="state_code" class="form-control" 
                               placeholder="e.g., JHR, KL, SEL" maxlength="10">
                        <small class="text-muted">Leave empty for national holidays</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
