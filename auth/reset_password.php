<?php
/**
 * ============================================
 * RESET PASSWORD PAGE
 * ============================================
 * Halaman untuk menetapkan password baru melalui token.
 * ============================================
 */

$pageTitle = 'Reset Password - MI-NES Payroll';
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isHR()) {
        redirect('../hr/dashboard.php');
    } else {
        redirect('../staff/dashboard.php');
    }
}

$message = '';
$messageType = '';
$token = $_GET['token'] ?? '';
$resetSuccess = false;

// Validate token on page load
$resetData = false;
if (!empty($token)) {
    $resetData = validatePasswordResetToken($token);
    
    if (!$resetData) {
        $message = 'Token reset password tidak sah atau telah tamat tempoh. Sila minta token baru.';
        $messageType = 'error';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = sanitize($_POST['token'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    if (empty($token)) {
        $errors[] = 'Token tidak sah.';
    }
    
    if (empty($newPassword)) {
        $errors[] = 'Sila masukkan password baru.';
    } elseif (strlen($newPassword) < 6) {
        $errors[] = 'Password mestilah sekurang-kurangnya 6 aksara.';
    }
    
    if ($newPassword !== $confirmPassword) {
        $errors[] = 'Password tidak sepadan.';
    }
    
    if (empty($errors)) {
        $result = resetPasswordWithToken($token, $newPassword);
        
        if ($result) {
            $resetSuccess = true;
            $message = 'Password berjaya ditetapkan semula. Anda boleh log masuk sekarang.';
            $messageType = 'success';
        } else {
            $message = 'Token tidak sah atau telah tamat tempoh. Sila minta token baru.';
            $messageType = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <?php if ($resetSuccess): ?>
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    <h2 class="mt-3">Password Berjaya Ditukar!</h2>
                <?php else: ?>
                    <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3rem;"></i>
                    <h2 class="mt-3">Tetapkan Password Baru</h2>
                    <?php if ($resetData): ?>
                        <p class="text-muted">Hi <strong><?= htmlspecialchars($resetData['full_name']) ?></strong>, 
                        sila masukkan password baru anda.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($resetSuccess): ?>
                <!-- Success State -->
                <div class="text-center">
                    <p class="mb-4">Password anda telah berjaya dikemaskini. Anda boleh log masuk menggunakan password baru.</p>
                    <a href="login.php" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Pergi ke Log Masuk
                    </a>
                </div>
            <?php elseif ($resetData): ?>
                <!-- Reset Form -->
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="new_password" name="new_password" 
                                   placeholder="Masukkan password baru" minlength="6" required autofocus>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                <i class="bi bi-eye" id="new_password_icon"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimum 6 aksara</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Sahkan Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Sahkan password baru" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                <i class="bi bi-eye" id="confirm_password_icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Tips password selamat:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Gunakan kombinasi huruf besar dan kecil</li>
                                <li>Sertakan nombor dan simbol</li>
                                <li>Elakkan maklumat peribadi mudah diteka</li>
                            </ul>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-2"></i>Tetapkan Password Baru
                    </button>
                </form>
            <?php else: ?>
                <!-- Invalid/Expired Token -->
                <div class="text-center">
                    <p class="text-muted mb-4">Token reset password tidak sah atau telah tamat tempoh.</p>
                    <a href="forgot_password.php" class="btn btn-primary w-100">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Minta Token Baru
                    </a>
                </div>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="login.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Log Masuk
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '_icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

        // Bootstrap form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Password match validation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Password tidak sepadan');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            });
        }
    </script>
</body>
</html>
