<?php
// PART-TIME DASHBOARD
?>
<!-- Part-Time Specific Welcome -->
<div class="alert alert-primary mb-4">
    <i class="bi bi-clock me-2"></i> Happy working! Don't forget to clock in/out for your hours.
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

    <!-- Part-Timers focus on Hours Worked -->
    <div class="col-md-6 col-lg-3">
        <div class="stats-card info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><?= number_format($attendanceStats['total_hours'], 1) ?></h2>
                    <p>Total Hours</p>
                </div>
                <i class="bi bi-stopwatch" style="font-size: 2rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><?= formatMoney($user['hourly_rate'] ?? 0) ?></h2>
                    <p>Hourly Rate</p>
                </div>
                <i class="bi bi-coin" style="font-size: 2rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><?= $latestPayslip ? formatMoney($latestPayslip['net_salary']) : '-' ?></h2>
                    <p>Latest Pay</p>
                </div>
                <i class="bi bi-cash-stack" style="font-size: 2rem; opacity: 0.7;"></i>
            </div>
        </div>
    </div>
</div>