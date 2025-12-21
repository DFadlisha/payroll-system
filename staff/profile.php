<?php
/**
 * ============================================
 * STAFF PROFILE PAGE
 * ============================================
 * Halaman untuk kemaskini profil peribadi.
 * ============================================
 */

$pageTitle = 'Profil Saya - MI-NES Payroll';
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
    $fullName = sanitize($_POST['full_name'] ?? '');
    $epfNumber = sanitize($_POST['epf_number'] ?? '');
    $socsoNumber = sanitize($_POST['socso_number'] ?? '');
    
    try {
        $conn = getConnection();
        
        // Get current user data from profiles
        $stmt = $conn->prepare("SELECT * FROM profiles WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $errors = [];
        
        // Validate
        if (empty($fullName)) {
            $errors[] = 'Full name is required.';
        }
        
        if (empty($errors)) {
            // Update profile (profiles table has limited editable fields)
            $stmt = $conn->prepare("
                UPDATE profiles SET 
                    full_name = ?, epf_number = ?, socso_number = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$fullName, $epfNumber, $socsoNumber, $userId]);
            
            // Update session
            $_SESSION['full_name'] = $fullName;
            
            $message = 'Profil berjaya dikemaskini.';
            $messageType = 'success';
        } else {
            $message = implode(' ', $errors);
            $messageType = 'error';
        }
        
    } catch (PDOException $e) {
        error_log("Profile update error: " . $e->getMessage());
        $message = 'Ralat sistem. Sila cuba lagi.';
        $messageType = 'error';
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
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div>
            <button class="mobile-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <span class="fw-bold">Profil Saya</span>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?>
            </div>
            <div>
                <div class="fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <small class="text-muted">Staff</small>
            </div>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="bi bi-person me-2"></i>Profil Saya</h1>
    </div>
    
    <?php if ($user): ?>
    <div class="row g-4">
        <!-- Profile Summary -->
        <div class="col-lg-4">
            <div class="card text-center">
                <div class="card-body py-5">
                    <div class="user-avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 3rem;">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <h4 class="mb-1"><?= htmlspecialchars($user['full_name']) ?></h4>
                    <p class="text-muted mb-2"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="badge bg-primary"><?= getEmploymentTypeName($user['employment_type']) ?></span>
                </div>
                <div class="card-footer bg-light">
                    <div class="row text-center">
                        <div class="col">
                            <small class="text-muted d-block">Syarikat</small>
                            <strong><?= htmlspecialchars($user['company_name'] ?? 'N/A') ?></strong>
                        </div>
                        <div class="col border-start">
                            <small class="text-muted d-block">Gaji Pokok</small>
                            <strong><?= formatMoney($user['basic_salary']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-pencil me-2"></i>Kemaskini Maklumat
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <!-- Personal Info -->
                        <h6 class="text-muted mb-3">Maklumat Peribadi</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" 
                                       value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. IC</label>
                                <input type="text" name="ic_number" class="form-control" 
                                       value="<?= htmlspecialchars($user['ic_number'] ?? '') ?>"
                                       placeholder="000000-00-0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" 
                                       value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                <small class="text-muted">Email tidak boleh ditukar.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Telefon</label>
                                <input type="text" name="phone" class="form-control" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                       placeholder="012-3456789">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea name="address" class="form-control" rows="2"
                                          placeholder="Alamat penuh..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Bank Info -->
                        <h6 class="text-muted mb-3">Maklumat Bank</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Nama Bank</label>
                                <select name="bank_name" class="form-select">
                                    <option value="">-- Pilih Bank --</option>
                                    <?php
                                    $banks = ['Maybank', 'CIMB Bank', 'Public Bank', 'RHB Bank', 'Hong Leong Bank', 
                                              'AmBank', 'Bank Islam', 'Bank Rakyat', 'BSN', 'Affin Bank', 'Alliance Bank'];
                                    foreach ($banks as $bank):
                                    ?>
                                        <option value="<?= $bank ?>" <?= ($user['bank_name'] ?? '') === $bank ? 'selected' : '' ?>>
                                            <?= $bank ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">No. Akaun Bank</label>
                                <input type="text" name="bank_account" class="form-control" 
                                       value="<?= htmlspecialchars($user['bank_account'] ?? '') ?>"
                                       placeholder="1234567890">
                            </div>
                        </div>
                        
                        <!-- Password Change -->
                        <h6 class="text-muted mb-3">Tukar Password</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Password Semasa</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="new_password" class="form-control"
                                       minlength="6">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sahkan Password</label>
                                <input type="password" name="confirm_password" class="form-control">
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Kosongkan jika tidak mahu tukar password.</small>
                            </div>
                        </div>
                        
                        <hr>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="alert alert-danger">Ralat memuatkan data profil.</div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
