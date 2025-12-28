<?php
/**
 * ============================================
 * HEADER TEMPLATE
 * ============================================
 * Header file for all pages.
 * Include at the beginning of each page.
 * ============================================
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/language.php';

// Set base URL
$baseUrl = '';

// Get current page for navigation highlight
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentFolder = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLang() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? __('app_name') ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Staff Mobile CSS -->
    <?php if ($currentFolder === 'staff'): ?>
        <link href="../assets/css/staff-mobile.css" rel="stylesheet">
    <?php endif; ?>

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2D3436;
            --sidebar-width: 250px;
            --sidebar-bg: #FFFFFF;
            --sidebar-text: #2D3436;
            --sidebar-active-bg: #D4F8D4;
            /* Light Green */
            --bg-color: #F7F8FA;

            /* Pastel Card Colors */
            --card-purple: #E6E1FF;
            --card-green: #D4F8D4;
            --card-orange: #FFE5D4;
            --card-blue: #D4E9FF;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background-color: var(--bg-color);
            color: #2D3436;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            padding-top: 20px;
            z-index: 1000;
            transition: all 0.3s;
            border-right: 1px solid #EAEAEA;
        }

        .sidebar-header {
            padding: 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-header h3 {
            color: var(--sidebar-text);
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .sidebar-header small {
            display: none;
            /* Hide subtitle for cleaner look */
        }

        .sidebar-menu {
            list-style: none;
            padding: 0 16px;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 8px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #636E72;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s;
            font-weight: 500;
        }

        .sidebar-menu a:hover {
            background: #F5F6F8;
            color: var(--sidebar-text);
        }

        .sidebar-menu a.active {
            background-color: #C1F0C1;
            /* Bright lime green active state */
            color: #1a1a1a;
            font-weight: 600;
        }

        .sidebar-menu a i {
            margin-right: 12px;
            font-size: 1.2rem;
            color: inherit;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: 100vh;
        }

        /* Top Navbar */
        .top-navbar {
            background: transparent;
            padding: 0;
            margin-bottom: 30px;
            box-shadow: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-navbar .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2D3436;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 12px;
            background: #fff;
            border-radius: 30px;
            border: 1px solid #EAEAEA;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #6C5CE7;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            background: #fff;
            padding: 20px;
            margin-bottom: 24px;
        }

        .card-header {
            background: transparent;
            border-bottom: none;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 0 0 16px 0;
        }

        /* Stats Cards */
        .stats-card {
            padding: 24px;
            border-radius: 20px;
            position: relative;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .stats-card:hover {
            transform: translateY(-2px);
        }

        /* Pastel Themes */
        .stats-card.purple {
            background-color: var(--card-purple);
            color: #3D2C8D;
        }

        .stats-card.green {
            background-color: var(--card-green);
            color: #1B5E20;
        }

        .stats-card.orange {
            background-color: var(--card-orange);
            color: #943D1B;
        }

        .stats-card.blue {
            background-color: var(--card-blue);
            color: #1565C0;
        }

        /* Legacy support map */
        .stats-card.success {
            background-color: var(--card-green);
            color: #1B5E20;
        }

        .stats-card.warning {
            background-color: var(--card-orange);
            color: #943D1B;
        }

        .stats-card.info {
            background-color: var(--card-blue);
            color: #1565C0;
        }

        .stats-card.danger {
            background-color: #FFD4D4;
            color: #C62828;
        }

        .stats-card h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }

        .stats-card p {
            margin: 0;
            font-weight: 600;
            font-size: 0.95rem;
            opacity: 0.8;
        }

        .stats-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.4);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stats-card i {
            font-size: 1rem;
        }

        /* Tables */
        .table {
            margin-bottom: 0;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .mobile-toggle {
                display: block !important;
            }
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Page Header */
        .page-header {
            margin-bottom: 25px;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 600;
            margin: 0;
        }

        .page-header .breadcrumb {
            margin: 5px 0 0 0;
            padding: 0;
            background: none;
        }

        /* Form Styles */
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(20, 184, 166, 0.15);
        }

        /* Buttons */
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: #0056b3;
            border-color: #0056b3;
        }

        /* Loading Spinner */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading.show {
            display: flex;
        }

        /* Mobile Sidebar Overlay */
        .sidebar-overlay {
            display: none;
        }
    </style>

    <!-- Mobile Menu Script -->
    <script>
        // Mobile menu toggle functionality
        function initMobileMenu() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            const toggleBtn = document.querySelector('.mobile-toggle');

            if (!sidebar) return;

            // Create overlay if it doesn't exist
            if (!overlay) {
                const newOverlay = document.createElement('div');
                newOverlay.className = 'sidebar-overlay';
                document.body.appendChild(newOverlay);
            }

            // Toggle sidebar
            function toggleSidebar() {
                const sidebarEl = document.querySelector('.sidebar');
                const overlayEl = document.querySelector('.sidebar-overlay');

                if (sidebarEl && overlayEl) {
                    sidebarEl.classList.toggle('show');
                    overlayEl.classList.toggle('show');
                    document.body.style.overflow = sidebarEl.classList.contains('show') ? 'hidden' : '';
                }
            }

            // Close sidebar
            function closeSidebar() {
                const sidebarEl = document.querySelector('.sidebar');
                const overlayEl = document.querySelector('.sidebar-overlay');

                if (sidebarEl && overlayEl) {
                    sidebarEl.classList.remove('show');
                    overlayEl.classList.remove('show');
                    document.body.style.overflow = '';
                }
            }

            // Event listeners
            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleSidebar);
            }

            const overlayEl = document.querySelector('.sidebar-overlay');
            if (overlayEl) {
                overlayEl.addEventListener('click', closeSidebar);
            }

            // Close sidebar when clicking on a menu link (mobile)
            const menuLinks = document.querySelectorAll('.sidebar-menu a');
            menuLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        closeSidebar();
                    }
                });
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initMobileMenu);
    </script>
</head>

<body>
    <!-- Loading Spinner -->
    <div class="loading" id="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>