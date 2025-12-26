<?php
/**
 * ============================================
 * RESET PASSWORD PAGE
 * ============================================
 * Users set a new password here using the token.
 * ============================================
 */

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/language.php';

$token = $_GET['token'] ?? '';
$message = '';
$messageType = '';
$validToken = false;

if (empty($token)) {
    $message = "Invalid or missing token.";
    $messageType = 'danger';
} else {
    // Validate token
    $resetData = validatePasswordResetToken($token);
    
    if ($resetData) {
        $validToken = true;
    } else {
        $message = "This password reset token is invalid or has expired.";
        $messageType = 'danger';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirmDetails = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = 'danger';
    } elseif ($password !== $confirmDetails) {
        $message = "Passwords do not match.";
        $messageType = 'danger';
    } else {
        if (resetPasswordWithToken($token, $password)) {
            $message = "Password has been reset successfully. You can now login.";
            $messageType = 'success';
            $validToken = false; // Disable form
            // Redirect after short delay
            header("refresh:2;url=login.php");
        } else {
            $message = "Failed to reset password. Please try again.";
            $messageType = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?= __('app_name') ?></title>
    
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
                <img src="../assets/logos/nes.jpg" alt="Logo" style="width: 80px; height: 80px; object-fit: contain; background: #fff; border-radius: 10px; padding: 5px;">
                <h1>Reset Password</h1>
                <p>Create a new password</p>
            </div>
            
            <div class="login-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($validToken): ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="bi bi-check-circle me-2"></i> Reset Password
                    </button>
                    
                </form>
                <?php else: ?>
                    <div class="text-center">
                        <a href="login.php" class="btn btn-primary">Back to Login</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
