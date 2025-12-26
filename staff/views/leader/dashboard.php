<?php
// LEADER DASHBOARD
?>
<!-- Leader Specific Welcome -->
<div class="alert alert-info mb-4">
    <i class="bi bi-briefcase me-2"></i> Welcome, Team Leader.
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

    <div class="col-md-6 col-lg-3">
        <div class="stats-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><?= $attendanceStats['overtime_hours'] ?? 0 ?></h2>
                    <p>OT Hours</p>
                </div>
                <i class="bi bi-stopwatch" style="font-size: 2rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><?= $latestPayslip ? formatMoney($latestPayslip['net_salary']) : '-' ?></h2>
                    <p>Latest Salary</p>
                </div>
                <i class="bi bi-cash-stack" style="font-size: 2rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>
</div>