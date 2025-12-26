# PDF Generation Library Setup

## Option 1: TCPDF (Recommended)

TCPDF is a popular PHP library for generating PDF documents.

### Installation via Composer (Recommended)

```bash
composer require tecnickcom/tcpdf
```

### Manual Installation


## Option 2: FPDF (Lightweight Alternative)

FPDF is a simpler, lightweight PDF library.

### Installation

1. Download FPDF from: http://www.fpdf.org/
2. Extract fpdf.php to `includes/` directory
3. Include in your code:

```php
require_once '../includes/fpdf.php';
```

## Recommended: TCPDF

For this payroll system, we recommend **TCPDF** because it supports:
- UTF-8 (for Malay language)
- HTML to PDF conversion
- Better styling options
- Professional layouts

## Installation Steps

### Step 1: Install Composer (if not installed)

Windows:
```powershell
# Download and run Composer-Setup.exe from https://getcomposer.org/
```

### Step 2: Navigate to Project Directory

```powershell
cd "c:\Users\User\Documents\SEM 7\INDUSTRIAL THINGS\NES SOLUTION AND NETWORK SDN BHD\payroll system"
```

### Step 3: Install TCPDF

```powershell
composer require tecnickcom/tcpdf
```

### Step 4: Verify Installation

Check that `vendor/tecnickcom/tcpdf/` directory exists.

## Alternative: Download TCPDF Directly

If Composer is not available:

1. Download: https://github.com/tecnickcom/TCPDF/archive/refs/heads/main.zip
2. Extract to: `vendor/tecnickcom/tcpdf/`
3. The main file should be at: `vendor/tecnickcom/tcpdf/tcpdf.php`

## Next Steps

After installation, the `generatePayslipPDF()` function in `includes/functions.php` will use TCPDF to generate payslips.

## Testing PDF Generation

```php
// Test if TCPDF is available
if (file_exists(__DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php')) {
    echo "TCPDF is installed!";
} else {
    echo "TCPDF not found. Please install it.";
}
```

## Usage Example

```php
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->AddPage();
$pdf->WriteHTML('<h1>Test PDF</h1>');
$pdf->Output('test.pdf', 'I'); // I = inline, D = download
```
