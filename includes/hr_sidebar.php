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
    <div class="sidebar-header">
        <h3><i class="bi bi-building me-2"></i><?= strtoupper($_SESSION['company_name'] ?? 'MI-NES') ?></h3>
        <small><?= __('app_subtitle') ?></small>
    </div>

    <ul class="sidebar-menu">
        <li><a href="/hr/dashboard.php" <?= $currentPage === 'dashboard' ? 'class="active"' : '' ?>><i
                    class="bi bi-speedometer2"></i> <?= __('nav.dashboard') ?></a></li>
        <li><a href="/hr/employees.php" <?= $currentPage === 'employees' ? 'class="active"' : '' ?>><i
                    class="bi bi-people"></i> <?= __('nav.employees') ?></a></li>
        <li><a href="/hr/attendance.php" <?= $currentPage === 'attendance' ? 'class="active"' : '' ?>><i
                    class="bi bi-calendar-check"></i> <?= __('nav.attendance') ?></a></li>
        <li><a href="/shared/attendance.php" <?= $currentPage === 'attendance' ? 'class="active"' : '' ?>><i class="bi bi-search"></i> Monitor</a></li>
        <li><a href="/hr/leaves.php" <?= $currentPage === 'leaves' ? 'class="active"' : '' ?>><i class="bi bi-calendar-x"></i>
                <?= __('nav.leaves') ?></a></li>
        <li><a href="/shared/payroll.php" <?= $currentPage === 'payroll' ? 'class="active"' : '' ?>><i
                    class="bi bi-cash-stack"></i> <?= __('nav.payroll') ?></a></li>
        <li><a href="/shared/holidays.php" <?= $currentPage === 'holidays' ? 'class="active"' : '' ?>><i
                    class="bi bi-calendar-event"></i> <?= __('nav.public_holidays') ?></a></li>
        <li><a href="/hr/locations.php" <?= $currentPage === 'locations' ? 'class="active"' : '' ?>><i
                    class="bi bi-geo-alt"></i> Locations</a></li>
        <li><a href="/shared/reports.php" <?= $currentPage === 'reports' ? 'class="active"' : '' ?>><i
                    class="bi bi-file-earmark-bar-graph"></i> <?= __('nav.reports') ?></a></li>
        <li><a href="/hr/settings.php" <?= $currentPage === 'settings' ? 'class="active"' : '' ?>><i class="bi bi-gear"></i>
                <?= __('Settings') ?></a></li>
        <li class="mt-auto" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 20px;">
            <a href="/auth/logout.php"><i class="bi bi-box-arrow-left"></i> <?= __('logout') ?></a>
        </li>
    </ul>
</nav>