<?php
/**
 * ============================================
 * LOGIN PAGE
 * ============================================
 * Users login using email and password.
 * Redirect to HR or Staff dashboard based on role.
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

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = __('login_page.invalid_credentials');
    } else {
        try {
            $conn = getConnection();
            
            // Find user with email
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful - set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['company_id'] = $user['company_id'];
                
                // Redirect based on role
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
    
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #0d2137 100%);
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
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
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
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            height: auto;
        }
        
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
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
            background: #f8f9fa;
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
            <div class="login-header">
                <i class="bi bi-building" style="font-size: 3rem;"></i>
                <h1><?= __('app_name') ?></h1>
                <p><?= __('app_subtitle') ?></p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Demo Credentials -->
                <div class="demo-credentials">
                    <h6><i class="bi bi-info-circle me-1"></i> Demo Account</h6>
                    <p class="mb-1"><strong>HR Admin:</strong> <code>admin@nes.com.my</code></p>
                    <p class="mb-1"><strong>Staff:</strong> <code>staff@nes.com.my</code></p>
                    <p class="mb-0"><strong>Password:</strong> <code>password123</code></p>
                </div>
                
                <form method="POST" action="" class="needs-validation" novalidate>
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
                <p class="mb-0">
                    &copy; <?= date('Y') ?> NES Solution & Network Sdn Bhd
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
