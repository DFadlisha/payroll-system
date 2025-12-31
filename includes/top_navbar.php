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
        <!-- Mobile Toggle (Visible only on mobile) -->
        <button class="mobile-toggle me-3 text-secondary" onclick="toggleSidebar()"><i
                class="bi bi-list fs-4"></i></button>

        <!-- Search Bar (Minimalist) -->
        <div class="d-none d-md-flex align-items-center position-relative text-muted ms-2">
            <i class="bi bi-search position-absolute ms-3"></i>
            <input type="text" class="form-control rounded-pill border-0 ps-5 bg-transparent"
                style="min-width: 300px; background-color: #F1F5F9 !important;" placeholder="Search (Ctrl + K)">
        </div>
    </div>

    <!-- Right Actions -->
    <div class="d-flex align-items-center gap-4">
        <?= getLanguageSwitcher() ?>

        <!-- Notification Bell (Placeholder) -->
        <div class="position-relative cursor-pointer text-secondary transition-hover">
            <i class="bi bi-bell fs-5"></i>
            <span
                class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                <span class="visually-hidden">New alerts</span>
            </span>
        </div>

        <!-- User Profile (Minimal) -->
        <div class="dropdown">
            <div class="d-flex align-items-center gap-3 cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold text-dark fs-7 lh-1 mb-1 text-truncate" style="max-width: 150px;">
                        <?= htmlspecialchars($_SESSION['full_name']) ?>
                    </div>
                    <small class="text-secondary" style="font-size: 0.75rem;"><?= ucfirst($_SESSION['role']) ?></small>
                </div>
                <div class="user-avatar shadow-sm border border-2 border-white">
                    <?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?>
                </div>
            </div>
            <!-- Dropdown Menu -->
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2 p-2">
                <li><a class="dropdown-item rounded-3 py-2" href="../staff/profile.php"><i
                            class="bi bi-person me-2"></i>Profile</a></li>
                <?php if (isHR()): ?>
                    <li><a class="dropdown-item rounded-3 py-2" href="../hr/settings.php"><i
                                class="bi bi-gear me-2"></i>Settings</a></li>
                <?php endif; ?>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item rounded-3 py-2 text-danger" href="../auth/logout.php"><i
                            class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
</div>