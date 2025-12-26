<!-- Flash Messages -->
<?php if ($message): ?>
    <div class="alert alert-<?= $messageType === 'error' ? 'danger' : $messageType ?> alert-dismissible fade show">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-calendar-x me-2"></i><?= __('leaves.title') ?></h1>
    </div>
    <?php if ($action !== 'new'): ?>
        <a href="?action=new" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i><?= __('leaves.apply') ?>
        </a>
    <?php else: ?>
        <a href="leaves.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i><?= __('back') ?>
        </a>
    <?php endif; ?>
</div>

<!-- Leave Balance Cards (Staff Standard) -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Annual Leave</h6>
                        <h3 class="mb-0">
                            <?= $leaveBalance['annual']['total'] - $leaveBalance['annual']['used'] ?>
                            <small class="text-muted fs-6">/ <?= $leaveBalance['annual']['total'] ?> days</small>
                        </h3>
                    </div>
                    <div class="text-primary">
                        <i class="bi bi-calendar-check" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 8px;">
                    <?php $annualPercent = ($leaveBalance['annual']['used'] / max($leaveBalance['annual']['total'], 1)) * 100; ?>
                    <div class="progress-bar bg-primary" style="width: <?= $annualPercent ?>%"></div>
                </div>
                <small class="text-muted">Used: <?= $leaveBalance['annual']['used'] ?> days</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Medical Leave</h6>
                        <h3 class="mb-0">
                            <?= $leaveBalance['medical']['total'] - $leaveBalance['medical']['used'] ?>
                            <small class="text-muted fs-6">/ <?= $leaveBalance['medical']['total'] ?> days</small>
                        </h3>
                    </div>
                    <div class="text-danger">
                        <i class="bi bi-heart-pulse" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 8px;">
                    <?php $medicalPercent = ($leaveBalance['medical']['used'] / max($leaveBalance['medical']['total'], 1)) * 100; ?>
                    <div class="progress-bar bg-danger" style="width: <?= $medicalPercent ?>%"></div>
                </div>
                <small class="text-muted">Used: <?= $leaveBalance['medical']['used'] ?> days</small>
            </div>
        </div>
    </div>
</div>

<?php if ($action === 'new'): ?>
    <!-- Leave Application Form -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-plus-circle me-2"></i>Leave Application Form
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type" class="form-select" required>
                            <option value="">-- Select Leave Type --</option>
                            <option value="annual">Annual Leave</option>
                            <option value="medical">Medical Leave</option>
                            <option value="emergency">Emergency Leave</option>
                            <option value="unpaid">Unpaid Leave</option>
                            <option value="other">Other</option>
                        </select>

                        <div class="invalid-feedback">Please select leave type.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        <div class="invalid-feedback">Please select start date.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                        <div class="invalid-feedback">Please select end date.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3"
                            placeholder="State reason for leave application..."></textarea>
                    </div>

                    <div class="col-12">
                        <hr>
                        <button type="submit" name="submit_leave" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Submit Application
                        </button>
                        <a href="leaves.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Leave History -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i><?= __('leaves.my_leaves') ?>
        </div>
        <div class="card-body">
            <?php if (empty($leaves)): ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                    <?= __('no_data') ?>
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= __('date') ?></th>
                                <th><?= __('leaves.leave_type') ?></th>
                                <th><?= __('leaves.start_date') ?>/<?= __('leaves.end_date') ?></th>
                                <th><?= __('leaves.days') ?></th>
                                <th><?= __('leaves.reason') ?></th>
                                <th><?= __('status') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($leaves as $leave):
                                $badge = getLeaveStatusBadge($leave['status']);
                                ?>
                                <tr>
                                    <td><?= formatDate($leave['created_at']) ?></td>
                                    <td><?= getLeaveTypeName($leave['leave_type']) ?></td>
                                    <td>
                                        <?= formatDate($leave['start_date']) ?> -
                                        <?= formatDate($leave['end_date']) ?>
                                    </td>
                                    <td><?= $leave['total_days'] ?></td>
                                    <td><?= htmlspecialchars($leave['reason'] ?: '-') ?></td>
                                    <td>
                                        <span class="badge <?= $badge['class'] ?>"><?= $badge['name'] ?></span>
                                        <?php if ($leave['status'] === 'rejected' && $leave['rejection_reason']): ?>
                                            <br><small class="text-danger"><?= htmlspecialchars($leave['rejection_reason']) ?></small>
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