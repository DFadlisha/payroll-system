<?php
/**
 * ============================================
 * PAYROLL REPORTS EXPORT
 * ============================================
 * Export payroll and attendance data to CSV/Excel.
 * ============================================
 */

require_once '../includes/header.php';
requireHR(); // Ensure access rights

$pageTitle = 'Reports & Exports - MI-NES Payroll';

// Default values: Current Month/Year
$selectedMonth = $_GET['month'] ?? date('n');
$selectedYear = $_GET['year'] ?? date('Y');
?>

<?php include '../includes/hr_sidebar.php'; ?>

<div class="main-content">
    <?php
    $navTitle = __('nav.reports');
    include '../includes/top_navbar.php';
    ?>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <h2 class="fw-bold text-dark mb-4"><i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>Reports
                Center</h2>
            <p class="text-muted">Generate and download comprehensive reports for payroll and attendance analysis. Files
                are downloaded in CSV format, compatible with Microsoft Excel.</p>

            <form action="export_report.php" method="GET" class="row g-3 align-items-end p-4 bg-light rounded-3 border">

                <!-- Report Type -->
                <div class="col-md-4">
                    <label class="form-label fw-bold">Report Type</label>
                    <select name="type" class="form-select form-select-lg" required>
                        <option value="payroll_summary">💰 Payroll Summary (Monthly)</option>
                        <option value="attendance_summary">🕒 Attendance Statistics</option>
                        <option value="employee_list">👥 Complete Employee List</option>
                    </select>
                </div>

                <!-- Month -->
                <div class="col-md-3">
                    <label class="form-label fw-bold">Month</label>
                    <select name="month" class="form-select form-select-lg">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Year -->
                <div class="col-md-2">
                    <label class="form-label fw-bold">Year</label>
                    <select name="year" class="form-select form-select-lg">
                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $selectedYear ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Action -->
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold">
                        <i class="bi bi-download me-2"></i>Download CSV
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Previews / Info Cards -->
    <div class="row g-4">
        <!-- Payroll Info -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-blue-light text-primary rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-cash-coin fs-1"></i>
                    </div>
                    <h5 class="fw-bold">Payroll Summary</h5>
                    <p class="text-muted small">Includes Basic Salary, Overtime breakdown, EPF, SOSCO, EIS, Tax, and Net
                        Pay for all employees.</p>
                </div>
            </div>
        </div>

        <!-- Attendance Info -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-green-light text-success rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-calendar-check fs-1"></i>
                    </div>
                    <h5 class="fw-bold">Attendance Stats</h5>
                    <p class="text-muted small">Aggregated data on Present days, Lateness (mins), Absence, and total OT
                        hours worked.</p>
                </div>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-purple-light text-purple rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-people fs-1"></i>
                    </div>
                    <h5 class="fw-bold">Employee Data</h5>
                    <p class="text-muted small">Full list of active employees, designated roles, joined dates, and
                        contact information.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>