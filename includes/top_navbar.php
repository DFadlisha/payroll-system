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
    <div>
        <button class="mobile-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
        <span class="fw-bold"><?= $navTitle ?? __('nav.dashboard') ?></span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <?= getLanguageSwitcher() ?>
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div>
                <div class="fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <small class="text-muted"><?= __('roles.' . $_SESSION['role']) ?></small>
            </div>
        </div>
    </div>
</div>
