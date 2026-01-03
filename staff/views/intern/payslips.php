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

<!-- Intern Specific Welcome/Info -->
<?php if (!$viewId): ?>
    <div class="alert alert-warning mb-4">
        <i class="bi bi-info-circle me-2"></i> Monthly internship allowances are posted here.
    </div>
<?php endif; ?>

<?php if ($currentPayslip): ?>
    <!-- View Single Payslip (Intern Simplified) -->
    <div class="card" id="payslip">
        <div class="card-body">
            <!-- Header -->
            <div class="text-center border-bottom pb-3 mb-3">
                <h4 class="mb-1">NES SOLUTION & NETWORK SDN BHD</h4>
                <p class="text-muted mb-0">Internship Allowance Slip</p>
            </div>

            <!-- Employee Info -->
            <div class="row mb-4">
                <div class="col-12">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted" width="20%">Name:</td>
                            <td><strong><?= htmlspecialchars($currentPayslip['full_name']) ?></strong></td>
                            <td class="text-muted" width="20%">Month/Year:</td>
                            <td><strong><?= getMonthName($currentPayslip['month']) ?>
                                    <?= $currentPayslip['year'] ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">IC No:</td>
                            <td><?= htmlspecialchars($currentPayslip['ic_number'] ?? '-') ?></td>
                            <td class="text-muted">Bank:</td>
                            <td><?= htmlspecialchars($currentPayslip['bank_name'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Role:</td>
                            <td>Intern</td>
                            <td class="text-muted">Account No:</td>
                            <td><?= htmlspecialchars($currentPayslip['bank_account'] ?? '-') ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Earnings Only (Interns mostly just allowance) -->
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="card bg-light mb-3">
                        <div class="card-header bg-warning text-dark">
                            <i class="bi bi-cash me-2"></i>Allowance Details
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td>Monthly Allowance</td>
                                    <td class="text-end"><?= formatMoney($currentPayslip['basic_salary']) ?></td>
                                </tr>
                                <?php if ($currentPayslip['overtime_pay'] > 0): ?>
                                    <tr>
                                        <td>Overtime Claims</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['overtime_pay']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($currentPayslip['allowances'] > 0): ?>
                                    <tr>
                                        <td>Other Allowances</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['allowances']) ?></td>
                                    </tr>
                                <?php endif; ?>

                                <!-- Deductions for interns are rare, but if any (e.g. damaged equipment) -->
                                <?php if ($currentPayslip['total_deductions'] > 0): ?>
                                    <tr class="text-danger border-top">
                                        <td>Deductions</td>
                                        <td class="text-end">- <?= formatMoney($currentPayslip['total_deductions']) ?></td>
                                    </tr>
                                <?php endif; ?>

                                <tr class="border-top fa-2x">
                                    <td><strong>NET ALLOWANCE</strong></td>
                                    <td class="text-end">
                                        <strong><?= formatMoney($currentPayslip['net_salary']) ?></strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interns usually don't have Employer Contributions section -->

            <!-- Print Button -->
            <div class="mt-4 text-center d-print-none">
                <button onclick="window.print()" class="btn btn-outline-dark me-2">
                    <i class="bi bi-printer me-2"></i>Print Slip
                </button>
                <a href="../includes/generate_payslip_pdf.php?id=<?= $currentPayslip['id'] ?>" class="btn btn-warning"
                    target="_blank">
                    <i class="bi bi-file-pdf me-2"></i>Download PDF
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Intern Payslip List -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Allowance History
        </div>
        <div class="card-body">
            <?php if (empty($payslips)): ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                    No records available.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Month/Year</th>
                                <th>Allowance</th>
                                <th>Adjustments</th>
                                <th>Net Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payslips as $payslip):
                                $netInfo = $payslip['net_pay'] ?? $payslip['net_salary'] ?? 0;
                                $adjustments = ($payslip['gross_pay'] ?? 0) - ($payslip['basic_salary'] ?? 0) - ($payslip['total_deductions'] ?? 0);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= getMonthName($payslip['month']) ?>             <?= $payslip['year'] ?></strong>
                                    </td>
                                    <td><?= formatMoney($payslip['basic_salary']) ?></td>
                                    <td><?= formatMoney($adjustments) ?></td>
                                    <td><strong><?= formatMoney($netInfo) ?></strong></td>
                                    <td>
                                        <a href="?id=<?= $payslip['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <!-- PDF might still use generic generator, which is fine -->
                                        <a href="../includes/generate_payslip_pdf.php?id=<?= $payslip['id'] ?>"
                                            class="btn btn-sm btn-outline-danger" target="_blank" title="Download PDF">
                                            <i class="bi bi-file-pdf"></i>
                                        </a>
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