<?php
/**
 * ============================================
 * HR PAYROLL PAGE
 * ============================================
 * Halaman untuk generate dan urus gaji pekerja.
 * ============================================
 */

$pageTitle = 'Pengurusan Gaji - MI-NES Payroll';
require_once '../includes/header.php';
requireHR();

$companyId = $_SESSION['company_id'];
$message = '';
$messageType = '';

// Get selected month/year (default current)
$selectedMonth = intval($_GET['month'] ?? date('n'));
$selectedYear = intval($_GET['year'] ?? date('Y'));

// Process payroll generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_payroll'])) {
    try {
        $conn = getConnection();
        
        // Get rates from payroll_rates table
        $stmt = $conn->prepare("SELECT rate_name, rate_value FROM payroll_rates WHERE company_id = ? AND is_active = true");
        $stmt->execute([$companyId]);
        $ratesData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Set rates from database (with defaults if not found)
        $BASIC_STAFF = floatval($ratesData['BASIC_STAFF'] ?? 1700.00);
        $BASIC_INTERN = floatval($ratesData['BASIC_INTERN'] ?? 800.00);
        $RATE_OT_NORMAL = floatval($ratesData['RATE_OT_NORMAL'] ?? 10.00);
        $RATE_OT_SUNDAY = floatval($ratesData['RATE_OT_SUNDAY'] ?? 12.50);
        $RATE_OT_PUBLIC = floatval($ratesData['RATE_OT_PUBLIC'] ?? 20.00);
        $RATE_PROJECT = floatval($ratesData['RATE_PROJECT'] ?? 15.00);
        $RATE_SHIFT = floatval($ratesData['RATE_SHIFT'] ?? 10.00);
        $RATE_ATTENDANCE = floatval($ratesData['RATE_ATTENDANCE'] ?? 5.00);
        $RATE_LATE = floatval($ratesData['RATE_LATE'] ?? 1.00);
        
        // Daily rates for part-time and intern
        $PART_TIME_DAILY_RATE = 70.83;
        $INTERN_DAILY_RATE = 33.33;
        
        // Get all active employees (all roles: staff, part_time, intern)
        $stmt = $conn->prepare("SELECT * FROM users WHERE company_id = ? AND is_active = 1 AND role IN ('staff', 'part_time', 'intern')");
        $stmt->execute([$companyId]);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $generated = 0;
        
        foreach ($employees as $emp) {
            // Check if payroll already exists
            $stmt = $conn->prepare("SELECT id FROM payroll WHERE user_id = ? AND month = ? AND year = ?");
            $stmt->execute([$emp['id'], $selectedMonth, $selectedYear]);
            
            if (!$stmt->fetch()) {
                // Get attendance for the month
                $stmt = $conn->prepare("
                    SELECT 
                        COUNT(*) as days_worked,
                        SUM(CASE WHEN status = 'late' THEN late_minutes ELSE 0 END) as total_late_minutes,
                        COALESCE(SUM(ot_hours), 0) as total_ot_hours,
                        COALESCE(SUM(ot_sunday_hours), 0) as total_ot_sunday_hours,
                        COALESCE(SUM(ot_public_hours), 0) as total_ot_public_hours,
                        COALESCE(SUM(project_hours), 0) as total_project_hours,
                        COALESCE(SUM(extra_shifts), 0) as total_extra_shifts
                    FROM attendance 
                    WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ? AND status IN ('present', 'late')
                ");
                $stmt->execute([$emp['id'], $selectedMonth, $selectedYear]);
                $attendance = $stmt->fetch();
                
                $daysWorked = $attendance['days_worked'] ?? 0;
                $totalLateMinutes = $attendance['total_late_minutes'] ?? 0;
                $otHours = $attendance['total_ot_hours'] ?? 0;
                $otSundayHours = $attendance['total_ot_sunday_hours'] ?? 0;
                $otPublicHours = $attendance['total_ot_public_hours'] ?? 0;
                $projectHours = $attendance['total_project_hours'] ?? 0;
                $extraShifts = $attendance['total_extra_shifts'] ?? 0;
                
                // Calculate salary based on role
                $basicSalary = 0;
                $role = $emp['role'] ?? 'staff';
                
                switch ($role) {
                    case 'part_time':
                        // Part-time: RM 70.83 per day
                        $basicSalary = $daysWorked * $PART_TIME_DAILY_RATE;
                        break;
                    case 'intern':
                        // Intern: RM 33.33 per day
                        $basicSalary = $daysWorked * $INTERN_DAILY_RATE;
                        break;
                    default: // staff (full-time)
                        // Full-time: Monthly basic salary from profile or default
                        $basicSalary = $emp['basic_salary'] ?? $BASIC_STAFF;
                        break;
                }
                
                // Calculate allowances
                $otNormalAllowance = $otHours * $RATE_OT_NORMAL;
                $otSundayAllowance = $otSundayHours * $RATE_OT_SUNDAY;
                $otPublicAllowance = $otPublicHours * $RATE_OT_PUBLIC;
                $totalOtAllowance = $otNormalAllowance + $otSundayAllowance + $otPublicAllowance;
                
                $projectAllowance = $projectHours * $RATE_PROJECT;
                $shiftAllowance = $extraShifts * $RATE_SHIFT;
                $attendanceBonus = $daysWorked * $RATE_ATTENDANCE;
                
                // Calculate deductions
                $lateDeduction = $totalLateMinutes * $RATE_LATE;
                
                // Gross salary = Basic + All Allowances
                $grossSalary = $basicSalary + $totalOtAllowance + $projectAllowance + $shiftAllowance + $attendanceBonus;
                
                // Calculate statutory deductions (for full-time staff only)
                $epfEmployee = 0;
                $epfEmployer = 0;
                $socsoEmployee = 0;
                $socsoEmployer = 0;
                $eisEmployee = 0;
                $eisEmployer = 0;
                
                // Only full-time staff have statutory deductions
                if ($role === 'staff') {
                    // EPF: Employee 11%, Employer 12%
                    $epfEmployee = $grossSalary * 0.11;
                    $epfEmployer = $grossSalary * 0.12;
                    
                    // SOCSO: Based on salary range (simplified)
                    $socsoEmployee = min($grossSalary * 0.005, 39.35);
                    $socsoEmployer = min($grossSalary * 0.0175, 137.70);
                    
                    // EIS: 0.2% each
                    $eisEmployee = $grossSalary * 0.002;
                    $eisEmployer = $grossSalary * 0.002;
                }
                
                $totalDeductions = $epfEmployee + $socsoEmployee + $eisEmployee + $lateDeduction;
                $netSalary = $grossSalary - $totalDeductions;
                
                // Insert payroll
                $stmt = $conn->prepare("
                    INSERT INTO payroll (user_id, month, year, basic_salary, gross_salary,
                        epf_employee, epf_employer, socso_employee, socso_employer,
                        eis_employee, eis_employer, total_deductions, net_salary, 
                        days_worked, ot_hours, ot_allowance, ot_sunday_hours, ot_sunday_allowance,
                        ot_public_hours, ot_public_allowance, project_hours, project_allowance,
                        extra_shifts, shift_allowance, attendance_bonus, late_minutes, late_deduction, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')
                ");
                $stmt->execute([
                    $emp['id'], $selectedMonth, $selectedYear, $basicSalary, $grossSalary,
                    $epfEmployee, $epfEmployer, $socsoEmployee, $socsoEmployer,
                    $eisEmployee, $eisEmployer, $totalDeductions, $netSalary, 
                    $daysWorked, $otHours, $otNormalAllowance, $otSundayHours, $otSundayAllowance,
                    $otPublicHours, $otPublicAllowance, $projectHours, $projectAllowance,
                    $extraShifts, $shiftAllowance, $attendanceBonus, $totalLateMinutes, $lateDeduction
                ]);
                
                $generated++;
            }
        }
        
        $message = "Payroll generated successfully for $generated employees.";
        $messageType = 'success';
        
    } catch (PDOException $e) {
        error_log("Generate payroll error: " . $e->getMessage());
        $message = 'Ralat sistem. Sila cuba lagi.';
        $messageType = 'error';
    }
}

// Process status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $payrollId = intval($_POST['payroll_id']);
    $newStatus = sanitize($_POST['new_status']);
    
    try {
        $conn = getConnection();
        $paymentDate = $newStatus === 'paid' ? date('Y-m-d') : null;
        
        $stmt = $conn->prepare("UPDATE payroll SET status = ?, payment_date = ? WHERE id = ?");
        $stmt->execute([$newStatus, $paymentDate, $payrollId]);
        
        $message = 'Status gaji berjaya dikemaskini.';
        $messageType = 'success';
    } catch (PDOException $e) {
        error_log("Update payroll status error: " . $e->getMessage());
        $message = 'Ralat sistem.';
        $messageType = 'error';
    }
}

// Get payroll data
try {
    $conn = getConnection();
    
    $stmt = $conn->prepare("
        SELECT p.*, u.full_name, u.employment_type
        FROM payroll p
        JOIN users u ON p.user_id = u.id
        WHERE u.company_id = ? AND p.month = ? AND p.year = ?
        ORDER BY u.full_name
    ");
    $stmt->execute([$companyId, $selectedMonth, $selectedYear]);
    $payrollList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate totals
    $totals = [
        'gross' => 0,
        'deductions' => 0,
        'net' => 0,
        'epf_employer' => 0,
        'socso_employer' => 0,
        'eis_employer' => 0
    ];
    
    foreach ($payrollList as $p) {
        $totals['gross'] += $p['gross_salary'];
        $totals['deductions'] += $p['total_deductions'];
        $totals['net'] += $p['net_salary'];
        $totals['epf_employer'] += $p['epf_employer'];
        $totals['socso_employer'] += $p['socso_employer'];
        $totals['eis_employer'] += $p['eis_employer'];
    }
    
} catch (PDOException $e) {
    error_log("Payroll fetch error: " . $e->getMessage());
    $payrollList = [];
    $totals = ['gross' => 0, 'deductions' => 0, 'net' => 0, 'epf_employer' => 0, 'socso_employer' => 0, 'eis_employer' => 0];
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
        <li><a href="leaves.php"><i class="bi bi-calendar-x"></i> Cuti</a></li>
        <li><a href="payroll.php" class="active"><i class="bi bi-cash-stack"></i> Gaji</a></li>
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
            <span class="fw-bold">Pengurusan Gaji</span>
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
        <h1><i class="bi bi-cash-stack me-2"></i>Pengurusan Gaji</h1>
    </div>
    
    <!-- Month/Year Selector & Generate -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form method="GET" class="row g-2 align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0">Bulan:</label>
                        </div>
                        <div class="col-auto">
                            <select name="month" class="form-select" onchange="this.form.submit()">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                                        <?= getMonthName($m) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="year" class="form-select" onchange="this.form.submit()">
                                <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                                    <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="month" value="<?= $selectedMonth ?>">
                        <input type="hidden" name="year" value="<?= $selectedYear ?>">
                        <button type="submit" name="generate_payroll" class="btn btn-primary"
                                onclick="return confirm('Jana gaji untuk <?= getMonthName($selectedMonth) ?> <?= $selectedYear ?>?')">
                            <i class="bi bi-calculator me-2"></i>Jana Gaji
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <h2><?= formatMoney($totals['gross']) ?></h2>
                <p>Jumlah Gaji Kasar</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card danger">
                <h2><?= formatMoney($totals['deductions']) ?></h2>
                <p>Jumlah Potongan</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card success">
                <h2><?= formatMoney($totals['net']) ?></h2>
                <p>Jumlah Gaji Bersih</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card warning">
                <h2><?= formatMoney($totals['epf_employer'] + $totals['socso_employer'] + $totals['eis_employer']) ?></h2>
                <p>Caruman Majikan</p>
            </div>
        </div>
    </div>
    
    <!-- Payroll Table -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table me-2"></i>Senarai Gaji - <?= getMonthName($selectedMonth) ?> <?= $selectedYear ?>
        </div>
        <div class="card-body">
            <?php if (empty($payrollList)): ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                    Tiada data gaji untuk bulan ini.<br>
                    <small>Klik "Jana Gaji" untuk menjana gaji pekerja.</small>
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Jenis</th>
                                <th>Gaji Pokok</th>
                                <th>KWSP</th>
                                <th>SOCSO</th>
                                <th>EIS</th>
                                <th>Gaji Bersih</th>
                                <th>Status</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payrollList as $p): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['full_name']) ?></strong></td>
                                    <td><?= getEmploymentTypeName($p['employment_type']) ?></td>
                                    <td><?= formatMoney($p['basic_salary']) ?></td>
                                    <td><?= formatMoney($p['epf_employee']) ?></td>
                                    <td><?= formatMoney($p['socso_employee']) ?></td>
                                    <td><?= formatMoney($p['eis_employee']) ?></td>
                                    <td><strong><?= formatMoney($p['net_salary']) ?></strong></td>
                                    <td>
                                        <?php
                                        $statusBadge = [
                                            'draft' => ['Draf', 'bg-secondary'],
                                            'finalized' => ['Disahkan', 'bg-warning'],
                                            'paid' => ['Dibayar', 'bg-success'],
                                        ];
                                        $badge = $statusBadge[$p['status']] ?? ['N/A', 'bg-secondary'];
                                        ?>
                                        <span class="badge <?= $badge[1] ?>"><?= $badge[0] ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="payroll_id" value="<?= $p['id'] ?>">
                                            <select name="new_status" class="form-select form-select-sm d-inline-block" style="width: auto;"
                                                    onchange="this.form.submit()">
                                                <option value="">Tukar...</option>
                                                <option value="draft">Draf</option>
                                                <option value="finalized">Sahkan</option>
                                                <option value="paid">Dibayar</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2"><strong>JUMLAH</strong></td>
                                <td><strong><?= formatMoney($totals['gross']) ?></strong></td>
                                <td colspan="3"><strong><?= formatMoney($totals['deductions']) ?></strong></td>
                                <td><strong><?= formatMoney($totals['net']) ?></strong></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
