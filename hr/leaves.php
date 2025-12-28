<?php
/**
 * ============================================
 * HR LEAVES MANAGEMENT PAGE
 * ============================================
 * Manage employee leave requests.
 * ============================================
 */

$pageTitle = 'Leave Management - MI-NES Payroll';
require_once '../includes/header.php';
requireHR();

$companyId = $_SESSION['company_id'];
$message = '';
$messageType = '';

// Process leave approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leaveId = $_POST['leave_id'] ?? '';
    $action = $_POST['action'] ?? '';

    try {
        $conn = getConnection();

        // Fetch leave and user details first
        $stmt = $conn->prepare("SELECT l.*, p.email, p.full_name FROM leaves l JOIN profiles p ON l.user_id = p.id WHERE l.id = ?");
        $stmt->execute([$leaveId]);
        $leaveRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($leaveRequest) {
            $emailHelper = new stdClass(); // Or just use boolean check
            $emailSent = false;

            if ($action === 'approve') {
                $stmt = $conn->prepare("
                    UPDATE leaves SET status = 'approved', reviewed_by = ?, reviewed_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$_SESSION['user_id'], $leaveId]);
                $message = 'Leave request approved successfully.';
                $messageType = 'success';

                // Send Email
                $subject = "Leave Approved - MI-NES Payroll";
                $body = "
                    <p>Hi {$leaveRequest['full_name']},</p>
                    <p>Your leave request has been <strong>APPROVED</strong>.</p>
                    <p><strong>Type:</strong> " . getLeaveTypeName($leaveRequest['leave_type']) . "<br>
                    <strong>Date:</strong> " . formatDate($leaveRequest['start_date']) . " to " . formatDate($leaveRequest['end_date']) . "</p>
                ";
                sendEmail($leaveRequest['email'], $subject, $body);

            } elseif ($action === 'reject') {
                $stmt = $conn->prepare("
                    UPDATE leaves SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW(), rejection_reason = ?
                    WHERE id = ?
                ");
                $rejectionReason = $_POST['rejection_reason'] ?? ''; // Fixed: Capture reason
                $stmt->execute([$_SESSION['user_id'], $rejectionReason, $leaveId]); // Correct param order
                $message = 'Leave request rejected.';
                $messageType = 'warning';

                // Send Email
                $subject = "Leave Rejected - MI-NES Payroll";
                $body = "
                    <p>Hi {$leaveRequest['full_name']},</p>
                    <p>Your leave request has been <strong>REJECTED</strong>.</p>
                    <p><strong>Reason:</strong> " . htmlspecialchars($rejectionReason) . "</p>
                ";
                sendEmail($leaveRequest['email'], $subject, $body);
            }
        }
    } catch (PDOException $e) {
        error_log("Leave action error: " . $e->getMessage());
        $message = 'System error. Please try again.';
        $messageType = 'error';
    }
}

// Get leaves (using profiles table - Supabase schema)
$status = $_GET['status'] ?? 'pending';

try {
    $conn = getConnection();

    $sql = "
        SELECT l.*, p.full_name, p.employment_type, p.role
        FROM leaves l
        JOIN profiles p ON l.user_id = p.id
        WHERE p.company_id = ?
    ";

    if ($status !== 'all') {
        $sql .= " AND l.status = ?";
    }
    $sql .= " ORDER BY l.created_at DESC";

    $stmt = $conn->prepare($sql);
    if ($status !== 'all') {
        $stmt->execute([$companyId, $status]);
    } else {
        $stmt->execute([$companyId]);
    }
    $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count by status
    $stmt = $conn->prepare("
        SELECT l.status, COUNT(*) as count
        FROM leaves l
        JOIN profiles p ON l.user_id = p.id
        WHERE p.company_id = ?
        GROUP BY l.status
    ");
    $stmt->execute([$companyId]);
    $counts = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $counts[$row['status']] = $row['count'];
    }

} catch (PDOException $e) {
    error_log("Leaves fetch error: " . $e->getMessage());
    $leaves = [];
    $counts = [];
}
?>

<?php include '../includes/hr_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <?php
    $navTitle = 'Leave Management';
    include '../includes/top_navbar.php';
    ?>

    <!-- Flash Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Welcome Header -->
    <div class="mb-4">
        <p class="text-muted mb-1">Human Resources</p>
        <h2 class="fw-bold">Leave Management</h2>
        <div class="d-flex align-items-center mt-2 text-muted">
            <i class="bi bi-info-circle me-2"></i> Manage employee time off requests efficiently.
        </div>
    </div>

    <!-- Status Filter -->
    <div class="card mb-4 border-0 shadow-sm rounded-4">
        <div class="card-body p-2">
            <div class="nav nav-pills nav-fill gap-2 p-1 bg-light rounded-3" role="tablist">
                <a href="?status=pending"
                    class="nav-link rounded-3 <?= $status === 'pending' ? 'active shadow-sm' : '' ?>">
                    Pending <span
                        class="badge bg-white text-primary rounded-pill ms-2"><?= $counts['pending'] ?? 0 ?></span>
                </a>
                <a href="?status=approved"
                    class="nav-link rounded-3 <?= $status === 'approved' ? 'active bg-success shadow-sm' : '' ?>">
                    Approved <span
                        class="badge bg-white text-success rounded-pill ms-2"><?= $counts['approved'] ?? 0 ?></span>
                </a>
                <a href="?status=rejected"
                    class="nav-link rounded-3 <?= $status === 'rejected' ? 'active bg-danger shadow-sm' : '' ?>">
                    Rejected <span
                        class="badge bg-white text-danger rounded-pill ms-2"><?= $counts['rejected'] ?? 0 ?></span>
                </a>
                <a href="?status=all"
                    class="nav-link rounded-3 <?= $status === 'all' ? 'active bg-secondary shadow-sm' : '' ?>">
                    View All
                </a>
            </div>
        </div>
    </div>

    <!-- Leaves Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-card-list me-2 text-primary"></i>Requests List</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($leaves)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-inbox text-muted" style="font-size: 3.5rem; opacity: 0.3;"></i>
                    </div>
                    <h5 class="fw-bold text-muted">No requests found</h5>
                    <p class="text-muted small">There are no leave requests with this status currently.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Employee</th>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaves as $leave):
                                $badge = getLeaveStatusBadge($leave['status']);
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar-sm me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 40px;">
                                                <?= strtoupper(substr($leave['full_name'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($leave['full_name']) ?>
                                                </div>
                                                <small
                                                    class="text-muted"><?= ucwords(str_replace('_', ' ', $leave['role'] ?? 'Staff')) ?>
                                                    • <?= getEmploymentTypeName($leave['employment_type']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= getLeaveTypeName($leave['leave_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?= $leave['total_days'] ?> Days</div>
                                        <small class="text-muted"><?= formatDate($leave['start_date']) ?> -
                                            <?= formatDate($leave['end_date']) ?></small>
                                    </td>
                                    <td>
                                        <p class="mb-0 text-truncate" style="max-width: 200px;"
                                            title="<?= htmlspecialchars($leave['reason']) ?>">
                                            <?= htmlspecialchars($leave['reason'] ?: 'No reason provided') ?>
                                        </p>
                                    </td>
                                    <td>
                                        <span
                                            class="badge <?= $badge['class'] ?> rounded-pill px-3 py-2"><?= $badge['name'] ?></span>
                                        <?php if ($leave['status'] === 'rejected' && $leave['rejection_reason']): ?>
                                            <div class="mt-1 small text-danger">
                                                <i
                                                    class="bi bi-info-circle me-1"></i><?= htmlspecialchars($leave['rejection_reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if ($leave['status'] === 'pending'): ?>
                                            <div class="btn-group shadow-sm">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="leave_id" value="<?= $leave['id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success" data-bs-toggle="tooltip"
                                                        title="Approve" onclick="return confirm('Approve this leave request?')">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#rejectModal<?= $leave['id'] ?>" title="Reject">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>

                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal<?= $leave['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow">
                                                        <form method="POST">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title">Reject Request</h5>
                                                                <button type="button" class="btn-close btn-close-white"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body text-start">
                                                                <input type="hidden" name="leave_id" value="<?= $leave['id'] ?>">
                                                                <input type="hidden" name="action" value="reject">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Reason for Rejection</label>
                                                                    <textarea name="rejection_reason" class="form-control" rows="3"
                                                                        placeholder="Please explain why this request is being rejected..."
                                                                        required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light">
                                                                <button type="button"
                                                                    class="btn btn-link text-muted text-decoration-none"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger px-4">Confirm
                                                                    Rejection</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-light text-muted" disabled>
                                                <?= $leave['status'] === 'approved' ? 'Approved' : 'Rejected' ?>
                                            </button>
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
</div>

<?php require_once '../includes/footer.php'; ?>