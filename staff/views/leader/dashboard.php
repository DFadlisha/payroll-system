<?php
// TEAM LEADER DASHBOARD
?>
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card glass-card border-0 p-4 h-100 animate-fade-in" style="animation-delay: 0.1s">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-sm bg-success text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Days Present</span>
            </div>
            <h2 class="mb-0 fw-bold"><?= $attendanceStats['present'] ?? 0 ?></h2>
            <div class="mt-2" style="font-size: 0.75rem;">
                <span class="text-success fw-bold">Leading by example</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card glass-card border-0 p-4 h-100 animate-fade-in" style="animation-delay: 0.2s">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-sm bg-purple text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                    <i class="bi bi-people"></i>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Team Overview</span>
            </div>
            <h2 class="mb-0 fw-bold text-purple">Active</h2>
            <div class="mt-2" style="font-size: 0.75rem;">
                <span class="text-muted">Directing operations</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card glass-card border-0 p-4 h-100 animate-fade-in" style="animation-delay: 0.3s">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-sm bg-warning text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                    <i class="bi bi-clock-history"></i>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Overtime Hrs</span>
            </div>
            <h2 class="mb-0 fw-bold text-warning"><?= $attendanceStats['overtime_hours'] ?? 0 ?></h2>
            <div class="mt-2" style="font-size: 0.75rem;">
                <span class="text-muted">Dedication shown</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card glass-card border-0 p-4 h-100 animate-fade-in" style="animation-delay: 0.4s">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-sm bg-primary text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center me-3" style="width:40px;height:40px;">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <span class="text-muted small fw-bold text-uppercase">Latest Salary</span>
            </div>
            <h2 class="mb-0 fw-bold text-primary"><?= $latestPayslip ? 'RM ' . number_format($latestPayslip['net_pay'] ?? 0, 0) : '-' ?></h2>
            <div class="mt-2" style="font-size: 0.75rem;">
                <span class="text-muted"><?= $latestPayslip ? date('F Y', strtotime($latestPayslip['year'].'-'.$latestPayslip['month'].'-01')) : 'No Data' ?></span>
            </div>
        </div>
    </div>
</div>