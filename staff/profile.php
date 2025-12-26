<?php
/**
 * ============================================
 * STAFF PROFILE PAGE
 * ============================================
 * Halaman untuk kemaskini profil peribadi.
 * ============================================
 */

$pageTitle = 'My Profile - MI-NES Payroll';
require_once '../includes/header.php';
requireLogin();

if (isHR()) {
    redirect('../hr/dashboard.php');
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Process profile update (Supabase profiles table - limited fields)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_profile';

    if ($action === 'update_profile') {
        $fullName = sanitize($_POST['full_name'] ?? '');
        $icNumber = sanitize($_POST['ic_number'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $bankName = sanitize($_POST['bank_name'] ?? '');
        $bankAccount = sanitize($_POST['bank_account'] ?? '');
        $dependents = intval($_POST['dependents'] ?? 0);

        try {
            $conn = getConnection();

            $errors = [];

            // Validate
            if (empty($fullName)) {
                $errors[] = 'Full name is required.';
            }

            // Validate dependents range
            if ($dependents < 0 || $dependents > 10) {
                $errors[] = 'Number of dependents must be between 0 and 10.';
            }

            if (empty($errors)) {
                // Update profile with all fields including dependents
                $stmt = $conn->prepare("
                    UPDATE profiles SET 
                        full_name = ?, 
                        ic_number = ?, 
                        phone = ?, 
                        address = ?, 
                        bank_name = ?, 
                        bank_account = ?,
                        dependents = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$fullName, $icNumber, $phone, $address, $bankName, $bankAccount, $dependents, $userId]);

                // Update session
                $_SESSION['full_name'] = $fullName;

                $message = 'Profile successfully updated.';
                $messageType = 'success';
            } else {
                $message = implode(' ', $errors);
                $messageType = 'error';
            }

        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            $message = 'System error. Please try again.';
            $messageType = 'error';
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        try {
            $conn = getConnection();

            // Get current password hash
            $stmt = $conn->prepare("SELECT password FROM profiles WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $errors = [];

            // Validate
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $errors[] = 'All password fields are required.';
            } elseif (!password_verify($currentPassword, $user['password'])) {
                $errors[] = 'Current password is incorrect.';
            } elseif (strlen($newPassword) < 6) {
                $errors[] = 'New password must be at least 6 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $errors[] = 'New password and confirmation do not match.';
            } elseif ($currentPassword === $newPassword) {
                $errors[] = 'New password must be different from current password.';
            }

            if (empty($errors)) {
                // Hash new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update password
                $stmt = $conn->prepare("
                    UPDATE profiles SET 
                        password = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$hashedPassword, $userId]);

                $message = 'Password successfully changed. Please login again with your new password.';
                $messageType = 'success';

                // Log the user out for security (force re-login with new password)
                // Uncomment if you want to force re-login:
                // session_destroy();
                // redirect('../auth/login.php?message=password_changed');

            } else {
                $message = implode(' ', $errors);
                $messageType = 'error';
            }

        } catch (PDOException $e) {
            error_log("Password change error: " . $e->getMessage());
            $message = 'System error. Please try again.';
            $messageType = 'error';
        }
    }
}

// Get user profile (Supabase profiles table)
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT p.*, c.name as company_name FROM profiles p LEFT JOIN companies c ON p.company_id = c.id WHERE p.id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Profile fetch error: " . $e->getMessage());
    $user = null;
}
?>

<?php include '../includes/staff_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <?php
    $navTitle = __('nav.profile');
    include '../includes/top_navbar.php';

    // Router for profile views
    $role = $user['role'] ?? '';
    $type = $user['employment_type'] ?? '';

    if ($role === 'intern' || $type === 'intern') {
        include 'views/intern/profile.php';
    } elseif ($role === 'leader' || $type === 'leader') {
        include 'views/leader/profile.php';
    } elseif ($role === 'part_time' || $type === 'part_time') {
        include 'views/part_time/profile.php';
    } else {
        include 'views/permanent/profile.php';
    }
    ?>
</div>

<?php require_once '../includes/footer.php'; ?>