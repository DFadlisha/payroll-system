<?php
/**
 * ============================================
 * STAFF SIDEBAR TEMPLATE
 * ============================================
 * Shared sidebar for all Staff pages
 * ============================================
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay"></div>

<!-- Sidebar -->
<nav class="sidebar">
    <button class="sidebar-close d-md-none" onclick="document.querySelector('.sidebar').classList.remove('show'); document.querySelector('.sidebar-overlay').classList.remove('show'); document.body.style.overflow = '';">
        <i class="bi bi-x"></i>
    </button>
    
    <div class="sidebar-header">
        <h3><i class="bi bi-building me-2"></i>MI-NES</h3>
        <small><?= __('app_subtitle') ?></small>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" <?= $currentPage === 'dashboard' ? 'class="active"' : '' ?>><i class="bi bi-speedometer2"></i> <?= __('nav.dashboard') ?></a></li>
        <li><a href="attendance.php" <?= $currentPage === 'attendance' ? 'class="active"' : '' ?>><i class="bi bi-calendar-check"></i> <?= __('nav.attendance') ?></a></li>
        <li><a href="leaves.php" <?= $currentPage === 'leaves' ? 'class="active"' : '' ?>><i class="bi bi-calendar-x"></i> <?= __('nav.leaves') ?></a></li>
        <li><a href="payslips.php" <?= $currentPage === 'payslips' ? 'class="active"' : '' ?>><i class="bi bi-receipt"></i> <?= __('nav.payslips') ?></a></li>
        <li><a href="profile.php" <?= $currentPage === 'profile' ? 'class="active"' : '' ?>><i class="bi bi-person"></i> <?= __('nav.profile') ?></a></li>
        <li class="mt-auto" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 20px;">
            <a href="../auth/logout.php"><i class="bi bi-box-arrow-left"></i> <?= __('logout') ?></a>
        </li>
    </ul>
</nav>
