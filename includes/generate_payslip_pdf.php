<?php
/**
 * ============================================
 * PDF PAYSLIP GENERATOR
 * ============================================
 * Generate Malaysian payslip in PDF format using TCPDF
 * ============================================
 */

require_once __DIR__ . '/../includes/functions.php';

// Check if TCPDF is available
if (!file_exists(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php')) {
    die('TCPDF library not found. Please install it using: composer require tecnickcom/tcpdf');
}

require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

/**
 * Generate PDF payslip
 * @param string $payrollId Payroll record ID
 * @param string $mode Output mode: 'I' (inline), 'D' (download), 'S' (string), 'F' (file)
 * @return mixed PDF output based on mode
 */
function generatePayslipPDF($payrollId, $mode = 'I') {
    try {
        $conn = getConnection();
        
        // Get payroll data with employee details
        $stmt = $conn->prepare("
            SELECT 
                pr.*,
                p.full_name,
                p.email,
                p.ic_number,
                p.phone,
                p.employment_type,
                p.basic_salary,
                p.hourly_rate,
                p.epf_number,
                p.socso_number,
                p.bank_name,
                p.bank_account,
                c.name as company_name,
                c.logo_url
            FROM payroll pr
            JOIN profiles p ON pr.user_id = p.id
            JOIN companies c ON p.company_id = c.id
            WHERE pr.id = ?
        ");
        $stmt->execute([$payrollId]);
        $payroll = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payroll) {
            throw new Exception('Payroll record not found');
        }
        
        // Create PDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('MI-NES Payroll System');
        $pdf->SetAuthor($payroll['company_name']);
        $pdf->SetTitle('Payslip - ' . $payroll['full_name']);
        $pdf->SetSubject('Monthly Payslip');
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        
        // Add page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Generate HTML content
        $html = generatePayslipHTML($payroll);
        
        // Write HTML
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Output PDF
        $filename = 'Payslip_' . $payroll['full_name'] . '_' . $payroll['month'] . '_' . $payroll['year'] . '.pdf';
        
        return $pdf->Output($filename, $mode);
        
    } catch (Exception $e) {
        error_log("PDF generation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate HTML content for payslip
 * @param array $payroll Payroll data
 * @return string HTML content
 */
function generatePayslipHTML($payroll) {
    $monthName = date('F', mktime(0, 0, 0, $payroll['month'], 1));
    $paymentDate = formatDate($payroll['payment_date'] ?? $payroll['created_at']);
    
    $html = '
    <style>
        body { font-family: helvetica; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .company-name { font-size: 18pt; font-weight: bold; color: #0d6efd; }
        .doc-title { font-size: 14pt; font-weight: bold; margin-top: 10px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 5px; border-bottom: 1px solid #ddd; }
        .info-label { width: 40%; font-weight: bold; color: #666; }
        .section-title { font-size: 12pt; font-weight: bold; background: #0d6efd; color: white; 
                         padding: 5px 10px; margin: 15px 0 10px 0; }
        .amounts-table { width: 100%; border-collapse: collapse; }
        .amounts-table th { background: #f8f9fa; padding: 8px; text-align: left; border: 1px solid #dee2e6; }
        .amounts-table td { padding: 8px; border: 1px solid #dee2e6; }
        .amount-value { text-align: right; font-weight: bold; }
        .total-row { background: #e7f3ff; font-weight: bold; font-size: 11pt; }
        .net-pay-row { background: #d4edda; font-weight: bold; font-size: 12pt; color: #155724; }
        .footer { margin-top: 30px; text-align: center; font-size: 8pt; color: #666; }
        .signature-section { margin-top: 40px; }
        .signature-line { border-top: 1px solid #000; width: 200px; margin: 0 auto; padding-top: 5px; }
    </style>
    
    <div class="header">
        <div class="company-name">' . htmlspecialchars($payroll['company_name']) . '</div>
        <div class="doc-title">SLIP GAJI / PAYSLIP</div>
        <div style="margin-top: 5px; color: #666;">' . $monthName . ' ' . $payroll['year'] . '</div>
    </div>
    
    <table class="info-table">
        <tr>
            <td class="info-label">Nama / Name:</td>
            <td>' . htmlspecialchars($payroll['full_name']) . '</td>
            <td class="info-label">No. Pekerja / Staff No:</td>
            <td>' . substr($payroll['user_id'], 0, 8) . '</td>
        </tr>
        <tr>
            <td class="info-label">No. K/P / IC No:</td>
            <td>' . htmlspecialchars($payroll['ic_number'] ?? 'N/A') . '</td>
            <td class="info-label">Tarikh Bayaran / Payment Date:</td>
            <td>' . $paymentDate . '</td>
        </tr>
        <tr>
            <td class="info-label">No. EPF:</td>
            <td>' . htmlspecialchars($payroll['epf_number'] ?? 'N/A') . '</td>
            <td class="info-label">No. SOCSO:</td>
            <td>' . htmlspecialchars($payroll['socso_number'] ?? 'N/A') . '</td>
        </tr>
        <tr>
            <td class="info-label">Jenis Pekerjaan / Employment:</td>
            <td>' . getEmploymentTypeName($payroll['employment_type']) . '</td>
            <td class="info-label">Gaji Pokok / Basic Salary:</td>
            <td class="amount-value">RM ' . number_format($payroll['basic_salary'], 2) . '</td>
        </tr>
    </table>
    
    <div class="section-title">PENDAPATAN / EARNINGS</div>
    <table class="amounts-table">
        <thead>
            <tr>
                <th width="50%">Butiran / Description</th>
                <th width="25%" style="text-align: center;">Jam / Hours</th>
                <th width="25%" style="text-align: right;">Amaun / Amount (RM)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gaji Pokok / Basic Salary</td>
                <td style="text-align: center;">' . number_format($payroll['total_hours'], 2) . '</td>
                <td class="amount-value">' . number_format($payroll['basic_salary'], 2) . '</td>
            </tr>';
    
    if ($payroll['ot_normal'] > 0) {
        $otNormalHours = floatval($payroll['ot_normal']) / (floatval($payroll['hourly_rate']) * 1.5);
        $html .= '
            <tr>
                <td>OT Biasa / Normal OT (1.5x)</td>
                <td style="text-align: center;">' . number_format($otNormalHours, 2) . '</td>
                <td class="amount-value">' . number_format($payroll['ot_normal'], 2) . '</td>
            </tr>';
    }
    
    if ($payroll['ot_sunday'] > 0) {
        $otSundayHours = floatval($payroll['ot_sunday']) / (floatval($payroll['hourly_rate']) * 2.0);
        $html .= '
            <tr>
                <td>OT Ahad / Sunday OT (2.0x)</td>
                <td style="text-align: center;">' . number_format($otSundayHours, 2) . '</td>
                <td class="amount-value">' . number_format($payroll['ot_sunday'], 2) . '</td>
            </tr>';
    }
    
    if ($payroll['ot_public'] > 0) {
        $otPublicHours = floatval($payroll['ot_public']) / (floatval($payroll['hourly_rate']) * 3.0);
        $html .= '
            <tr>
                <td>OT Cuti Umum / Public Holiday OT (3.0x)</td>
                <td style="text-align: center;">' . number_format($otPublicHours, 2) . '</td>
                <td class="amount-value">' . number_format($payroll['ot_public'], 2) . '</td>
            </tr>';
    }
    
    if ($payroll['allowances'] > 0) {
        $html .= '
            <tr>
                <td>Elaun / Allowances</td>
                <td style="text-align: center;">-</td>
                <td class="amount-value">' . number_format($payroll['allowances'], 2) . '</td>
            </tr>';
    }
    
    $html .= '
            <tr class="total-row">
                <td colspan="2">Jumlah Pendapatan / Gross Pay</td>
                <td class="amount-value">RM ' . number_format($payroll['gross_pay'], 2) . '</td>
            </tr>
        </tbody>
    </table>
    
    <div class="section-title">POTONGAN / DEDUCTIONS</div>
    <table class="amounts-table">
        <thead>
            <tr>
                <th width="50%">Butiran / Description</th>
                <th width="25%" style="text-align: center;">Kadar / Rate (%)</th>
                <th width="25%" style="text-align: right;">Amaun / Amount (RM)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>EPF Pekerja / Employee EPF</td>
                <td style="text-align: center;">11%</td>
                <td class="amount-value">' . number_format($payroll['epf_employee'], 2) . '</td>
            </tr>
            <tr>
                <td>SOCSO Pekerja / Employee SOCSO</td>
                <td style="text-align: center;">-</td>
                <td class="amount-value">' . number_format($payroll['socso_employee'], 2) . '</td>
            </tr>
            <tr>
                <td>EIS Pekerja / Employee EIS</td>
                <td style="text-align: center;">0.2%</td>
                <td class="amount-value">' . number_format($payroll['eis_employee'], 2) . '</td>
            </tr>
            <tr>
                <td>Cukai PCB / PCB Tax</td>
                <td style="text-align: center;">-</td>
                <td class="amount-value">' . number_format($payroll['pcb_tax'], 2) . '</td>
            </tr>';
    
    if ($payroll['other_deductions'] > 0) {
        $html .= '
            <tr>
                <td>Potongan Lain / Other Deductions</td>
                <td style="text-align: center;">-</td>
                <td class="amount-value">' . number_format($payroll['other_deductions'], 2) . '</td>
            </tr>';
    }
    
    $html .= '
            <tr class="total-row">
                <td colspan="2">Jumlah Potongan / Total Deductions</td>
                <td class="amount-value">RM ' . number_format($payroll['total_deductions'], 2) . '</td>
            </tr>
        </tbody>
    </table>
    
    <table class="amounts-table" style="margin-top: 15px;">
        <tr class="net-pay-row">
            <td style="font-size: 13pt;">GAJI BERSIH / NET PAY</td>
            <td class="amount-value" style="font-size: 13pt;">RM ' . number_format($payroll['net_pay'], 2) . '</td>
        </tr>
    </table>
    
    <div class="section-title">MAKLUMAT BANK / BANK DETAILS</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Bank / Bank Name:</td>
            <td>' . htmlspecialchars($payroll['bank_name'] ?? 'N/A') . '</td>
        </tr>
        <tr>
            <td class="info-label">No. Akaun / Account No:</td>
            <td>' . htmlspecialchars($payroll['bank_account'] ?? 'N/A') . '</td>
        </tr>
    </table>
    
    <div class="signature-section">
        <table width="100%">
            <tr>
                <td width="50%" style="text-align: center;">
                    <div style="margin-top: 50px;" class="signature-line">Tandatangan Pekerja</div>
                    <div style="font-size: 8pt; color: #666;">Employee Signature</div>
                </td>
                <td width="50%" style="text-align: center;">
                    <div style="margin-top: 50px;" class="signature-line">Tandatangan Majikan</div>
                    <div style="font-size: 8pt; color: #666;">Employer Signature</div>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        <div style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 20px;">
            <p>Dokumen ini dijana secara automatik oleh Sistem Gaji MI-NES</p>
            <p>This document is automatically generated by MI-NES Payroll System</p>
            <p>Tarikh cetak / Print date: ' . date('d/m/Y H:i:s') . '</p>
        </div>
    </div>
    ';
    
    return $html;
}

// Handle direct access (download payslip)
if (basename($_SERVER['PHP_SELF']) === 'generate_payslip_pdf.php') {
    requireLogin();
    
    $payrollId = $_GET['id'] ?? '';
    
    if (empty($payrollId)) {
        die('Payroll ID is required');
    }
    
    // Verify user access
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT user_id FROM payroll WHERE id = ?");
    $stmt->execute([$payrollId]);
    $payroll = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$payroll) {
        die('Payroll record not found');
    }
    
    // Check if user owns this payslip or is HR
    $currentUser = getCurrentUser();
    if ($payroll['user_id'] !== $currentUser['id'] && !isHR()) {
        die('Access denied');
    }
    
    // Generate and download PDF
    generatePayslipPDF($payrollId, 'D');
}
?>
