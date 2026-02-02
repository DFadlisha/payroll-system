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
            /* Soft Purple Background like the reference */
            background: linear-gradient(135deg, #E0E7FF 0%, #DDD6FE 100%);
            background-attachment: fixed;
            color: #1E293B;
            -webkit-font-smoothing: antialiased;
            letter-spacing: -0.01em;
            min-height: 100vh;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: #1E293B;
            letter-spacing: -0.02em;
        }

        /* Utility Classes */
        .glass-card {
            background: #FFFFFF;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .premium-shadow {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .gradient-text {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .btn-premium {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white !important;
            border: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
        }

        .btn-premium:active {
            transform: translateY(0);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .animate-slide-in {
            animation: slideIn 0.4s ease-out forwards;
        }

        /* Sidebar Styles - Vibrant Blue like reference */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #4F46E5 0%, #3730A3 100%);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            border-right: none;
            box-shadow: 4px 0 24px rgba(79, 70, 229, 0.2);
            display: flex;
            flex-direction: column;
            padding: 24px 16px;
        }

        .sidebar-header {
            padding: 20px 16px;
            margin-bottom: 24px;
            background: transparent;
            border-bottom: none;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-header h3 {
            color: #FFFFFF;
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.2;
            word-break: break-word;
            text-shadow: none;
        }

        /* Logo circle */
        .sidebar-logo {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-logo::after {
            content: '';
            width: 24px;
            height: 24px;
            background: white;
            border-radius: 50%;
            clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);
        }

        /* Search bar in sidebar */
        .sidebar-search {
            margin-bottom: 20px;
            position: relative;
        }

        .sidebar-search input {
            width: 100%;
            padding: 12px 16px 12px 40px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: white;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .sidebar-search input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .sidebar-search input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
            outline: none;
        }

        .sidebar-search i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        /* User Avatar */
        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            color: #4F46E5;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .sidebar-menu .nav-link {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 4px;
            background: transparent;
        }

        .sidebar-menu .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #FFFFFF;
        }

        .sidebar-menu .nav-link.active {
            background: rgba(15, 23, 42, 0.3);
            color: #FFFFFF;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .sidebar-menu .nav-link.active i {
            color: #FFFFFF;
        }

        .sidebar-menu .nav-link i {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            transition: color 0.2s;
            margin-right: 14px;
            width: 24px;
            text-align: center;
        }

        .sidebar-menu .nav-link:hover i {
            color: #FFFFFF;
        }

        .text-xs { font-size: 0.75rem; }
        .tracking-wider { letter-spacing: 0.05em; }
        .tracking-widest { letter-spacing: 0.1em; }
        .cursor-pointer { cursor: pointer; }
        .bg-success-soft { background-color: rgba(16, 185, 129, 0.1); }
        .avatar-sm { width: 32px; height: 32px; }
        .avatar-lg { width: 64px; height: 64px; }

        /* Main Content - Light background */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 32px;
            min-height: 100vh;
            background: transparent;
        }

        /* Top Navbar - Clean like reference */
        .top-navbar {
            background: transparent;
            padding: 0 0 24px 0;
            margin: 0 0 32px 0;
            border-bottom: none;
            box-shadow: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 900;
        }

        .top-navbar .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1E293B;
            letter-spacing: -0.8px;
        }

        /* Cards - Clean white like reference */
        .card {
            background: #FFFFFF;
            border: none;
            border-radius: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 24px;
            margin-bottom: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #F1F5F9;
            font-weight: 700;
            font-size: 1.1rem;
            color: #1E293B;
            padding: 0 0 16px 0;
            margin-bottom: 20px;
        }

        /* Stats Cards - Modern with vibrant colors like reference */
        .stats-card {
            padding: 28px;
            border-radius: 20px;
            position: relative;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            background: #FFFFFF;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        /* Color Variations - Vibrant like reference */
        .stats-card.purple {
            background: linear-gradient(135deg, #7C3AED 0%, #6366F1 100%);
        }

        .stats-card.purple .stats-icon-bg {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .stats-card.purple h2,
        .stats-card.purple p {
            color: white;
        }

        .stats-card.green,
        .stats-card.success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        }

        .stats-card.green .stats-icon-bg,
        .stats-card.success .stats-icon-bg {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .stats-card.green h2,
        .stats-card.green p,
        .stats-card.success h2,
        .stats-card.success p {
            color: white;
        }

        .stats-card.orange,
        .stats-card.warning {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        }

        .stats-card.orange .stats-icon-bg,
        .stats-card.warning .stats-icon-bg {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .stats-card.orange h2,
        .stats-card.orange p,
        .stats-card.warning h2,
        .stats-card.warning p {
            color: white;
        }

        .stats-card.blue,
        .stats-card.info {
            background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
        }

        .stats-card.blue .stats-icon-bg,
        .stats-card.info .stats-icon-bg {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .stats-card.blue h2,
        .stats-card.blue p,
        .stats-card.info h2,
        .stats-card.info p {
            color: white;
        }

        .stats-card.red,
        .stats-card.danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        }

        .stats-card.red .stats-icon-bg,
        .stats-card.danger .stats-icon-bg {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .stats-card.red h2,
        .stats-card.red p,
        .stats-card.danger h2,
        .stats-card.danger p {
            color: white;
        }

        .stats-card h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0;
            line-height: 1;
            letter-spacing: -1px;
        }

        .stats-card p {
            margin: 0;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.95;
        }

        .stats-icon {
            position: absolute;
            top: 24px;
            right: 24px;
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        /* Helper for icon background in new cards */
        .stats-icon-bg {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Buttons & Forms */
        .btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            border: none;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-success {
            background-color: #10B981;
            border: none;
        }

        .btn-success:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            padding: 10px 14px;
            border: 1px solid #E2E8F0;
            background-color: #FFFFFF;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            border-color: #4F46E5;
        }

        /* Modern Tables */
        .table {
            --bs-table-bg: transparent;
            margin-bottom: 0;
        }

        .table th {
            font-weight: 600;
            color: #64748B;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 14px 20px;
            background: #F8FAFC;
            border-bottom: 2px solid #E2E8F0;
        }

        .table td {
            padding: 16px 20px;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #F1F5F9;
            font-size: 0.9rem;
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
    <link rel="apple-touch-icon" href="<?= $baseUrl ?>/assets/logos/mi-nes-logo.jpg">
    <link rel="icon" type="image/jpeg" href="<?= $baseUrl ?>/assets/logos/mi-nes-logo.jpg">
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