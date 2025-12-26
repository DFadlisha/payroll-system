# MI-NES Payroll System - Project Documentation

## Part 1: Project Compliance & Status Report

**Date:** December 26, 2025
**System:** MI-NES Payroll System
**Version:** 1.0 (Pre-Release)

---

### **1. Executive Summary**

This report outlines the verified completed features and the remaining critical gaps required for the full production launch of the MI-NES Payroll System. While the core payroll calculation logic (Overtime & PCB) is functional, statutory contribution accuracy and user lifecycle features remain incomplete.

---

### **2. ✅ Verified Completed Features (Core Payroll)**

The following features have been code-reviewed and confirmed as implemented in `hr/payroll.php` and `includes/functions.php`.

#### **A. Automated Overtime Calculation**

* **Status:** **COMPLETE**
* **Implementation:**
* The system automatically identifies the nature of the day (Normal, Sunday, or Public Holiday) by querying the `attendance` and `public_holidays` tables.
* **Logic Verified:**
* **Normal Day:** 1.5x Hourly Rate.
* **Rest Day (Sunday):** 2.0x Hourly Rate.
* **Public Holiday:** 3.0x Hourly Rate.


* **Code Reference:** `hr/payroll.php` iterates through individual attendance records to apply the correct multiplier per day, ensuring accuracy even if a user works mixed day types in one month.

#### **B. PCB (Tax) Deduction**

* **Status:** **COMPLETE** (Standard Calculation)
* **Implementation:**
* The placeholder logic (`$pcbTax = 0`) has been replaced with a functional `calculatePCB()` call.
* **Logic Verified:**
* Uses LHDN 2024 tax brackets (0% to 28%).
* Includes tax relief for Personal (RM9,000) and Dependents (RM2,000/child).
* Dynamically fetches the dependent count from the employee's profile.

---

### **3. ⚠️ Partial Implementations (Compliance Risks)**

These features exist but may not fully meet strict Malaysian statutory compliance standards without further refinement.

#### **A. Statutory Contributions (EPF / SOCSO / EIS)**

* **Status:** **PARTIAL / APPROXIMATION**
* **Current Issue:** The system calculates these values using flat percentages (e.g., `0.11` for EPF, `0.005` for SOCSO).
* **Compliance Gap:**
* **EPF:** Generally acceptable as a percentage, but specific "Third Schedule" tables often dictate exact rounding rules.
* **SOCSO & EIS:** strictly require **Contribution Tables** (e.g., "Salary RM 2,000–2,100 pays RM 10.35"). Using a flat percentage often results in cent-level errors (e.g., calculating RM 9.87 instead of the required RM 9.90).


* **Action Required:** Implement lookup tables for SOCSO and EIS if 100% compliance is required, or accept the approximation for internal use.

#### **B. Email Notifications**

* **Status:** **PARTIAL**
* **Current State:** The system sends a basic HTML email upon payroll generation.
* **Missing Scope:** No notifications exist for:
* Leave approval/rejection.
* Password reset requests.
* New account registration.

---

### **4. ❌ Missing Modules (Not Started)**

The following features are referenced in the architecture but are currently missing from the codebase.

#### **A. Forgot Password Functionality**

* **Priority:** **HIGH**
* **Problem:** Users cannot reset their passwords if lost.
* **Missing Files:** `auth/forgot-password.php`, `auth/reset-password.php`.
* **Requirement:** Token generation logic, database table for password reset tokens, and email integration.

#### **B. PDF Payslip Generation**

* **Priority:** **MEDIUM**
* **Problem:** Payslips are currently only viewable as HTML or via email body. Staff cannot download a formal PDF document for banking/loan purposes.
* **Requirement:** Integration of `TCPDF` or `DOMPDF` library to generate downloadable files.

#### **C. Database Schema Cleanup**

* **Priority:** **LOW (Technical Debt)**
* **Problem:** The project currently relies on multiple "fix" SQL files (`fix_all_errors.sql`, `add_dependents_column.sql`) rather than a single, clean source of truth.
* **Recommendation:** Consolidate all SQL changes into a single `database/schema.sql` for easier deployment.

---

### **5. Implementation Roadmap (Next Steps)**

To finalize the system, follow this prioritized order:

1. **Immediate Fixes (Compliance):**
* [ ] Decide on SOCSO/EIS: Keep flat rate (easier) or implement lookup tables (compliant).
* [ ] Test the PCB calculation with real salary data to ensure the 2024 tax brackets align with expectations.


2. **Critical User Features:**
* [ ] Create `auth/forgot-password.php` to prevent user lockouts.
* [ ] Implement PDF Payslip generation so HR doesn't have to manually print web pages.


3. **Final Polish:**
* [ ] Expand email notifications to cover Leave and Profile updates.
* [ ] Consolidate database SQL files.

---

## Part 2: Mobile Implementation Guide

### 1. Overview
The staff section of the MI-NES Payroll System has been optimized for mobile devices, providing a responsive and user-friendly experience across all screen sizes.

### 2. Key Features

#### Responsive Navigation
- **Mobile Menu Toggle**: A hamburger menu button appears on mobile devices to access the sidebar
- **Overlay**: When the sidebar is open on mobile, a dark overlay covers the main content
- **Touch-Optimized**: All interactive elements have minimum touch target sizes of 44x44px
- **Auto-Close**: Sidebar automatically closes when a menu item is selected

#### Adaptive Layout
- **Flexible Grid**: Content automatically adjusts to fit smaller screens
- **Stacked Elements**: Form buttons and headers stack vertically on mobile
- **Optimized Typography**: Font sizes adjust for better readability on small screens
- **Compact Cards**: Stats cards and information cards resize appropriately

#### Mobile-Friendly Tables
- **Horizontal Scrolling**: Tables scroll horizontally on small screens
- **Reduced Font Size**: Table text is smaller but still readable
- **Hidden Columns**: Less critical columns are hidden on mobile (using .hide-mobile class)

### 3. File Structure for Mobile

```
assets/css/
  └── staff-mobile.css     # All mobile styles

includes/
  ├── header.php           # Loads mobile CSS + JS
  ├── staff_sidebar.php    # Mobile-ready sidebar
  └── top_navbar.php       # Has toggle button

staff/
  ├── dashboard.php        # ✓ Mobile ready
  ├── attendance.php       # ✓ Mobile ready
  ├── leaves.php           # ✓ Mobile ready
  ├── payslips.php         # ✓ Mobile ready
  └── profile.php          # ✓ Mobile ready
```

### 4. Developer Quick Reference

#### CSS Classes (Bootstrap 5)
```html
<div class="d-block d-md-none">Mobile only</div>
<div class="d-none d-md-block">Desktop only</div>
<div class="hide-mobile">Hide specific element on mobile</div>
```

#### JavaScript Functions
```javascript
toggleSidebar()  // Opens/closes mobile menu
closeSidebar()   // Always closes menu
```

#### Testing Checklist
- [ ] Menu toggle works
- [ ] Sidebar closes on selection
- [ ] Buttons are tappable (44px+)
- [ ] Forms work properly
- [ ] Tables scroll horizontally
- [ ] No horizontal page scroll
- [ ] Landscape mode works

---

## Part 3: Technical Setup Guides

### PDF Generation Library Setup

#### Option 1: TCPDF (Recommended)

TCPDF is a popular PHP library for generating PDF documents.

**Installation via Composer (Recommended)**
```bash
composer require tecnickcom/tcpdf
```

#### Option 2: FPDF (Lightweight Alternative)

1. Download FPDF from: http://www.fpdf.org/
2. Extract fpdf.php to `includes/` directory
3. Include in your code: `require_once '../includes/fpdf.php';`

#### Recommended: TCPDF
For this payroll system, we recommend **TCPDF** because it supports:
- UTF-8 (for Malay language)
- HTML to PDF conversion
- Better styling options
- Professional layouts

#### Installation Steps
1. **Navigate to Project Directory:**
   ```powershell
   cd "c:\Users\User\Documents\SEM 7\INDUSTRIAL THINGS\NES SOLUTION AND NETWORK SDN BHD\payroll system"
   ```
2. **Install TCPDF:**
   ```powershell
   composer require tecnickcom/tcpdf
   ```
3. **Verify Installation:**
   Check that `vendor/tecnickcom/tcpdf/` directory exists.

#### Usage Example
```php
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->AddPage();
$pdf->WriteHTML('<h1>Test PDF</h1>');
$pdf->Output('test.pdf', 'I');
```
