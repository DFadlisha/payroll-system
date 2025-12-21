<?php
/**
 * ============================================
 * HR LEAVES MANAGEMENT PAGE
 * ============================================
 * Halaman untuk urus permohonan cuti pekerja.
 * ============================================
 */

$pageTitle = 'Pengurusan Cuti - MI-NES Payroll';
require_once '../includes/header.php';
requireHR();

$companyId = $_SESSION['company_id'];
$message = '';
$messageType = '';

// Process leave approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $leaveId = intval($_POST['leave_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    try {
        $conn = getConnection();
        
        if ($action === 'approve') {
            $stmt = $conn->prepare("
                UPDATE leaves SET status = 'approved', approved_by = ?, approved_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $leaveId]);
            $message = 'Permohonan cuti telah diluluskan.';
            $messageType = 'success';
        } elseif ($action === 'reject') {
            $reason = sanitize($_POST['rejection_reason'] ?? '');
            $stmt = $conn->prepare("
                UPDATE leaves SET status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ?
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id'], $reason, $leaveId]);
            $message = 'Permohonan cuti telah ditolak.';
            $messageType = 'warning';
        }
        
    } catch (PDOException $e) {
        error_log("Leave action error: " . $e->getMessage());
        $message = 'Ralat sistem. Sila cuba lagi.';
        $messageType = 'error';
    }
}

// Get leaves
$status = $_GET['status'] ?? 'pending';

try {
    $conn = getConnection();
    
    $sql = "
        SELECT l.*, u.full_name, u.employment_type
        FROM leaves l
        JOIN users u ON l.user_id = u.id
        WHERE u.company_id = ?
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
        JOIN users u ON l.user_id = u.id
        WHERE u.company_id = ?
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

<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-header">
        <h3><i class="bi bi-building me-2"></i>MI-NES</h3>
        <small>Payroll System</small>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li><a href="employees.php"><i class="bi bi-people"></i> Pekerja</a></li>
        <li><a href="attendance.php"><i class="bi bi-calendar-check"></i> Kehadiran</a></li>
        <li><a href="leaves.php" class="active"><i class="bi bi-calendar-x"></i> Cuti</a></li>
        <li><a href="payroll.php"><i class="bi bi-cash-stack"></i> Gaji</a></li>
        <li><a href="reports.php"><i class="bi bi-file-earmark-bar-graph"></i> Laporan</a></li>
        <li class="mt-auto" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 20px;">
            <a href="../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Log Keluar</a>
        </li>
    </ul>
</nav>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div>
            <button class="mobile-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <span class="fw-bold">Pengurusan Cuti</span>
        </div>
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div>
                <div class="fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <small class="text-muted">HR Admin</small>
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
        <h1><i class="bi bi-calendar-x me-2"></i>Pengurusan Cuti</h1>
    </div>
    
    <!-- Status Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="?status=pending" class="btn <?= $status === 'pending' ? 'btn-primary' : 'btn-outline-primary' ?>">
                    Menunggu <span class="badge bg-light text-dark"><?= $counts['pending'] ?? 0 ?></span>
                </a>
                <a href="?status=approved" class="btn <?= $status === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">
                    Diluluskan <span class="badge bg-light text-dark"><?= $counts['approved'] ?? 0 ?></span>
                </a>
                <a href="?status=rejected" class="btn <?= $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">
                    Ditolak <span class="badge bg-light text-dark"><?= $counts['rejected'] ?? 0 ?></span>
                </a>
                <a href="?status=all" class="btn <?= $status === 'all' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                    Semua
                </a>
            </div>
        </div>
    </div>
    
    <!-- Leaves Table -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table me-2"></i>Senarai Permohonan Cuti
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
                                <th>Pekerja</th>
                                <th>Jenis Cuti</th>
                                <th>Tempoh</th>
                                <th>Hari</th>
                                <th>Sebab</th>
                                <th>Status</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaves as $leave): 
                                $badge = getLeaveStatusBadge($leave['status']);
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($leave['full_name']) ?></strong><br>
                                        <small class="text-muted"><?= getEmploymentTypeName($leave['employment_type']) ?></small>
                                    </td>
                                    <td><?= getLeaveTypeName($leave['leave_type']) ?></td>
                                    <td>
                                        <?= formatDate($leave['start_date']) ?><br>
                                        <small>hingga <?= formatDate($leave['end_date']) ?></small>
                                    </td>
                                    <td><?= $leave['total_days'] ?></td>
                                    <td><?= htmlspecialchars($leave['reason'] ?: '-') ?></td>
                                    <td>
                                        <span class="badge <?= $badge['class'] ?>"><?= $badge['name'] ?></span>
                                        <?php if ($leave['status'] === 'rejected' && $leave['rejection_reason']): ?>
                                            <br><small class="text-danger"><?= htmlspecialchars($leave['rejection_reason']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($leave['status'] === 'pending'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="leave_id" value="<?= $leave['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-sm btn-success" 
                                                        onclick="return confirm('Luluskan permohonan cuti ini?')">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" data-bs-target="#rejectModal<?= $leave['id'] ?>">
                                                <i class="bi bi-x"></i>
                                            </button>
                                            
                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal<?= $leave['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Tolak Permohonan</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="leave_id" value="<?= $leave['id'] ?>">
                                                                <input type="hidden" name="action" value="reject">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Sebab Ditolak</label>
                                                                    <textarea name="rejection_reason" class="form-control" rows="3" 
                                                                              placeholder="Nyatakan sebab permohonan ditolak..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">Tolak</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
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
