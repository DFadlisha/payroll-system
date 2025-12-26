<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-receipt me-2"></i><?= __('nav.payslips') ?></h1>
    </div>
    <?php if ($viewId): ?>
        <a href="payslips.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i><?= __('back') ?>
        </a>
    <?php endif; ?>
</div>

<!-- Leader Copy of Permanent View for now -->
<?php include 'c:/Users/User/Documents/SEM 7/INDUSTRIAL THINGS/NES SOLUTION AND NETWORK SDN BHD/payroll system/staff/views/permanent/payslips.php'; ?>