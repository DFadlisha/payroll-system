<!-- Flash Messages -->
<?php if ($message): ?>
    <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="bi bi-person me-2"></i>My Profile</h1>
</div>

<?php if ($user): ?>
    <div class="row g-4">
        <!-- Profile Summary -->
        <div class="col-lg-4">
            <?php
            // Role specific settings for Part-Time
            $themeColor = 'primary';
            $roleLabel = 'Part-Time Staff';
            $icon = 'bi-clock-history';

            $textClass = 'text-white';
            $bgClass = 'bg-' . $themeColor;
            ?>

            <div class="card border-<?= $themeColor ?> shadow-sm">
                <!-- Colored Header for Visual Distinction -->
                <div class="card-header <?= $bgClass ?> <?= $textClass ?> text-center py-4">
                    <div class="mb-3">
                        <div class="user-avatar mx-auto border border-4 border-white"
                            style="width: 100px; height: 100px; font-size: 3rem; background-color: rgba(255,255,255,0.2); color: inherit;">
                            <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                        </div>
                    </div>
                    <h4 class="mb-1 fw-bold"><?= htmlspecialchars($user['full_name']) ?></h4>
                    <p class="mb-2 opacity-75"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="badge bg-white text-primary rounded-pill px-3 py-2">
                        <i class="bi <?= $icon ?> me-1"></i> <?= $roleLabel ?>
                    </span>
                </div>

                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="bi bi-building me-2"></i>Company</span>
                            <span class="fw-bold text-end"><?= htmlspecialchars($user['company_name'] ?? 'N/A') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="bi bi-person-workspace me-2"></i>Employment Type</span>
                            <span class="badge bg-secondary"><?= getEmploymentTypeName($user['employment_type']) ?></span>
                        </li>

                        <!-- Part-Time Specific: Hourly Rate instead of Basic Salary maybe? Or both if applicable -->
                        <?php if ($user['hourly_rate'] > 0): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="bi bi-coin me-2"></i>Hourly Rate</span>
                                <span class="fw-bold"><?= formatMoney($user['hourly_rate']) ?>/hr</span>
                            </li>
                        <?php endif; ?>

                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="bi bi-cash me-2"></i>Basic Salary (if any)</span>
                            <span class="fw-bold"><?= formatMoney($user['basic_salary']) ?></span>
                        </li>

                        <?php if (!empty($user['ic_number'])): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted"><i class="bi bi-card-heading me-2"></i>IC No.</span>
                                <span><?= htmlspecialchars($user['ic_number']) ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-pencil me-2"></i>Update Information
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="update_profile">
                        <!-- Personal Info -->
                        <h6 class="text-muted mb-3">Personal Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control"
                                    value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IC No.</label>
                                <input type="text" name="ic_number" class="form-control"
                                    value="<?= htmlspecialchars($user['ic_number'] ?? '') ?>" placeholder="000000-00-0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>"
                                    disabled>
                                <small class="text-muted">Email cannot be changed.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone No.</label>
                                <input type="text" name="phone" class="form-control"
                                    value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="012-3456789">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"
                                    placeholder="Full address..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Number of Dependents (For PCB Tax Calculation)</label>
                                <input type="number" name="dependents" class="form-control"
                                    value="<?= intval($user['dependents'] ?? 0) ?>" min="0" max="10" placeholder="0">
                                <small class="text-muted">Total dependents for LHDN tax relief (Max: 6)</small>
                            </div>
                        </div>

                        <!-- Bank Info -->
                        <h6 class="text-muted mb-3">Bank Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Bank Name</label>
                                <select name="bank_name" class="form-select">
                                    <option value="">-- Select Bank --</option>
                                    <?php
                                    $banks = [
                                        'Maybank',
                                        'CIMB Bank',
                                        'Public Bank',
                                        'RHB Bank',
                                        'Hong Leong Bank',
                                        'AmBank',
                                        'Bank Islam',
                                        'Bank Rakyat',
                                        'BSN',
                                        'Affin Bank',
                                        'Alliance Bank'
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
                                <label class="form-label">Bank Account No.</label>
                                <input type="text" name="bank_account" class="form-control"
                                    value="<?= htmlspecialchars($user['bank_account'] ?? '') ?>" placeholder="1234567890">
                            </div>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Save Changes
                        </button>
                    </form>

                    <!-- Password Change Form (Separate) -->
                    <form method="POST" class="mt-4" onsubmit="return confirmPasswordChange()">
                        <input type="hidden" name="action" value="change_password">
                        <h6 class="text-muted mb-3">Change Password</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password <span class="text-danger">*</span></label>
                                <input type="password" name="new_password" id="new_password" class="form-control"
                                    minlength="6" required>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key me-2"></i>Change Password
                        </button>
                    </form>

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