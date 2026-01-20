<?php
// PART TIME DASHBOARD
?>
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card glass-card border-0 p-4 h-100 animate-fade-in" style="animation-delay: 0.1s">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-sm bg-success text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Shifts Worked</span>
            </div>
            <h2 class="mb-0 fw-bold"><?= $attendanceStats['present'] ?? 0 ?></h2>
            <div class="mt-2" style="font-size: 0.75rem;">
                <span class="text-success fw-bold">Flexible progress</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card glass-card border-0 p-4 h-100 animate-fade-in" style="animation-delay: 0.2s">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-sm bg-purple text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                    <i class="bi bi-clock"></i>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Total Hours</span>
            </div>
            <h2 class="mb-0 fw-bold text-purple"><?= $attendanceStats['total_hours'] ?? 0 ?></h2>
            <div class="mt-2" style="font-size: 0.75rem;">
                <span class="text-muted">Every hour counts</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card glass-card border-0 p-4 h-100 animate-fade-in" style="animation-delay: 0.3s">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-sm bg-danger text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Absent</span>
            </div>
            <h2 class="mb-0 fw-bold text-danger"><?= $attendanceStats['absent'] ?? 0 ?></h2>
            <div class="mt-2" style="font-size: 0.75rem;">
                <span class="text-muted">Availability is key</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card glass-card border-0 p-4 h-100 animate-fade-in" style="animation-delay: 0.4s">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-sm bg-primary text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                    <i class="bi bi-cash"></i>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Est. Earnings</span>
            </div>
            <h2 class="mb-0 fw-bold text-primary">RM <?= number_format(($attendanceStats['present'] ?? 0) * 75, 0) ?></h2>
            <div class="mt-2" style="font-size: 0.75rem;">
                <span class="text-muted">Based on daily rate</span>
            </div>
        </div>
    </div>
</div>