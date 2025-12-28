<?php
// INTERN DASHBOARD
?>
<!-- Welcome Message -->
<div class="mb-4">
    <p class="text-muted mb-1">Overview</p>
    <h4 class="fw-bold">Internship Progress</h4>
    <div class="d-flex align-items-center mt-2 text-muted">
        <i class="bi bi-calendar3 me-2"></i> Current Month: <strong><?= $user['internship_months'] ?? 1 ?></strong>
    </div>
</div>

<!-- Stats Cards Grid -->
<div class="row g-4 mb-5">
    <!-- Present Days (Purple) -->
    <div class="col-md-6 col-lg-3">
        <div class="stats-card purple">
            <div class="stats-icon">
                <i class="bi bi-person-check text-dark"></i>
            </div>
            <div>
                <p>Days Present</p>
                <h2><?= $attendanceStats['present'] ?? 0 ?></h2>
            </div>
        </div>
    </div>

    <!-- Allowance (Green) -->
    <div class="col-md-6 col-lg-3">
        <div class="stats-card green">
            <div class="stats-icon">
                <i class="bi bi-cash-coin text-dark"></i>
            </div>
            <div>
                <p>Allowance</p>
                <h2><?= $latestPayslip ? 'RM' . number_format($latestPayslip['net_salary'], 0) : '-' ?></h2>
            </div>
        </div>
    </div>

    <!-- Late Days (Orange) -->
    <div class="col-md-6 col-lg-3">
        <div class="stats-card orange">
            <div class="stats-icon">
                <i class="bi bi-clock-history text-dark"></i>
            </div>
            <div>
                <p>Days Late</p>
                <h2><?= $attendanceStats['late'] ?? 0 ?></h2>
            </div>
        </div>
    </div>

    <!-- Total Hours (Blue) -->
    <div class="col-md-6 col-lg-3">
        <div class="stats-card blue">
            <div class="stats-icon">
                <i class="bi bi-hourglass-split text-dark"></i>
            </div>
            <div>
                <p>Total Hours</p>
                <h2><?= number_format($attendanceStats['total_hours'], 0) ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity / Info Section matching image lower part structure -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Recent Attendance</span>
                <i class="bi bi-arrow-up-right-circle text-muted"></i>
            </div>
            <div class="card-body">
                <!-- Simple list as placeholder for "Individual Reports" -->
                <div class="d-flex align-items-center mb-4 p-3 rounded" style="background: #F8F9FA;">
                    <div class="bg-white p-2 rounded-circle shadow-sm me-3 text-center"
                        style="width: 48px; height: 48px; line-height: 32px;">
                        <span class="fw-bold text-primary"><?= date('d') ?></span>
                    </div>
                    <div>
                        <h6 class="mb-1 fw-bold">Today's Check-in</h6>
                        <small class="text-muted">Recorded at <?= date('H:i A') ?></small>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Present</span>
                    </div>
                </div>

                <p class="text-muted text-center mt-5">
                    <i class="bi bi-bar-chart-line mb-2" style="font-size: 2rem; opacity: 0.2;"></i><br>
                    Attendance performance is good!
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <span>Monthly Overview</span>
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <div class="position-relative mb-3" style="width: 150px; height: 150px;">
                    <!-- CSS Donut Chart Placeholder -->
                    <div
                        style="width: 100%; height: 100%; border-radius: 50%; border: 15px solid #F0F0F0; border-top: 15px solid var(--card-purple); border-right: 15px solid var(--card-blue);">
                    </div>
                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                        <h4 class="m-0 fw-bold"><?= intval($attendanceStats['attendance_percentage'] ?? 0) ?>%</h4>
                        <small class="text-muted">Attendance</small>
                    </div>
                </div>
                <div class="w-100 mt-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted"><span
                                class="badge bg-primary dot p-1 me-2 rounded-circle"></span>Present</span>
                        <span class="fw-bold"><?= $attendanceStats['present'] ?? 0 ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><span
                                class="badge bg-danger dot p-1 me-2 rounded-circle"></span>Absent</span>
                        <span class="fw-bold"><?= $attendanceStats['absent'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>