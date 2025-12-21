<?php
/**
 * ============================================
 * HR EMPLOYEES MANAGEMENT PAGE
 * ============================================
 * Halaman untuk urus pekerja.
 * Tambah, edit, padam pekerja.
 * ============================================
 */

$pageTitle = __('employees.title') . ' - ' . __('app_name');
require_once '../includes/header.php';
requireHR();

$companyId = $_SESSION['company_id'];
$action = $_GET['action'] ?? '';
$editId = $_GET['id'] ?? null;
$message = '';
$messageType = '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    
    // Add new employee
    if (isset($_POST['add_employee'])) {
        $email = sanitize($_POST['email'] ?? '');
        $fullName = sanitize($_POST['full_name'] ?? '');
        $role = sanitize($_POST['role'] ?? 'staff');
        $employmentType = sanitize($_POST['employment_type'] ?? 'permanent');
        $basicSalary = floatval($_POST['basic_salary'] ?? 0);
        $hourlyRate = floatval($_POST['hourly_rate'] ?? 0);
        $epfNumber = sanitize($_POST['epf_number'] ?? '');
        $socsoNumber = sanitize($_POST['socso_number'] ?? '');
        $citizenshipStatus = sanitize($_POST['citizenship_status'] ?? 'citizen');
        $password = $_POST['password'] ?? 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Check if email exists in profiles
            $stmt = $conn->prepare("SELECT id FROM profiles WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $message = 'Email already exists in system.';
                $messageType = 'error';
            } else {
                // Generate UUID for new profile
                $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                );
                
                $stmt = $conn->prepare("
                    INSERT INTO profiles (id, email, full_name, password, role, employment_type, basic_salary, hourly_rate, company_id, epf_number, socso_number, citizenship_status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$uuid, $email, $fullName, $hashedPassword, $role, $employmentType, $basicSalary, $hourlyRate, $companyId, $epfNumber, $socsoNumber, $citizenshipStatus]);
                
                $message = 'Employee added successfully.';
                $messageType = 'success';
                $action = '';
            }
        } catch (PDOException $e) {
            error_log("Add employee error: " . $e->getMessage());
            $message = 'System error. Please try again.';
            $messageType = 'error';
        }
    }
    
    // Update employee
    if (isset($_POST['update_employee'])) {
        $id = $_POST['employee_id'];
        $fullName = sanitize($_POST['full_name'] ?? '');
        $role = sanitize($_POST['role'] ?? 'staff');
        $employmentType = sanitize($_POST['employment_type'] ?? 'permanent');
        $basicSalary = floatval($_POST['basic_salary'] ?? 0);
        $hourlyRate = floatval($_POST['hourly_rate'] ?? 0);
        $epfNumber = sanitize($_POST['epf_number'] ?? '');
        $socsoNumber = sanitize($_POST['socso_number'] ?? '');
        $citizenshipStatus = sanitize($_POST['citizenship_status'] ?? 'citizen');
        
        try {
            $stmt = $conn->prepare("
                UPDATE profiles SET full_name = ?, role = ?, employment_type = ?, basic_salary = ?, 
                       hourly_rate = ?, epf_number = ?, socso_number = ?, citizenship_status = ?, updated_at = NOW()
                WHERE id = ? AND company_id = ?
            ");
            $stmt->execute([$fullName, $role, $employmentType, $basicSalary, $hourlyRate, $epfNumber, $socsoNumber, $citizenshipStatus, $id, $companyId]);
            
            $message = 'Employee updated successfully.';
            $messageType = 'success';
            $action = '';
            $editId = null;
        } catch (PDOException $e) {
            error_log("Update employee error: " . $e->getMessage());
            $message = 'System error. Please try again.';
            $messageType = 'error';
        }
    }
    
    // Delete employee
    if (isset($_POST['delete_employee'])) {
        $id = $_POST['employee_id'];
        
        try {
            // Delete profile (or you could add a status column if you want soft delete)
            $stmt = $conn->prepare("DELETE FROM profiles WHERE id = ? AND company_id = ?");
            $stmt->execute([$id, $companyId]);
            
            $message = 'Employee deleted successfully.';
            $messageType = 'success';
        } catch (PDOException $e) {
            error_log("Delete employee error: " . $e->getMessage());
            $message = 'System error. Please try again.';
            $messageType = 'error';
        }
    }
}

// Get employees list from profiles table
try {
    $conn = getConnection();
    
    $sql = "SELECT p.*, c.name as company_name 
            FROM profiles p 
            LEFT JOIN companies c ON p.company_id = c.id 
            WHERE p.company_id = ?
            ORDER BY p.full_name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$companyId]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get employee for editing
    $editEmployee = null;
    if ($editId) {
        $stmt = $conn->prepare("SELECT * FROM profiles WHERE id = ? AND company_id = ?");
        $stmt->execute([$editId, $companyId]);
        $editEmployee = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    error_log("Employee fetch error: " . $e->getMessage());
    $employees = [];
}
?>

<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-header">
        <h3><i class="bi bi-building me-2"></i>MI-NES</h3>
        <small><?= __('app_subtitle') ?></small>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="bi bi-speedometer2"></i> <?= __('nav.dashboard') ?></a></li>
        <li><a href="employees.php" class="active"><i class="bi bi-people"></i> <?= __('nav.employees') ?></a></li>
        <li><a href="attendance.php"><i class="bi bi-calendar-check"></i> <?= __('nav.attendance') ?></a></li>
        <li><a href="leaves.php"><i class="bi bi-calendar-x"></i> <?= __('nav.leaves') ?></a></li>
        <li><a href="payroll.php"><i class="bi bi-cash-stack"></i> <?= __('nav.payroll') ?></a></li>
        <li><a href="reports.php"><i class="bi bi-file-earmark-bar-graph"></i> <?= __('nav.reports') ?></a></li>
        <li class="mt-auto" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 20px;">
            <a href="../auth/logout.php"><i class="bi bi-box-arrow-left"></i> <?= __('logout') ?></a>
        </li>
    </ul>
</nav>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div>
            <button class="mobile-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <span class="fw-bold"><?= __('employees.title') ?></span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <?= getLanguageSwitcher() ?>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
                <div>
                    <div class="fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                    <small class="text-muted"><?= __('roles.hr') ?></small>
                </div>
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
    <div class="page-header d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-people me-2"></i>Senarai Pekerja</h1>
        <?php if ($action !== 'add' && !$editId): ?>
            <a href="?action=add" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Tambah Pekerja
            </a>
        <?php else: ?>
            <a href="employees.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        <?php endif; ?>
    </div>
    
    <?php if ($action === 'add'): ?>
        <!-- Add Employee Form -->
        <div class="card">
            <div class="card-header"><i class="bi bi-person-plus me-2"></i>Tambah Pekerja Baru</div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Peranan</label>
                            <select name="role" class="form-select" id="addRole">
                                <option value="staff">Staff</option>
                                <option value="part_time">Part-Time</option>
                                <option value="intern">Intern</option>
                                <option value="hr">HR Admin</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Pekerjaan</label>
                            <select name="employment_type" class="form-select">
                                <option value="full_time">Sepenuh Masa</option>
                                <option value="part_time">Separuh Masa</option>
                                <option value="intern">Pelatih</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gaji Pokok (RM)</label>
                            <input type="number" name="basic_salary" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Telefon</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. IC</label>
                            <input type="text" name="ic_number" class="form-control">
                        </div>
                        <div class="col-md-6" id="addEpfContainer">
                            <label class="form-label">No. EPF / KWSP <span class="text-muted">(untuk Staff/Part-Time)</span></label>
                            <input type="text" name="epf_number" class="form-control" placeholder="Contoh: 12345678">
                        </div>
                        <div class="col-md-6" id="addInternMonthsContainer" style="display: none;">
                            <label class="form-label">Tempoh Internship (Bulan)</label>
                            <select name="internship_months" class="form-select">
                                <option value="0">-- Pilih --</option>
                                <?php for($i=1; $i<=12; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> bulan</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password</label>
                            <input type="text" name="password" class="form-control" value="password123">
                            <small class="text-muted">Default password. Pekerja boleh tukar selepas login.</small>
                        </div>
                        <div class="col-12">
                            <hr>
                            <button type="submit" name="add_employee" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Simpan
                            </button>
                            <a href="employees.php" class="btn btn-outline-secondary ms-2">Batal</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
    <?php elseif ($editEmployee): ?>
        <!-- Edit Employee Form -->
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil me-2"></i>Kemaskini Pekerja</div>
            <div class="card-body">
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="employee_id" value="<?= $editEmployee['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" 
                                   value="<?= htmlspecialchars($editEmployee['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?= htmlspecialchars($editEmployee['email']) ?>" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Peranan <span class="text-info"><i class="bi bi-info-circle" title="HR boleh tukar intern ke Staff/Part-Time"></i></span></label>
                            <select name="role" class="form-select" id="editRole">
                                <option value="staff" <?= $editEmployee['role'] === 'staff' ? 'selected' : '' ?>>Staff (Full-Time)</option>
                                <option value="part_time" <?= $editEmployee['role'] === 'part_time' ? 'selected' : '' ?>>Part-Time</option>
                                <option value="intern" <?= $editEmployee['role'] === 'intern' ? 'selected' : '' ?>>Intern</option>
                                <option value="hr" <?= $editEmployee['role'] === 'hr' ? 'selected' : '' ?>>HR Admin</option>
                            </select>
                            <?php if ($editEmployee['role'] === 'intern'): ?>
                                <small class="text-success"><i class="bi bi-arrow-up"></i> Boleh promosi ke Staff/Part-Time</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jenis Pekerjaan</label>
                            <select name="employment_type" class="form-select">
                                <option value="full_time" <?= $editEmployee['employment_type'] === 'full_time' ? 'selected' : '' ?>>Sepenuh Masa</option>
                                <option value="part_time" <?= $editEmployee['employment_type'] === 'part_time' ? 'selected' : '' ?>>Separuh Masa</option>
                                <option value="intern" <?= $editEmployee['employment_type'] === 'intern' ? 'selected' : '' ?>>Pelatih</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gaji Pokok (RM)</label>
                            <input type="number" name="basic_salary" class="form-control" step="0.01" 
                                   value="<?= $editEmployee['basic_salary'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Telefon</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($editEmployee['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. IC</label>
                            <input type="text" name="ic_number" class="form-control" value="<?= htmlspecialchars($editEmployee['ic_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-6" id="editEpfContainer">
                            <label class="form-label">No. EPF / KWSP <span class="text-danger">*</span> <span class="text-muted">(wajib untuk Staff/Part-Time)</span></label>
                            <input type="text" name="epf_number" class="form-control" 
                                   value="<?= htmlspecialchars($editEmployee['epf_number'] ?? '') ?>"
                                   placeholder="Contoh: 12345678">
                            <?php if (in_array($editEmployee['role'], ['staff', 'part_time']) && empty($editEmployee['epf_number'])): ?>
                                <small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Sila isi No. EPF</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6" id="editInternMonthsContainer" <?= $editEmployee['role'] !== 'intern' ? 'style="display:none;"' : '' ?>>
                            <label class="form-label">Tempoh Internship (Bulan)</label>
                            <select name="internship_months" class="form-select">
                                <option value="0">-- Pilih --</option>
                                <?php for($i=1; $i<=12; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($editEmployee['internship_months'] ?? 0) == $i ? 'selected' : '' ?>><?= $i ?> bulan</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                                       <?= $editEmployee['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">Aktif</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <hr>
                            <button type="submit" name="update_employee" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Kemaskini
                            </button>
                            <a href="employees.php" class="btn btn-outline-secondary ms-2">Batal</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Employees List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Senarai Pekerja (<?= count($employees) ?>)</span>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="showInactive" 
                           <?= isset($_GET['show_inactive']) ? 'checked' : '' ?>
                           onchange="location.href='employees.php' + (this.checked ? '?show_inactive=1' : '')">
                    <label class="form-check-label" for="showInactive">Papar tidak aktif</label>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($employees)): ?>
                    <p class="text-muted text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                        Tiada pekerja dalam senarai.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Peranan</th>
                                    <th>No. EPF</th>
                                    <th>Gaji Pokok</th>
                                    <th>Status</th>
                                    <th>Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $emp): ?>
                                    <tr class="<?= !$emp['is_active'] ? 'table-secondary' : '' ?>">
                                        <td><strong><?= htmlspecialchars($emp['full_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($emp['email']) ?></td>
                                        <td>
                                            <?php 
                                            $roleBadges = [
                                                'hr' => ['HR Admin', 'bg-primary'],
                                                'staff' => ['Staff', 'bg-success'],
                                                'part_time' => ['Part-Time', 'bg-info'],
                                                'intern' => ['Intern', 'bg-warning text-dark'],
                                            ];
                                            $badge = $roleBadges[$emp['role']] ?? ['Staff', 'bg-secondary'];
                                            ?>
                                            <span class="badge <?= $badge[1] ?>"><?= $badge[0] ?></span>
                                        </td>
                                        <td>
                                            <?php if (in_array($emp['role'], ['staff', 'part_time', 'hr'])): ?>
                                                <?php if (!empty($emp['epf_number'])): ?>
                                                    <span class="text-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($emp['epf_number']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Tiada</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatMoney($emp['basic_salary']) ?></td>
                                        <td>
                                            <span class="badge <?= $emp['is_active'] ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $emp['is_active'] ? 'Aktif' : 'Tidak Aktif' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($emp['id'] !== $_SESSION['user_id']): ?>
                                                <form method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Adakah anda pasti untuk memadam pekerja ini?')">
                                                    <input type="hidden" name="employee_id" value="<?= $emp['id'] ?>">
                                                    <button type="submit" name="delete_employee" class="btn btn-sm btn-outline-danger" title="Padam">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Toggle EPF and Internship fields based on role selection
function toggleRoleFields(roleSelect, prefix) {
    const role = roleSelect.value;
    const epfContainer = document.getElementById(prefix + 'EpfContainer');
    const internContainer = document.getElementById(prefix + 'InternMonthsContainer');
    
    if (role === 'intern') {
        if (epfContainer) epfContainer.style.display = 'none';
        if (internContainer) internContainer.style.display = 'block';
    } else {
        if (epfContainer) epfContainer.style.display = 'block';
        if (internContainer) internContainer.style.display = 'none';
    }
}

// Add form role change handler
const addRoleSelect = document.getElementById('addRole');
if (addRoleSelect) {
    addRoleSelect.addEventListener('change', function() {
        toggleRoleFields(this, 'add');
    });
    toggleRoleFields(addRoleSelect, 'add');
}

// Edit form role change handler
const editRoleSelect = document.getElementById('editRole');
if (editRoleSelect) {
    editRoleSelect.addEventListener('change', function() {
        toggleRoleFields(this, 'edit');
    });
}
</script>

<?php require_once '../includes/footer.php'; ?>
