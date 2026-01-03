<?php
/**
 * ============================================
 * GENERATE PAYSLIP PDF - DETAILED FORMAT
 * ============================================
 * Matches specific user design:
 * - A4 Landscape
 * - Detailed Daily Grid (WD, PH, MC, AL, UPL, Late, OT Splits, Allowances)
 * - Bottom Summary Section
 * ============================================
 */

require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once 'functions.php';

if (!isLoggedIn()) {
    die("Access denied");
}

$payrollId = $_GET['id'] ?? '';
if (empty($payrollId)) {
    die("Invalid Payslip ID");
}

try {
    $conn = getConnection();

    // 1. Fetch Payroll & Profile Data
    $stmt = $conn->prepare("
        SELECT p.*, 
               pr.full_name, pr.ic_number, pr.epf_number, pr.socso_number, 
               pr.tax_number, pr.bank_name, pr.bank_account_number, 
               pr.employment_type, pr.role, pr.phone,
               wl.name as assigned_location_name,
               c.name as company_name, c.address as company_address
        FROM payroll p
        JOIN profiles pr ON p.user_id = pr.id
        LEFT JOIN work_locations wl ON pr.location_id = wl.id
        LEFT JOIN companies c ON pr.company_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$payrollId]);
    $payslip = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payslip) {
        die("Payslip not found");
    }
    if (!isHR() && $payslip['user_id'] !== $_SESSION['user_id']) {
        die("Unauthorized access");
    }

    $month = $payslip['month'];
    $year = $payslip['year'];
    $userId = $payslip['user_id'];
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    // 2. Fetch Attendance Data (Indexed by Date)
    $stmt = $conn->prepare("
        SELECT 
            DATE(clock_in) as work_date,
            clock_in, clock_out,
            total_hours, overtime_hours,
            ot_hours, ot_sunday_hours, ot_public_hours,
            late_minutes,
            status,
            project_hours
        FROM attendance
        WHERE user_id = ? 
        AND EXTRACT(MONTH FROM clock_in) = ? 
        AND EXTRACT(YEAR FROM clock_in) = ?
    ");
    $stmt->execute([$userId, $month, $year]);
    $attendanceRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $attendanceMap = [];
    foreach ($attendanceRaw as $row) {
        $attendanceMap[$row['work_date']] = $row;
    }

    // 3. Fetch Leaves (Indexed by Date)
    // We need to expand multi-day leaves into individual dates
    $stmt = $conn->prepare("
        SELECT leave_type, start_date, end_date 
        FROM leaves 
        WHERE user_id = ? AND status = 'approved'
        AND (
            (EXTRACT(MONTH FROM start_date) = ? AND EXTRACT(YEAR FROM start_date) = ?)
            OR 
            (EXTRACT(MONTH FROM end_date) = ? AND EXTRACT(YEAR FROM end_date) = ?)
        )
    ");
    $stmt->execute([$userId, $month, $year, $month, $year]);
    $leavesRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $leaveMap = [];

    foreach ($leavesRaw as $leave) {
        $start = new DateTime($leave['start_date']);
        $end = new DateTime($leave['end_date']);
        // Constrain to current month
        $monthStart = new DateTime("$year-$month-01");
        $monthEnd = new DateTime("$year-$month-$daysInMonth");

        if ($start < $monthStart)
            $start = $monthStart;
        if ($end > $monthEnd)
            $end = $monthEnd;

        while ($start <= $end) {
            $leaveMap[$start->format('Y-m-d')] = $leave['leave_type'];
            $start->modify('+1 day');
        }
    }

    // 4. Init PDF (Landscape)
    $pdf = new TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('MI-NES Payroll');
    $pdf->SetTitle('Payslip ' . $month . '-' . $year);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(5, 5, 5);
    $pdf->SetAutoPageBreak(FALSE); // Disable auto break to prevent overlay issues, we control height
    $pdf->AddPage();

    // CENTERING LOGIC
    // Total Table Width = 225mm
    // Page Width = 297mm
    $startX = 36;

    // --- HEADER ---
    $pdf->SetY(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 5, strtoupper($payslip['company_name'] ?? 'NES SOLUTION & NETWORK SDN BHD'), 0, 1, 'C');
    $pdf->SetFont('helvetica', 'B', 10);
    $monthName = strtoupper(date('F Y', mktime(0, 0, 0, $month, 10, $year)));
    $pdf->Cell(0, 5, $monthName, 0, 1, 'C');

    // --- INFO BLOCK ---
    // Compact Layout: 2 Lines
    $pdf->SetFont('helvetica', '', 8);
    $yInfo = $pdf->GetY() + 2;

    // Line 1: Name | IC | Role
    $pdf->Text($startX, $yInfo, 'NAME: ' . strtoupper($payslip['full_name']));
    $pdf->Text($startX + 80, $yInfo, 'IC: ' . ($payslip['ic_number'] ?? '-'));
    $pdf->Text($startX + 140, $yInfo, 'ROLE: ' . strtoupper($payslip['role'] ?? '-'));

    // Line 2: Basic | Phone | Type
    $pdf->Text($startX, $yInfo + 4, 'BASIC: ' . number_format($payslip['basic_salary'], 2));
    $pdf->Text($startX + 80, $yInfo + 4, 'TEL: ' . ($payslip['phone'] ?? '-'));
    $pdf->Text($startX + 140, $yInfo + 4, 'TYPE: ' . strtoupper($payslip['employment_type'] ?? '-'));

    // Line 3: Location
    $pdf->Text($startX, $yInfo + 8, 'WORK LOCATION: ' . strtoupper($payslip['assigned_location_name'] ?? 'OFFICE'));

    $pdf->SetY($yInfo + 14); // Start table after info

    // --- ATTENDANCE GRID ---
    $hHeight = 3.8; // Aggressively small row height

    $wDate = 15;
    $wDay = 10;
    $wLoc = 35;
    $wIn = 12;
    $wOut = 12;
    $wCheck = 8;
    $wLate = 12;
    $wOT = 12;
    $wAllow = 15;

    // Header Row 1
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->SetFillColor(200, 200, 200);

    $pdf->SetX($startX);
    $pdf->Cell($wDate + $wDay + $wLoc + $wIn + $wOut, $hHeight, 'ATTENDANCE DETAILS', 1, 0, 'C', true);
    $pdf->Cell($wCheck * 6, $hHeight, 'ACTUAL WORKING DAY', 1, 0, 'C', true);
    $pdf->Cell($wLate, $hHeight, 'LATE', 1, 0, 'C', true);
    $pdf->Cell($wOT * 3, $hHeight, 'OVERTIME (HOURS)', 1, 0, 'C', true);
    $pdf->Cell($wAllow * 3, $hHeight, 'ALLOWANCE', 1, 1, 'C', true);

    // Header Row 2
    $pdf->SetX($startX);
    $pdf->SetFillColor(230, 230, 230);
    $pdf->Cell($wDate, $hHeight, 'DATE', 1, 0, 'C', true);
    $pdf->Cell($wDay, $hHeight, 'DAY', 1, 0, 'C', true);
    $pdf->Cell($wLoc, $hHeight, 'LOCATION/REM', 1, 0, 'C', true);
    $pdf->Cell($wIn, $hHeight, 'IN', 1, 0, 'C', true);
    $pdf->Cell($wOut, $hHeight, 'OUT', 1, 0, 'C', true);

    $pdf->Cell($wCheck, $hHeight, 'WD', 1, 0, 'C', true);
    $pdf->Cell($wCheck, $hHeight, 'PH', 1, 0, 'C', true);
    $pdf->Cell($wCheck, $hHeight, 'MC', 1, 0, 'C', true);
    $pdf->Cell($wCheck, $hHeight, 'AL', 1, 0, 'C', true);
    $pdf->Cell($wCheck, $hHeight, 'UPL', 1, 0, 'C', true);
    $pdf->Cell($wCheck, $hHeight, 'ABS', 1, 0, 'C', true);

    $pdf->Cell($wLate, $hHeight, 'MIN', 1, 0, 'C', true);

    $pdf->Cell($wOT, $hHeight, '1.5', 1, 0, 'C', true);
    $pdf->Cell($wOT, $hHeight, '2.0', 1, 0, 'C', true);
    $pdf->Cell($wOT, $hHeight, '3.0', 1, 0, 'C', true);

    $pdf->Cell($wAllow, $hHeight, 'PROJ', 1, 0, 'C', true);
    $pdf->Cell($wAllow, $hHeight, 'SHFT', 1, 0, 'C', true);
    $pdf->Cell($wAllow, $hHeight, 'ATTN', 1, 1, 'C', true);

    // --- LOOP DAYS ---
    $pdf->SetFont('helvetica', '', 7);

    $totalWD = 0;
    $totalPH = 0;
    $totalMC = 0;
    $totalAL = 0;
    $totalUPL = 0;
    $totalABS = 0;
    $totalLateMins = 0;
    $totalOT15 = 0;
    $totalOT20 = 0;
    $totalOT30 = 0;

    // OPTIMIZATION: Fetch holidays automatically via API/Cache (No Database dependency)
    $holidayDates = [];
    $apiHolidays = getMalaysiaHolidays($year); // Fetches from date.nager.at or local JSON

    $monthPrefix = sprintf('%04d-%02d', $year, $month);

    if (!empty($apiHolidays)) {
        foreach ($apiHolidays as $hDate => $hName) {
            // Filter only holidays for the current month
            if (strpos($hDate, $monthPrefix) === 0) {
                $holidayDates[] = $hDate;
            }
        }
    }

    for ($d = 1; $d <= $daysInMonth; $d++) {
        $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $timestamp = strtotime($currentDate);
        $dayName = strtoupper(substr(date('D', $timestamp), 0, 3));
        // Weekend Logic: Sunday is always OFF. Saturday is OFF on 1st and 3rd week.
        $isWeekend = ($dayName === 'SUN');
        if ($dayName === 'SAT') {
            $weekNum = ceil($d / 7); // 1-7=1st, 8-14=2nd, 15-21=3rd, etc.
            if ($weekNum == 1 || $weekNum == 3) {
                $isWeekend = true;
            }
        }
        $isPH = in_array($currentDate, $holidayDates);

        $att = $attendanceMap[$currentDate] ?? null;
        $leave = $leaveMap[$currentDate] ?? null;

        $txtLoc = '';
        $txtIn = '';
        $txtOut = '';
        $chkWD = '';
        $chkPH = '';
        $chkMC = '';
        $chkAL = '';
        $chkUPL = '';
        $chkABS = '';
        $valLate = '';
        $valOT15 = '';
        $valOT20 = '';
        $valOT30 = '';
        $valProj = '';
        $valShift = '';
        $valAttn = '';

        $fill = false;
        if ($isWeekend) {
            $pdf->SetFillColor(235, 235, 235);
            $fill = true;
        } elseif ($isPH) {
            $pdf->SetFillColor(255, 235, 235);
            $fill = true;
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        if ($att) {
            $txtIn = date('H:i', strtotime($att['clock_in']));
            $txtOut = $att['clock_out'] ? date('H:i', strtotime($att['clock_out'])) : '';
            $txtLoc = 'PRESENT';

            if ($isPH) {
                $chkPH = '1';
                $totalPH++;
            } elseif ($isWeekend) {
                $chkWD = '1';
            } // Weekend work
            else {
                $chkWD = '1';
                $totalWD++;
            }

            if ($att['late_minutes'] > 0) {
                $valLate = $att['late_minutes'];
                $totalLateMins += floatval($valLate);
            }

            $otNormal = floatval($att['ot_hours']);
            $otSun = floatval($att['ot_sunday_hours']);
            $otPub = floatval($att['ot_public_hours']);
            if ($otNormal > 0) {
                $valOT15 = $otNormal;
                $totalOT15 += $otNormal;
            }
            if ($otSun > 0) {
                $valOT20 = $otSun;
                $totalOT20 += $otSun;
            }
            if ($otPub > 0) {
                $valOT30 = $otPub;
                $totalOT30 += $otPub;
            }

            if ($att['project_hours'] > 0)
                $valProj = $att['project_hours'];

        } else {
            if ($leave) {
                $txtLoc = strtoupper(substr($leave, 0, 8));
                if ($leave === 'medical') {
                    $chkMC = '1';
                    $totalMC++;
                } elseif ($leave === 'annual') {
                    $chkAL = '1';
                    $totalAL++;
                } elseif ($leave === 'unpaid') {
                    $chkUPL = '1';
                    $totalUPL++;
                }
            } elseif ($isPH) {
                $txtLoc = 'PH';
                $chkPH = '1';
                $totalPH++;
            } elseif ($isWeekend) {
                $txtLoc = 'OFF';
            } else {
                $txtLoc = 'ABS';
                $chkABS = '1';
                $totalABS++;
            }
        }

        $pdf->SetX($startX);
        $pdf->Cell($wDate, $hHeight, $d . '-' . strtoupper(substr($monthName, 0, 3)), 1, 0, 'C', $fill);
        $pdf->Cell($wDay, $hHeight, $dayName, 1, 0, 'C', $fill);
        $pdf->Cell($wLoc, $hHeight, $txtLoc, 1, 0, 'L', $fill);
        $pdf->Cell($wIn, $hHeight, $txtIn, 1, 0, 'C', $fill);
        $pdf->Cell($wOut, $hHeight, $txtOut, 1, 0, 'C', $fill);
        $pdf->Cell($wCheck, $hHeight, $chkWD, 1, 0, 'C', $fill);
        $pdf->Cell($wCheck, $hHeight, $chkPH, 1, 0, 'C', $fill);
        $pdf->Cell($wCheck, $hHeight, $chkMC, 1, 0, 'C', $fill);
        $pdf->Cell($wCheck, $hHeight, $chkAL, 1, 0, 'C', $fill);
        $pdf->Cell($wCheck, $hHeight, $chkUPL, 1, 0, 'C', $fill);
        $pdf->Cell($wCheck, $hHeight, $chkABS, 1, 0, 'C', $fill);
        $pdf->Cell($wLate, $hHeight, $valLate, 1, 0, 'C', $fill);
        $pdf->Cell($wOT, $hHeight, $valOT15, 1, 0, 'C', $fill);
        $pdf->Cell($wOT, $hHeight, $valOT20, 1, 0, 'C', $fill);
        $pdf->Cell($wOT, $hHeight, $valOT30, 1, 0, 'C', $fill);
        $pdf->Cell($wAllow, $hHeight, $valProj, 1, 0, 'C', $fill);
        $pdf->Cell($wAllow, $hHeight, $valShift, 1, 0, 'C', $fill);
        $pdf->Cell($wAllow, $hHeight, $valAttn, 1, 1, 'C', $fill);
    }

    // TOTAL ROW
    $pdf->SetX($startX);
    $pdf->SetFont('helvetica', 'B', 7);
    $pdf->Cell($wDate + $wDay + $wLoc + $wIn + $wOut, $hHeight, 'TOTAL', 1, 0, 'R', 0);
    $pdf->Cell($wCheck, $hHeight, $totalWD, 1, 0, 'C', 0);
    $pdf->Cell($wCheck, $hHeight, $totalPH, 1, 0, 'C', 0);
    $pdf->Cell($wCheck, $hHeight, $totalMC, 1, 0, 'C', 0);
    $pdf->Cell($wCheck, $hHeight, $totalAL, 1, 0, 'C', 0);
    $pdf->Cell($wCheck, $hHeight, $totalUPL, 1, 0, 'C', 0);
    $pdf->Cell($wCheck, $hHeight, $totalABS, 1, 0, 'C', 0);
    $pdf->Cell($wLate, $hHeight, $totalLateMins, 1, 0, 'C', 0);
    $pdf->Cell($wOT, $hHeight, number_format($totalOT15, 1), 1, 0, 'C', 0);
    $pdf->Cell($wOT, $hHeight, number_format($totalOT20, 1), 1, 0, 'C', 0);
    $pdf->Cell($wOT, $hHeight, number_format($totalOT30, 1), 1, 0, 'C', 0);
    $pdf->Cell($wAllow * 3, $hHeight, '', 1, 1, 'C', 0);

    $pdf->Ln(2);

    // --- FINANCIAL SUMMARY (Single Block compact) ---
    $ySum = $pdf->GetY();
    $leftBoxX = $startX;
    $rightBoxX = $startX + 115;
    $lineH = 4; // Smaller line height

    // LEFT: EARNINGS
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Text($leftBoxX, $ySum, 'EARNINGS');

    $rowsE = [
        ['BASIC PAY', number_format($payslip['basic_salary'], 2)],
        ['NORMAL OT', number_format($payslip['ot_normal'], 2)],
        ['REST DAY OT', number_format($payslip['ot_sunday'], 2)],
        ['PUBLIC HOL OT', number_format($payslip['ot_public'], 2)],
        ['ALLOWANCES', '0.00'],
        ['GROSS PAY', number_format($payslip['gross_pay'], 2)]
    ];

    $curY = $ySum + 4;
    $pdf->SetFont('helvetica', '', 7);

    foreach ($rowsE as $r) {
        $isBold = ($r[0] === 'GROSS PAY');
        if ($isBold)
            $pdf->SetFont('helvetica', 'B', 7);

        $pdf->Text($leftBoxX, $curY, $r[0]);
        $pdf->SetXY($leftBoxX + 60, $curY);
        // Align amounts right
        $pdf->Cell(20, 0, $r[1], 0, 0, 'R');
        $curY += $lineH;

        if ($isBold)
            $pdf->SetFont('helvetica', '', 7);
    }

    // RIGHT: DEDUCTIONS
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->Text($rightBoxX, $ySum, 'DEDUCTIONS');

    $curY = $ySum + 4;
    $rowsD = [
        ['EPF', number_format($payslip['epf_employee'], 2)],
        ['SOCSO', number_format($payslip['socso_employee'], 2)],
        ['EIS', number_format($payslip['eis_employee'], 2)],
        ['PCB (TAX)', number_format($payslip['pcb_tax'], 2)],
    ];

    $pdf->SetFont('helvetica', '', 7);
    foreach ($rowsD as $r) {
        $pdf->Text($rightBoxX, $curY, $r[0]);
        $pdf->SetXY($rightBoxX + 60, $curY);
        $pdf->Cell(20, 0, $r[1], 0, 0, 'R');
        $curY += $lineH;
    }

    // NET PAY ROW
    $curY += 2;
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(230, 255, 230);
    $pdf->SetXY($rightBoxX, $curY);
    $pdf->Cell(80, 6, 'NET PAY: RM ' . number_format($payslip['net_pay'], 2), 1, 0, 'C', true);

    // Signature Line
    $pdf->Line($startX, $curY + 15, $startX + 225, $curY + 15);
    $pdf->SetFont('helvetica', 'I', 7);
    $pdf->Text($startX, $curY + 16, 'This is a computer-generated document. No signature is required.');

    $pdf->Output('Payslip.pdf', 'I');

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
