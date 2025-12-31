<?php
// STAFF (Full-Time) DASHBOARD
?>
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stats-card green">
            <div class="stats-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div>
                <p>Days Present</p>
                <h2><?= $attendanceStats['present'] ?? 0 ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card orange">
            <div class="stats-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <p>Days Late</p>
                <h2><?= $attendanceStats['late'] ?? 0 ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card red">
            <div class="stats-icon">
                <i class="bi bi-calendar-x"></i>
            </div>
            <div>
                <p>Days Absent</p>
                <h2><?= $attendanceStats['absent'] ?? 0 ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card blue">
            <div class="stats-icon">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <p>Latest Salary</p>
                <h2><?= $latestPayslip ? 'RM ' . number_format($latestPayslip['net_pay'] ?? 0, 0) : '-' ?></h2>
            </div>
        </div>
    </div>
</div>