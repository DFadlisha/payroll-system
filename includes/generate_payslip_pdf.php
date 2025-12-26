<?php
/**
 * ============================================
 * GENERATE PAYSLIP PDF
 * ============================================
 * Generates a PDF version of the payslip
 * using TCPDF library.
 * ============================================
 */

require_once '../vendor/autoload.php';
require_once '../config/database.php';
require_once 'functions.php';

// Check login
if (!isLoggedIn()) {
    die("Access denied");
}

$payrollId = $_GET['id'] ?? '';
if (empty($payrollId)) {
    die("Invalid Payslip ID");
}

try {
    $conn = getConnection();

    // Fetch Payroll Data
    $stmt = $conn->prepare("
        SELECT p.*, 
               pr.full_name, pr.ic_number, pr.epf_number, pr.socso_number, 
               pr.tax_number, pr.bank_name, pr.bank_account, pr.employment_type,
               c.name as company_name, c.address as company_address
        FROM payroll p
        JOIN profiles pr ON p.user_id = pr.id
        LEFT JOIN companies c ON pr.company_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$payrollId]);
    $payslip = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payslip) {
        die("Payslip not found");
    }

    // Check permission (User can only view their own, HR can view all)
    if (!isHR() && $payslip['user_id'] !== $_SESSION['user_id']) {
        die("Unauthorized access");
    }

    // Calculate derived values if columns don't exist
    $payslip['gross_pay'] = $payslip['gross_pay'] ?? $payslip['gross_salary'] ?? 0;
    $payslip['net_pay'] = $payslip['net_pay'] ?? $payslip['net_salary'] ?? 0;

    // Total Overtime Pay
    $otPay = ($payslip['ot_normal'] ?? 0) + ($payslip['ot_sunday'] ?? 0) + ($payslip['ot_public'] ?? 0);

    // Setup PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('MI-NES Payroll System');
    $pdf->SetAuthor('MI-NES Payroll');
    $pdf->SetTitle('Payslip - ' . $payslip['full_name']);

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins
    $pdf->SetMargins(15, 15, 15);

    // Add a page
    $pdf->AddPage();

    // --- CONTENT GENERATION ---

    // Company Header
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, strtoupper($payslip['company_name'] ?? 'NES SOLUTION & NETWORK SDN BHD'), 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 5, $payslip['company_address'] ?? 'No.2, Jalan Kencana 1A/1, Pura Kencana, 83300 Sri Gading, Batu Pahat, Johor', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'PAYSLIP', 0, 1, 'C');
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(5);

    // Employee Info Box
    $pdf->SetFont('helvetica', '', 9);
    $col1_x = 15;
    $col2_x = 110;

    $currentY = $pdf->GetY();

    // Left Column
    $pdf->SetXY($col1_x, $currentY);
    $pdf->Cell(30, 6, 'Name:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(60, 6, $payslip['full_name'], 0, 1);

    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(30, 6, 'IC No:', 0, 0);
    $pdf->Cell(60, 6, $payslip['ic_number'] ?? '-', 0, 1);

    $pdf->Cell(30, 6, 'EPF No:', 0, 0);
    $pdf->Cell(60, 6, $payslip['epf_number'] ?? '-', 0, 1);

    $pdf->Cell(30, 6, 'SOCSO No:', 0, 0);
    $pdf->Cell(60, 6, $payslip['socso_number'] ?? '-', 0, 1);

    // Right Column
    $endY_left = $pdf->GetY();
    $pdf->SetXY($col2_x, $currentY);

    $monthName = date('F', mktime(0, 0, 0, $payslip['month'], 10));
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(30, 6, 'Period:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(50, 6, strtoupper($monthName) . ' ' . $payslip['year'], 0, 1);

    $pdf->SetXY($col2_x, $pdf->GetY());
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(30, 6, 'Bank:', 0, 0);
    $pdf->Cell(50, 6, $payslip['bank_name'] ?? '-', 0, 1);

    $pdf->SetXY($col2_x, $pdf->GetY());
    $pdf->Cell(30, 6, 'Acct No:', 0, 0);
    $pdf->Cell(50, 6, $payslip['bank_account'] ?? '-', 0, 1);

    $pdf->SetXY($col2_x, $pdf->GetY());
    $pdf->Cell(30, 6, 'Branch:', 0, 0);
    $pdf->Cell(50, 6, 'MAIN', 0, 1);

    $pdf->Ln(10);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(5);

    // Earnings & Deductions Headers
    $headerY = $pdf->GetY();
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(90, 8, 'EARNINGS', 1, 0, 'C', 0);
    $pdf->Cell(90, 8, 'DEDUCTIONS', 1, 1, 'C', 0);

    // Content Tables
    $contentY = $pdf->GetY();
    $boxHeight = 80;

    // Draw Box Frames
    $pdf->Rect(15, $contentY, 90, $boxHeight); // Earnings Box
    $pdf->Rect(105, $contentY, 90, $boxHeight); // Deductions Box

    // Earnings Content
    $pdf->SetXY(15, $contentY + 2);
    $pdf->SetFont('helvetica', '', 9);

    // Basic Pay
    $pdf->Cell(60, 6, ' Basic Salary', 0, 0);
    $pdf->Cell(25, 6, number_format($payslip['basic_salary'], 2), 0, 1, 'R');

    // Overtime
    if ($otPay > 0) {
        $pdf->SetX(15);
        $pdf->Cell(60, 6, ' Overtime Pay', 0, 0);
        $pdf->Cell(25, 6, number_format($otPay, 2), 0, 1, 'R');

        $pdf->SetFont('helvetica', 'I', 8);
        if ($payslip['ot_normal'] > 0) {
            $pdf->SetX(15);
            $pdf->Cell(60, 5, '  Normal Days (' . ($payslip['ot_normal_hours'] ?? 0) . ' hrs)', 0, 0);
            $pdf->Cell(25, 5, number_format($payslip['ot_normal'], 2), 0, 1, 'R');
        }
        if ($payslip['ot_sunday'] > 0) {
            $pdf->SetX(15);
            $pdf->Cell(60, 5, '  Rest Days (' . ($payslip['ot_sunday_hours'] ?? 0) . ' hrs)', 0, 0);
            $pdf->Cell(25, 5, number_format($payslip['ot_sunday'], 2), 0, 1, 'R');
        }
        if ($payslip['ot_public'] > 0) {
            $pdf->SetX(15);
            $pdf->Cell(60, 5, '  Public Holidays (' . ($payslip['ot_public_hours'] ?? 0) . ' hrs)', 0, 0);
            $pdf->Cell(25, 5, number_format($payslip['ot_public'], 2), 0, 1, 'R');
        }
        $pdf->SetFont('helvetica', '', 9);
    }

    // Allowances (if any)
    // Placeholder assuming no allowance logic yet in DB, but good to have

    // Deductions Content
    $pdf->SetXY(105, $contentY + 2);

    // EPF
    $pdf->SetX(105);
    $pdf->Cell(60, 6, ' EPF (Employee)', 0, 0);
    $pdf->Cell(25, 6, number_format($payslip['epf_employee'], 2), 0, 1, 'R');

    // SOCSO
    $pdf->SetX(105);
    $pdf->Cell(60, 6, ' SOCSO (Employee)', 0, 0);
    $pdf->Cell(25, 6, number_format($payslip['socso_employee'], 2), 0, 1, 'R');

    // EIS
    $pdf->SetX(105);
    $pdf->Cell(60, 6, ' EIS (Employee)', 0, 0);
    $pdf->Cell(25, 6, number_format($payslip['eis_employee'], 2), 0, 1, 'R');

    // PCB
    if ($payslip['pcb_tax'] > 0) {
        $pdf->SetX(105);
        $pdf->Cell(60, 6, ' PCB (Tax)', 0, 0);
        $pdf->Cell(25, 6, number_format($payslip['pcb_tax'], 2), 0, 1, 'R');
    }

    // Totals Line
    $pdf->SetXY(15, $contentY + $boxHeight);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(60, 8, ' TOTAL EARNINGS', 1, 0, 'L');
    $pdf->Cell(30, 8, number_format($payslip['gross_pay'], 2), 1, 0, 'R');

    $pdf->Cell(60, 8, ' TOTAL DEDUCTIONS', 1, 0, 'L');
    $pdf->Cell(30, 8, number_format(($payslip['gross_pay'] - $payslip['net_pay']), 2), 1, 1, 'R'); // total deductions

    // Net Pay
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(220, 230, 240); // Light blue
    $pdf->Cell(120, 10, ' NET PAY', 1, 0, 'R', true);
    $pdf->Cell(60, 10, 'RM ' . number_format($payslip['net_pay'], 2), 1, 1, 'C', true);

    // Footer Notes
    $pdf->Ln(15);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, '* This is a computer generated document. No signature is required.', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Date Generated: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

    // Output
    $pdf->Output('payslip_' . $payslip['month'] . '_' . $payslip['year'] . '.pdf', 'I');

} catch (Exception $e) {
    die("Error generating PDF: " . $e->getMessage());
}
