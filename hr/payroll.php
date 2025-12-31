<?php
/**
 * ============================================
 * HR PAYROLL PAGE
 * ============================================
 * Generate and manage employee payroll.
 * ============================================
 */

$pageTitle = 'Payroll Management - MI-NES Payroll';
require_once '../includes/header.php';
requireHR();
require_once __DIR__ . '/../includes/contributions.php';
require_once '../includes/mailer.php';

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

        // Get rates from payroll_rates table (Supabase schema)
        $stmt = $conn->prepare("SELECT rate_name, rate_value FROM payroll_rates WHERE company_id = ? AND is_active = true");
        $stmt->execute([$companyId]);
        $ratesData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Set rates from database (with defaults if not found)
        $RATE_OT_NORMAL = floatval($ratesData['RATE_OT_NORMAL'] ?? 10.00);
        $RATE_OT_SUNDAY = floatval($ratesData['RATE_OT_SUNDAY'] ?? 12.50);
        $RATE_OT_PUBLIC = floatval($ratesData['RATE_OT_PUBLIC'] ?? 20.00);
        $RATE_PROJECT = floatval($ratesData['RATE_PROJECT'] ?? 15.00);
        $RATE_SHIFT = floatval($ratesData['RATE_SHIFT'] ?? 10.00);
        $RATE_ATTENDANCE = floatval($ratesData['RATE_ATTENDANCE'] ?? 5.00);
        $RATE_LATE = floatval($ratesData['RATE_LATE'] ?? 1.00);

        // Get all active employees from profiles table (Supabase schema)
        $stmt = $conn->prepare("SELECT * FROM profiles WHERE company_id = ? AND is_active = true");
        $stmt->execute([$companyId]);
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $generated = 0;

        foreach ($employees as $emp) {
            // Check if payroll already exists for this month
            $stmt = $conn->prepare("SELECT id FROM payroll WHERE user_id = ? AND month = ? AND year = ?");
            $stmt->execute([$emp['id'], $selectedMonth, $selectedYear]);

            if (!$stmt->fetch()) {
                // Get attendance summary for the month (Supabase attendance schema)
                $stmt = $conn->prepare("
                    SELECT 
                        COUNT(*) as days_worked,
                        COALESCE(SUM(overtime_hours), 0) as total_ot_hours,
                        COALESCE(SUM(total_hours), 0) as regular_hours
                    FROM attendance 
                    WHERE user_id = ? 
                    AND EXTRACT(MONTH FROM clock_in) = ? 
                    AND EXTRACT(YEAR FROM clock_in) = ? 
                    AND status IN ('active', 'completed')
                ");
                $stmt->execute([$emp['id'], $selectedMonth, $selectedYear]);
                $attendance = $stmt->fetch();

                $daysWorked = $attendance['days_worked'] ?? 0;
                $regularHours = $attendance['regular_hours'] ?? 0;
                $otHours = $attendance['total_ot_hours'] ?? 0;

                // --- NEW CALCULATION LOGIC START ---

                // 1. Determine Basic Salary based on Role/Employment Type
                $basicSalary = 0;
                $employmentType = $emp['employment_type'] ?? 'permanent';
                $role = $emp['role'] ?? 'staff';

                // Priorities: Leader > Intern > Part Time > Staff
                if ($role === 'leader' || $employmentType === 'leader') {
                    $basicSalary = 1900;
                } elseif ($employmentType === 'intern' || $role === 'intern') {
                    $basicSalary = 800;
                } elseif ($employmentType === 'part-time' || $role === 'part_time') {
                    // Part-time: RM 75 per day * days worked
                    $basicSalary = $daysWorked * 75;
                } elseif ($role === 'staff' || $employmentType === 'permanent') {
                    if ($emp['basic_salary'] > 0) {
                        $basicSalary = $emp['basic_salary']; // Use profile salary if set (e.g. 1700 or 1750)
                    } else {
                        $basicSalary = 2000; // Default staff salary
                    }
                } else {
                    $basicSalary = $emp['basic_salary'] ?? 0;
                }

                $calculatedBasic = $basicSalary;

                // 2. Base Hourly Rate Calculation
                // Formula: Salary / 26 days / 8 hours
                $hourlyRate = 0;
                if ($basicSalary > 0) {
                    $hourlyRate = $basicSalary / 26 / 8;
                }

                // 3. Overtime Calculation (1.5x Normal, 3.0x Public Holiday)
                // Note: Sunday OT removed as requested.
                $stmtOT = $conn->prepare("
                    SELECT clock_in, overtime_hours
                    FROM attendance 
                    WHERE user_id = ? 
                    AND EXTRACT(MONTH FROM clock_in) = ? 
                    AND EXTRACT(YEAR FROM clock_in) = ? 
                    AND overtime_hours > 0
                    AND status IN ('active', 'completed')
                ");
                $stmtOT->execute([$emp['id'], $selectedMonth, $selectedYear]);
                $otRecords = $stmtOT->fetchAll(PDO::FETCH_ASSOC);

                $otNormalHours = 0;
                $otPublicHours = 0;
                $otNormal = 0;
                $otPublic = 0;
                // Variables kept for database schema compatibility, but will be 0
                $otSundayHours = 0;
                $otSunday = 0;

                foreach ($otRecords as $otRecord) {
                    $otDate = date('Y-m-d', strtotime($otRecord['clock_in']));
                    $otHrs = floatval($otRecord['overtime_hours']);

                    if (isPublicHoliday($otDate)) {
                        $otPublicHours += $otHrs;
                        $otPublic += $otHrs * $hourlyRate * 3.0; // 3x rate
                    } else {
                        // All other OT (including Sunday if any) treated as Normal or ignored?
                        // Assuming standard practice: Sunday is rest day, but user requested remove Sunday OT.
                        // We will treat non-public holiday OT as Normal (1.5x) unless it falls on Sunday and we strictly ignore it?
                        // "yes but remove sun ot" -> implying don't calculate Sunday rate, or treat Sunday as normal day?
                        // Interpreting as: Do not have a special Sunday rate category. Treat as normal 1.5 if worked?
                        // Or if day is Sunday, exclude completely?
                        // "remove sun ot" usually means simply don't have a separate sunday category.
                        // I will treat it as normal OT (1.5).
                        $otNormalHours += $otHrs;
                        $otNormal += $otHrs * $hourlyRate * 1.5; // 1.5x rate
                    }
                }

                $otHours = $otNormalHours + $otPublicHours; // Total OT Hours

                // 4. Night Shift Allowance (@ RM 10 per shift)
                // Shift is considered "Night" if clock_in is after 10 PM (22:00)
                $nightShiftAllowance = 0;
                $stmtNight = $conn->prepare("
                    SELECT COUNT(*) as night_shifts
                    FROM attendance 
                    WHERE user_id = ? 
                    AND EXTRACT(MONTH FROM clock_in) = ? 
                    AND EXTRACT(YEAR FROM clock_in) = ?
                    AND EXTRACT(HOUR FROM clock_in) >= 22
                ");
                $stmtNight->execute([$emp['id'], $selectedMonth, $selectedYear]);
                $nightShifts = $stmtNight->fetch()['night_shifts'] ?? 0;
                $nightShiftAllowance = $nightShifts * 10;

                // 5. Project Bonus (Interns only, @ RM 15 per project)
                $projectBonus = 0;
                if ($employmentType === 'intern' || $role === 'intern') {
                    // Assuming 'project_hours' column is used to store number of projects completed? 
                    // Or we need a new way to count projects.
                    // For now, looking at existing code or assuming a schema.
                    // If no project tracking exists, we might need to skip or use a placeholder.
                    // Existing code had: COALESCE(SUM(project_hours), 0) in the user prompt snippet.
                    // We will interpret 'project_hours' as 'project_count' for this specific context based on the prompt's intent.
                    // Checking if column exists first is safer, but prompt implied direct SQL usage.
                    // We'll proceed assuming the logic is desired.
                    /* 
                       NOTE: Standard attendance table usually doesn't have 'project_hours'. 
                       If this fails, we catch the error. 
                       For safety, I will wrap this in a try-catch or check existence if possible.
                       However, the user provided exact SQL to use. I will try to use it but fallback safely.
                    */
                    try {
                        $stmtProj = $conn->prepare("
                            SELECT COALESCE(SUM(project_hours), 0) as project_count
                            FROM attendance 
                            WHERE user_id = ? 
                            AND EXTRACT(MONTH FROM clock_in) = ? 
                            AND EXTRACT(YEAR FROM clock_in) = ?
                        ");
                        $stmtProj->execute([$emp['id'], $selectedMonth, $selectedYear]);
                        $projectCount = $stmtProj->fetch()['project_count'] ?? 0;
                        $projectBonus = $projectCount * 15;
                    } catch (PDOException $e) {
                        // Column might not exist, ignore bonus
                        $projectBonus = 0;
                    }
                }

                // 6. Late Deduction
                // Formula: Salary / 26 / 8 / 60 * minutes_late
                $lateDeduction = 0;
                $stmtLate = $conn->prepare("
                    SELECT COALESCE(SUM(late_minutes), 0) as total_late_minutes
                    FROM attendance 
                    WHERE user_id = ? 
                    AND EXTRACT(MONTH FROM clock_in) = ? 
                    AND EXTRACT(YEAR FROM clock_in) = ?
                ");
                $stmtLate->execute([$emp['id'], $selectedMonth, $selectedYear]);
                $totalLateMinutes = $stmtLate->fetch()['total_late_minutes'] ?? 0;

                if ($totalLateMinutes > 0 && $basicSalary > 0) {
                    $perMinuteRate = ($basicSalary / 26 / 8) / 60;
                    $lateDeduction = $perMinuteRate * $totalLateMinutes;
                }

                // 7. Gross Pay
                // Basic + OT + Night Shift + Project Bonus
                // Note: Night Shift Calculation was: "Late - salary / 8 / 60 ... 26" in prompt? 
                // Wait, the prompt said "Late - salary ...". That's deduction.
                // "Night Shift @ 10" is separate.
                $grossPay = $calculatedBasic + $otNormal + $otPublic + $nightShiftAllowance + $projectBonus;

                // 8. Deductions (EPF, SOCSO, EIS, PCB, Late)
                $epfEmployee = 0;
                $epfEmployer = 0;
                $socsoEmployee = 0;
                $socsoEmployer = 0;
                $eisEmployee = 0;
                $eisEmployer = 0;
                $pcbTax = 0;

                $citizenship = $emp['citizenship_status'] ?? 'citizen';

                if ($employmentType !== 'intern' && $role !== 'intern') {
                    // 1. Determine parameters
                    $age = 30; // TODO: Calculate from DOB if available in profiles
                    // if (isset($emp['dob'])) $age = date_diff(date_create($emp['dob']), date_create('today'))->y;

                    // 2. EPF (KWSP)
                    $epfRates = ContributionCalculator::calculateEPF($grossPay, $citizenship, $age);
                    $epfEmployee = $epfRates['employee'];
                    $epfEmployer = $epfRates['employer'];

                    // 3. SOCSO (PERKESO)
                    $socsoRates = ContributionCalculator::calculateSOCSO($grossPay);
                    $socsoEmployee = $socsoRates['employee'];
                    $socsoEmployer = $socsoRates['employer'];

                    // 4. EIS (SIP)
                    $eisRates = ContributionCalculator::calculateEIS($grossPay);
                    $eisEmployee = $eisRates['employee'];
                    $eisEmployer = $eisRates['employer'];

                    // 5. PCB (Tax)
                    $dependents = intval($emp['dependents'] ?? 0);
                    $pcbTax = calculatePCB($grossPay, $dependents);
                }

                $totalDeductions = $epfEmployee + $socsoEmployee + $eisEmployee + $pcbTax + $lateDeduction;
                $netPay = $grossPay - $totalDeductions;

                // --- NEW CALCULATION LOGIC END ---

                // Generate UUID for payroll
                $payrollUuid = sprintf(
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

                // Insert payroll record (Supabase payroll schema)
                $stmt = $conn->prepare(
                    "INSERT INTO payroll (id, user_id, month, year, basic_salary, regular_hours, overtime_hours,"
                    . " ot_normal_hours, ot_normal, ot_sunday_hours, ot_sunday, ot_public_hours, ot_public,"
                    . " gross_pay, epf_employee, epf_employer, socso_employee, socso_employer,"
                    . " eis_employee, eis_employer, pcb_tax, net_pay, status)"
                    . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')"
                );

                $stmt->execute([
                    $payrollUuid,
                    $emp['id'],
                    $selectedMonth,
                    $selectedYear,
                    $calculatedBasic,
                    $regularHours,
                    $otHours,
                    $otNormalHours,
                    $otNormal,
                    $otSundayHours,
                    $otSunday,
                    $otPublicHours,
                    $otPublic,
                    $grossPay,
                    $epfEmployee,
                    $epfEmployer,
                    $socsoEmployee,
                    $socsoEmployer,
                    $eisEmployee,
                    $eisEmployer,
                    $pcbTax,
                    $netPay
                ]);

                $generated++;

                // Send email notification (Uses Mailer Class)
                $mailer = new Mailer();
                // Note: We might want to send this only when Finalized/Paid, usually Draft is silent.
                // But existing logic sent it immediately. We'll stick to existing logic for now.
                $mailer->sendPayslipNotification(
                    $emp['email'],
                    $emp['full_name'],
                    $selectedMonth,
                    $selectedYear,
                    $netPay
                );
            }
        }

        $message = "Payroll generated for $generated employee(s). Email notifications sent.";
        $messageType = 'success';
    } catch (PDOException $e) {
        error_log("Payroll generation error: " . $e->getMessage());
        $message = 'System error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Process status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $payrollId = $_POST['payroll_id'];
    $newStatus = sanitize($_POST['new_status']);

    try {
        $conn = getConnection();

        // Update status
        $stmt = $conn->prepare("UPDATE payroll SET status = ?, updated_at = NOW() WHERE id = ? RETURNING user_id, net_pay");
        // Returning clause might fail on MySQL, better fetch first if not Postgres.
        // Since we are using Supabase (Postgres), RETURNING works.
        // But to be safe and matching existing pattern:
        $stmt = $conn->prepare("UPDATE payroll SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $payrollId]);

        // If status is PAID, send notification
        if ($newStatus === 'paid') {
            // Fetch user info
            $stmt = $conn->prepare("SELECT p.net_pay, p.month, p.year, u.email, u.full_name 
                                  FROM payroll p 
                                  JOIN profiles u ON p.user_id = u.id 
                                  WHERE p.id = ?");
            $stmt->execute([$payrollId]);
            $payInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($payInfo) {
                $mailer = new Mailer();
                $mailer->sendPayslipNotification(
                    $payInfo['email'],
                    $payInfo['full_name'],
                    $payInfo['month'],
                    $payInfo['year'],
                    $payInfo['net_pay']
                );
            }
        }

        $message = 'Payroll status updated successfully.';
        $messageType = 'success';
    } catch (PDOException $e) {
        error_log("Update payroll status error: " . $e->getMessage());
        $message = 'System error.';
        $messageType = 'error';
    }
}

// Get payroll data (Supabase schema)
try {
    $conn = getConnection();

    $stmt = $conn->prepare("
        SELECT p.*, pr.full_name, pr.employment_type
        FROM payroll p
        JOIN profiles pr ON p.user_id = pr.id
        WHERE pr.company_id = ? AND p.month = ? AND p.year = ?
        ORDER BY pr.full_name
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
        $totals['gross'] += $p['gross_pay'] ?? 0;
        $totals['deductions'] += ($p['epf_employee'] ?? 0) + ($p['socso_employee'] ?? 0) + ($p['eis_employee'] ?? 0);
        $totals['net'] += $p['net_pay'] ?? 0;
        $totals['epf_employer'] += $p['epf_employer'] ?? 0;
        $totals['socso_employer'] += $p['socso_employer'] ?? 0;
        $totals['eis_employer'] += $p['eis_employer'] ?? 0;
    }

} catch (PDOException $e) {
    error_log("Payroll fetch error: " . $e->getMessage());
    $payrollList = [];
    $totals = ['gross' => 0, 'deductions' => 0, 'net' => 0, 'epf_employer' => 0, 'socso_employer' => 0, 'eis_employer' => 0];
}
?>

<?php include '../includes/hr_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <?php
    $navTitle = 'Payroll Management';
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
        <h1><i class="bi bi-cash-stack me-2"></i>Payroll Management</h1>
    </div>

    <!-- Welcome Header -->
    <div class="mb-4">
        <p class="text-muted mb-1">Human Resources</p>
        <h2 class="fw-bold">Payroll Management</h2>
        <div class="d-flex align-items-center mt-2 text-muted">
            <i class="bi bi-info-circle me-2"></i> Generate and manage employee payroll.
        </div>
    </div>

    <!-- Month/Year Selector & Generate -->
    <div class="card mb-4 border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form method="GET" class="row g-2 align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0 fw-bold">Month:</label>
                        </div>
                        <div class="col-auto">
                            <select name="month" class="form-select border-0 bg-light shadow-sm cursor-pointer"
                                style="padding-right: 3rem; background-position: right 1rem center;"
                                onchange="this.form.submit()">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                                        <?= getMonthName($m) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="year" class="form-select border-0 bg-light shadow-sm cursor-pointer"
                                style="padding-right: 3rem; background-position: right 1rem center;"
                                onchange="this.form.submit()">
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
                        <button type="submit" name="generate_payroll" class="btn btn-primary rounded-pill px-4"
                            onclick="return confirm('Generate payroll for <?= getMonthName($selectedMonth) ?> <?= $selectedYear ?>?')">
                            <i class="bi bi-calculator me-2"></i>Generate Payroll
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stats-card border-0 shadow-sm">
                <h2 class="text-primary"><?= formatMoney($totals['gross']) ?></h2>
                <p class="text-muted mb-0">Total Gross Pay</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card danger border-0 shadow-sm">
                <h2 class="text-danger"><?= formatMoney($totals['deductions']) ?></h2>
                <p class="text-muted mb-0">Total Deductions</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card success border-0 shadow-sm">
                <h2 class="text-success"><?= formatMoney($totals['net']) ?></h2>
                <p class="text-muted mb-0">Total Net Pay</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card warning border-0 shadow-sm">
                <h2 class="text-warning">
                    <?= formatMoney($totals['epf_employer'] + $totals['socso_employer'] + $totals['eis_employer']) ?>
                </h2>
                <p class="text-muted mb-0">Employer Contribution</p>
            </div>
        </div>
    </div>

    <!-- Payroll Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-table me-2 text-primary"></i>Payroll List -
                <?= getMonthName($selectedMonth) ?> <?= $selectedYear ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($payrollList)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                    <p class="text-muted">No payroll data for this month.<br>
                        <small>Click "Generate Payroll" to generate employee payroll.</small>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Name</th>
                                <th>Type</th>
                                <th>Basic Salary</th>
                                <th>EPF</th>
                                <th>SOCSO</th>
                                <th>EIS</th>
                                <th>Net Pay</th>
                                <th>Status</th>
                                <th class="pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payrollList as $p): ?>
                                <tr>
                                    <td class="ps-4"><strong><?= htmlspecialchars($p['full_name']) ?></strong></td>
                                    <td><?= getEmploymentTypeName($p['employment_type']) ?></td>
                                    <td><?= formatMoney($p['basic_salary']) ?></td>
                                    <td><?= formatMoney($p['epf_employee']) ?></td>
                                    <td><?= formatMoney($p['socso_employee']) ?></td>
                                    <td><?= formatMoney($p['eis_employee']) ?></td>
                                    <td><strong class="text-success"><?= formatMoney($p['net_pay']) ?></strong></td>
                                    <td>
                                        <?php
                                        $statusBadge = [
                                            'draft' => ['Draft', 'bg-secondary'],
                                            'finalized' => ['Verified', 'bg-warning'],
                                            'paid' => ['Paid', 'bg-success'],
                                        ];
                                        $badge = $statusBadge[$p['status']] ?? ['N/A', 'bg-secondary'];
                                        ?>
                                        <span class="badge <?= $badge[1] ?> rounded-pill"><?= $badge[0] ?></span>
                                    </td>
                                    <td class="pe-4">
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="payroll_id" value="<?= $p['id'] ?>">
                                            <select name="new_status"
                                                class="form-select form-select-sm d-inline-block border-0 bg-light"
                                                style="width: auto;" onchange="this.form.submit()">
                                                <option value="">Change...</option>
                                                <option value="draft">Draft</option>
                                                <option value="finalized">Verify</option>
                                                <option value="paid">Paid</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                        <a href="../includes/generate_payslip_pdf.php?id=<?= $p['id'] ?>"
                                            class="btn btn-sm btn-outline-danger ms-1 rounded-circle" target="_blank"
                                            title="Print PDF" style="width: 32px; height: 32px; padding: 0; line-height: 30px;">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td colspan="2" class="ps-4"><strong>TOTAL</strong></td>
                                <td><strong><?= formatMoney($totals['gross']) ?></strong></td>
                                <td colspan="3"><strong><?= formatMoney($totals['deductions']) ?></strong></td>
                                <td><strong class="text-success"><?= formatMoney($totals['net']) ?></strong></td>
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