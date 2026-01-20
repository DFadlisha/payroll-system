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
    
    <div class="sidebar-header border-bottom border-light mb-2">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-sm bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm p-1" style="width: 42px; height: 42px;">
                <img src="../assets/logos/mi-nes-logo.jpg" alt="Logo" class="img-fluid rounded-circle w-100 h-100 object-fit-cover">
            </div>
            <div class="overflow-hidden">
                <h6 class="mb-0 fw-bold text-dark text-truncate">MI-NES SYSTEM</h6>
                <small class="text-muted text-xs text-uppercase tracking-wider" style="font-size: 0.65rem;">Staff Member</small>
            </div>
        </div>
    </div>
    
    <div class="px-3 py-4 flex-grow-1 overflow-auto">
        <ul class="sidebar-menu list-unstyled mb-0">
            <li class="menu-label small text-muted text-uppercase fw-bold mb-3 px-2" style="font-size: 0.65rem; letter-spacing: 0.05em;">My Workspace</li>
            <li><a href="/staff/dashboard.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'dashboard' ? 'active' : '' ?>"><i class="bi bi-grid-fill me-3"></i> <?= __('nav.dashboard') ?></a></li>
            <li><a href="/staff/attendance.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'attendance' ? 'active' : '' ?>"><i class="bi bi-clock-history me-3"></i> <?= __('nav.attendance') ?></a></li>
            <li><a href="/staff/leaves.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'leaves' ? 'active' : '' ?>"><i class="bi bi-calendar2-heart-fill me-3"></i> <?= __('nav.leaves') ?></a></li>
            <li><a href="/staff/payslips.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'payslips' ? 'active' : '' ?>"><i class="bi bi-receipt-cutoff me-3"></i> <?= __('nav.payslips') ?></a></li>
        </ul>
    </div>

    <div class="sidebar-footer p-3 border-top border-light">
        <ul class="sidebar-menu list-unstyled mb-0">
            <li><a href="/staff/profile.php" class="nav-link rounded-3 mb-1 <?= $currentPage === 'profile' ? 'active' : '' ?>"><i class="bi bi-person-fill-gear me-3"></i> My Profile</a></li>
            <li><a href="/auth/logout.php" class="nav-link rounded-3 text-danger"><i class="bi bi-power me-3"></i> <?= __('logout') ?></a></li>
        </ul>
    </div>
</nav>
