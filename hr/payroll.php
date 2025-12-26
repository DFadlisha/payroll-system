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

        // Get all employees from profiles table (Supabase schema)
        $stmt = $conn->prepare("SELECT * FROM profiles WHERE company_id = ?");
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

                // Get employee's basic salary from profile
                $basicSalary = $emp['basic_salary'] ?? 0;
                $hourlyRate = $emp['hourly_rate'] ?? 0;
                $employmentType = $emp['employment_type'] ?? 'permanent';

                // Calculate salary based on employment type
                $calculatedBasic = 0;
                if ($employmentType === 'part-time' && $hourlyRate > 0) {
                    $calculatedBasic = $regularHours * $hourlyRate;
                } else {
                    $calculatedBasic = $basicSalary;
                }

                // Calculate OT allowances with automatic day-type detection
                // Get detailed attendance records for OT breakdown
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
                $otSundayHours = 0;
                $otPublicHours = 0;

                $otNormal = 0;
                $otSunday = 0;
                $otPublic = 0;

                foreach ($otRecords as $otRecord) {
                    $otDate = date('Y-m-d', strtotime($otRecord['clock_in']));
                    $otHrs = floatval($otRecord['overtime_hours']);

                    // Determine OT type based on date and accumulate hours + amount
                    if (isPublicHoliday($otDate)) {
                        $otPublicHours += $otHrs;
                        $otPublic += $otHrs * $RATE_OT_PUBLIC;
                    } elseif (isSunday($otDate)) {
                        $otSundayHours += $otHrs;
                        $otSunday += $otHrs * $RATE_OT_SUNDAY;
                    } else {
                        $otNormalHours += $otHrs;
                        $otNormal += $otHrs * $RATE_OT_NORMAL;
                    }
                }

                // Recalculate total OT hours (accurate breakdown sum)
                $otHours = $otNormalHours + $otSundayHours + $otPublicHours;

                // Gross pay calculation
                $grossPay = $calculatedBasic + $otNormal + $otSunday + $otPublic;

                // Statutory deductions based on citizenship_status
                $epfEmployee = 0;
                $epfEmployer = 0;
                $socsoEmployee = 0;
                $socsoEmployer = 0;
                $eisEmployee = 0;
                $eisEmployer = 0;

                $citizenship = $emp['citizenship_status'] ?? 'citizen';

                // Calculate PCB (Monthly Tax Deduction) based on LHDN rates
                $dependents = intval($emp['dependents'] ?? 0);
                $pcbTax = calculatePCB($grossPay, $dependents);

                if ($employmentType !== 'intern') {
                    // EPF: Employee 11%, Employer 12% (for citizens/PR)
                    // Part-timers also subject to EPF if earning > RM10
                    if ($citizenship === 'citizen' || $citizenship === 'permanent_resident') {
                        $epfEmployee = $grossPay * 0.11;
                        $epfEmployer = $grossPay * 0.12;
                    }

                    // SOCSO (Contribution capped at monthly salary of RM 6,000 as of Oct 2024)
                    // Approximation using 0.5% (Employee) and 1.75% (Employer)
                    $socsoEmployee = min($grossPay * 0.005, 29.75); // Capped at ~RM 29.75
                    $socsoEmployer = min($grossPay * 0.0175, 104.15); // Capped at ~RM 104.15

                    // EIS: 0.2% each (Capped at RM 6,000)
                    $eisEmployee = min($grossPay * 0.002, 11.90); // Capped at ~RM 11.90
                    $eisEmployer = min($grossPay * 0.002, 11.90); // Capped at ~RM 11.90
                } else {
                    // Explicitly set 0 for interns to be safe
                    $epfEmployee = 0;
                    $epfEmployer = 0;
                    $socsoEmployee = 0;
                    $socsoEmployer = 0;
                    $eisEmployee = 0;
                    $eisEmployer = 0;
                }

                $netPay = $grossPay - $epfEmployee - $socsoEmployee - $eisEmployee - $pcbTax;

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

                // Send email notification to employee
                $appUrl = Environment::get('APP_URL', 'http://localhost');
                $payslipUrl = $appUrl . '/staff/payslips.php?id=' . $payrollUuid;

                $monthName = getMonthName($selectedMonth);
                $emailSubject = "Slip Gaji {$monthName} {$selectedYear} - MI-NES Payroll";

                $emailBody = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: #0d6efd; color: white; padding: 20px; text-align: center; }
                            .content { background: #f8f9fa; padding: 30px; border: 1px solid #dee2e6; }
                            .amount-box { background: #d4edda; border: 2px solid #28a745; padding: 15px; 
                                         text-align: center; margin: 20px 0; border-radius: 5px; }
                            .amount { font-size: 24pt; font-weight: bold; color: #155724; }
                            .button { display: inline-block; padding: 12px 30px; background: #0d6efd; color: white; 
                                     text-decoration: none; border-radius: 5px; margin: 20px 0; }
                            .info-table { width: 100%; margin: 15px 0; }
                            .info-table td { padding: 8px; border-bottom: 1px solid #dee2e6; }
                            .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>Slip Gaji Bulanan</h2>
                            </div>
                            <div class='content'>
                                <p>Hi <strong>{$emp['full_name']}</strong>,</p>
                                
                                <p>Slip gaji anda untuk bulan <strong>{$monthName} {$selectedYear}</strong> telah disediakan.</p>
                                
                                <div class='amount-box'>
                                    <div>Gaji Bersih (Net Pay)</div>
                                    <div class='amount'>RM " . number_format($netPay, 2) . "</div>
                                </div>
                                
                                <table class='info-table'>
                                    <tr>
                                        <td><strong>Gaji Pokok:</strong></td>
                                        <td style='text-align: right;'>RM " . number_format($calculatedBasic, 2) . "</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pendapatan Kasar:</strong></td>
                                        <td style='text-align: right;'>RM " . number_format($grossPay, 2) . "</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Potongan:</strong></td>
                                        <td style='text-align: right;'>RM " . number_format($epfEmployee + $socsoEmployee + $eisEmployee + $pcbTax, 2) . "</td>
                                    </tr>
                                    <tr style='background: #d4edda;'>
                                        <td><strong>Gaji Bersih:</strong></td>
                                        <td style='text-align: right;'><strong>RM " . number_format($netPay, 2) . "</strong></td>
                                    </tr>
                                </table>
                                
                                <div style='text-align: center;'>
                                    <a href='{$payslipUrl}' class='button'>Lihat Slip Gaji</a>
                                </div>
                                
                                <p style='margin-top: 20px;'>Atau salin pautan ini:</p>
                                <p style='word-break: break-all; color: #0d6efd;'>{$payslipUrl}</p>
                                
                                <p style='margin-top: 20px; font-size: 10pt; color: #666;'>
                                    <strong>Nota:</strong> Sila semak slip gaji anda dengan teliti. 
                                    Jika ada sebarang pertanyaan, sila hubungi Jabatan HR.
                                </p>
                            </div>
                            <div class='footer'>
                                <p>Email ini dijana secara automatik. Sila jangan balas.</p>
                                <p>&copy; " . date('Y') . " MI-NES Payroll System. All rights reserved.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";

                sendEmail($emp['email'], $emailSubject, $emailBody);
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

        $stmt = $conn->prepare("UPDATE payroll SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$newStatus, $payrollId]);

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
// ... (Sidebar translation implicitly handled by replacement or if separate file needs checking)

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div>
            <button class="mobile-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <span class="fw-bold">Payroll Management</span>
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
        <h1><i class="bi bi-cash-stack me-2"></i>Payroll Management</h1>
    </div>

    <!-- Month/Year Selector & Generate -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form method="GET" class="row g-2 align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0">Month:</label>
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
            <div class="stats-card">
                <h2><?= formatMoney($totals['gross']) ?></h2>
                <p>Total Gross Pay</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card danger">
                <h2><?= formatMoney($totals['deductions']) ?></h2>
                <p>Total Deductions</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card success">
                <h2><?= formatMoney($totals['net']) ?></h2>
                <p>Total Net Pay</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card warning">
                <h2><?= formatMoney($totals['epf_employer'] + $totals['socso_employer'] + $totals['eis_employer']) ?>
                </h2>
                <p>Employer Contribution</p>
            </div>
        </div>
    </div>

    <!-- Payroll Table -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table me-2"></i>Payroll List - <?= getMonthName($selectedMonth) ?> <?= $selectedYear ?>
        </div>
        <div class="card-body">
            <?php if (empty($payrollList)): ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                    No payroll data for this month.<br>
                    <small>Click "Generate Payroll" to generate employee payroll.</small>
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Basic Salary</th>
                                <th>EPF</th>
                                <th>SOCSO</th>
                                <th>EIS</th>
                                <th>Net Pay</th>
                                <th>Status</th>
                                <th>Actions</th>
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
                                    <td><strong><?= formatMoney($p['net_pay']) ?></strong></td>
                                    <td>
                                        <?php
                                        $statusBadge = [
                                            'draft' => ['Draft', 'bg-secondary'],
                                            'finalized' => ['Verified', 'bg-warning'],
                                            'paid' => ['Paid', 'bg-success'],
                                        ];
                                        $badge = $statusBadge[$p['status']] ?? ['N/A', 'bg-secondary'];
                                        ?>
                                        <span class="badge <?= $badge[1] ?>"><?= $badge[0] ?></span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="payroll_id" value="<?= $p['id'] ?>">
                                            <select name="new_status" class="form-select form-select-sm d-inline-block"
                                                style="width: auto;" onchange="this.form.submit()">
                                                <option value="">Change...</option>
                                                <option value="draft">Draft</option>
                                                <option value="finalized">Verify</option>
                                                <option value="paid">Paid</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                        <a href="../includes/generate_payslip_pdf.php?id=<?= $p['id'] ?>"
                                            class="btn btn-sm btn-outline-danger ms-1" target="_blank" title="Print PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2"><strong>TOTAL</strong></td>
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