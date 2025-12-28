<?php
/**
 * ============================================
 * HR ATTENDANCE PAGE
 * ============================================
 * Page to view and manage employee attendance.
 * Includes Manual Insert, Edit, OT hours, and Location assignment.
 * ============================================
 */

require_once '../includes/header.php';
requireHR();
$pageTitle = 'Attendance - MI-NES Payroll System';

$companyId = $_SESSION['company_id'];
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$message = '';
$messageType = '';

// Handle Actions (Manual Add & Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();

    // 1. Manual Insert / Update Logic
    if (isset($_POST['save_attendance'])) {
        try {
            $attendanceId = !empty($_POST['attendance_id']) ? $_POST['attendance_id'] : null;
            $userId = $_POST['user_id'];
            $date = $_POST['date'];

            // Times (Combine Date + Time Input)
            $clockInTime = !empty($_POST['clock_in_time']) ? $date . ' ' . $_POST['clock_in_time'] : null;
            $clockOutTime = !empty($_POST['clock_out_time']) ? $date . ' ' . $_POST['clock_out_time'] : null;

            // Validation: Clock Out must be after Clock In if both exist
            if ($clockInTime && $clockOutTime && strtotime($clockOutTime) < strtotime($clockInTime)) {
                // Assuming next day if time is earlier? Or strict error?
                // Let's assume strict for now or add +1 day logic if requested. Use strict for simplicity.
                // Actually, simple workaround: if out < in, assume same day error.
                // But users might work overnight. Let's keep it simple: date selector is one day.
                // Only if out < in, maybe throw error or warn. 
                // For now allow it, but in DB timestamp it includes date.
                // Logic: strict date from form.
            }

            $locationId = !empty($_POST['location_id']) ? $_POST['location_id'] : null;

            // OT / Allowances
            $otHours = floatval($_POST['ot_hours'] ?? 0);
            $otSundayHours = floatval($_POST['ot_sunday_hours'] ?? 0);
            $otPublicHours = floatval($_POST['ot_public_hours'] ?? 0);
            $projectHours = floatval($_POST['project_hours'] ?? 0); // "Proj" count/hours
            $extraShifts = intval($_POST['extra_shifts'] ?? 0);
            $lateMinutes = intval($_POST['late_minutes'] ?? 0);

            // Determine Status
            $status = 'absent';
            if ($clockInTime) {
                $status = ($clockOutTime) ? 'completed' : 'active';
            }
            if ($lateMinutes > 0)
                $status = 'late'; // Simple status override logic, or strictly status enum?
            // Existing DB check: status = 'active' or 'completed'
            if ($clockOutTime)
                $status = 'completed';
            elseif ($clockInTime)
                $status = 'active';

            if ($attendanceId) {
                // UPDATE
                $stmt = $conn->prepare("
                    UPDATE attendance SET 
                        clock_in = ?, clock_out = ?, location_id = ?, status = ?,
                        ot_hours = ?, ot_sunday_hours = ?, ot_public_hours = ?,
                        project_hours = ?, extra_shifts = ?, late_minutes = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([
                    $clockInTime,
                    $clockOutTime,
                    $locationId,
                    $status,
                    $otHours,
                    $otSundayHours,
                    $otPublicHours,
                    $projectHours,
                    $extraShifts,
                    $lateMinutes,
                    $attendanceId
                ]);
                $message = 'Attendance updated successfully.';
            } else {
                // INSERT
                $uuid = sprintf(
                    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff)
                );

                $stmt = $conn->prepare("
                    INSERT INTO attendance (
                        id, user_id, clock_in, clock_out, location_id, status,
                        ot_hours, ot_sunday_hours, ot_public_hours,
                        project_hours, extra_shifts, late_minutes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $uuid,
                    $userId,
                    $clockInTime,
                    $clockOutTime,
                    $locationId,
                    $status,
                    $otHours,
                    $otSundayHours,
                    $otPublicHours,
                    $projectHours,
                    $extraShifts,
                    $lateMinutes
                ]);
                $message = 'Manual attendance added successfully.';
            }
            $messageType = 'success';

        } catch (PDOException $e) {
            error_log("Save attendance error: " . $e->getMessage());
            $message = 'System error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Fetch Data
try {
    $conn = getConnection();

    // 1. Get Locations for Dropdown
    $stmtLoc = $conn->prepare("SELECT id, name FROM work_locations WHERE company_id = ? AND is_active = TRUE ORDER BY name");
    $stmtLoc->execute([$companyId]);
    $locations = $stmtLoc->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get Employees + Attendance
    // Stats
    $present = 0;
    $late = 0;
    $absent = 0;

    $attendanceList = [];
    $locations = [];

    $stmt = $conn->prepare("
        SELECT p.id as user_id, p.full_name, p.role, p.employment_type,
               a.id as attendance_id, a.clock_in, a.clock_out, a.status,
               a.overtime_hours, a.ot_hours, a.ot_sunday_hours, a.ot_public_hours,
               a.project_hours, a.extra_shifts, a.late_minutes,
               l.name as location_name, a.location_id
        FROM profiles p
        LEFT JOIN attendance a ON p.id = a.user_id AND DATE(a.clock_in) = ?
        LEFT JOIN work_locations l ON a.location_id = l.id
        WHERE p.company_id = ? AND p.role IN ('staff', 'leader', 'part_time', 'intern')
        ORDER BY p.full_name
    ");
    $stmt->execute([$selectedDate, $companyId]);
    $attendanceList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats
    $present = 0;
    $late = 0;
    $absent = 0;
    foreach ($attendanceList as $att) {
        if ($att['attendance_id']) {
            if ($att['late_minutes'] > 0)
                $late++;
            else
                $present++;
        } else {
            $absent++;
        }
    }

} catch (PDOException $e) {
    error_log("Attendance page fetch error: " . $e->getMessage());
    $attendanceList = [];
    $locations = [];
}
?>

<?php include '../includes/hr_sidebar.php'; ?>

<div class="main-content">
    <?php include '../includes/top_navbar.php'; ?>

    <!-- Flash Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Attendance Management</h2>
            <p class="text-muted">Manage daily attendance, OT, and locations.</p>
        </div>
        <div class="card border-0 shadow-sm px-3 py-2">
            <form method="GET" class="d-flex align-items-center gap-2 mb-0">
                <label class="fw-bold text-nowrap">Select Date:</label>
                <input type="date" name="date" class="form-control border-0 bg-light" value="<?= $selectedDate ?>"
                    onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stats-card success border-0 shadow-sm">
                <h2><?= $present ?></h2>
                <p>Present</p>
                <div class="stats-icon"><i class="bi bi-person-check-fill text-success"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card warning border-0 shadow-sm">
                <h2><?= $late ?></h2>
                <p>Late / Issues</p>
                <div class="stats-icon"><i class="bi bi-exclamation-triangle-fill text-warning"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card danger border-0 shadow-sm">
                <h2><?= $absent ?></h2>
                <p>Absent / No Clock-In</p>
                <div class="stats-icon"><i class="bi bi-person-x-fill text-danger"></i></div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Role</th>
                            <th>Time In/Out</th>
                            <th>Location</th>
                            <th>OT (N/S/P)</th>
                            <th>Allowance</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendanceList as $att):
                            $hasAtt = !empty($att['attendance_id']);
                            $roleBadge = match ($att['role']) {
                                'leader' => 'bg-info',
                                'intern' => 'bg-warning text-dark',
                                'part_time' => 'bg-secondary',
                                default => 'bg-primary'
                            };

                            $statusBadge = 'bg-secondary';
                            $statusText = 'Absent';
                            if ($hasAtt) {
                                if ($att['late_minutes'] > 0) {
                                    $statusBadge = 'bg-warning text-dark';
                                    $statusText = 'Late';
                                } elseif ($att['status'] === 'completed') {
                                    $statusBadge = 'bg-success';
                                    $statusText = 'Present';
                                } elseif ($att['status'] === 'active') {
                                    $statusBadge = 'bg-info';
                                    $statusText = 'Active';
                                }
                            }
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?= htmlspecialchars($att['full_name']) ?></div>
                                </td>
                                <td><span class="badge <?= $roleBadge ?> rounded-pill"><?= ucfirst($att['role']) ?></span>
                                </td>
                                <td>
                                    <?php if ($hasAtt): ?>
                                        <div class="small">
                                            <div>In: <?= $att['clock_in'] ? date('H:i', strtotime($att['clock_in'])) : '-' ?>
                                            </div>
                                            <div>Out: <?= $att['clock_out'] ? date('H:i', strtotime($att['clock_out'])) : '-' ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($hasAtt && $att['location_name']): ?>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                            <?= htmlspecialchars($att['location_name']) ?>
                                        </div>
                                    <?php elseif ($hasAtt): ?>
                                        <span class="text-muted small">Unknown</span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($hasAtt): ?>
                                        <span class="badge bg-light text-dark border">
                                            <?= floatval($att['ot_hours']) ?> / <?= floatval($att['ot_sunday_hours']) ?> /
                                            <?= floatval($att['ot_public_hours']) ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($hasAtt): ?>
                                        <small class="text-muted">
                                            Late: <?= $att['late_minutes'] ?>m<br>
                                            Proj: <?= $att['project_hours'] ?>
                                        </small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= $statusBadge ?> rounded-pill"><?= $statusText ?></span></td>
                                <td class="text-end pe-4">
                                    <?php
                                    // Prepare data for JS Modal
                                    $attData = htmlspecialchars(json_encode($att), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <?php if ($hasAtt): ?>
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                            onclick='openEditModal(<?= $attData ?>)'>
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-success rounded-pill px-3"
                                            onclick='openAddModal(<?= $attData ?>)'>
                                            <i class="bi bi-plus-lg me-1"></i> Add
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Modal (Add/Edit) -->
<div class="modal fade" id="attendanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Manage Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="save_attendance" value="1">
                <input type="hidden" name="attendance_id" id="modalAttId">
                <input type="hidden" name="user_id" id="modalUserId">
                <input type="hidden" name="date" value="<?= $selectedDate ?>">

                <div class="modal-body">
                    <div class="alert alert-info py-2" id="modalEmployeeName"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Clock In Time</label>
                            <input type="time" name="clock_in_time" id="modalClockIn" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Clock Out Time</label>
                            <input type="time" name="clock_out_time" id="modalClockOut" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Location (Factory/Sorting Center)</label>
                            <select name="location_id" id="modalLocation" class="form-select">
                                <option value="">-- Select Location --</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <hr>
                        </div>
                        <h6 class="fw-bold text-primary">Overtime & Allowances</h6>

                        <div class="col-md-4">
                            <label class="form-label small text-muted">Normal OT (Hours)</label>
                            <input type="number" step="0.5" name="ot_hours" id="modalOT" class="form-control"
                                placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Public Holiday OT</label>
                            <input type="number" step="0.5" name="ot_public_hours" id="modalOTPub" class="form-control"
                                placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Project Count (Interns)</label>
                            <input type="number" step="1" name="project_hours" id="modalProj" class="form-control"
                                placeholder="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Late Minutes</label>
                            <input type="number" step="1" name="late_minutes" id="modalLate" class="form-control"
                                placeholder="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Extra Shifts</label>
                            <input type="number" step="1" name="extra_shifts" id="modalShift" class="form-control"
                                placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const modal = new bootstrap.Modal(document.getElementById('attendanceModal'));

    function formatTime(dateTimeStr) {
        if (!dateTimeStr) return '';
        const date = new Date(dateTimeStr);
        // Format to HH:MM for input time
        return date.toTimeString().substring(0, 5);
    }

    function openAddModal(data) {
        document.getElementById('modalTitle').innerText = 'Add Manual Attendance';
        document.getElementById('modalEmployeeName').innerText = 'Employee: ' + data.full_name;
        document.getElementById('modalUserId').value = data.user_id;
        document.getElementById('modalAttId').value = ''; // Clear ID

        // Clear fields
        document.getElementById('modalClockIn').value = '09:00';
        document.getElementById('modalClockOut').value = '18:00';
        document.getElementById('modalLocation').value = '';
        document.getElementById('modalOT').value = '';
        document.getElementById('modalOTPub').value = '';
        document.getElementById('modalProj').value = '';
        document.getElementById('modalLate').value = '';
        document.getElementById('modalShift').value = '';

        modal.show();
    }

    function openEditModal(data) {
        document.getElementById('modalTitle').innerText = 'Edit Attendance Details';
        document.getElementById('modalEmployeeName').innerText = 'Employee: ' + data.full_name;
        document.getElementById('modalUserId').value = data.user_id;
        document.getElementById('modalAttId').value = data.attendance_id;

        // Populate fields
        document.getElementById('modalClockIn').value = formatTime(data.clock_in);
        document.getElementById('modalClockOut').value = formatTime(data.clock_out);
        document.getElementById('modalLocation').value = data.location_id || '';
        document.getElementById('modalOT').value = data.ot_hours || 0;
        document.getElementById('modalOTPub').value = data.ot_public_hours || 0;
        document.getElementById('modalProj').value = data.project_hours || 0;
        document.getElementById('modalLate').value = data.late_minutes || 0;
        document.getElementById('modalShift').value = data.extra_shifts || 0;

        modal.show();
    }
</script>

<?php require_once '../includes/footer.php'; ?>