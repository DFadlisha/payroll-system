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
        <h3><i class="bi bi-building me-2"></i>MI-NES</h3>
        <small><?= __('app_subtitle') ?></small>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" <?= $currentPage === 'dashboard' ? 'class="active"' : '' ?>><i class="bi bi-speedometer2"></i> <?= __('nav.dashboard') ?></a></li>
        <li><a href="employees.php" <?= $currentPage === 'employees' ? 'class="active"' : '' ?>><i class="bi bi-people"></i> <?= __('nav.employees') ?></a></li>
        <li><a href="attendance.php" <?= $currentPage === 'attendance' ? 'class="active"' : '' ?>><i class="bi bi-calendar-check"></i> <?= __('nav.attendance') ?></a></li>
        <li><a href="leaves.php" <?= $currentPage === 'leaves' ? 'class="active"' : '' ?>><i class="bi bi-calendar-x"></i> <?= __('nav.leaves') ?></a></li>
        <li><a href="payroll.php" <?= $currentPage === 'payroll' ? 'class="active"' : '' ?>><i class="bi bi-cash-stack"></i> <?= __('nav.payroll') ?></a></li>
        <li><a href="reports.php" <?= $currentPage === 'reports' ? 'class="active"' : '' ?>><i class="bi bi-file-earmark-bar-graph"></i> <?= __('nav.reports') ?></a></li>
        <li class="mt-auto" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 20px;">
            <a href="../auth/logout.php"><i class="bi bi-box-arrow-left"></i> <?= __('logout') ?></a>
        </li>
    </ul>
</nav>
