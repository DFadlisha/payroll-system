<?php
/**
 * ============================================
 * HOME PAGE - MI-NES PAYROLL SYSTEM
 * ============================================
 */
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'hr') {
        header('Location: hr/dashboard.php');
    } else {
        header('Location: staff/dashboard.php');
    }
    exit();
}

// Language selection
$lang = 'en';

$translations = [
    'en' => [
        'welcome' => 'Welcome to MI-NES Payroll System',
        'subtitle' => 'Modern Payroll Management Solution',
        'description' => 'Comprehensive payroll system for NES Solution & Network Sdn Bhd and Mentari Infiniti Sdn Bhd.',
        'features_title' => 'Key Features',
        'feature1' => 'Employee Management',
        'feature1_desc' => 'Manage employee profiles, contracts, and employment types',
        'feature2' => 'Attendance Tracking',
        'feature2_desc' => 'Real-time clock in/out with location tracking and overtime calculation',
        'feature3' => 'Leave Management',
        'feature3_desc' => 'Annual, sick, emergency, and unpaid leave requests with approval workflow',
        'feature4' => 'Payroll Processing',
        'feature4_desc' => 'Automated salary calculation with EPF, SOCSO, EIS, and PCB deductions',
        'feature5' => 'Reports & Analytics',
        'feature5_desc' => 'Comprehensive reports for payroll, attendance, and leave statistics',
        'feature6' => 'Multi-Company Support',
        'feature6_desc' => 'Support for multiple companies with separate configurations',
        'login_btn' => 'Login to Your Account',
        'register_btn' => 'Create New Account',
        'companies' => 'Our Companies',
        'powered_by' => 'Powered by'
    ]
];

$t = $translations[$lang];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MI-NES Payroll System - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- PWA -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#FFD400">
    <link rel="apple-touch-icon" href="assets/logos/nes.jpg">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js');
            });
        }
    </script>
    <style>
        :root {
            --primary-color: #FFD400;
            /* yellow */
            --secondary-color: #f97316;
            /* NES orange */
            --accent-teal: #14b8a6;
            /* Mentari teal */
            --accent-pink: #ec4899;
            /* Mentari pink */
            --text-dark: #000000;
            --text-muted: #6b7280;
            --surface: #ffffff;
            --surface-alt: #f8fafc;
            --border: #e5e7eb;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-teal) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .hero-section {
            padding: 80px 0;
            color: white;
            text-align: center;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .hero-description {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto 3rem;
            opacity: 0.9;
        }

        .cta-buttons .btn {
            padding: 15px 40px;
            font-size: 1.1rem;
            margin: 10px;
            border-radius: 50px;
            transition: all 0.3s;
        }

        .btn-light:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-outline-light:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-3px);
        }

        .features-section {
            background: white;
            padding: 80px 0;
        }

        .feature-card {
            padding: 30px;
            border-radius: 15px;
            background: var(--surface);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            height: 100%;
            border: 1px solid var(--border);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }

        .feature-desc {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .companies-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 0;
            color: white;
        }

        .company-logo {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .company-logo:hover {
            transform: scale(1.05);
        }

        .company-logo img {
            max-width: 100%;
            max-height: 100px;
            object-fit: contain;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
            text-align: center;
        }

        footer {
            background: #1f2937;
            color: white;
            padding: 30px 0;
            text-align: center;
        }
    </style>
</head>

<body>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title"><?= $t['welcome'] ?></h1>
            <p class="hero-subtitle"><?= $t['subtitle'] ?></p>
            <p class="hero-description"><?= $t['description'] ?></p>

            <div class="cta-buttons">
                <a href="auth/login.php" class="btn btn-light btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i><?= $t['login_btn'] ?>
                </a>
                <a href="auth/register.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-person-plus me-2"></i><?= $t['register_btn'] ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title" style="color: #1f2937;"><?= $t['features_title'] ?></h2>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h3 class="feature-title"><?= $t['feature1'] ?></h3>
                        <p class="feature-desc"><?= $t['feature1_desc'] ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h3 class="feature-title"><?= $t['feature2'] ?></h3>
                        <p class="feature-desc"><?= $t['feature2_desc'] ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h3 class="feature-title"><?= $t['feature3'] ?></h3>
                        <p class="feature-desc"><?= $t['feature3_desc'] ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <h3 class="feature-title"><?= $t['feature4'] ?></h3>
                        <p class="feature-desc"><?= $t['feature4_desc'] ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3 class="feature-title"><?= $t['feature5'] ?></h3>
                        <p class="feature-desc"><?= $t['feature5_desc'] ?></p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <h3 class="feature-title"><?= $t['feature6'] ?></h3>
                        <p class="feature-desc"><?= $t['feature6_desc'] ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Companies Section -->
    <section class="companies-section">
        <div class="container">
            <h2 class="section-title"><?= $t['companies'] ?></h2>

            <div class="row g-4 justify-content-center">
                <div class="col-md-5">
                    <div class="company-logo">
                        <img src="assets/logos/nes.jpg" alt="NES SOLUTION & NETWORK SDN BHD">
                    </div>
                    <h5 class="text-center mt-3">NES SOLUTION & NETWORK SDN BHD</h5>
                </div>

                <div class="col-md-5">
                    <div class="company-logo">
                        <img src="assets/logos/mentari.png" alt="MENTARI INFINITI SDN BHD">
                    </div>
                    <h5 class="text-center mt-3">MENTARI INFINITI SDN BHD</h5>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p class="mb-0">&copy; 2025 MI-NES PAYROLL SYSTEM. <?= $t['powered_by'] ?> NES SOLUTION & NETWORK SDN BHD
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>