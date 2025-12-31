<?php
/**
 * ============================================
 * ADMIN SETTINGS
 * ============================================
 * Global system settings page.
 * Manage Company Profile and System Preferences.
 * ============================================
 */

require_once '../includes/functions.php';
require_once '../config/database.php';
requireHR();

require_once '../includes/header.php';
require_once '../includes/mailer.php';

$pageTitle = 'Settings - MI-NES Payroll';

$companyId = $_SESSION['company_id'];
$conn = getConnection();
$message = '';
$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_company') {
        $name = trim($_POST['name']);
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $regNo = trim($_POST['registration_number']);

        try {
            $stmt = $conn->prepare("
                UPDATE companies 
                SET name = ?, address = ?, phone = ?, email = ?, registration_number = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $address, $phone, $email, $regNo, $companyId]);
            $message = "Company profile updated successfully!";
        } catch (Exception $e) {
            $error = "Error updating profile: " . $e->getMessage();
        }
    } elseif ($action === 'test_email') {
        $mailer = new Mailer();
        $testEmail = $_POST['test_email_address'];
        if ($mailer->send($testEmail, "Test Email from MI-NES", "<p>This is a <strong>test email</strong> to verify your payroll system notifications are working.</p>")) {
            $message = "Test email sent to $testEmail!";
        } else {
            $error = "Failed to send test email. Check server logs.";
        }
    }
}

// Fetch Company Data
$stmt = $conn->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->execute([$companyId]);
$company = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<?php include '../includes/hr_sidebar.php'; ?>

<div class="main-content">
    <?php
    $navTitle = 'System Settings';
    include '../includes/top_navbar.php';
    ?>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Company Profile Settings -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent py-3 border-bottom-0">
                    <h5 class="fw-bold m-0 text-dark"><i class="bi bi-building me-2 text-primary"></i>Company Profile
                    </h5>
                </div>
                <div class="card-body pt-0">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_company">

                        <div class="mb-3">
                            <label class="form-label text-start">Company Name</label>
                            <input type="text" name="name" class="form-control"
                                value="<?= htmlspecialchars($company['name']) ?>" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Registration No / SSM</label>
                                <input type="text" name="registration_number" class="form-control"
                                    value="<?= htmlspecialchars($company['registration_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" name="phone" class="form-control"
                                    value="<?= htmlspecialchars($company['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Official Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($company['email'] ?? '') ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Company Address</label>
                            <textarea name="address" class="form-control"
                                rows="3"><?= htmlspecialchars($company['address'] ?? '') ?></textarea>
                            <div class="form-text">This address will appear on all employee payslips.</div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4 fw-bold">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- System / Email Settings -->
        <div class="col-lg-4">
            <!-- Email Test -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent py-3 border-bottom-0">
                    <h5 class="fw-bold m-0 text-dark"><i class="bi bi-envelope me-2 text-warning"></i>Email
                        Notifications</h5>
                </div>
                <div class="card-body pt-0">
                    <p class="text-muted small">The system uses PHP's <code>mail()</code> function by default. Use this
                        to verify your server can send emails.</p>

                    <form method="POST">
                        <input type="hidden" name="action" value="test_email">
                        <label class="form-label">Test Email Address</label>
                        <div class="input-group mb-3">
                            <input type="email" name="test_email_address" class="form-control"
                                placeholder="your@email.com" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>"
                                required>
                            <button class="btn btn-warning text-dark" type="submit">Send Test</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Other Settings Placeholder -->
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body text-center p-4">
                    <i class="bi bi-gear-wide-connected fs-1 text-muted mb-3"></i>
                    <h6>More Settings Coming Soon</h6>
                    <p class="small text-muted">Payroll logic configuration and RBAC settings will be available in
                        future updates.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>