<?php
/**
 * ============================================
 * LOGIN PAGE
 * ============================================
 * Users login using email and password.
 * Select company before login.
 * ============================================
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/language.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    if (isHR()) {
        redirect('../hr/dashboard.php');
    } else {
        redirect('../staff/dashboard.php');
    }
}

$error = '';
$companies = [];

// Get list of companies from Supabase
try {
    $conn = getConnection();
    $stmt = $conn->query("SELECT id, name, logo_url FROM companies ORDER BY name");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert logo_url to local filename if needed
    foreach ($companies as &$company) {
        if (empty($company['logo_url'])) {
            $company['logo_url'] = 'nes.jpg'; // default
        }
    }
    unset($company);

    // Put NES first in the list for display clarity
    usort($companies, function ($a, $b) {
        $aIsNes = stripos($a['name'], 'nes') !== false;
        $bIsNes = stripos($b['name'], 'nes') !== false;
        if ($aIsNes === $bIsNes) {
            return strcasecmp($a['name'], $b['name']);
        }
        return $aIsNes ? -1 : 1;
    });
} catch (PDOException $e) {
    // If companies table doesn't exist or error, use default
    // If companies table doesn't exist or error, log it
    error_log("Failed to fetch companies: " . $e->getMessage());
    $companies = [];
}

// Debug helper: show companies when ?debug=1 is passed
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    echo '<pre style="background:#111;color:#0f0;padding:10px;">';
    echo "Companies loaded:\n";
    print_r($companies);
    echo '</pre>';
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $companyId = $_POST['company_id'] ?? '';

    // Validate input
    if (empty($email) || empty($password)) {
        $error = __('login_page.invalid_credentials');
    } else {
        try {
            $conn = getConnection();

            // Find user profile with email and company (profiles table uses UUID)
            $stmt = $conn->prepare("SELECT p.*, c.name as company_name, c.logo_url 
                                    FROM profiles p 
                                    LEFT JOIN companies c ON p.company_id = c.id 
                                    WHERE p.email = ? AND (p.company_id = ? OR ? = '')");
            $stmt->execute([$email, $companyId, $companyId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Login successful - set session (using data from JOIN)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role']; // hr or staff
                $_SESSION['employment_type'] = $user['employment_type']; // permanent, leader, intern, part-time
                $_SESSION['company_id'] = $user['company_id'];
                $_SESSION['company_name'] = $user['company_name'] ?? 'Company';
                $_SESSION['company_logo'] = $user['logo_url'] ?? 'nes.jpg';
                $_SESSION['basic_salary'] = $user['basic_salary'] ?? 0;
                $_SESSION['hourly_rate'] = $user['hourly_rate'] ?? 0;

                // Redirect based on role (hr or staff)
                if ($user['role'] === 'hr') {
                    redirect('../hr/dashboard.php');
                } else {
                    redirect('../staff/dashboard.php');
                }
            } else {
                $error = __('login_page.invalid_credentials');
            }
        } catch (PDOException $e) {
            $error = __('errors.system_error');
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLang() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('login') ?> - <?= __('app_name') ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Auth CSS -->
    <link href="../assets/css/auth.css" rel="stylesheet">

    <!-- PWA -->
    <link rel="manifest" href="../manifest.json">
    <meta name="theme-color" content="#0d6efd">
    <link rel="apple-touch-icon" href="../assets/logos/nes.jpg">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('../sw.js');
            });
        }
    </script>

    <style>
        .header-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 10px;
            background: white;
            padding: 5px;
            border-radius: 6px;
        }

        .demo-credentials {
            font-size: 0.8rem;
            margin-top: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .demo-credentials strong {
            display: block;
            margin-bottom: 10px;
        }

        .demo-credentials code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="login-container animate-up">
        <!-- Language Switcher -->
        <div class="text-end mb-4">
            <div class="d-inline-block glass-card p-1 px-3">
                <?= getLanguageSwitcher() ?>
            </div>
        </div>

        <div class="login-card">
            <div class="login-header" id="loginHeader">
                <h1 id="headerTitle"><?= __('app_name') ?></h1>
                <p class="text-white-50 small fw-bold text-uppercase tracking-widest"><?= __('app_subtitle') ?></p>
            </div>

            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Company Selection -->
                <div class="company-select-container">
                    <label class="form-label text-center d-block mb-2 text-uppercase small fw-bold">
                        <i class="bi bi-building me-1"></i> Select Company
                    </label>
                    <div class="company-cards">
                        <?php foreach ($companies as $index => $company): ?>
                            <div class="company-card <?= $index === 0 ? 'selected' : '' ?>"
                                data-company-id="<?= htmlspecialchars($company['id']) ?>"
                                data-company-name="<?= htmlspecialchars($company['name']) ?>"
                                data-company-logo="<?= htmlspecialchars($company['logo_url'] ?? 'nes.jpg') ?>"
                                onclick="selectCompany(this)">
                                <img src="../assets/logos/<?= htmlspecialchars($company['logo_url'] ?? 'nes.jpg') ?>"
                                    alt="<?= htmlspecialchars($company['name']) ?>" class="company-logo"
                                    onerror="this.src='../assets/logos/nes.jpg'">
                                <div class="company-name"><?= strtoupper(htmlspecialchars($company['name'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <form method="POST" action="" class="needs-validation" novalidate>
                    <input type="hidden" name="company_id" id="companyId" value="<?= $companies[0]['id'] ?? 1 ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label text-uppercase small fw-bold">
                            <i class="bi bi-envelope me-1"></i> <?= __('login_page.email') ?>
                        </label>
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="example@company.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required autofocus>
                        <div class="invalid-feedback"><?= __('errors.required_field') ?></div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label text-uppercase small fw-bold">
                            <i class="bi bi-lock me-1"></i> <?= __('login_page.password') ?>
                        </label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="<?= __('login_page.password') ?>" required>
                            <i class="bi bi-eye password-toggle" onclick="togglePassword()"></i>
                        </div>
                        <div class="invalid-feedback"><?= __('errors.required_field') ?></div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember"><?= __('login_page.remember') ?></label>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-shield-lock-fill me-2"></i> <?= __('login_page.btn_login') ?>
                    </button>

                    <div class="text-center mt-3">
                        <a href="forgot_password.php" class="text-decoration-none small">
                            <i class="bi bi-key me-1"></i><?= __('login_page.forgot_password') ?>
                        </a>
                    </div>

                    <div class="text-center mt-2">
                        <span class="text-muted"><?= __('login_page.no_account') ?></span>
                        <a href="register.php" class="text-decoration-none"> <?= __('login_page.sign_up') ?></a>
                    </div>
                </form>
            </div>

            <div class="login-footer">
                <p class="mb-0" id="footerText">
                    &copy; <?= date('Y') ?> <span
                        id="footerCompany"><?= strtoupper(htmlspecialchars($companies[0]['name'] ?? 'Company')) ?></span>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.querySelector('.password-toggle');

            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Select company function
        function selectCompany(element) {
            // Remove selected class from all cards
            document.querySelectorAll('.company-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selected class to clicked card
            element.classList.add('selected');

            // Update hidden input
            document.getElementById('companyId').value = element.dataset.companyId;

            // Update header info (logo update removed)
            const logo = element.dataset.companyLogo;

            // Update footer company name
            document.getElementById('footerCompany').textContent = element.dataset.companyName;
        }

        // Form validation
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>

</html>