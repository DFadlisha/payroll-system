<!-- Flash Messages -->
<?php if ($message): ?>
    <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-person me-2"></i>Leader Profile</h1>
</div>

<?php if ($user): ?>
    <div class="row g-4">
        <!-- Profile Summary -->
        <div class="col-lg-4">
            <?php
            // Role specific settings for Leader
            $themeColor = 'info';
            $roleLabel = 'Leader';
            $icon = 'bi-briefcase-fill';
            ?>

            <div class="card glass-card border-0 animate-fade-in h-100">
                <!-- Header with Gradient -->
                <div class="card-header border-0 bg-transparent text-center py-5 position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100 h-100" 
                         style="background: linear-gradient(135deg, rgba(56, 189, 248, 0.15) 0%, rgba(14, 165, 233, 0.15) 100%); z-index: 0;"></div>
                    
                    <div class="position-relative z-1">
                        <div class="mb-4">
                            <div class="user-avatar mx-auto shadow-lg d-flex align-items-center justify-content-center gradient-text bg-white"
                                style="width: 110px; height: 110px; font-size: 3.5rem; border-radius: 50%; border: 4px solid rgba(255,255,255,0.8);">
                                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                            </div>
                        </div>
                        <h4 class="mb-1 fw-bold"><?= htmlspecialchars($user['full_name']) ?></h4>
                        <p class="mb-3 text-muted small"><?= htmlspecialchars($user['email']) ?></p>
                        <span class="badge bg-info-soft text-info rounded-pill px-4 py-2 border border-info-subtle">
                            <i class="bi <?= $icon ?> me-1"></i> <?= $roleLabel ?>
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-white bg-opacity-50 border border-light">
                            <span class="text-muted small text-uppercase fw-bold"><i class="bi bi-building me-2 text-info"></i>Company</span>
                            <span class="fw-semibold text-end text-dark"><?= htmlspecialchars($user['company_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-white bg-opacity-50 border border-light">
                            <span class="text-muted small text-uppercase fw-bold"><i class="bi bi-person-workspace me-2 text-info"></i>Type</span>
                            <span class="badge bg-secondary-soft text-secondary border"><?= getEmploymentTypeName($user['employment_type']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-white bg-opacity-50 border border-light">
                            <span class="text-muted small text-uppercase fw-bold"><i class="bi bi-cash me-2 text-info"></i>Basic Salary</span>
                            <span class="fw-bold text-success font-monospace fs-5"><?= formatMoney($user['basic_salary']) ?></span>
                        </div>

                        <?php if (!empty($user['ic_number'])): ?>
                            <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-white bg-opacity-50 border border-light">
                                <span class="text-muted small text-uppercase fw-bold"><i class="bi bi-card-heading me-2 text-info"></i>IC No.</span>
                                <span class="font-monospace"><?= htmlspecialchars($user['ic_number']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="col-lg-8">
            <div class="card glass-card border-0 animate-fade-in" style="animation-delay: 0.1s;">
                <div class="card-header bg-transparent border-bottom border-light py-4 ps-4">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-pencil-square me-2 text-info"></i>Update Information</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="update_profile">
                        <!-- Personal Info -->
                        <h6 class="text-info text-uppercase small fw-bold mb-4 ps-1 border-start border-4 border-info ps-2">Personal Information</h6>
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control form-control-lg fs-6"
                                    value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">IC No.</label>
                                <input type="text" name="ic_number" class="form-control form-control-lg fs-6"
                                    value="<?= htmlspecialchars($user['ic_number'] ?? '') ?>" placeholder="000000-00-0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Email</label>
                                <input type="email" class="form-control form-control-lg fs-6 bg-light text-muted" value="<?= htmlspecialchars($user['email']) ?>"
                                    disabled style="cursor: not-allowed;">
                                <div class="form-text"><i class="bi bi-lock-fill me-1"></i>Email cannot be changed contact HR for updates.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Phone No.</label>
                                <input type="text" name="phone" class="form-control form-control-lg fs-6"
                                    value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="012-3456789">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted small fw-bold">Address</label>
                                <textarea name="address" class="form-control" rows="3"
                                    placeholder="Enter your full residential address..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Number of Dependents</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-people"></i></span>
                                    <input type="number" name="dependents" class="form-control form-control-lg fs-6 border-start-0"
                                        value="<?= intval($user['dependents'] ?? 0) ?>" min="0" max="10" placeholder="0">
                                </div>
                                <div class="form-text text-info"><i class="bi bi-info-circle me-1"></i>Used for PCB Tax Calculation (Max: 10)</div>
                            </div>
                        </div>

                        <!-- Bank Info -->
                        <h6 class="text-info text-uppercase small fw-bold mb-4 ps-1 border-start border-4 border-info ps-2">Bank Information</h6>
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Bank Name</label>
                                <select name="bank_name" class="form-select form-select-lg fs-6">
                                    <option value="">-- Select Bank --</option>
                                    <?php
                                    $banks = [
                                        'Maybank', 'CIMB Bank', 'Public Bank', 'RHB Bank', 'Hong Leong Bank',
                                        'AmBank', 'Bank Islam', 'Bank Rakyat', 'BSN', 'Affin Bank', 'Alliance Bank'
                                    ];
                                    foreach ($banks as $bank):
                                        ?>
                                        <option value="<?= $bank ?>" <?= ($user['bank_name'] ?? '') === $bank ? 'selected' : '' ?>>
                                            <?= $bank ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">Bank Account No.</label>
                                <input type="text" name="bank_account" class="form-control form-control-lg fs-6"
                                    value="<?= htmlspecialchars($user['bank_account'] ?? '') ?>" placeholder="Enter account number">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end pt-3 border-top border-light">
                            <button type="submit" class="btn btn-premium px-5 py-2">
                                <i class="bi bi-check-lg me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>

                    <!-- Password Change Form (Separate) -->
                    <div class="mt-5 pt-4 border-top border-light">
                        <form method="POST" onsubmit="return confirmPasswordChange()">
                            <input type="hidden" name="action" value="change_password">
                            <h6 class="text-danger text-uppercase small fw-bold mb-4 ps-1 border-start border-4 border-danger ps-2">Security Settings</h6>
                            
                            <div class="row g-4 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required placeholder="********">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold">New Password</label>
                                    <input type="password" name="new_password" id="new_password" class="form-control"
                                        minlength="6" required placeholder="min. 6 chars">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold">Confirm Password</label>
                                    <div class="input-group">
                                        <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
                                        <button type="submit" class="btn btn-warning text-white">
                                            Change
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <script>
                        function confirmPasswordChange() {
                            return confirm('Are you sure you want to change your password? You may need to login again.');
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-danger">Error loading profile data.</div>
<?php endif; ?>