<?php
// INTERN DASHBOARD
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
        <div class="stats-card purple">
            <div class="stats-icon">
                <i class="bi bi-briefcase"></i>
            </div>
            <div>
                <p>Project Hours</p>
                <h2><?= $attendanceStats['active'] ?? 0 ?></h2> <!-- Using active/project hours logic if avail -->
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card orange">
            <div class="stats-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <p>Training Days</p>
                <h2><?= $attendanceStats['total_days'] ?? 0 ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card blue">
            <div class="stats-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <p>Allowance</p>
                <h2><?= $latestPayslip ? 'RM ' . number_format($latestPayslip['net_pay'] ?? 0, 0) : '-' ?></h2>
            </div>
        </div>
    </div>
</div>