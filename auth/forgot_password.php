<?php
/**
 * ============================================
 * FORGOT PASSWORD PAGE
 * ============================================
 * Halaman untuk reset password melalui email.
 * ============================================
 */

$pageTitle = 'Lupa Password - MI-NES Payroll';
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
$emailSent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        $message = 'Sila masukkan alamat email.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Format email tidak sah.';
        $messageType = 'error';
    } else {
        // Create reset token
        $resetData = createPasswordResetToken($email);
        
        if ($resetData) {
            // Generate reset link
            $appUrl = Environment::get('APP_URL', 'http://localhost');
            $resetLink = $appUrl . '/auth/reset_password.php?token=' . $resetData['token'];
            
            // Send email
            $subject = 'Reset Password - MI-NES Payroll System';
            $htmlBody = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #0d6efd; color: white; padding: 20px; text-align: center; }
                        .content { background: #f8f9fa; padding: 30px; border: 1px solid #dee2e6; }
                        .button { display: inline-block; padding: 12px 30px; background: #0d6efd; color: white; 
                                 text-decoration: none; border-radius: 5px; margin: 20px 0; }
                        .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
                        .warning { background: #fff3cd; border: 1px solid #ffecb5; padding: 15px; margin: 15px 0; 
                                  border-radius: 5px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Reset Password</h2>
                        </div>
                        <div class='content'>
                            <p>Hi <strong>{$resetData['full_name']}</strong>,</p>
                            
                            <p>Kami menerima permintaan untuk reset password akaun anda. Klik butang di bawah untuk menetapkan password baru:</p>
                            
                            <div style='text-align: center;'>
                                <a href='{$resetLink}' class='button'>Reset Password</a>
                            </div>
                            
                            <p>Atau salin pautan ini ke pelayar anda:</p>
                            <p style='word-break: break-all; color: #0d6efd;'>{$resetLink}</p>
                            
                            <div class='warning'>
                                <strong>⚠️ Penting:</strong>
                                <ul>
                                    <li>Pautan ini akan tamat tempoh dalam <strong>1 jam</strong></li>
                                    <li>Jika anda tidak meminta reset password, sila abaikan email ini</li>
                                    <li>Jangan kongsikan pautan ini dengan sesiapa</li>
                                </ul>
                            </div>
                            
                            <p>Jika butang di atas tidak berfungsi, sila salin dan tampal URL penuh ke dalam pelayar anda.</p>
                        </div>
                        <div class='footer'>
                            <p>Email ini dijana secara automatik. Sila jangan balas.</p>
                            <p>&copy; " . date('Y') . " MI-NES Payroll System. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $emailResult = sendEmail($email, $subject, $htmlBody);
            
            if ($emailResult) {
                $emailSent = true;
                $message = "Email reset password telah dihantar ke <strong>{$email}</strong>. Sila semak inbox anda (dan folder spam).";
                $messageType = 'success';
            } else {
                $message = 'Gagal menghantar email. Sila cuba lagi atau hubungi pentadbir sistem.';
                $messageType = 'error';
            }
        } else {
            // Don't reveal if email exists (security best practice)
            $emailSent = true;
            $message = "Jika email <strong>{$email}</strong> wujud dalam sistem, anda akan menerima arahan reset password.";
            $messageType = 'info';
        }
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
                <i class="bi bi-key-fill text-primary" style="font-size: 3rem;"></i>
                <h2 class="mt-3">Lupa Password?</h2>
                <p class="text-muted">Masukkan email anda untuk menerima arahan reset password.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
                    <?= $message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!$emailSent): ?>
            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="nama@example.com" required autofocus>
                    </div>
                    <div class="invalid-feedback">
                        Sila masukkan alamat email yang sah.
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-send me-2"></i>Hantar Email Reset
                </button>
            </form>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="login.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left me-2"></i>Kembali ke Log Masuk
                </a>
            </div>

            <?php if ($emailSent): ?>
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="text-muted mb-2"><i class="bi bi-info-circle me-2"></i>Tidak terima email?</h6>
                <ul class="small text-muted mb-0">
                    <li>Semak folder spam atau junk mail</li>
                    <li>Pastikan email yang dimasukkan betul</li>
                    <li>Tunggu beberapa minit (email mungkin lambat)</li>
                    <li>Cuba hantar semula selepas 5 minit</li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>
</body>
</html>
