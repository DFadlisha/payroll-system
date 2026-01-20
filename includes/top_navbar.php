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
        <div class="d-none d-md-flex align-items-center position-relative transition-all ms-2 group">
            <i class="bi bi-search position-absolute ms-3 text-muted opacity-50"></i>
            <input type="text" class="form-control rounded-pill border-0 ps-5"
                style="min-width: 320px; background-color: rgba(241, 245, 249, 0.7) !important; backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.5) !important;" placeholder="Search (Ctrl + K)">
        </div>
    </div>

    <!-- Right Actions -->
    <div class="d-flex align-items-center gap-4">
        <?= getLanguageSwitcher() ?>

        <!-- Notification Bell -->
        <?php
        $notifCount = 0;
        $notifications = [];

        // Check for alerts (HR)
        if (isHR()) {
            try {
                if (function_exists('getConnection')) {
                    $conn = getConnection();

                    // 1. Pending Payroll
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM payroll WHERE status = 'draft'");
                    $stmt->execute();
                    $pendingPayroll = $stmt->fetchColumn();
                    if ($pendingPayroll > 0) {
                        $notifCount++;
                        $notifications[] = ['msg' => "$pendingPayroll payslips pending review", 'link' => '../hr/payroll.php', 'icon' => 'bi-cash'];
                    }

                    // 2. Pending Leaves
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM leaves WHERE status = 'pending'");
                    $stmt->execute();
                    $pendingLeaves = $stmt->fetchColumn();
                    if ($pendingLeaves > 0) {
                        $notifCount++;
                        $notifications[] = ['msg' => "$pendingLeaves leave requests pending", 'link' => '../hr/leaves.php', 'icon' => 'bi-calendar-check'];
                    }

                    // 3. Present Today (Clocked In)
                    $today = date('Y-m-d');
                    $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) FROM attendance WHERE DATE(clock_in) = ? AND status IN ('active', 'completed')");
                    $stmt->execute([$today]);
                    $presentToday = $stmt->fetchColumn();

                    if ($presentToday > 0) {
                        // We don't increment notifCount for this as it's 'info', not 'alert'. 
                        // Or maybe we do to show activity? 
                        // User said "INSERT NEW NOTIFICATION", implying they want to see it.
                        // I will add it to the list but not necessarily trigger the 'red dot' unless you want alerts for everything.
                        // Using consistent UI: Add to list.
                        // Optional: $notifCount++; (if you want red dot for presence)
                        // Let's keep red dot for "Actions Required" (Pending stuff), but show this in the list.
                        $notifications[] = ['msg' => "$presentToday staff present today", 'link' => '../hr/attendance.php', 'icon' => 'bi-person-check', 'type' => 'info'];
                    }
                }
            } catch (Exception $e) {
            }
        }
        ?>
        <div class="dropdown">
            <div class="position-relative cursor-pointer text-secondary transition-hover" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="bi bi-bell fs-5"></i>
                <?php if ($notifCount > 0): ?>
                    <span
                        class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                        <span class="visually-hidden">New alerts</span>
                    </span>
                <?php endif; ?>
            </div>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2 p-2" style="min-width: 250px;">
                <li>
                    <h6 class="dropdown-header text-uppercase small fw-bold">Notifications</h6>
                </li>
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notification):
                        $isInfo = isset($notification['type']) && $notification['type'] === 'info';
                        $iconBg = $isInfo ? 'bg-primary bg-opacity-10 text-primary' : 'bg-warning bg-opacity-10 text-warning';
                        $subText = $isInfo ? 'Info' : 'Action Required';
                        ?>
                        <li>
                            <a class="dropdown-item rounded-3 py-2 d-flex align-items-center"
                                href="<?= $notification['link'] ?>">
                                <div class="<?= $iconBg ?> rounded-circle p-2 me-3">
                                    <i class="bi <?= $notification['icon'] ?>"></i>
                                </div>
                                <div>
                                    <small class="fw-bold d-block"><?= $notification['msg'] ?></small>
                                    <span class="text-xs text-muted"><?= $subText ?></span>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li><a class="dropdown-item rounded-3 py-3 text-center text-muted">No new notifications</a></li>
                <?php endif; ?>
            </ul>
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