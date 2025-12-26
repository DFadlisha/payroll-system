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
$baseUrl = '/payroll-php';

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
            --primary-color: #FFD400; /* yellow */
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, #1e3a5f 0%, #0d2137 100%);
            padding-top: 20px;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            color: #fff;
            margin: 0;
            font-size: 1.2rem;
        }
        
        .sidebar-header small {
            color: rgba(255,255,255,0.6);
            font-size: 0.8rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        
        .sidebar-menu li {
            margin: 5px 15px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
        }
        
        /* Top Navbar */
        .top-navbar {
            background: #fff;
            padding: 15px 25px;
            margin: -20px -20px 20px -20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .card-header {
            background: #fff;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }
        
        /* Stats Cards */
        .stats-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0056b3 100%);
            color: #fff;
            border-radius: 12px;
            padding: 20px;
        }
        
        .stats-card.success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }
        
        .stats-card.warning {
            background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%);
            color: #212529;
        }
        
        .stats-card.danger {
            background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);
        }
        
        .stats-card h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stats-card p {
            margin: 0;
            opacity: 0.9;
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
            background: rgba(255,255,255,0.8);
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
