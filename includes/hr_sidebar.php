<?php
/**
 * ============================================
 * HR SIDEBAR TEMPLATE
 * ============================================
 * Shared sidebar for all HR pages
 * ============================================
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-header border-bottom border-light mb-2">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px;">
                <i class="bi bi-stack text-white fs-5"></i>
            </div>
            <div class="overflow-hidden">
                <h6 class="mb-0 fw-bold text-dark text-truncate"><?= strtoupper($_SESSION['company_name'] ?? 'MI-NES') ?></h6>
                <small class="text-muted text-xs text-uppercase tracking-wider" style="font-size: 0.65rem;">HR Executive</small>
            </div>
        </div>
    </div>

    <div class="px-3 py-2 flex-grow-1 overflow-auto">
        <ul class="sidebar-menu list-unstyled mb-0">
            <li class="menu-label small text-muted text-uppercase fw-bold mb-2 px-2" style="font-size: 0.65rem; letter-spacing: 0.05em;">Main Menu</li>
            <li><a href="/hr/dashboard.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-grid-fill me-3"></i> <?= __('nav.dashboard') ?></a></li>
            <li><a href="/hr/employees.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'employees' ? 'active' : '' ?>"><i class="bi bi-person-badge-fill me-3"></i> <?= __('nav.employees') ?></a></li>
            <li><a href="/hr/attendance.php" class="nav-link rounded-3 mb-1 <?= ($currentPage === 'attendance' && $currentFolder === 'hr') ? 'active' : '' ?>"><i class="bi bi-clock-fill me-3"></i> <?= __('nav.attendance') ?></a></li>
            <li><a href="/shared/attendance.php" class="nav-link rounded-3 mb-1 <?= ($currentPage === 'attendance' && $currentFolder === 'shared') ? 'active' : '' ?>"><i class="bi bi-shield-check me-3"></i> Monitor</a></li>
            <li><a href="/hr/leaves.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'leaves' ? 'active' : '' ?>"><i class="bi bi-calendar2-week-fill me-3"></i> <?= __('nav.leaves') ?></a></li>
            
            <li class="menu-label small text-muted text-uppercase fw-bold mb-2 mt-4 px-2" style="font-size: 0.65rem; letter-spacing: 0.05em;">Operations</li>
            <li><a href="/shared/payroll.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'payroll' ? 'active' : '' ?>"><i class="bi bi-wallet2 me-3"></i> <?= __('nav.payroll') ?></a></li>
            <li><a href="/shared/holidays.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'holidays' ? 'active' : '' ?>"><i class="bi bi-calendar-event-fill me-3"></i> <?= __('nav.public_holidays') ?></a></li>
            <li><a href="/hr/locations.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'locations' ? 'active' : '' ?>"><i class="bi bi-geo-alt-fill me-3"></i> Locations</a></li>
            <li><a href="/shared/reports.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'reports' ? 'active' : '' ?>"><i class="bi bi-bar-chart-steps me-3"></i> <?= __('nav.reports') ?></a></li>
        </ul>
    </div>

    <div class="sidebar-footer p-3 border-top border-light">
        <ul class="sidebar-menu list-unstyled mb-0">
            <li><a href="/hr/settings.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'settings' ? 'active' : '' ?>"><i class="bi bi-gear-fill me-3"></i> <?= __('Settings') ?></a></li>
            <li><a href="/auth/logout.php" class="nav-link rounded-3 text-danger"><i class="bi bi-power me-3"></i> <?= __('logout') ?></a></li>
        </ul>
    </div>
</nav>