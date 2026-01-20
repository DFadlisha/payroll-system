<?php
/**
 * TRANSPARENT ATTENDANCE MONITOR - HR ONLY
 */

require_once '../includes/header.php';
requireHR();

$companyId = $_SESSION['company_id'];
$isAdmin = isHR();
$userId = $_SESSION['user_id'];

// Filter parameters
$selectedUserId = $_GET['user_id'] ?? ($isAdmin ? 'all' : $userId);
$selectedDate = $_GET['date'] ?? date('Y-m-d');

try {
    $conn = getConnection();
    
    // Fetch logs
    $sql = "SELECT a.*, p.full_name, p.role as user_role 
            FROM attendance a 
            JOIN profiles p ON a.user_id = p.id 
            WHERE p.company_id = ? AND DATE(a.clock_in) = ? ";
    
    $params = [$companyId, $selectedDate];
    
    if ($selectedUserId !== 'all') {
        $sql .= " AND a.user_id = ? ";
        $params[] = $selectedUserId;
    }
    
    $sql .= " ORDER BY a.clock_in DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();

    // Fetch staff list for filter if admin
    $staffList = [];
    if ($isAdmin) {
        $stmt = $conn->prepare("SELECT id, full_name FROM profiles WHERE company_id = ? AND is_active = TRUE ORDER BY full_name");
        $stmt->execute([$companyId]);
        $staffList = $stmt->fetchAll();
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<style>
    .verification-card {
        transition: transform 0.2s;
    }
    .verification-card:hover {
        transform: translateY(-5px);
    }
    .img-verification {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 12px;
    }
    .hash-badge {
        font-family: monospace;
        font-size: 0.7rem;
        word-break: break-all;
        background: #f8f9fa;
        color: #6c757d;
        border: 1px dashed #dee2e6;
    }
</style>

<?php 
if ($isAdmin) {
    include '../includes/hr_sidebar.php'; 
} else {
    include '../includes/staff_sidebar.php';
}
?>

<div class="main-content">
    <?php $navTitle = 'Attendance Monitor'; include '../includes/top_navbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-end gx-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Select Date</label>
                <input type="date" id="filterDate" class="form-control" value="<?= $selectedDate ?>" onchange="updateFilters()">
            </div>
            <?php if ($isAdmin): ?>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Individual Staff</label>
                    <select id="filterStaff" class="form-select" onchange="updateFilters()">
                        <option value="all">-- All Members --</option>
                        <?php foreach ($staffList as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= $selectedUserId == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="col-md-auto">
                <div class="badge bg-info-soft text-info rounded-pill px-3 py-2 border border-info">
                    <i class="bi bi-info-circle me-1"></i> Data displayed is read-only for transparency
                </div>
            </div>
        </div>

        <?php if (empty($logs)): ?>
            <div class="text-center py-5 bg-white rounded-4 shadow-sm border border-dashed">
                <i class="bi bi-calendar-x display-4 text-muted"></i>
                <p class="text-muted mt-3">No verified attendance records for this selection.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($logs as $log): ?>
                    <div class="col-xl-4 col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 verification-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                                        <?= strtoupper(substr($log['full_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($log['full_name']) ?></h6>
                                        <span class="badge bg-light text-muted small rounded-pill"><?= date('h:i A', strtotime($log['clock_in'])) ?> In</span>
                                    </div>
                                    <div class="ms-auto text-end">
                                        <span class="badge bg-success-soft text-success rounded-pill px-2">
                                            <i class="bi bi-patch-check-fill"></i> Verified
                                        </span>
                                    </div>
                                </div>

                                <div class="position-relative mb-3">
                                    <img src="../<?= htmlspecialchars($log['clock_in_photo']) ?>" class="img-verification" alt="Verification Photo">
                                    <div class="position-absolute bottom-0 start-0 w-100 p-2 bg-dark bg-opacity-50 blur p-2 rounded-bottom text-white small">
                                        <i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($log['gps_location']) ?>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="small text-muted mb-1 d-block"><i class="bi bi-shield-lock me-1"></i> Integrity Hash (SHA-256)</label>
                                    <div class="hash-badge p-2 rounded small"><?= $log['photo_hash'] ?: 'N/A' ?></div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                    <div class="small text-muted">
                                        <i class="bi bi-laptop me-1"></i> <?= $log['ip_address'] ?>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modal-<?= $log['id'] ?>">
                                        Check Maps
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal for detailed verification -->
                        <div class="modal fade" id="modal-<?= $log['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Security Audit</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4 text-center">
                                        <img src="../<?= htmlspecialchars($log['clock_in_photo']) ?>" class="img-fluid rounded-4 mb-3 shadow-sm">
                                        <div class="text-start">
                                            <p><strong>Staff:</strong> <?= htmlspecialchars($log['full_name']) ?></p>
                                            <p><strong>Device Info:</strong> <span class="small text-muted"><?= htmlspecialchars($log['device_info']) ?></span></p>
                                            <hr>
                                            <a href="https://www.google.com/maps?q=<?= explode(',', $log['gps_location'] ?? '0,0')[0] ?>,<?= explode(',', $log['gps_location'] ?? '0,0')[1] ?>" target="_blank" class="btn btn-danger w-100 rounded-pill">
                                                <i class="bi bi-map me-1"></i> Open GPS Location in Google Maps
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function updateFilters() {
        const date = document.getElementById('filterDate').value;
        const staffSelect = document.getElementById('filterStaff');
        const staffId = staffSelect ? staffSelect.value : '<?= $userId ?>';
        window.location.href = `?date=${date}&user_id=${staffId}`;
    }
</script>

<?php require_once '../includes/footer.php'; ?>
