<?php
/**
 * ============================================
 * FORGOT PASSWORD PAGE
 * ============================================
 * Users can request a password reset link here.
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

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');

    if (empty($email) || !isValidEmail($email)) {
        $message = "Please enter a valid email address.";
        $messageType = 'danger';
    } else {
        // Create token
        $result = createPasswordResetToken($email);

        if ($result) {
            // Send email
            $resetLink = Environment::get('APP_URL', 'http://localhost/payroll%20system') . '/auth/reset_password.php?token=' . $result['token'];

            $subject = "Reset Password - MI-NES Payroll";
            $body = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2>Reset Your Password</h2>
                    <p>Hi {$result['full_name']},</p>
                    <p>We received a request to reset your password. Click the link below to set a new password:</p>
                    <p><a href='{$resetLink}' style='background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Reset Password</a></p>
                    <p>Or copy this link: <br> $resetLink</p>
                    <p>This link expires in 1 hour.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                </body>
                </html>
            ";

            if (sendEmail($email, $subject, $body)) {
                $message = "Password reset link has been sent to your email.";
                $messageType = 'success';
            } else {
                $message = "Failed to send email. Please try again later.";
                $messageType = 'danger';
            }
        } else {
            // For security, don't reveal if email exists or not, but for now we might handle it simple
            // If result is false, it usually means user not found or DB error.
            // Let's just say "If the email matches our records, a link has been sent."
            $message = "If an account exists with this email, a reset link has been sent.";
            $messageType = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLang() ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?= __('app_name') ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Auth CSS -->
    <link href="../assets/css/auth.css" rel="stylesheet">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="../assets/logos/nes.jpg" alt="Logo"
                    style="width: 80px; height: 80px; object-fit: contain; background: #fff; border-radius: 10px; padding: 5px;">
                <h1>Forgot Password</h1>
                <p>Enter your email to reset password</p>
            </div>

            <div class="login-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-1"></i> Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email"
                            placeholder="example@company.com" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="bi bi-send me-2"></i> Send Reset Link
                    </button>

                    <div class="text-center mt-3">
                        <a href="login.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Back to Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>