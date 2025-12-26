<?php
// INTERN DASHBOARD
?>
<!-- Intern Specific Welcome -->
<div class="alert alert-warning mb-4">
    <i class="bi bi-mortarboard me-2"></i> You are currently in month
    <strong><?= $user['internship_months'] ?? 1 ?></strong> of your internship.
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stats-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><?= $attendanceStats['present'] ?? 0 ?></h2>
                    <p>Days Present</p>
                </div>
                <i class="bi bi-calendar-check" style="font-size: 2rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><?= $attendanceStats['late'] ?? 0 ?></h2>
                    <p>Days Late</p>
                </div>
                <i class="bi bi-clock-history" style="font-size: 2rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>

    <!-- Interns care about hours logged for logbooks -->
    <div class="col-md-6 col-lg-3">
        <div class="stats-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><?= number_format($attendanceStats['total_hours'], 1) ?></h2>
                    <p>Total Hours</p>
                </div>
                <i class="bi bi-hourglass-split" style="font-size: 2rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><?= $latestPayslip ? formatMoney($latestPayslip['net_salary']) : '-' ?></h2>
                    <p>Allowance</p>
                </div>
                <i class="bi bi-cash-coin" style="font-size: 2rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>
</div>