<?php
/**
 * ============================================
 * HR REPORTS PAGE
 * ============================================
 * Generate and view reports.
 * ============================================
 */

$pageTitle = 'Reports - MI-NES Payroll';
require_once '../includes/header.php';
requireHR();

$companyId = $_SESSION['company_id'];
$reportType = $_GET['report'] ?? '';
$month = intval($_GET['month'] ?? date('n'));
$year = intval($_GET['year'] ?? date('Y'));

$reportData = [];

if ($reportType) {
    try {
        $conn = getConnection();

        switch ($reportType) {
            case 'attendance':
                $stmt = $conn->prepare("
                    SELECT u.full_name, u.employment_type,
                           COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
                           COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late,
                           COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent
                    FROM users u
                    LEFT JOIN attendance a ON u.id = a.user_id 
                         AND MONTH(a.date) = ? AND YEAR(a.date) = ?
                    WHERE u.company_id = ? AND u.is_active = 1
                    GROUP BY u.id
                    ORDER BY u.full_name
                ");
                $stmt->execute([$month, $year, $companyId]);
                $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'leaves':
                $stmt = $conn->prepare("
                    SELECT u.full_name,
                           SUM(CASE WHEN l.leave_type = 'annual' AND l.status = 'approved' THEN l.total_days ELSE 0 END) as annual,
                           SUM(CASE WHEN l.leave_type = 'medical' AND l.status = 'approved' THEN l.total_days ELSE 0 END) as medical,
                           SUM(CASE WHEN l.leave_type = 'emergency' AND l.status = 'approved' THEN l.total_days ELSE 0 END) as emergency,
                           SUM(CASE WHEN l.status = 'approved' THEN l.total_days ELSE 0 END) as total
                    FROM users u
                    LEFT JOIN leaves l ON u.id = l.user_id AND YEAR(l.start_date) = ?
                    WHERE u.company_id = ? AND u.is_active = 1
                    GROUP BY u.id
                    ORDER BY u.full_name
                ");
                $stmt->execute([$year, $companyId]);
                $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;

            case 'payroll':
                $stmt = $conn->prepare("
                    SELECT u.full_name, u.employment_type, p.*
                    FROM payroll p
                    JOIN users u ON p.user_id = u.id
                    WHERE u.company_id = ? AND p.month = ? AND p.year = ?
                    ORDER BY u.full_name
                ");
                $stmt->execute([$companyId, $month, $year]);
                $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
        }

    } catch (PDOException $e) {
        error_log("Report error: " . $e->getMessage());
    }
}
?>

<?php include '../includes/hr_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <?php
    $navTitle = 'Reports';
    include '../includes/top_navbar.php';
    ?>

    <!-- Welcome Header -->
    <div class="mb-4">
        <p class="text-muted mb-1">Human Resources</p>
        <h2 class="fw-bold">Reports</h2>
        <div class="d-flex align-items-center mt-2 text-muted">
            <i class="bi bi-info-circle me-2"></i> Generate and view system reports.
        </div>
    </div>

    <?php if (!$reportType): ?>
        <!-- Report Selection -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-4"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-calendar-check text-primary" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="fw-bold">Attendance Report</h5>
                        <p class="text-muted mb-4">Monthly attendance summary per employee.</p>
                        <a href="?report=attendance" class="btn btn-primary rounded-pill px-4">Generate Report</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5">
                        <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-4"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-calendar-x text-warning" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="fw-bold">Leave Report</h5>
                        <p class="text-muted mb-4">Yearly leave summary per employee.</p>
                        <a href="?report=leaves" class="btn btn-warning text-white rounded-pill px-4">Generate Report</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5">
                        <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-4"
                            style="width: 80px; height: 80px;">
                            <i class="bi bi-cash-stack text-success" style="font-size: 2.5rem;"></i>
                        </div>
                        <h5 class="fw-bold">Payroll Report</h5>
                        <p class="text-muted mb-4">Monthly payroll summary for all employees.</p>
                        <a href="?report=payroll" class="btn btn-success text-white rounded-pill px-4">Generate Report</a>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Filter Form -->
        <div class="card mb-4 border-0 shadow-sm rounded-4">
            <div class="card-body">
                <form method="GET" class="row align-items-center g-3">
                    <input type="hidden" name="report" value="<?= $reportType ?>">

                    <?php if ($reportType !== 'leaves'): ?>
                        <div class="col-auto">
                            <label class="form-label mb-0 fw-bold">Month:</label>
                        </div>
                        <div class="col-auto">
                            <select name="month" class="form-select border-0 bg-light">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                                        <?= getMonthName($m) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="col-auto">
                        <label class="form-label mb-0 fw-bold">Year:</label>
                    </div>
                    <div class="col-auto">
                        <select name="year" class="form-select border-0 bg-light">
                            <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-search me-2"></i> Filter
                        </button>
                    </div>

                    <div class="col-auto">
                        <a href="reports.php" class="btn btn-light rounded-pill px-4 text-muted">
                            <i class="bi bi-arrow-left me-2"></i> Back
                        </a>
                    </div>

                    <div class="col-auto ms-auto">
                        <button type="button" onclick="window.print()" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="bi bi-printer me-2"></i> Print
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Content -->
        <div class="card border-0 shadow-sm rounded-4" id="printArea">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold h5 mb-0">
                    <?php
                    $titles = [
                        'attendance' => 'Attendance Report',
                        'leaves' => 'Leave Report',
                        'payroll' => 'Payroll Report'
                    ];
                    echo '<i class="bi bi-file-text me-2 text-primary"></i>' . ($titles[$reportType] ?? 'Report');
                    ?>
                </span>
                <span class="text-muted">
                    <?php if ($reportType !== 'leaves'): ?>
                        <?= getMonthName($month) ?>         <?= $year ?>
                    <?php else: ?>
                        Year <?= $year ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($reportData)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                        <p class="text-muted">No data available to display.</p>
                    </div>
                <?php elseif ($reportType === 'attendance'): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Name</th>
                                    <th>Type</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Late</th>
                                    <th class="text-center">Absent</th>
                                    <th class="text-center pe-4">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($row['full_name']) ?></td>
                                        <td><?= getEmploymentTypeName($row['employment_type']) ?></td>
                                        <td class="text-center text-success fw-bold"><?= $row['present'] ?></td>
                                        <td class="text-center text-warning fw-bold"><?= $row['late'] ?></td>
                                        <td class="text-center text-danger fw-bold"><?= $row['absent'] ?></td>
                                        <td class="text-center pe-4"><strong><?= $row['present'] + $row['late'] ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($reportType === 'leaves'): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Name</th>
                                    <th class="text-center">Annual</th>
                                    <th class="text-center">Medical</th>
                                    <th class="text-center">Emergency</th>
                                    <th class="text-center pe-4">Total Taken</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($row['full_name']) ?></td>
                                        <td class="text-center"><?= $row['annual'] ?></td>
                                        <td class="text-center"><?= $row['medical'] ?></td>
                                        <td class="text-center"><?= $row['emergency'] ?></td>
                                        <td class="text-center pe-4"><strong><?= $row['total'] ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($reportType === 'payroll'): ?>
                    <?php
                    $totals = ['basic' => 0, 'gross' => 0, 'deductions' => 0, 'net' => 0];
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Name</th>
                                    <th class="text-end">Basic Salary</th>
                                    <th class="text-end">Gross Pay</th>
                                    <th class="text-end">Deductions</th>
                                    <th class="text-end">Net Pay</th>
                                    <th class="text-center pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData as $row):
                                    $totals['basic'] += $row['basic_salary'];
                                    $totals['gross'] += $row['gross_salary'];
                                    $totals['deductions'] += $row['total_deductions'];
                                    $totals['net'] += $row['net_salary'];
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($row['full_name']) ?></td>
                                        <td class="text-end"><?= formatMoney($row['basic_salary']) ?></td>
                                        <td class="text-end"><?= formatMoney($row['gross_salary']) ?></td>
                                        <td class="text-end text-danger"><?= formatMoney($row['total_deductions']) ?></td>
                                        <td class="text-end fw-bold text-success"><?= formatMoney($row['net_salary']) ?></td>
                                        <td class="text-center pe-4">
                                            <?php
                                            $statusBadge = [
                                                'draft' => 'bg-secondary',
                                                'finalized' => 'bg-warning',
                                                'paid' => 'bg-success',
                                            ];
                                            ?>
                                            <span class="badge <?= $statusBadge[$row['status']] ?? 'bg-secondary' ?> rounded-pill">
                                                <?= ucfirst($row['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td class="ps-4"><strong>TOTAL</strong></td>
                                    <td class="text-end"><strong><?= formatMoney($totals['basic']) ?></strong></td>
                                    <td class="text-end"><strong><?= formatMoney($totals['gross']) ?></strong></td>
                                    <td class="text-end text-danger"><strong><?= formatMoney($totals['deductions']) ?></strong>
                                    </td>
                                    <td class="text-end text-success"><strong><?= formatMoney($totals['net']) ?></strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    @media print {

        .sidebar,
        .top-navbar,
        .page-header,
        form,
        .btn {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #000 !important;
        }
    }
</style>

<?php require_once '../includes/footer.php'; ?>