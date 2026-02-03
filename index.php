<?php
// Start output buffering
ob_start();

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
        'feature2' => 'Smart Attendance',
        'feature2_desc' => 'Secure clock in/out with GPS tracking, photo verification, and digital security hashing',

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
    <link rel="apple-touch-icon" href="assets/logos/mi-nes-logo.jpg">
    <link rel="icon" type="image/jpeg" href="assets/logos/mi-nes-logo.jpg">
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
            --secondary-color: #f97316;
            --accent-teal: #14b8a6;
            --accent-pink: #ec4899;
        }

        body {
            /* Premium Dark Glassmorphic Gradient Background */
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4c1d95 100%);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }

        /* Animated background overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3), transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255, 119, 255, 0.3), transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(138, 180, 248, 0.3), transparent 50%);
            pointer-events: none;
            z-index: 0;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 0.8; }
        }

        .hero-section {
            padding: 80px 0 100px;
            color: white;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .hero-logo-img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            margin-bottom: 2rem;
            background: white;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            letter-spacing: -2px;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-subtitle {
            font-size: 1.75rem;
            margin-bottom: 2rem;
            opacity: 0.95;
            font-weight: 600;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .hero-description {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 3.5rem;
            opacity: 0.9;
            line-height: 1.6;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .cta-buttons {
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }

        .cta-buttons .btn {
            padding: 18px 45px;
            font-size: 1.15rem;
            margin: 12px;
            border-radius: 60px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: #667eea;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .btn-light:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 
                0 16px 48px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            background: white;
        }

        .btn-outline-light {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.4);
            color: white;
        }

        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.25);
        }

        .features-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 100px 0;
            position: relative;
            z-index: 1;
        }

        .feature-card {
            padding: 35px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 8px 32px rgba(31, 38, 135, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 
                0 16px 48px rgba(31, 38, 135, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .feature-icon {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 4px 12px rgba(102, 126, 234, 0.3));
        }

        .feature-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: white;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .feature-desc {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            line-height: 1.6;
        }

        .companies-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.3) 100%);
            backdrop-filter: blur(20px);
            padding: 80px 0;
            color: white;
            position: relative;
            z-index: 1;
        }

        .company-logo {
            background: rgba(255, 255, 255, 0.95);
            padding: 35px;
            border-radius: 24px;
            box-shadow: 
                0 12px 40px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            height: 170px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .company-logo:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
        }

        .company-logo img {
            max-width: 100%;
            max-height: 110px;
            object-fit: contain;
        }

        .section-title {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 4rem;
            text-align: center;
            text-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            letter-spacing: -1.5px;
        }

        footer {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            color: white;
            padding: 40px 0;
            text-align: center;
            position: relative;
            z-index: 1;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        footer p {
            margin: 0;
            opacity: 0.9;
        }
    </style>
</head>

<body>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <img src="assets/logos/mi-nes-logo.jpg" alt="MI-NES Logo" class="hero-logo-img">
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
            <h2 class="section-title text-white"><?= $t['features_title'] ?></h2>

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