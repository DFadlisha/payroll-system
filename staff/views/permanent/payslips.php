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

<?php if ($currentPayslip): ?>
    <!-- View Single Payslip -->
    <div class="card" id="payslip">
        <div class="card-body">
            <!-- Header -->
            <div class="text-center border-bottom pb-3 mb-3">
                <h4 class="mb-1">NES SOLUTION & NETWORK SDN BHD</h4>
                <p class="text-muted mb-0">Payslip</p>
            </div>

            <!-- Employee Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted" width="40%">Name:</td>
                            <td><strong><?= htmlspecialchars($currentPayslip['full_name']) ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">IC No:</td>
                            <td><?= htmlspecialchars($currentPayslip['ic_number'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Employment Type:</td>
                            <td><?= getEmploymentTypeName($currentPayslip['employment_type']) ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="text-muted" width="40%">Month/Year:</td>
                            <td><strong><?= getMonthName($currentPayslip['month']) ?>
                                    <?= $currentPayslip['year'] ?></strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Bank:</td>
                            <td><?= htmlspecialchars($currentPayslip['bank_name'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Account No:</td>
                            <td><?= htmlspecialchars($currentPayslip['bank_account'] ?? '-') ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Earnings & Deductions -->
            <div class="row">
                <!-- Earnings -->
                <div class="col-md-6">
                    <div class="card bg-light mb-3">
                        <div class="card-header bg-success text-white">
                            <i class="bi bi-plus-circle me-2"></i>Earnings
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td><?= __('payroll.basic_salary') ?></td>
                                    <td class="text-end"><?= formatMoney($currentPayslip['basic_salary']) ?></td>
                                </tr>
                                <?php if ($currentPayslip['overtime_pay'] > 0): ?>
                                    <tr>
                                        <td>Overtime (<?= $currentPayslip['overtime_hours'] ?>
                                            <?= __('hours') ?? 'hours' ?>)
                                        </td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['overtime_pay']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($currentPayslip['allowances'] > 0): ?>
                                    <tr>
                                        <td><?= __('payroll.allowances') ?></td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['allowances']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($currentPayslip['bonus'] > 0): ?>
                                    <tr>
                                        <td><?= __('payroll.attendance_bonus') ?></td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['bonus']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="border-top">
                                    <td><strong>Total Earnings</strong></td>
                                    <td class="text-end">
                                        <strong><?= formatMoney($currentPayslip['gross_salary']) ?></strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Deductions -->
                <div class="col-md-6">
                    <div class="card bg-light mb-3">
                        <div class="card-header bg-danger text-white">
                            <i class="bi bi-dash-circle me-2"></i>Deductions
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td>EPF</td>
                                    <td class="text-end"><?= formatMoney($currentPayslip['epf_employee']) ?></td>
                                </tr>
                                <tr>
                                    <td>SOCSO</td>
                                    <td class="text-end"><?= formatMoney($currentPayslip['socso_employee']) ?></td>
                                </tr>
                                <tr>
                                    <td>EIS</td>
                                    <td class="text-end"><?= formatMoney($currentPayslip['eis_employee']) ?></td>
                                </tr>
                                <?php if ($currentPayslip['pcb_tax'] > 0): ?>
                                    <tr>
                                        <td>PCB (Tax)</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['pcb_tax']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <?php if ($currentPayslip['other_deductions'] > 0): ?>
                                    <tr>
                                        <td>Other Deductions</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['other_deductions']) ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr class="border-top">
                                    <td><strong>Total Deductions</strong></td>
                                    <td class="text-end">
                                        <strong><?= formatMoney($currentPayslip['total_deductions']) ?></strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Net Salary -->
            <div class="card bg-primary text-white">
                <div class="card-body text-center py-4">
                    <h5 class="mb-2">Net Salary</h5>
                    <h2 class="mb-0"><?= formatMoney($currentPayslip['net_salary']) ?></h2>
                </div>
            </div>

            <!-- Employer Contributions -->
            <div class="mt-4">
                <h6 class="text-muted">Employer Contributions</h6>
                <div class="row">
                    <div class="col-md-4">
                        <small class="text-muted">Employer EPF:</small>
                        <strong><?= formatMoney($currentPayslip['epf_employer']) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Employer SOCSO:</small>
                        <strong><?= formatMoney($currentPayslip['socso_employer']) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Employer EIS:</small>
                        <strong><?= formatMoney($currentPayslip['eis_employer']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Print Button -->
            <div class="mt-4 text-center d-print-none">
                <button onclick="window.print()" class="btn btn-outline-primary me-2">
                    <i class="bi bi-printer me-2"></i>Print Payslip
                </button>
                <a href="../includes/generate_payslip_pdf.php?id=<?= $currentPayslip['id'] ?>" class="btn btn-danger"
                    target="_blank">
                    <i class="bi bi-file-pdf me-2"></i>Download PDF
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Payslip List -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Payslip History
        </div>
        <div class="card-body">
            <?php if (empty($payslips)): ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                    No payslips available.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Month/Year</th>
                                <th>Basic Salary</th>
                                <th>Earnings</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payslips as $payslip):
                                // Normalize for list view
                                $grossInfo = $payslip['gross_pay'] ?? $payslip['gross_salary'] ?? 0;
                                $netInfo = $payslip['net_pay'] ?? $payslip['net_salary'] ?? 0;
                                $deductionInfo = ($payslip['epf_employee'] ?? 0) + ($payslip['socso_employee'] ?? 0) + ($payslip['eis_employee'] ?? 0) + ($payslip['pcb_tax'] ?? 0);
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= getMonthName($payslip['month']) ?>             <?= $payslip['year'] ?></strong>
                                    </td>
                                    <td><?= formatMoney($payslip['basic_salary']) ?></td>
                                    <td class="text-success"><?= formatMoney($grossInfo) ?></td>
                                    <td class="text-danger"><?= formatMoney($deductionInfo) ?></td>
                                    <td><strong><?= formatMoney($netInfo) ?></strong></td>
                                    <td>
                                        <a href="?id=<?= $payslip['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
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