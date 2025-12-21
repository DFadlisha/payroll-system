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
    $companies = [
        ['id' => 'nes-001', 'name' => 'NES Solution & Network Sdn Bhd', 'logo_url' => 'nes.jpg'],
        ['id' => 'mi-001', 'name' => 'Mentari Infiniti Network Sdn Bhd', 'logo_url' => 'mentari.png']
    ];
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
                $_SESSION['employment_type'] = $user['employment_type']; // permanent, contract, intern, part-time
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
</head>
<body>
    <div class="login-container">
        :root {
            --primary-color: #2563eb;
            --secondary-color: #f97316;
            --accent-teal: #14b8a6;
            --accent-pink: #ec4899;
            --text-dark: #1f2937;
            --text-muted: #6b7280;
            --surface: #ffffff;
            --surface-alt: #f8fafc;
            --border: #e5e7eb;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-teal) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        
        .login-card {
            background: var(--surface);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.28);
            overflow: hidden;
            border: 1px solid var(--border);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-teal) 100%);
            padding: 30px;
            text-align: center;
            color: #fff;
        }
        
        .login-header h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .login-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-floating {
            margin-bottom: 15px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid var(--border);
            padding: 12px 15px;
            height: auto;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 10px;
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            background: var(--surface-alt);
            font-size: 0.85rem;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        /* Company Selection */
        .company-select-container {
            margin-bottom: 20px;
        }
        
        .company-cards {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .company-card {
            border: 2px solid var(--border);
            border-radius: 12px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            flex: 1;
            min-width: 120px;
            max-width: 150px;
        }
        
        .company-card:hover {
            border-color: var(--primary-color);
            background: rgba(37, 99, 235, 0.05);
        }
        
        .company-card.selected {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.12), rgba(20, 184, 166, 0.12));
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.22);
        }
        
        .company-card img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 8px;
        }
        
        .company-card .company-name {
            font-size: 0.75rem;
            font-weight: 600;
            color: #333;
        }
        
        .company-card .company-short {
            font-size: 0.65rem;
            color: #6c757d;
        }
        
        /* Demo credentials box */
        .demo-credentials {
            background: #e7f3ff;
            border: 1px solid #b6d4fe;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }
        
        .demo-credentials h6 {
            color: #0d6efd;
    <!-- Custom Auth CSS -->
    <link href="../assets/css/auth.css" rel="stylesheet">
    
    <style>
        .header-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 10px;
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
    <div class="login-container">
        <!-- Language Switcher -->
        <div class="text-end mb-3">
            <?= getLanguageSwitcher() ?>
        </div>
        
        <div class="login-card">
            <div class="login-header" id="loginHeader">
                <img src="../assets/logos/nes.jpg" alt="Logo" id="headerLogo" style="width: 80px; height: 80px; object-fit: contain; background: #fff; border-radius: 10px; padding: 5px;">
                <h1 id="headerTitle"><?= __('app_name') ?></h1>
                <p><?= __('app_subtitle') ?></p>
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
                    <label class="form-label text-center d-block mb-2">
                        <i class="bi bi-building me-1"></i> <?= getCurrentLang() === 'ms' ? 'Pilih Syarikat' : 'Select Company' ?>
                    </label>
                    <div class="company-cards">
                        <?php foreach ($companies as $index => $company): ?>
                            <div class="company-card <?= $index === 0 ? 'selected' : '' ?>" 
                                 data-company-id="<?= htmlspecialchars($company['id']) ?>"
                                 data-company-name="<?= htmlspecialchars($company['name']) ?>"
                                 data-company-logo="<?= htmlspecialchars($company['logo_url'] ?? 'nes.jpg') ?>"
                                 onclick="selectCompany(this)">
                                <img src="../assets/logos/<?= htmlspecialchars($company['logo_url'] ?? 'nes.jpg') ?>" 
                                     alt="<?= htmlspecialchars($company['name']) ?>"
                                     onerror="this.src='../assets/logos/nes.jpg'">
                                <div class="company-name"><?= htmlspecialchars(explode(' ', $company['name'])[0]) ?></div>
                                <div class="company-short"><?= htmlspecialchars(substr($company['name'], 0, 25)) ?>...</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <form method="POST" action="" class="needs-validation" novalidate>
                    <input type="hidden" name="company_id" id="companyId" value="<?= $companies[0]['id'] ?? 1 ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-1"></i> <?= __('login_page.email') ?>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="example@company.com" 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required autofocus>
                        <div class="invalid-feedback"><?= __('errors.required_field') ?></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
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
                    
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i> <?= __('login_page.btn_login') ?>
                    </button>
                    
                    <div class="text-center mt-3">
                        <span class="text-muted"><?= __('login_page.no_account') ?></span>
                        <a href="register.php" class="text-decoration-none"> <?= __('login_page.sign_up') ?></a>
                    </div>
                </form>
            </div>
            
            <div class="login-footer">
                <p class="mb-0" id="footerText">
                    &copy; <?= date('Y') ?> <span id="footerCompany"><?= htmlspecialchars($companies[0]['name'] ?? 'Company') ?></span>
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
            
            // Update header logo
            const logo = element.dataset.companyLogo;
            if (logo) {
                document.getElementById('headerLogo').src = '../assets/logos/' + logo;
            }
            
            // Update footer company name
            document.getElementById('footerCompany').textContent = element.dataset.companyName;
        }
        
        // Form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
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
