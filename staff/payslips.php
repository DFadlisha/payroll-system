<?php
/**
 * ============================================
 * STAFF PAYSLIPS PAGE
 * ============================================
 * Halaman untuk lihat slip gaji.
 * ============================================
 */

$pageTitle = 'Slip Gaji - MI-NES Payroll';
require_once '../includes/header.php';
requireLogin();

if (isHR()) {
    redirect('../hr/dashboard.php');
}

$userId = $_SESSION['user_id'];
$viewId = $_GET['id'] ?? null;

try {
    $conn = getConnection();
    
    // Get payslip history (Supabase schema - status: draft, finalized, paid)
    $stmt = $conn->prepare("
        SELECT * FROM payroll 
        WHERE user_id = ? AND status IN ('finalized', 'paid')
        ORDER BY year DESC, month DESC
    ");
    $stmt->execute([$userId]);
    $payslips = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If viewing specific payslip
    $currentPayslip = null;
    if ($viewId) {
        $stmt = $conn->prepare("
            SELECT p.*, pr.full_name, pr.epf_number, pr.socso_number, pr.employment_type, pr.basic_salary as profile_salary
            FROM payroll p
            JOIN profiles pr ON p.user_id = pr.id
            WHERE p.id = ? AND p.user_id = ?
        ");
        $stmt->execute([$viewId, $userId]);
        $currentPayslip = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    error_log("Payslip fetch error: " . $e->getMessage());
    $payslips = [];
    $currentPayslip = null;
}
?>

<?php include '../includes/staff_sidebar.php'; ?>
        <li>
            <a href="profile.php">
                <i class="bi bi-person"></i> Profil
            </a>
        </li>
        <li class="mt-auto" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 20px;">
            <a href="../auth/logout.php">
                <i class="bi bi-box-arrow-left"></i> Log Keluar
            </a>
        </li>
    </ul>
</nav>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div>
            <button class="mobile-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <span class="fw-bold">Slip Gaji</span>
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
    
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-receipt me-2"></i>Slip Gaji</h1>
        </div>
        <?php if ($viewId): ?>
            <a href="payslips.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Kembali
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
                    <p class="text-muted mb-0">Slip Gaji / Payslip</p>
                </div>
                
                <!-- Employee Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Nama:</td>
                                <td><strong><?= htmlspecialchars($currentPayslip['full_name']) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">No. IC:</td>
                                <td><?= htmlspecialchars($currentPayslip['ic_number'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Jenis Pekerja:</td>
                                <td><?= getEmploymentTypeName($currentPayslip['employment_type']) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="text-muted" width="40%">Bulan/Tahun:</td>
                                <td><strong><?= getMonthName($currentPayslip['month']) ?> <?= $currentPayslip['year'] ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Bank:</td>
                                <td><?= htmlspecialchars($currentPayslip['bank_name'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">No. Akaun:</td>
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
                                <i class="bi bi-plus-circle me-2"></i>Pendapatan (Earnings)
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td>Gaji Pokok</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['basic_salary']) ?></td>
                                    </tr>
                                    <?php if ($currentPayslip['overtime_pay'] > 0): ?>
                                    <tr>
                                        <td>Overtime (<?= $currentPayslip['overtime_hours'] ?> jam)</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['overtime_pay']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($currentPayslip['allowances'] > 0): ?>
                                    <tr>
                                        <td>Elaun</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['allowances']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($currentPayslip['bonus'] > 0): ?>
                                    <tr>
                                        <td>Bonus</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['bonus']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="border-top">
                                        <td><strong>Jumlah Pendapatan</strong></td>
                                        <td class="text-end"><strong><?= formatMoney($currentPayslip['gross_salary']) ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Deductions -->
                    <div class="col-md-6">
                        <div class="card bg-light mb-3">
                            <div class="card-header bg-danger text-white">
                                <i class="bi bi-dash-circle me-2"></i>Potongan (Deductions)
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td>KWSP (EPF)</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['epf_employee']) ?></td>
                                    </tr>
                                    <tr>
                                        <td>PERKESO (SOCSO)</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['socso_employee']) ?></td>
                                    </tr>
                                    <tr>
                                        <td>EIS</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['eis_employee']) ?></td>
                                    </tr>
                                    <?php if ($currentPayslip['pcb_tax'] > 0): ?>
                                    <tr>
                                        <td>PCB (Cukai)</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['pcb_tax']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($currentPayslip['other_deductions'] > 0): ?>
                                    <tr>
                                        <td>Potongan Lain</td>
                                        <td class="text-end"><?= formatMoney($currentPayslip['other_deductions']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="border-top">
                                        <td><strong>Jumlah Potongan</strong></td>
                                        <td class="text-end"><strong><?= formatMoney($currentPayslip['total_deductions']) ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Net Salary -->
                <div class="card bg-primary text-white">
                    <div class="card-body text-center py-4">
                        <h5 class="mb-2">Gaji Bersih (Net Salary)</h5>
                        <h2 class="mb-0"><?= formatMoney($currentPayslip['net_salary']) ?></h2>
                    </div>
                </div>
                
                <!-- Employer Contributions -->
                <div class="mt-4">
                    <h6 class="text-muted">Caruman Majikan (Employer Contributions)</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">KWSP Majikan:</small>
                            <strong><?= formatMoney($currentPayslip['epf_employer']) ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">PERKESO Majikan:</small>
                            <strong><?= formatMoney($currentPayslip['socso_employer']) ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">EIS Majikan:</small>
                            <strong><?= formatMoney($currentPayslip['eis_employer']) ?></strong>
                        </div>
                    </div>
                </div>
                
                <!-- Print Button -->
                <div class="mt-4 text-center d-print-none">
                    <button onclick="window.print()" class="btn btn-outline-primary">
                        <i class="bi bi-printer me-2"></i>Cetak Slip
                    </button>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Payslip List -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clock-history me-2"></i>Sejarah Slip Gaji
            </div>
            <div class="card-body">
                <?php if (empty($payslips)): ?>
                    <p class="text-muted text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                        Tiada slip gaji.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Bulan/Tahun</th>
                                    <th>Gaji Pokok</th>
                                    <th>Pendapatan</th>
                                    <th>Potongan</th>
                                    <th>Gaji Bersih</th>
                                    <th>Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payslips as $payslip): ?>
                                    <tr>
                                        <td>
                                            <strong><?= getMonthName($payslip['month']) ?> <?= $payslip['year'] ?></strong>
                                        </td>
                                        <td><?= formatMoney($payslip['basic_salary']) ?></td>
                                        <td class="text-success"><?= formatMoney($payslip['gross_salary']) ?></td>
                                        <td class="text-danger"><?= formatMoney($payslip['total_deductions']) ?></td>
                                        <td><strong><?= formatMoney($payslip['net_salary']) ?></strong></td>
                                        <td>
                                            <a href="?id=<?= $payslip['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Lihat
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
</div>

<style>
@media print {
    .sidebar, .top-navbar, .page-header, .d-print-none {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
