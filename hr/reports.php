<?php
/**
 * ============================================
 * HR REPORTS PAGE
 * ============================================
 * Halaman untuk jana laporan.
 * ============================================
 */

$pageTitle = 'Laporan - MI-NES Payroll';
require_once '../includes/header.php';
requireHR();

$companyId = $_SESSION['company_id'];
$reportType = $_GET['report'] ?? '';
$month = intval($_GET['month'] ?? date('n'));
$year = intval($_GET['year'] ?? date('Y'));

$reportData = [];

if ($reportType) {
    try {
        $conn = getConnection();
        
        switch ($reportType) {
            case 'attendance':
                $stmt = $conn->prepare("
                    SELECT u.full_name, u.employment_type,
                           COUNT(CASE WHEN a.status = 'present' THEN 1 END) as present,
                           COUNT(CASE WHEN a.status = 'late' THEN 1 END) as late,
                           COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as absent
                    FROM users u
                    LEFT JOIN attendance a ON u.id = a.user_id 
                         AND MONTH(a.date) = ? AND YEAR(a.date) = ?
                    WHERE u.company_id = ? AND u.is_active = 1
                    GROUP BY u.id
                    ORDER BY u.full_name
                ");
                $stmt->execute([$month, $year, $companyId]);
                $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
                
            case 'leaves':
                $stmt = $conn->prepare("
                    SELECT u.full_name,
                           SUM(CASE WHEN l.leave_type = 'annual' AND l.status = 'approved' THEN l.total_days ELSE 0 END) as annual,
                           SUM(CASE WHEN l.leave_type = 'medical' AND l.status = 'approved' THEN l.total_days ELSE 0 END) as medical,
                           SUM(CASE WHEN l.leave_type = 'emergency' AND l.status = 'approved' THEN l.total_days ELSE 0 END) as emergency,
                           SUM(CASE WHEN l.status = 'approved' THEN l.total_days ELSE 0 END) as total
                    FROM users u
                    LEFT JOIN leaves l ON u.id = l.user_id AND YEAR(l.start_date) = ?
                    WHERE u.company_id = ? AND u.is_active = 1
                    GROUP BY u.id
                    ORDER BY u.full_name
                ");
                $stmt->execute([$year, $companyId]);
                $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
                
            case 'payroll':
                $stmt = $conn->prepare("
                    SELECT u.full_name, u.employment_type, p.*
                    FROM payroll p
                    JOIN users u ON p.user_id = u.id
                    WHERE u.company_id = ? AND p.month = ? AND p.year = ?
                    ORDER BY u.full_name
                ");
                $stmt->execute([$companyId, $month, $year]);
                $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                break;
        }
        
    } catch (PDOException $e) {
        error_log("Report error: " . $e->getMessage());
    }
}
?>

<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-header">
        <h3><i class="bi bi-building me-2"></i>MI-NES</h3>
        <small>Payroll System</small>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li><a href="employees.php"><i class="bi bi-people"></i> Pekerja</a></li>
        <li><a href="attendance.php"><i class="bi bi-calendar-check"></i> Kehadiran</a></li>
        <li><a href="leaves.php"><i class="bi bi-calendar-x"></i> Cuti</a></li>
        <li><a href="payroll.php"><i class="bi bi-cash-stack"></i> Gaji</a></li>
        <li><a href="reports.php" class="active"><i class="bi bi-file-earmark-bar-graph"></i> Laporan</a></li>
        <li class="mt-auto" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px; margin-top: 20px;">
            <a href="../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Log Keluar</a>
        </li>
    </ul>
</nav>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div>
            <button class="mobile-toggle" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <span class="fw-bold">Laporan</span>
        </div>
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div>
                <div class="fw-bold"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <small class="text-muted">HR Admin</small>
            </div>
        </div>
    </div>
    
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan</h1>
    </div>
    
    <?php if (!$reportType): ?>
        <!-- Report Selection -->
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar-check text-primary" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Laporan Kehadiran</h5>
                        <p class="text-muted">Ringkasan kehadiran pekerja mengikut bulan.</p>
                        <a href="?report=attendance" class="btn btn-primary">Jana Laporan</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calendar-x text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Laporan Cuti</h5>
                        <p class="text-muted">Ringkasan cuti pekerja mengikut tahun.</p>
                        <a href="?report=leaves" class="btn btn-warning">Jana Laporan</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-cash-stack text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Laporan Gaji</h5>
                        <p class="text-muted">Ringkasan gaji bulanan semua pekerja.</p>
                        <a href="?report=payroll" class="btn btn-success">Jana Laporan</a>
                    </div>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row align-items-center g-3">
                    <input type="hidden" name="report" value="<?= $reportType ?>">
                    
                    <?php if ($reportType !== 'leaves'): ?>
                        <div class="col-auto">
                            <label class="form-label mb-0">Bulan:</label>
                        </div>
                        <div class="col-auto">
                            <select name="month" class="form-select">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                                        <?= getMonthName($m) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="col-auto">
                        <label class="form-label mb-0">Tahun:</label>
                    </div>
                    <div class="col-auto">
                        <select name="year" class="form-select">
                            <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Tapis
                        </button>
                    </div>
                    
                    <div class="col-auto">
                        <a href="reports.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                    
                    <div class="col-auto ms-auto">
                        <button type="button" onclick="window.print()" class="btn btn-outline-primary">
                            <i class="bi bi-printer me-1"></i> Cetak
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Report Content -->
        <div class="card" id="printArea">
            <div class="card-header d-flex justify-content-between">
                <span>
                    <?php
                    $titles = [
                        'attendance' => 'Laporan Kehadiran',
                        'leaves' => 'Laporan Cuti',
                        'payroll' => 'Laporan Gaji'
                    ];
                    echo '<i class="bi bi-file-text me-2"></i>' . ($titles[$reportType] ?? 'Laporan');
                    ?>
                </span>
                <span>
                    <?php if ($reportType !== 'leaves'): ?>
                        <?= getMonthName($month) ?> <?= $year ?>
                    <?php else: ?>
                        Tahun <?= $year ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="card-body">
                <?php if (empty($reportData)): ?>
                    <p class="text-muted text-center py-4">Tiada data untuk dipaparkan.</p>
                <?php elseif ($reportType === 'attendance'): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Jenis</th>
                                    <th class="text-center">Hadir</th>
                                    <th class="text-center">Lewat</th>
                                    <th class="text-center">Tidak Hadir</th>
                                    <th class="text-center">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                                        <td><?= getEmploymentTypeName($row['employment_type']) ?></td>
                                        <td class="text-center text-success"><?= $row['present'] ?></td>
                                        <td class="text-center text-warning"><?= $row['late'] ?></td>
                                        <td class="text-center text-danger"><?= $row['absent'] ?></td>
                                        <td class="text-center"><strong><?= $row['present'] + $row['late'] ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                <?php elseif ($reportType === 'leaves'): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th class="text-center">Tahunan</th>
                                    <th class="text-center">Sakit</th>
                                    <th class="text-center">Kecemasan</th>
                                    <th class="text-center">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                                        <td class="text-center"><?= $row['annual'] ?></td>
                                        <td class="text-center"><?= $row['medical'] ?></td>
                                        <td class="text-center"><?= $row['emergency'] ?></td>
                                        <td class="text-center"><strong><?= $row['total'] ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                <?php elseif ($reportType === 'payroll'): ?>
                    <?php
                    $totals = ['basic' => 0, 'gross' => 0, 'deductions' => 0, 'net' => 0];
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th class="text-end">Gaji Pokok</th>
                                    <th class="text-end">Pendapatan</th>
                                    <th class="text-end">Potongan</th>
                                    <th class="text-end">Gaji Bersih</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportData as $row): 
                                    $totals['basic'] += $row['basic_salary'];
                                    $totals['gross'] += $row['gross_salary'];
                                    $totals['deductions'] += $row['total_deductions'];
                                    $totals['net'] += $row['net_salary'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                                        <td class="text-end"><?= formatMoney($row['basic_salary']) ?></td>
                                        <td class="text-end"><?= formatMoney($row['gross_salary']) ?></td>
                                        <td class="text-end text-danger"><?= formatMoney($row['total_deductions']) ?></td>
                                        <td class="text-end"><strong><?= formatMoney($row['net_salary']) ?></strong></td>
                                        <td class="text-center">
                                            <?php
                                            $statusBadge = [
                                                'draft' => 'bg-secondary',
                                                'finalized' => 'bg-warning',
                                                'paid' => 'bg-success',
                                            ];
                                            ?>
                                            <span class="badge <?= $statusBadge[$row['status']] ?? 'bg-secondary' ?>">
                                                <?= ucfirst($row['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td><strong>JUMLAH</strong></td>
                                    <td class="text-end"><strong><?= formatMoney($totals['basic']) ?></strong></td>
                                    <td class="text-end"><strong><?= formatMoney($totals['gross']) ?></strong></td>
                                    <td class="text-end text-danger"><strong><?= formatMoney($totals['deductions']) ?></strong></td>
                                    <td class="text-end"><strong><?= formatMoney($totals['net']) ?></strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
@media print {
    .sidebar, .top-navbar, .page-header, form, .btn { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #000 !important; }
}
</style>

<?php require_once '../includes/footer.php'; ?>
