# MI-NES Payroll System (PHP + Supabase)

Sistem Pengurusan Gaji untuk NES Solution & Network Sdn Bhd.

# MI-NES Payroll System — Consolidated Documentation

> NOTE: This file consolidates multiple top-level documentation files into a single `README.md`.
>
> Merged files:
> - PDF_LIBRARY_SETUP.md
> - PCB_OT_INTEGRATION_REPORT.md
> - MOBILE_VIEW_GUIDE.md
> - MOBILE_VIEW_DEMO.md
> - MOBILE_QUICK_REFERENCE.md
> - MOBILE_IMPLEMENTATION_SUMMARY.md
> - LAUNCH_READINESS_REPORT.md

---

## Original README (project overview)

The original README content is preserved below.

---

# MI-NES Payroll System (PHP + Supabase)

Sistem Pengurusan Gaji untuk NES Solution & Network Sdn Bhd.

## 📋 Keperluan Sistem (System Requirements)

- PHP 7.4 atau lebih tinggi (dengan extension pgsql)
- Supabase Account (database PostgreSQL)
- Web server (Apache/Nginx) atau XAMPP/WAMP/Laragon

## 🚀 Cara Pemasangan (Installation)

### 1. Pasang XAMPP (Recommended for Windows)
Download dan pasang XAMPP dari: https://www.apachefriends.org/

### 2. Enable PostgreSQL Extension

1. Buka `php.ini` dalam folder XAMPP (contoh: `C:\xampp\php\php.ini`)
2. Cari line `;extension=pgsql` dan buang `;` di depan
3. Restart Apache

### 3. Konfigurasi Supabase

Edit fail `config/database.php` dan masukkan maklumat dari Supabase Dashboard:

```php
// Pergi ke: Supabase Dashboard > Settings > Database
define('DB_HOST', 'aws-0-ap-southeast-1.pooler.supabase.com');  // Host
define('DB_PORT', '6543');                                        // Port
define('DB_NAME', 'postgres');                                    // Database name
define('DB_USER', 'postgres.your-project-ref');                   // User
define('DB_PASS', 'your-database-password');                      // Password
```

### 4. Jalankan Aplikasi

1. Copy folder ke dalam `C:\xampp\htdocs\payroll`
2. Buka browser dan pergi ke: http://localhost/payroll

## 👤 Akaun Demo

| Peranan | Email | Password |
|---------|-------|----------|
| HR Admin | admin@nes.com.my | password123 |
| Staff | staff@nes.com.my | password123 |
| Intern | intern@nes.com.my | password123 |

## 📁 Struktur Folder

```
payroll-php/
├── auth/                   # Halaman login & logout
│   ├── login.php
│   └── logout.php
├── config/                 # Konfigurasi
│   └── database.php
├── database/               # SQL schema
│   └── database.sql
├── hr/                     # Halaman HR Admin
│   ├── dashboard.php
│   ├── employees.php
│   ├── attendance.php
│   ├── leaves.php
│   ├── payroll.php
│   └── reports.php
├── includes/               # Fail yang dikongsi
│   ├── functions.php       # Helper functions
│   ├── header.php          # HTML header
│   └── footer.php          # HTML footer
├── staff/                  # Halaman Staff
│   ├── dashboard.php
│   ├── attendance.php      # Clock in/out
│   ├── leaves.php          # Permohonan cuti
│   ├── payslips.php        # Slip gaji
│   └── profile.php         # Kemaskini profil
├── index.php               # Entry point
└── README.md               # Dokumentasi
```

## ✨ Ciri-ciri Utama (Features)

### Untuk HR Admin:
- 👥 Urus pekerja (tambah, edit, padam)
- 📊 Lihat kehadiran pekerja
- 📝 Luluskan/tolak permohonan cuti
- 💰 Jana dan urus gaji bulanan
- 📈 Jana laporan

### Untuk Staff:
- ⏰ Clock in/out
- 📅 Lihat rekod kehadiran
- 🏖️ Mohon cuti
- 💵 Lihat slip gaji
- 👤 Kemaskini profil

## 🔒 Keselamatan (Security)

- Password di-hash menggunakan bcrypt
- Session-based authentication
- Input sanitization
- PDO prepared statements (prevent SQL injection)
- XSS protection dengan htmlspecialchars()

## 🇲🇾 Pengiraan Gaji Malaysia

Sistem ini mengikut kadar potongan Malaysia:

| Jenis | Pekerja | Majikan |
|-------|---------|---------|
| KWSP/EPF | 11% | 12% |
| PERKESO/SOCSO | ~0.5% | ~1.75% |
| EIS | 0.2% | 0.2% |

## 📞 Sokongan (Support)

Jika ada masalah, hubungi:
- Email: support@nes.com.my
- Tel: 03-12345678

## 📜 Lesen (License)

Hak Cipta © 2024 NES Solution & Network Sdn Bhd. Semua hak terpelihara.

---

## Merged: PDF_LIBRARY_SETUP.md

# PDF Generation Library Setup

## Option 1: TCPDF (Recommended)

TCPDF is a popular PHP library for generating PDF documents.

### Installation via Composer (Recommended)

```bash
composer require tecnickcom/tcpdf
```

### Manual Installation

1. Download TCPDF from: https://github.com/tecnickcom/TCPDF/releases
2. Extract to `vendor/tecnickcom/tcpdf/` directory
3. Include in your code:

```php
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';
```

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

---

## Merged: PCB_OT_INTEGRATION_REPORT.md

# PCB and OT Integration Completion Report

## Date: <?= date('Y-m-d H:i:s') ?>

---

## ✅ Integration Summary

All critical payroll calculation issues have been successfully integrated into the production codebase.

### 1. Helper Functions Added to `includes/functions.php`

Four new helper functions were appended to the functions file:

#### `isPublicHoliday($date)`
- **Purpose**: Check if a given date is a public holiday
- **Database**: Queries `public_holidays` table
- **Return**: Boolean (true if public holiday, false otherwise)
- **Usage**: Used by OT rate calculation to apply 3x rate

#### `isSunday($date)`
- **Purpose**: Check if a given date is Sunday (rest day)
- **Return**: Boolean (true if Sunday, false otherwise)
- **Usage**: Used by OT rate calculation to apply 2x rate

#### `getOvertimeRate($date, $hourlyRate)`
- **Purpose**: Calculate overtime rate based on Malaysian Labour Law
- **Rates**:
  - **Normal day**: 1.5x hourly rate
  - **Rest day (Sunday)**: 2.0x hourly rate
  - **Public holiday**: 3.0x hourly rate
- **Return**: Array with `rate`, `type`, and `hourly_rate`

#### `calculatePCB($monthlyIncome, $dependents = 0)`
- **Purpose**: Calculate monthly PCB (tax deduction) using LHDN 2024 rates
- **Tax Brackets** (Chargeable Income):
  - RM 0 - 5,000: 0%
  - RM 5,001 - 20,000: 1%
  - RM 20,001 - 35,000: 3%
  - RM 35,001 - 50,000: 6%
  - RM 50,001 - 70,000: 11%
  - RM 70,001 - 100,000: 19%
  - RM 100,001 - 150,000: 25%
  - RM 150,001 - 250,000: 26%
  - RM 250,001+: 28%
- **Tax Relief**:
  - Personal relief: RM9,000
  - Dependent relief: RM2,000 per dependent (max 6)
- **Return**: Float (monthly PCB amount)

---

### 2. Overtime Calculation Integration in `hr/payroll.php`

**Lines 85-110 (Approximate)**

**Before:**
```php
$otNormal = $otHours * $RATE_OT_NORMAL;
$otSunday = 0; // Will need to calculate based on day of week
$otPublic = 0; // Will need public holiday data
```

**After:**
```php
// Get detailed attendance records for OT breakdown
$stmtOT = $conn->prepare(""
	SELECT clock_in, overtime_hours
	FROM attendance 
	WHERE user_id = ? 
	AND EXTRACT(MONTH FROM clock_in) = ? 
	AND EXTRACT(YEAR FROM clock_in) = ? 
	AND overtime_hours > 0
	AND status IN ('active', 'completed')
"");
$stmtOT->execute([$emp['id'], $selectedMonth, $selectedYear]);
$otRecords = $stmtOT->fetchAll(PDO::FETCH_ASSOC);

$otNormal = 0;
$otSunday = 0;
$otPublic = 0;

foreach ($otRecords as $otRecord) {
	$otDate = date('Y-m-d', strtotime($otRecord['clock_in']));
	$otHrs = floatval($otRecord['overtime_hours']);
    
	// Determine OT type based on date
	if (isPublicHoliday($otDate)) {
		$otPublic += $otHrs * $RATE_OT_PUBLIC;
	} elseif (isSunday($otDate)) {
		$otSunday += $otHrs * $RATE_OT_SUNDAY;
	} else {
		$otNormal += $otHrs * $RATE_OT_NORMAL;
	}
}
```

**Impact:**
- ✅ Automatic detection of OT day type (normal/Sunday/public holiday)
- ✅ Correct application of 1.5x, 2x, or 3x rates
- ✅ Individual OT record iteration for precise calculation
- ✅ Integration with `public_holidays` table

---

### 3. PCB Tax Calculation Integration in `hr/payroll.php`

**Line 101-104 (Approximate)**

**Before:**
```php
$pcbTax = 0;
```

**After:**
```php
// Calculate PCB (Monthly Tax Deduction) based on LHDN rates
$dependents = intval($emp['dependents'] ?? 0);
$pcbTax = calculatePCB($grossPay, $dependents);
```

**Impact:**
- ✅ Replaced hardcoded zero with actual LHDN-compliant calculation
- ✅ Uses employee's declared dependents for tax relief
- ✅ Applies 9 progressive tax brackets
- ✅ Annual tax divided by 12 for monthly deduction

---

### 4. Database Schema Enhancement

**New File:** `database/add_dependents_column.sql`

```sql
ALTER TABLE profiles 
ADD COLUMN IF NOT EXISTS dependents INTEGER DEFAULT 0 
CHECK (dependents >= 0 AND dependents <= 10);
```

**Purpose:** Store number of tax dependents for each employee

**Instructions to Apply:**
```bash
psql -U your_username -d your_database -f database/add_dependents_column.sql
```
Or run manually in Supabase SQL editor.

---

### 5. Staff Profile Page Enhancement

**File:** `staff/profile.php`

**Changes:**
1. **New form field**: Dependents input
   - Type: Number (0-10)
   - Label: "Bilangan Tanggungan (Untuk Pengiraan Cukai PCB)"
   - Help text: "Jumlah tanggungan untuk pelepasan cukai LHDN (Max: 6)"

2. **Backend handling**:
   - Added `$dependents` variable extraction from POST
   - Validation: Must be between 0-10
   - SQL UPDATE includes `dependents = ?` parameter

**Impact:**
- ✅ Staff can declare their dependents
- ✅ PCB calculation uses actual dependent count
- ✅ Real-time tax relief adjustment

---

## Merged: MOBILE_VIEW_GUIDE.md

# Staff Mobile View - Implementation Guide

## Overview
The staff section of the MI-NES Payroll System has been optimized for mobile devices, providing a responsive and user-friendly experience across all screen sizes.

## Key Features

### 1. **Responsive Navigation**
- **Mobile Menu Toggle**: A hamburger menu button appears on mobile devices to access the sidebar
- **Overlay**: When the sidebar is open on mobile, a dark overlay covers the main content
- **Touch-Optimized**: All interactive elements have minimum touch target sizes of 44x44px
- **Auto-Close**: Sidebar automatically closes when a menu item is selected

### 2. **Adaptive Layout**
- **Mobile-First Grid**: Layouts shift from multi-column to single-column on smaller screens
- **Card-Based Design**: Data tables convert to card views on mobile for better readability
- **Hidden/Visible Utilities**: `d-none` and `d-block` classes used to toggle elements based on screen width

### 3. **Specific Page Optimizations**

#### **Dashboard (`staff/dashboard.php`)**
- **Hero Card**: Resizes and stacks content vertically
- **Stats Grid**: Shifts from 4 columns to 1 column
- **Activity Feed**: Optimized padding and font sizes

#### **Attendance (`staff/attendance.php`)**
- **Clock In/Out**: Large, full-width buttons for easy tapping
- **Location Map**: Responsive iframe height (250px on mobile)
- **History Table**: Simplified view with horizontal scrolling if needed

#### **Payslips (`staff/payslips.php`)**
- **List View**: Payslips appear as cards with a "Download" button
- **Modal Preview**: Responsive modal for viewing details

#### **Profile (`staff/profile.php`)**
- **Avatar Upload**: Centered and touch-friendly
- **Form Groups**: Full width inputs with larger labels

## Development Guidelines

When modifying the mobile view:

1. **Use Bootstrap 5 Breakpoints**:
   - Mobile: `< 576px` (`col-12`)
   - Tablet: `≥ 576px` (`col-sm`)
   - Desktop: `≥ 768px` (`col-md`)

2. **CSS Variables**:
   Use the defined variables in `index.css` for consistent spacing and colors.

3. **Testing**:
   Always test on:
   - Chrome DevTools (Device Toolbar)
   - Real Android Device (via USB debugging)
   - Real iOS Device (via local network)

---

## Merged: MOBILE_VIEW_DEMO.md

# Mobile View Demo & Visuals

This section describes the visual changes and demo flows for the mobile interface.

## 1. Login Screen
- **Before**: Fixed width centered box.
- **After**: Full width card on mobile, centered logo.

## 2. Dashboard
- **Desktop**: Sidebar on left, stats in row.
- **Mobile**: Hamburger menu, stats stacked vertically, "Clock In" button prominent at top.

## 3. Navigation
- **Action**: Click hamburger menu.
- **Result**: Sidebar slides in from left with dark overlay. Clicking overlay closes sidebar.

## 4. Payslip View
- **Mobile**: Each payslip row becomes a card:
  ```
  [ JANUARY 2025 ]
  Status: Paid
  Net Pay: RM 3,500.00
  [ Download PDF ]
  ```

---

## Merged: MOBILE_QUICK_REFERENCE.md

# Mobile Development - Quick Reference

## Common Classes (Bootstrap 5)

| Class | Description |
|-------|-------------|
| `d-none d-md-block` | Hide on mobile, show on desktop |
| `d-block d-md-none` | Show on mobile, hide on desktop |
| `flex-column flex-md-row` | Stack on mobile, row on desktop |
| `w-100` | Full width (useful for buttons on mobile) |
| `mb-3 mb-md-0` | Margin bottom on mobile, none on desktop |
| `text-center text-md-start` | Center text on mobile, align left on desktop |

## Custom CSS Helpers (in `includes/header.php`)

```css
@media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.show { transform: translateX(0); }
    .main-content { margin-left: 0; padding: 15px; }
}
```

## JavaScript Helpers (in `includes/header.php`)

```javascript
toggleSidebar() // Toggles the .show class on sidebar
```

---

## Merged: MOBILE_IMPLEMENTATION_SUMMARY.md

# Mobile Implementation Summary

## Status: ✅ Completed

The following files were modified to support mobile responsiveness:

1. **`includes/header.php`**
   - Added Mobile CSS styles (`@media` queries)
   - Added JavaScript for Sidebar Toggle
   - Added Hamburger Menu Button in Navbar template
   - Added `meta viewport` tag (already existed, confirmed)

2. **`includes/top_navbar.php`**
   - Added Hamburger Icon (`bi-list`) visible only on mobile
   - Adjusted padding and layout for small screens

3. **`staff/dashboard.php`**
   - Refactored `row` and `col` classes to `col-12 col-md-3` etc.
   - Added "Welcome" text responsive sizing

4. **`staff/attendance.php`**
   - Made Clock In/Out section responsive
   - Wrapped tables in `.table-responsive`

5. **`staff/payslips.php`**
   - Added card-based view for mobile (hidden on desktop)
   - Kept table view for desktop (hidden on mobile)

6. **`staff/leaves.php`**
   - Optimized "Apply Leave" modal for mobile width

---

## Merged: LAUNCH_READINESS_REPORT.md

# Launch Readiness Report - MI-NES Payroll System

## 🚀 System Status: READY FOR DEPLOYMENT

### 1. Core Functionality
- ✅ **Authentication**: Login, Logout, Session Management (Secure)
- ✅ **Roles**: HR Admin, Staff, Intern, Leader (Permanent/Contract)
- ✅ **Database**: Supabase PostgreSQL connected via Transaction Pooler (Port 6543)
- ✅ **PDF Generation**: TCPDF Installed and Configured

### 2. HR Modules
- ✅ **Employee Management**: Add, Edit, View Profiles
- ✅ **Attendance**: Daily Logs, Monthly Reports, OT Calculation
- ✅ **Payroll**:
  - Auto-calculation of Basic, OT, Allowances
  - EPF, SOCSO, EIS Deductions (Standard Rates)
  - **PCB Integration**: LHDN 2024 Tax Brackets implemented
- ✅ **Reports**: Excel Exports, PDF summary

### 3. Staff Modules
- ✅ **Dashboard**: Mobile-responsive overview
- ✅ **Attendance**: Geolocation Clock In/Out
- ✅ **Leaves**: Apply, View Status (Pending, Approved, Rejected)
- ✅ **Payslips**: View history, Download PDF
- ✅ **Profile**: Update basic info, **Declare Dependents (for PCB)**

### 4. Technical Optimizations
- ✅ **Performance**: Database connection pooling enabled, persistent connections
- ✅ **Security**: `htmlspecialchars` protection, PDO prepared statements, Password Hashing
- ✅ **Mobile**: Fully responsive layout for Staff portal

### 5. Deployment Checklist
- [x] Configure `.env` with Supabase Credentials
- [x] Run `composer install` for TCPDF
- [x] Import `database.sql` to Supabase
- [x] Run `database/add_dependents_column.sql` migration
- [x] Upload code to Web Server (Apache/Render)

### 6. Known Issues / Future Improvements
- *Minor*: Notification system uses session polling (could be upgraded to WebSockets)
- *Minor*: Email notifications rely on basic PHP `mail()` (suggest upgrading to PHPMailer/SMTP)

---

## End of Consolidated Documentation
