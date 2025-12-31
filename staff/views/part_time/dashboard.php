<?php
// PART TIME DASHBOARD
?>
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stats-card green">
            <div class="stats-icon">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div>
                <p>Shifts Worked</p>
                <h2><?= $attendanceStats['present'] ?? 0 ?></h2>
                <small class="text-success fw-bold">Paid Daily</small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card purple">
            <div class="stats-icon">
                <i class="bi bi-clock"></i>
            </div>
            <div>
                <p>Total Hours</p>
                <h2><?= $attendanceStats['total_hours'] ?? 0 ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card red">
            <div class="stats-icon">
                <i class="bi bi-calendar-x"></i>
            </div>
            <div>
                <p>Absent</p>
                <h2><?= $attendanceStats['absent'] ?? 0 ?></h2>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="stats-card blue">
            <div class="stats-icon">
                <i class="bi bi-cash"></i>
            </div>
            <div>
                <p>Est. Earnings</p>
                <!-- Simple est: RM 75 * days present -->
                <h2>RM <?= number_format(($attendanceStats['present'] ?? 0) * 75, 0) ?></h2>
            </div>
        </div>
    </div>
</div>