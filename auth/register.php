<?php
/**
 * ============================================
 * REGISTRATION PAGE (Sign Up)
 * ============================================
 * New users can register for an account.
 * Default role is 'staff'. HR can change roles later.
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
$success = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? 'staff');
    $internship_months = intval($_POST['internship_months'] ?? 0);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Valid roles
    $valid_roles = ['staff', 'part_time', 'intern'];
    
    // Validate input
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = __('errors.required_field');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = __('errors.invalid_request');
    } elseif (!in_array($role, $valid_roles)) {
        $error = __('errors.invalid_request');
    } elseif ($role === 'intern' && ($internship_months < 1 || $internship_months > 12)) {
        $error = __('errors.invalid_request');
    } elseif (strlen($password) < 6) {
        $error = __('errors.invalid_request');
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $conn = getConnection();
            
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'This email is already registered. Please use a different email or login.';
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Set internship_months only for interns
                $intern_months = ($role === 'intern') ? $internship_months : null;
                
                // Insert new user
                $stmt = $conn->prepare("
                    INSERT INTO users (full_name, email, phone, password, role, internship_months, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
                ");
                $stmt->execute([$full_name, $email, $phone, $hashed_password, $role, $intern_months]);
                
                $success = 'Registration successful! You can now login with your email and password.';
                
                // Clear form data
                $full_name = $email = $phone = '';
            }
        } catch (PDOException $e) {
            $error = 'System error. Please try again later.';
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - MI-NES Payroll System</title>
    
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
            padding: 20px 0;
        }
        
        .register-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .register-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .register-header {
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
            padding: 30px;
            text-align: center;
            color: #fff;
        }
        
        .register-header h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .register-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .register-body {
            padding: 30px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            height: auto;
        }
        
        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
        }
        
        .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            height: auto;
        }
        
        .form-select:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
        }
        
        .btn-register {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 10px;
        }
        
        .register-footer {
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
        
        .form-label {
            font-weight: 500;
            color: #333;
        }
        
        .required {
            color: #dc3545;
        }
        
        .password-requirements {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Language Switcher -->
        <div class="text-end mb-3">
            <?= getLanguageSwitcher() ?>
        </div>
        
        <div class="register-card">
            <div class="register-header">
                <i class="bi bi-person-plus" style="font-size: 3rem;"></i>
                <h1><?= __('register_page.title') ?></h1>
                <p><?= __('app_name') ?></p>
            </div>
            
            <div class="register-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <br><br>
                        <a href="login.php" class="btn btn-success btn-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> <?= __('login_page.btn_login') ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if (!$success): ?>
                <form method="POST" action="" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">
                            <i class="bi bi-person me-1"></i> Full Name <span class="required">*</span>
                        </label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               placeholder="Enter your full name" 
                               value="<?= htmlspecialchars($full_name ?? '') ?>"
                               required autofocus>
                        <div class="invalid-feedback">Please enter your full name.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-1"></i> Email <span class="required">*</span>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="example@company.com" 
                               value="<?= htmlspecialchars($email ?? '') ?>"
                               required>
                        <div class="invalid-feedback">Please enter a valid email address.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">
                            <i class="bi bi-telephone me-1"></i> Phone Number
                        </label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               placeholder="012-345 6789" 
                               value="<?= htmlspecialchars($phone ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">
                            <i class="bi bi-briefcase me-1"></i> Employment Type <span class="required">*</span>
                        </label>
                        <select class="form-select" id="role" name="role" required onchange="toggleInternshipField()">
                            <option value="" disabled <?= empty($role ?? '') ? 'selected' : '' ?>>-- Select your role --</option>
                            <option value="staff" <?= ($role ?? '') === 'staff' ? 'selected' : '' ?>>
                                Full-Time Staff
                            </option>
                            <option value="part_time" <?= ($role ?? '') === 'part_time' ? 'selected' : '' ?>>
                                Part-Time
                            </option>
                            <option value="intern" <?= ($role ?? '') === 'intern' ? 'selected' : '' ?>>
                                Intern
                            </option>
                        </select>
                        <div class="invalid-feedback">Please select your employment type.</div>
                    </div>
                    
                    <!-- Internship Duration (Only for Intern) -->
                    <div class="mb-3" id="internship_field" style="display: <?= ($role ?? '') === 'intern' ? 'block' : 'none' ?>;">
                        <label for="internship_months" class="form-label">
                            <i class="bi bi-calendar-range me-1"></i> Internship Duration <span class="required">*</span>
                        </label>
                        <select class="form-select" id="internship_months" name="internship_months">
                            <option value="">-- Select duration --</option>
                            <option value="1" <?= ($internship_months ?? '') == 1 ? 'selected' : '' ?>>1 Month</option>
                            <option value="2" <?= ($internship_months ?? '') == 2 ? 'selected' : '' ?>>2 Months</option>
                            <option value="3" <?= ($internship_months ?? '') == 3 ? 'selected' : '' ?>>3 Months</option>
                            <option value="4" <?= ($internship_months ?? '') == 4 ? 'selected' : '' ?>>4 Months</option>
                            <option value="5" <?= ($internship_months ?? '') == 5 ? 'selected' : '' ?>>5 Months</option>
                            <option value="6" <?= ($internship_months ?? '') == 6 ? 'selected' : '' ?>>6 Months</option>
                            <option value="7" <?= ($internship_months ?? '') == 7 ? 'selected' : '' ?>>7 Months</option>
                            <option value="8" <?= ($internship_months ?? '') == 8 ? 'selected' : '' ?>>8 Months</option>
                            <option value="9" <?= ($internship_months ?? '') == 9 ? 'selected' : '' ?>>9 Months</option>
                            <option value="10" <?= ($internship_months ?? '') == 10 ? 'selected' : '' ?>>10 Months</option>
                            <option value="11" <?= ($internship_months ?? '') == 11 ? 'selected' : '' ?>>11 Months</option>
                            <option value="12" <?= ($internship_months ?? '') == 12 ? 'selected' : '' ?>>12 Months</option>
                        </select>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> NRL entitlement = 1 day per month of internship
                        </small>
                        <div class="invalid-feedback">Please select internship duration.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i> Password <span class="required">*</span>
                        </label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Create a password" required minlength="6">
                            <i class="bi bi-eye password-toggle" onclick="togglePassword('password')"></i>
                        </div>
                        <div class="password-requirements">
                            <i class="bi bi-info-circle"></i> Minimum 6 characters
                        </div>
                        <div class="invalid-feedback">Please enter a password (min 6 characters).</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">
                            <i class="bi bi-lock-fill me-1"></i> Confirm Password <span class="required">*</span>
                        </label>
                        <div class="password-wrapper">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm your password" required>
                            <i class="bi bi-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                        </div>
                        <div class="invalid-feedback">Please confirm your password.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-register">
                        <i class="bi bi-person-plus me-2"></i> Create Account
                    </button>
                    
                    <div class="text-center mt-3">
                        <span class="text-muted">Already have an account?</span>
                        <a href="login.php" class="text-decoration-none"> Login here</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
            
            <div class="register-footer">
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
        function togglePassword(fieldId) {
            const password = document.getElementById(fieldId);
            const icon = password.nextElementSibling;
            
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
        
        // Toggle internship duration field
        function toggleInternshipField() {
            var role = document.getElementById('role').value;
            var internshipField = document.getElementById('internship_field');
            var internshipMonths = document.getElementById('internship_months');
            
            if (role === 'intern') {
                internshipField.style.display = 'block';
                internshipMonths.required = true;
            } else {
                internshipField.style.display = 'none';
                internshipMonths.required = false;
                internshipMonths.value = '';
            }
        }
        
        // Form validation
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    // Check if passwords match
                    var password = document.getElementById('password');
                    var confirmPassword = document.getElementById('confirm_password');
                    
                    if (password.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Passwords do not match');
                    } else {
                        confirmPassword.setCustomValidity('');
                    }
                    
                    // Check internship months for intern
                    var role = document.getElementById('role').value;
                    var internshipMonths = document.getElementById('internship_months');
                    if (role === 'intern' && !internshipMonths.value) {
                        internshipMonths.setCustomValidity('Please select internship duration');
                    } else {
                        internshipMonths.setCustomValidity('');
                    }
                    
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
        
        // Real-time password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            var password = document.getElementById('password');
            if (this.value !== password.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
