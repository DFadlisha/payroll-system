<?php
/**
 * ============================================
 * REGISTRATION PAGE (Sign Up)
 * ============================================
 * New users can register for an account.
 * Select company and role during registration.
 * ============================================
 */

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

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
$companies = [];

// Get list of companies from Supabase
try {
    $conn = getConnection();
    $stmt = $conn->query("SELECT id, name, logo_url FROM companies ORDER BY name");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($companies as &$company) {
        if (empty($company['logo_url'])) {
            $company['logo_url'] = 'nes.jpg';
        }
    }
    unset($company);

    // Put NES first for consistent display order
    usort($companies, function ($a, $b) {
        $aIsNes = stripos($a['name'], 'nes') !== false;
        $bIsNes = stripos($b['name'], 'nes') !== false;
        if ($aIsNes === $bIsNes) {
            return strcasecmp($a['name'], $b['name']);
        }
        return $aIsNes ? -1 : 1;
    });
} catch (PDOException $e) {
    // If companies table doesn't exist, use default
    // If companies table doesn't exist, log it
    error_log("Failed to fetch companies: " . $e->getMessage());
    $companies = [];
}

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? 'staff');
    $employment_type = sanitize($_POST['employment_type'] ?? 'permanent');
    $company_id = $_POST['company_id'] ?? '';
    $basic_salary = floatval($_POST['basic_salary'] ?? 0);
    $hourly_rate = floatval($_POST['hourly_rate'] ?? 0);
    $internship_months = intval($_POST['internship_months'] ?? 0);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Valid roles
    $valid_roles = ['staff', 'hr'];

    // Valid employment types from Supabase schema
    $valid_types = ['permanent', 'leader', 'intern', 'part-time'];

    // Validate input
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = __('errors.required_field');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = __('errors.invalid_request');
    } elseif (!in_array($role, $valid_roles)) {
        $error = __('errors.invalid_request');
    } elseif (!in_array($employment_type, $valid_types)) {
        $error = __('errors.invalid_request');
    } elseif (strlen($password) < 6) {
        $error = __('errors.invalid_request');
    } elseif ($password !== $confirm_password) {
        $error = __('register_page.password_mismatch');
    } else {
        try {
            $conn = getConnection();

            // Check if email already exists in profiles
            $stmt = $conn->prepare("SELECT id FROM profiles WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $error = __('register_page.email_exists');
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Generate UUID for profile
                $uuid = sprintf(
                    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff)
                );

                $company_id_value = !empty($company_id) ? $company_id : null;

                // Try to insert with internship_months column first (if it exists)
                try {
                    $stmt = $conn->prepare("
                        INSERT INTO profiles (id, email, full_name, password, role, employment_type, company_id, basic_salary, hourly_rate, internship_months, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $internship_value = ($employment_type === 'intern') ? $internship_months : null;
                    $stmt->execute([$uuid, $email, $full_name, $hashed_password, $role, $employment_type, $company_id_value, $basic_salary, $hourly_rate, $internship_value]);
                } catch (PDOException $e) {
                    // If internship_months column doesn't exist, insert without it
                    if (strpos($e->getMessage(), 'internship_months') !== false) {
                        $stmt = $conn->prepare("
                            INSERT INTO profiles (id, email, full_name, password, role, employment_type, company_id, basic_salary, hourly_rate, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$uuid, $email, $full_name, $hashed_password, $role, $employment_type, $company_id_value, $basic_salary, $hourly_rate]);
                    } else {
                        throw $e;
                    }
                }

                // Send Welcome Email
                $subject = "Welcome to " . __('app_name');
                $body = "
                    <html>
                    <body>
                        <h2>Welcome, {$full_name}!</h2>
                        <p>Your account has been successfully created.</p>
                        <p><strong>Login Email:</strong> {$email}</p>
                        <p>You can now login to the system using the password you set.</p>
                        <p><a href='" . Environment::get('APP_URL', 'http://localhost') . "/auth/login.php'>Login Here</a></p>
                    </body>
                    </html>
                ";
                sendEmail($email, $subject, $body);

                $success = __('register_page.register_success');

                // Clear form data
                $full_name = $email = $phone = '';
            }
        } catch (PDOException $e) {
            $error = __('errors.system_error');
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
    <title><?= __('register') ?> - <?= __('app_name') ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Auth CSS -->
    <link href="../assets/css/auth.css" rel="stylesheet">
    <link rel="apple-touch-icon" href="../assets/logos/mi-nes-logo.jpg">
    <link rel="icon" type="image/jpeg" href="../assets/logos/mi-nes-logo.jpg">

    <style>
        .header-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            margin-bottom: 10px;
            border-radius: 8px;
            background: white;
            padding: 5px;
        }
    </style>
</head>

<body>
    <div class="register-container animate-up">
        <!-- Language Switcher -->
        <div class="text-end mb-4">
            <div class="d-inline-block glass-card p-1 px-3">
                <?= getLanguageSwitcher() ?>
            </div>
        </div>

        <div class="register-card">
            <div class="register-header">
                <h1><?= __('register_page.title') ?></h1>
                <p id="headerCompanyName" class="text-white-50 small fw-bold text-uppercase tracking-widest"><?= strtoupper(__('app_name')) ?></p>
            </div>

            <div class="register-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert glass-card border-0 p-5 text-center">
                        <div class="avatar-lg bg-success-soft text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; font-size: 2.5rem; background: rgba(16, 185, 129, 0.1);">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                        <h2 class="fw-bold mb-3"><?= __('register_page.register_success') ?></h2>
                        <p class="text-muted mb-4">Your account has been created. You can now access your dashboard.</p>
                        <a href="login.php" class="btn btn-premium w-100 py-3">
                            <i class="bi bi-box-arrow-in-right me-2"></i> <?= __('login_page.btn_login') ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!$success): ?>
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <!-- Company Selection -->
                        <div class="company-selection">
                            <div class="section-label text-uppercase small fw-bold">
                                <i class="bi bi-building"></i> <?= __('login_page.select_company') ?> <span
                                    class="required">*</span>
                            </div>
                            <div class="company-cards">
                                <?php foreach ($companies as $index => $company): ?>
                                    <div class="company-card glass-card <?= $index === 0 ? 'selected' : '' ?>"
                                        onclick="selectCompany('<?= htmlspecialchars($company['id']) ?>', '<?= htmlspecialchars($company['logo_url'] ?? 'nes.jpg') ?>', '<?= htmlspecialchars($company['name']) ?>')">
                                        <div class="check-badge">
                                            <i class="bi bi-check-lg"></i>
                                        </div>
                                        <div class="company-logo-wrapper">
                                            <img src="../assets/logos/<?= htmlspecialchars($company['logo_url'] ?? 'nes.jpg') ?>"
                                                alt="<?= htmlspecialchars($company['name']) ?>" class="company-logo"
                                                onerror="this.src='../assets/logos/nes.jpg'">
                                        </div>
                                        <div class="company-name"><?= strtoupper(htmlspecialchars($company['name'])) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="company_id" id="company_id"
                                value="<?= htmlspecialchars($companies[0]['id'] ?? '') ?>">
                        </div>

                        <!-- Role Selection -->
                        <div class="mb-3">
                            <label for="role" class="form-label text-uppercase small fw-bold">
                                <i class="bi bi-shield-check me-1"></i> Account Type <span class="required">*</span>
                            </label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="staff" selected>Staff Member</option>
                                <option value="hr">HR Manager</option>
                            </select>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> HR managers have full access to manage employees and
                                payroll
                            </small>
                            <div class="invalid-feedback">Please select an account type.</div>
                        </div>

                        <div class="mb-3">
                            <label for="full_name" class="form-label text-uppercase small fw-bold">
                                <i class="bi bi-person me-1"></i> Full Name <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                placeholder="Enter your full name" value="<?= htmlspecialchars($full_name ?? '') ?>"
                                required autofocus>
                            <div class="invalid-feedback">Please enter your full name.</div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label text-uppercase small fw-bold">
                                <i class="bi bi-envelope me-1"></i> Work Email <span class="required">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                placeholder="yourname@company.com" value="<?= htmlspecialchars($email ?? '') ?>" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label text-uppercase small fw-bold">
                                <i class="bi bi-telephone me-1"></i> Contact Number
                            </label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="+60 12-345 6789"
                                value="<?= htmlspecialchars($phone ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="employment_type" class="form-label">
                                <i class="bi bi-briefcase me-1"></i> Employment Type <span class="required">*</span>
                            </label>
                            <select class="form-select" id="employment_type" name="employment_type" required
                                onchange="toggleSalaryFields()">
                                <option value="" disabled <?= empty($employment_type ?? '') ? 'selected' : '' ?>>Select type
                                </option>
                                <option value="permanent" <?= ($employment_type ?? '') === 'permanent' ? 'selected' : '' ?>>
                                    Permanent</option>
                                <option value="leader" <?= ($employment_type ?? '') === 'leader' ? 'selected' : '' ?>>
                                    Leader</option>
                                <option value="part-time" <?= ($employment_type ?? '') === 'part-time' ? 'selected' : '' ?>>
                                    Part-Time</option>
                                <option value="intern" <?= ($employment_type ?? '') === 'intern' ? 'selected' : '' ?>>
                                    Internship</option>
                            </select>
                            <div class="invalid-feedback">Please select employment type.</div>
                        </div>

                        <!-- Salary Fields -->
                        <div class="row" id="salary_fields">
                            <div class="col-md-6 mb-3">
                                <label for="basic_salary" class="form-label">
                                    <i class="bi bi-cash me-1"></i> Basic Salary (RM)
                                </label>
                                <input type="number" class="form-control" id="basic_salary" name="basic_salary"
                                    placeholder="0.00" step="0.01" min="0"
                                    value="<?= htmlspecialchars($basic_salary ?? '0') ?>">
                                <small class="text-muted">Monthly salary for permanent/leader/part-time</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hourly_rate" class="form-label">
                                    <i class="bi bi-clock me-1"></i> Hourly Rate (RM)
                                </label>
                                <input type="number" class="form-control" id="hourly_rate" name="hourly_rate"
                                    placeholder="0.00" step="0.01" min="0"
                                    value="<?= htmlspecialchars($hourly_rate ?? '0') ?>">
                                <small class="text-muted">For part-time employees</small>
                            </div>
                        </div>

                        <!-- Internship Duration (Only for Interns) -->
                        <div class="mb-3" id="internship_field" style="display: none;">
                            <label for="internship_months" class="form-label">
                                <i class="bi bi-calendar-range me-1"></i> Internship Duration (Months) <span
                                    class="required">*</span>
                            </label>
                            <input type="number" class="form-control" id="internship_months" name="internship_months"
                                placeholder="e.g., 3 or 6" min="1" max="12"
                                value="<?= htmlspecialchars($internship_months ?? '') ?>">
                            <small class="text-muted">Number of months for your internship program (e.g., 3 months = 3 days
                                NRL)</small>
                            <div class="invalid-feedback">Please enter internship duration (1-12 months).</div>
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

                        <button type="submit" class="btn btn-register">
                            <i class="bi bi-rocket-takeoff-fill me-2"></i> Get Started
                        </button>

                        <div class="text-center mt-3">
                            <span class="text-muted">Already registered?</span>
                            <a href="login.php" class="text-decoration-none fw-semibold"> Sign in</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <div class="register-footer">
                <p class="mb-0" id="footerCompany">
                    &copy; <?= date('Y') ?> <?= strtoupper($companies[0]['name'] ?? 'NES Solution & Network Sdn Bhd') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Select company function
        function selectCompany(companyId, logo, name) {
            // Update hidden input
            document.getElementById('company_id').value = companyId;

            // Update header info
            document.getElementById('headerCompanyName').textContent = name;

            // Update footer
            document.getElementById('footerCompany').innerHTML = '&copy; <?= date('Y') ?> ' + name;

            // Update card selection
            document.querySelectorAll('.company-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
        }

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

        // Toggle salary fields based on employment type
        function toggleSalaryFields() {
            var empType = document.getElementById('employment_type').value;
            var basicSalary = document.getElementById('basic_salary');
            var hourlyRate = document.getElementById('hourly_rate');
            var internshipField = document.getElementById('internship_field');
            var internshipMonths = document.getElementById('internship_months');

            // Reset required
            basicSalary.required = false;
            hourlyRate.required = false;
            internshipMonths.required = false;

            // Hide all conditional fields first
            internshipField.style.display = 'none';

            if (empType === 'part-time') {
                hourlyRate.required = true;
            } else if (empType === 'permanent' || empType === 'leader' || empType === 'part-time') {
                basicSalary.required = true;
            } else if (empType === 'intern') {
                internshipField.style.display = 'block';
                internshipMonths.required = true;
            }
        }

        // Form validation
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    // Check if passwords match
                    var password = document.getElementById('password');
                    var confirmPassword = document.getElementById('confirm_password');

                    if (password.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Passwords do not match');
                    } else {
                        confirmPassword.setCustomValidity('');
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
        document.getElementById('confirm_password').addEventListener('input', function () {
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