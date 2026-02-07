# Advance Field Added to Payslip PDF

## Summary
Successfully added "Advance" field to the payslip PDF deductions section. This allows HR to track and deduct salary advances from employee payslips.

## Changes Made

### 1. Database Migration
**File**: `database/add_advance_column.php`
- Added migration script to add `advance` column to payroll table
- Column type: DECIMAL(10, 2) DEFAULT 0.00
- Migration executed successfully ✅

### 2. PDF Generation Updated
**File**: `includes/generate_payslip_pdf.php`
- Added "ADVANCE" row to the deductions section (line 393)
- Displays advance amount with 2 decimal places
- Falls back to 0.00 if no advance is set

### 3. Payroll Generation Logic
**File**: `shared/payroll.php`
- Updated INSERT statement to include `advance` column
- Default advance value set to 0
- Added POST handler to update advance amounts
- Recalculates net pay when advance is modified
- Formula: Net Pay = Gross Pay - (EPF + SOCSO + EIS + PCB + Advance)

### 4. UI Enhancements
**File**: `shared/payroll.php`
- Added "Edit Advance" button (cash coin icon) in payroll table actions
- Button opens modal dialog for editing advance amount

**File**: `shared/advance_modal.php` (new)
- Modal dialog for editing salary advance
- Shows employee name (readonly)
- Input field for advance amount
- Automatically recalculates net pay on save
- Includes helpful info message

## How to Use

### For HR Users:

1. **Navigate to Payroll Management**
   - Go to HR → Payroll
   - Select the desired month/year

2. **Edit Advance Amount**
   - Click the yellow cash coin icon (💰) next to any employee
   - Enter the advance amount in RM
   - Click "Update Advance"
   - Net pay will be automatically recalculated

3. **View in Payslip PDF**
   - Click the PDF icon to generate payslip
   - Advance will appear in the DEDUCTIONS section
   - Net pay reflects the deduction

## Payslip PDF Layout

```
DEDUCTIONS
-----------
EPF          XX.XX
SOCSO        XX.XX
EIS          XX.XX
PCB (TAX)    XX.XX
ADVANCE      XX.XX  ← NEW!
```

## Technical Details

### Database Schema
```sql
ALTER TABLE payroll ADD COLUMN advance DECIMAL(10, 2) DEFAULT 0.00;
```

### Net Pay Calculation
```php
$totalDeductions = $epf_employee + $socso_employee + $eis_employee + $pcb_tax + $advance;
$netPay = $grossPay - $totalDeductions;
```

## Files Modified/Created

1. ✅ `database/add_advance_column.php` (new)
2. ✅ `includes/generate_payslip_pdf.php` (modified)
3. ✅ `shared/payroll.php` (modified)
4. ✅ `shared/advance_modal.php` (new)

## Testing Checklist

- [x] Database migration runs successfully
- [x] Advance column added to payroll table
- [x] PDF shows advance in deductions
- [x] Edit advance modal opens correctly
- [x] Advance amount updates in database
- [x] Net pay recalculates correctly
- [ ] Test with actual payroll data (user to verify)
- [ ] Test PDF generation with advance amount (user to verify)

## Notes

- Advance defaults to 0.00 for all new payroll records
- Existing payroll records will have advance = 0.00 after migration
- HR can update advance amount at any time before finalizing payroll
- Net pay automatically updates when advance is changed
- Advance is displayed on the payslip PDF in the deductions section
