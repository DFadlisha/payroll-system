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


    <!-- Google Fonts: Outfit (Headings) & Inter (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">


    <!-- Custom CSS -->
    <style>
        :root {
            /* Branding Colors - Modern Indigo & Slate */
            --primary-color: #4F46E5; /* Indigo 600 */
            --primary-light: #818CF8;
            --primary-dark: #3730A3;
            --secondary-color: #64748B; /* Slate 500 */
            --accent-color: #F59E0B; /* Amber 500 */
            
            /* Status Colors */
            --success-color: #10B981;
            --info-color: #3B82F6;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;

            /* Sidebar & Layout */
            --sidebar-width: 280px;
            --sidebar-bg: #FFFFFF;
            --sidebar-text: #334155;
            --sidebar-active-bg: #F5F3FF;
            --sidebar-active-text: #4F46E5;

            /* Backgrounds */
            --bg-color: #F8FAFC;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: 1px solid rgba(255, 255, 255, 0.4);
            --glass-blur: blur(12px);

            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-md: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);

            /* Card Palette (Refined) */
            --card-purple: #F5F3FF;
            --card-purple-text: #7C3AED;
            --card-purple-icon: #DDD6FE;

            --card-green: #ECFDF5;
            --card-green-text: #059669;
            --card-green-icon: #A7F3D0;

            --card-blue: #EFF6FF;
            --card-blue-text: #2563EB;
            --card-blue-icon: #BFDBFE;

            --card-red: #FEF2F2;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-color);
            color: #1E293B;
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.01em;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: #0F172A;
            letter-spacing: -0.02em;
        }

        /* Utility Classes */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: var(--glass-border);
            box-shadow: var(--shadow-md);
            border-radius: 20px;
        }

        .premium-shadow {
            box-shadow: var(--shadow-lg);
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-premium {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white !important;
            border: none;
            padding: 10px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.35);
        }

        .btn-premium:active {
            transform: scale(0.98);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
            z-index: 1000;
            border-right: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 24px;
            margin-bottom: 8px;
        }

        .sidebar-header h3 {
            color: #0F172A;
            margin: 0;
            font-size: 1.1rem; /* Reduced from 1.5rem */
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.2;
            word-break: break-word; /* Allow wrapping for long names */
        }

        .sidebar-menu {
            list-style: none;
            padding: 0 16px;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        /* User Avatar fix */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--card-blue);
            color: var(--card-blue-text);
            font-weight: 700;
            font-size: 1rem;
        }

        .sidebar-menu .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            color: #64748B;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }

        .sidebar-menu .nav-link:hover {
            background: #F8FAFC;
            color: var(--primary-color);
            transform: translateX(4px);
        }

        .sidebar-menu .nav-link.active {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            font-weight: 600;
            box-shadow: inset 0 0 0 1px rgba(79, 70, 229, 0.1);
        }

        .sidebar-menu .nav-link.active i {
            color: var(--sidebar-active-text);
        }

        .sidebar-menu .nav-link i {
            font-size: 1.25rem;
            color: #94A3B8;
            transition: color 0.2s;
        }

        .sidebar-menu .nav-link:hover i {
            color: var(--primary-color);
        }

        .text-xs { font-size: 0.75rem; }
        .tracking-wider { letter-spacing: 0.05em; }
        .tracking-widest { letter-spacing: 0.1em; }
        .cursor-pointer { cursor: pointer; }
        .bg-success-soft { background-color: rgba(16, 185, 129, 0.1); }
        .avatar-sm { width: 32px; height: 32px; }
        .avatar-lg { width: 64px; height: 64px; }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 40px;
            min-height: 100vh;
            background: #F8FAFC;
            /* Clean solid background for professional feel */
            background-image: radial-gradient(#F1F5F9 2px, transparent 2px);
            background-size: 40px 40px;
            /* Subtle dot pattern */
        }

        /* Top Navbar */
        .top-navbar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            padding: 15px 25px;
            margin: -40px -40px 40px -40px; /* Offset main-content padding */
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .top-navbar .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #0F172A;
            letter-spacing: -1px;
        }

        /* Cards */
        .card {
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            background: #FFFFFF;
            padding: 24px;
            margin-bottom: 24px;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .card:hover {
            border-color: rgba(203, 213, 225, 0.8);
            box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.08);
            /* More refined shadow */
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #F1F5F9;
            font-weight: 700;
            font-size: 1.1rem;
            color: #334155;
            padding: 0 0 16px 0;
            margin-bottom: 20px;
        }

        /* Stats Cards */
        .stats-card {
            padding: 28px;
            border-radius: 24px;
            position: relative;
            min-height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        /* Color Variations */
        .stats-card.purple {
            background-color: var(--card-purple);
            color: var(--card-purple-text);
        }

        .stats-card.purple .stats-icon-bg {
            background: white;
            color: var(--card-purple-text);
        }

        .stats-card.green,
        .stats-card.success {
            background-color: var(--card-green);
            color: var(--card-green-text);
        }

        .stats-card.green .stats-icon-bg,
        .stats-card.success .stats-icon-bg {
            background: white;
            color: var(--card-green-text);
        }

        .stats-card.orange,
        .stats-card.warning {
            background-color: var(--card-orange);
            color: var(--card-orange-text);
        }

        .stats-card.orange .stats-icon-bg,
        .stats-card.warning .stats-icon-bg {
            background: white;
            color: var(--card-orange-text);
        }

        .stats-card.blue,
        .stats-card.info {
            background-color: var(--card-blue);
            color: var(--card-blue-text);
        }

        .stats-card.blue .stats-icon-bg,
        .stats-card.info .stats-icon-bg {
            background: white;
            color: var(--card-blue-text);
        }

        .stats-card.red,
        .stats-card.danger {
            background-color: var(--card-red);
            color: var(--card-red-text);
        }

        .stats-card.red .stats-icon-bg,
        .stats-card.danger .stats-icon-bg {
            background: white;
            color: var(--card-red-text);
        }

        .stats-card h2 {
            font-size: 2.75rem;
            font-weight: 800;
            margin: 0;
            line-height: 1.1;
            letter-spacing: -1px;
        }

        .stats-card p {
            margin: 0;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.8;
        }

        .stats-icon {
            position: absolute;
            top: 24px;
            right: 24px;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Helper for icon background in new cards */
        .stats-icon-bg {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        /* Buttons & Forms */
        .btn {
            padding: 12px 24px;
            border-radius: 50px;
            /* Pill shaped */
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background-color: #0F172A;
            /* Darker, more professional primary */
            border: 1px solid #0F172A;
        }

        .btn-primary:hover {
            background-color: #1E293B;
            border-color: #1E293B;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25);
        }

        .btn-success {
            background-color: #10B981;
            border: 1px solid #10B981;
        }

        .btn-success:hover {
            background-color: #059669;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
            transform: translateY(-2px);
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #E2E8F0;
            background-color: #FFFFFF;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            border-color: #818CF8;
        }

        /* Modern Tables */
        .table {
            --bs-table-bg: transparent;
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            color: #64748B;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 16px 24px;
            background: #F8FAFC;
            border-bottom: 2px solid #F1F5F9;
        }

        .table td {
            padding: 20px 24px;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #F1F5F9;
            font-size: 0.95rem;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table-hover tbody tr:hover {
            background-color: #F8FAFC;
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
                padding: 20px;
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
            color: #334155;
        }

        /* Loading & Overlay */
        .sidebar-overlay {
            display: none;
            margin: 0;
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.3);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading.show {
            display: flex;
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
    <!-- PWA -->
    <link rel="manifest" href="<?= $baseUrl ?>/manifest.json">
    <meta name="theme-color" content="#4F46E5">
    <link rel="apple-touch-icon" href="<?= $baseUrl ?>/assets/logos/nes.jpg">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?= $baseUrl ?>/sw.js')
                    .then(reg => console.log('SW registered'))
                    .catch(err => console.log('SW failed', err));
            });
        }
    </script>
</head>

<body>
    <!-- Loading Spinner -->
    <div class="loading" id="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>