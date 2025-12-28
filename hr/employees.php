<?php
/**
 * ============================================
 * HR EMPLOYEES MANAGEMENT PAGE
 * ============================================
 * Manage employees: Add, Edit, Delete.
 * ============================================
 */

require_once '../includes/header.php';
requireHR();
$pageTitle = 'Employees - MI-NES Payroll System';

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
        // Logic to handle basic_salary vs hourly_rate
        $inputAmount = floatval($_POST['basic_salary'] ?? 0);

        // Reset both first
        $basicSalary = 0;
        $hourlyRate = 0;

        if ($employmentType === 'part-time') {
            $hourlyRate = $inputAmount;
        } else {
            $basicSalary = $inputAmount;
        }

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
            // Delete profile
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

<?php include '../includes/hr_sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content">
    <?php
    $navTitle = 'Employees';
    include '../includes/top_navbar.php';
    ?>

    <!-- Flash Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Welcome Header & Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <p class="text-muted mb-1">Human Resources</p>
            <h2 class="fw-bold">Employee Management</h2>
            <div class="d-flex align-items-center mt-2 text-muted">
                <i class="bi bi-info-circle me-2"></i> Manage your company's employees.
            </div>
        </div>
        <div>
            <?php if ($action !== 'add' && !$editId): ?>
                <a href="?action=add" class="btn btn-primary rounded-pill px-4">
                    <i class="bi bi-plus-circle me-2"></i>Add Employee
                </a>
            <?php else: ?>
                <a href="employees.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i>Back to List
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($action === 'add'): ?>
        <!-- Add Employee Form -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus me-2 text-primary"></i>Add New Employee</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select bg-light border-0" id="addRole">
                                <option value="staff">Staff</option>
                                <option value="leader">Leader</option>
                                <option value="part_time">Part-Time</option>
                                <option value="intern">Intern</option>
                                <option value="hr">HR Admin</option>
                            </select>
                        </div>
                        <div class="col-md-4" style="display: none;">
                            <label class="form-label">Employment Type</label>
                            <select name="employment_type" class="form-select bg-light border-0" id="addEmploymentType">
                                <option value="permanent">Full-Time</option>
                                <option value="leader">Leader</option>
                                <option value="part-time">Part-Time</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Basic Salary (RM)</label>
                            <input type="number" name="basic_salary" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone No.</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IC/ID Number</label>
                            <input type="text" name="ic_number" class="form-control">
                        </div>
                        <div class="col-md-6" id="addEpfContainer">
                            <label class="form-label">EPF / KWSP No. <span class="text-muted">(for
                                    Staff/Leader)</span></label>
                            <input type="text" name="epf_number" class="form-control" placeholder="Ex: 12345678">
                        </div>
                        <div class="col-md-6" id="addInternMonthsContainer" style="display: none;">
                            <label class="form-label">Internship Duration (Months)</label>
                            <select name="internship_months" class="form-select bg-light border-0">
                                <option value="0">-- Select --</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> months</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password</label>
                            <input type="text" name="password" class="form-control" value="password123">
                            <small class="text-muted">Default password. Employee can change this after login.</small>
                        </div>
                        <div class="col-12 mt-4">
                            <hr>
                            <button type="submit" name="add_employee" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-check-circle me-2"></i>Save Employee
                            </button>
                            <a href="employees.php" class="btn btn-outline-secondary rounded-pill px-4 ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <?php elseif ($editEmployee): ?>
        <!-- Edit Employee Form -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Update Employee Details</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="employee_id" value="<?= $editEmployee['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control"
                                value="<?= htmlspecialchars($editEmployee['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control bg-light"
                                value="<?= htmlspecialchars($editEmployee['email']) ?>" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Role <span class="text-info"
                                    title="HR can change intern to Staff/Leader"><i
                                        class="bi bi-info-circle"></i></span></label>
                            <select name="role" class="form-select bg-light border-0" id="editRole">
                                <option value="staff" <?= $editEmployee['role'] === 'staff' ? 'selected' : '' ?>>Staff
                                    (Full-Time)</option>
                                <option value="leader" <?= $editEmployee['role'] === 'leader' ? 'selected' : '' ?>>Leader
                                </option>
                                <option value="part_time" <?= $editEmployee['role'] === 'part_time' ? 'selected' : '' ?>>
                                    Part-Time</option>
                                <option value="intern" <?= $editEmployee['role'] === 'intern' ? 'selected' : '' ?>>Intern
                                </option>
                                <option value="hr" <?= $editEmployee['role'] === 'hr' ? 'selected' : '' ?>>HR Admin</option>
                            </select>
                        </div>
                        <div class="col-md-4" style="display: none;">
                            <label class="form-label">Employment Type</label>
                            <select name="employment_type" class="form-select bg-light border-0" id="editEmploymentType">
                                <option value="permanent" <?= $editEmployee['employment_type'] === 'permanent' ? 'selected' : '' ?>>Full-Time</option>
                                <option value="leader" <?= $editEmployee['employment_type'] === 'leader' ? 'selected' : '' ?>>
                                    Leader</option>
                                <option value="part-time" <?= $editEmployee['employment_type'] === 'part-time' ? 'selected' : '' ?>>Part-Time</option>
                                <option value="intern" <?= $editEmployee['employment_type'] === 'intern' ? 'selected' : '' ?>>
                                    Intern</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Basic Salary (RM)</label>
                            <input type="number" name="basic_salary" class="form-control" step="0.01"
                                value="<?= $editEmployee['basic_salary'] ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone No.</label>
                            <input type="text" name="phone" class="form-control"
                                value="<?= htmlspecialchars($editEmployee['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IC/ID Number</label>
                            <input type="text" name="ic_number" class="form-control"
                                value="<?= htmlspecialchars($editEmployee['ic_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-6" id="editEpfContainer">
                            <label class="form-label">EPF / KWSP No. <span class="text-danger">*</span> <span
                                    class="text-muted">(required for Staff/Leader)</span></label>
                            <input type="text" name="epf_number" class="form-control"
                                value="<?= htmlspecialchars($editEmployee['epf_number'] ?? '') ?>"
                                placeholder="Ex: 12345678">
                        </div>
                        <div class="col-md-6" id="editInternMonthsContainer" <?= $editEmployee['role'] !== 'intern' ? 'style="display:none;"' : '' ?>>
                            <label class="form-label">Internship Duration (Months)</label>
                            <select name="internship_months" class="form-select bg-light border-0">
                                <option value="0">-- Select --</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($editEmployee['internship_months'] ?? 0) == $i ? 'selected' : '' ?>><?= $i ?> months</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="isActive"
                                    <?= isset($editEmployee['is_active']) && $editEmployee['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">Active Employee</label>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <hr>
                            <button type="submit" name="update_employee" class="btn btn-primary rounded-pill px-4">
                                <i class="bi bi-check-circle me-2"></i>Update Details
                            </button>
                            <a href="employees.php" class="btn btn-outline-secondary rounded-pill px-4 ms-2">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- Employees List -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-people me-2"></i>Employee List
                    (<?= count($employees) ?>)</h5>
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="showInactive" <?= isset($_GET['show_inactive']) ? 'checked' : '' ?>
                        onchange="location.href='employees.php' + (this.checked ? '?show_inactive=1' : '')">
                    <label class="form-check-label text-muted" for="showInactive">Show Inactive</label>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($employees)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted mb-3" style="font-size: 3rem; opacity: 0.5;"></i>
                        <p class="text-muted">No employees found.<br>
                            <small>Click "Add Employee" to create a new profile.</small>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>EPF No.</th>
                                    <th>Basic Salary</th>
                                    <th>Status</th>
                                    <th class="pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employees as $emp): ?>
                                    <?php
                                    $isActive = isset($emp['is_active']) ? $emp['is_active'] : true;
                                    ?>
                                    <tr class="<?= !$isActive ? 'table-secondary opacity-50' : '' ?>">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; font-size: 14px;">
                                                    <?= strtoupper(substr($emp['full_name'], 0, 1)) ?>
                                                </div>
                                                <span class="fw-bold"><?= htmlspecialchars($emp['full_name']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($emp['email']) ?></td>
                                        <td>
                                            <?php
                                            $roleBadges = [
                                                'hr' => ['HR Admin', 'bg-primary'],
                                                'staff' => ['Staff', 'bg-success'],
                                                'leader' => ['Leader', 'bg-info'],
                                                'part_time' => ['Part-Time', 'bg-secondary'],
                                                'intern' => ['Intern', 'bg-warning text-dark'],
                                            ];
                                            $badge = $roleBadges[$emp['role']] ?? ['Staff', 'bg-secondary'];
                                            ?>
                                            <span class="badge <?= $badge[1] ?> rounded-pill"><?= $badge[0] ?></span>
                                        </td>
                                        <td>
                                            <?php if (in_array($emp['role'], ['staff', 'leader', 'part_time', 'hr'])): ?>
                                                <?php if (!empty($emp['epf_number'])): ?>
                                                    <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>
                                                        <?= htmlspecialchars($emp['epf_number']) ?></span>
                                                <?php else: ?>
                                                    <span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i>
                                                        Missing</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= formatMoney($emp['basic_salary']) ?></td>
                                        <td>
                                            <span class="badge <?= $isActive ? 'bg-success' : 'bg-danger' ?> rounded-pill">
                                                <?= $isActive ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td class="pe-4">
                                            <a href="?id=<?= $emp['id'] ?>" class="btn btn-sm btn-outline-primary rounded-circle"
                                                title="Edit" style="width: 32px; height: 32px; padding: 0; line-height: 30px;">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($emp['id'] !== $_SESSION['user_id']): ?>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="employee_id" value="<?= $emp['id'] ?>">
                                                    <input type="hidden" name="delete_employee" value="1">
                                                    <button type="button" onclick="confirmDelete(this.form)"
                                                        class="btn btn-sm btn-outline-danger rounded-circle ms-1" title="Delete"
                                                        style="width: 32px; height: 32px; padding: 0; line-height: 30px;">
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function toggleRoleFields(roleSelect, prefix) {
        const role = roleSelect.value;
        const epfContainer = document.getElementById(prefix + 'EpfContainer');
        const internContainer = document.getElementById(prefix + 'InternMonthsContainer');
        const employmentTypeSelect = document.getElementById(prefix + 'EmploymentType');

        if (employmentTypeSelect) {
            if (role === 'staff' || role === 'hr') employmentTypeSelect.value = 'permanent';
            else if (role === 'leader') employmentTypeSelect.value = 'leader';
            else if (role === 'part_time') employmentTypeSelect.value = 'part-time';
            else if (role === 'intern') employmentTypeSelect.value = 'intern';
        }

        if (role === 'intern') {
            if (epfContainer) epfContainer.style.display = 'none';
            if (internContainer) internContainer.style.display = 'block';
        } else {
            if (epfContainer) epfContainer.style.display = 'block';
            if (internContainer) internContainer.style.display = 'none';
        }
    }

    const addRoleSelect = document.getElementById('addRole');
    if (addRoleSelect) {
        addRoleSelect.addEventListener('change', function () { toggleRoleFields(this, 'add'); });
        toggleRoleFields(addRoleSelect, 'add');
    }

    const editRoleSelect = document.getElementById('editRole');
    if (editRoleSelect) {
        editRoleSelect.addEventListener('change', function () { toggleRoleFields(this, 'edit'); });
        // Initial state set by PHP logic in style attribute, but consistent JS check is good.
    }

    function confirmDelete(form) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            borderRadius: '15px'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the form
                form.submit();
            }
        });
    }
</script>

<?php require_once '../includes/footer.php'; ?>