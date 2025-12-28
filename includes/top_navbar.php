<?php
/**
 * ============================================
 * TOP NAVBAR TEMPLATE
 * ============================================
 * Shared top navbar for all pages
 * @param string $pageTitle The title to display
 * ============================================
 */
?>
<!-- Top Navbar -->
<div class="top-navbar">
    <div class="d-flex align-items-center">
        <button class="mobile-toggle me-3" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
        <div class="page-title"><?= $navTitle ?? __('nav.dashboard') ?></div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <?= getLanguageSwitcher() ?>

        <!-- Search Bar (Visual Only as per design) -->
        <div class="d-none d-md-block me-3 position-relative">
            <i class="bi bi-search position-absolute text-muted"
                style="left: 10px; top: 50%; transform: translateY(-50%);"></i>
            <input type="text" class="form-control rounded-pill ps-5 bg-white border-0" placeholder="Search..."
                style="min-width: 250px;">
        </div>

        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div class="d-none d-md-block">
                <div class="fw-bold fs-7"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <small class="text-muted d-block lh-1"
                    style="font-size: 0.7rem;"><?= __('roles.' . $_SESSION['role']) ?></small>
            </div>
        </div>
    </div>
</div>