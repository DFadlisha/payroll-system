# Payroll System - Calculation Guide

## Overview
This payroll system supports **4 employment types** with different calculation methods based on attendance records:

1. **Permanent Staff** - Full-time employees with monthly salary
2. **Contract Staff** - Fixed-term employees with monthly salary  
3. **Part-Time Staff** - Hourly workers paid by hours worked
4. **Interns** - Trainees with allowances (no statutory deductions)

---

## Payroll Calculation Methods

### 1. Permanent/Contract Staff
**Pay Calculation:**
- **Base Pay:** Monthly basic salary
- **Overtime:** (Basic Salary / 160) × Overtime Hours × 1.5
- **Gross Pay:** Base Pay + Overtime Pay

**Statutory Deductions:**
- ✅ **EPF (Employee Provident Fund):**
  - Employee: 11% of gross pay
  - Employer: 13% (≤RM5,000) or 12% (>RM5,000)
  
- ✅ **SOCSO (Social Security Organization):**
  - Employee: RM5-25 (tiered based on salary)
  - Employer: RM17.50-87.50 (tiered based on salary)
  - Only applicable if gross pay ≤ RM5,000
  
- ✅ **EIS (Employment Insurance System):**
  - Employee: 0.2% (capped at RM4,000)
  - Employer: 0.2% (capped at RM4,000)

**Net Pay:** Gross Pay - (EPF Employee + SOCSO Employee + EIS Employee)

---

### 2. Part-Time Staff
**Pay Calculation:**
- **Regular Pay:** Hourly Rate × Regular Hours
- **Overtime Pay:** Hourly Rate × Overtime Hours × 1.5
- **Gross Pay:** Regular Pay + Overtime Pay

**Statutory Deductions:**
- ✅ **EPF:** Only if monthly gross pay ≥ RM1,000
  - Employee: 11% of gross pay
  - Employer: 13% (≤RM5,000) or 12% (>RM5,000)
  
- ✅ **SOCSO:** Applicable (same rates as permanent staff)
  
- ⚠️ **EIS:** Only if working ≥70 hours per month
  - Employee: 0.2% (capped at RM4,000)
  - Employer: 0.2% (capped at RM4,000)

**Net Pay:** Gross Pay - Applicable Deductions

---

### 3. Interns
**Pay Calculation:**
- **If Hourly Rate Set:** Hourly Rate × Total Hours
- **If Fixed Allowance:** Fixed monthly allowance (Basic Salary)
- **Gross Pay:** Total calculated pay (no overtime multiplier)

**Statutory Deductions:**
- ❌ **No EPF**
- ❌ **No SOCSO**
- ❌ **No EIS**

**Net Pay:** Gross Pay (no deductions)

---

## Attendance-Based Calculation

### How Attendance is Tracked
1. **Clock In/Out:** Employees clock in/out with GPS location
2. **Hours Calculation:** System calculates total hours worked
3. **Overtime Detection:** Hours beyond standard are marked as overtime
4. **Monthly Aggregation:** All attendance records are summed per month

### Payroll Generation Process
```
1. HR clicks "Generate Payroll" button
2. System fetches all employees
3. For each employee:
   - Retrieve attendance records for the month
   - Sum regular hours and overtime hours
   - Apply employment type-specific calculation
   - Calculate statutory deductions (if applicable)
   - Store payroll record
4. Display generated payroll summary
```

---

## Examples

### Example 1: Permanent Staff
**Employee:** John Doe (Permanent)
- Basic Salary: RM3,000
- Regular Hours: 160 hours
- Overtime Hours: 10 hours

**Calculation:**
- Regular Pay: RM3,000
- Overtime Pay: (RM3,000 / 160) × 10 × 1.5 = RM281.25
- **Gross Pay: RM3,281.25**

**Deductions:**
- EPF Employee: RM3,281.25 × 11% = RM360.94
- SOCSO Employee: RM15.00
- EIS Employee: RM6.56
- **Total Deductions: RM382.50**

**Net Pay: RM2,898.75**

---

### Example 2: Part-Time Staff
**Employee:** Jane Smith (Part-Time)
- Hourly Rate: RM15/hour
- Regular Hours: 80 hours
- Overtime Hours: 5 hours

**Calculation:**
- Regular Pay: RM15 × 80 = RM1,200
- Overtime Pay: RM15 × 5 × 1.5 = RM112.50
- **Gross Pay: RM1,312.50**

**Deductions:**
- EPF Employee: RM1,312.50 × 11% = RM144.38 (≥RM1,000)
- SOCSO Employee: RM7.50
- EIS Employee: RM2.63 (≥70 hours)
- **Total Deductions: RM154.51**

**Net Pay: RM1,157.99**

---

### Example 3: Intern
**Employee:** Ali (Intern)
- Fixed Allowance: RM800
- Hours Worked: 120 hours

**Calculation:**
- **Gross Pay: RM800** (fixed allowance)

**Deductions:**
- **None** (interns are exempt)

**Net Pay: RM800**

---

## System Features

### For HR (System View)
1. **Employee Payroll Overview** (`/hr/employees/payroll`)
   - View all employees by employment type
   - See current month attendance hours
   - Track attendance days and total hours
   - Quick access to employee details

2. **Generate Payroll** (`/hr/payroll`)
   - One-click payroll generation for all employees
   - Automatic calculation based on attendance
   - View payroll summary with deductions
   - Export payroll reports

3. **Payroll Records Table**
   - Employee name and type
   - Regular and overtime hours
   - Gross pay and deductions
   - Net pay and status (draft/finalized/paid)

### For Employees (Mobile View)
1. **Attendance Tracking** (`/mobile/attendance`)
   - Clock in/out with location
   - View attendance history
   - See hours worked

2. **Payslips** (`/mobile/payslips`)
   - View all payslips
   - Download payslip records
   - See detailed breakdown of pay and deductions

---

## Database Schema Updates

Run these SQL scripts in order:
1. `006_add_employment_type.sql` - Adds employment_type column
2. `009_add_location_tracking.sql` - Adds location fields to attendance

---

## Configuration

### Standard Work Hours
- Standard month: 160 hours
- Overtime multiplier: 1.5×

### Deduction Thresholds
- Part-time EPF: ≥ RM1,000/month
- Part-time EIS: ≥ 70 hours/month
- SOCSO cap: RM5,000
- EIS cap: RM4,000

---

## Notes

1. **Citizenship Status:** Foreigners are exempt from EPF
2. **Calculation Accuracy:** All amounts rounded to 2 decimal places
3. **Upsert Logic:** Payroll records are upserted to prevent duplicates
4. **Status Flow:** Draft → Finalized → Paid

---

## Support

For payroll calculation questions or issues:
1. Check employee's employment type is set correctly
2. Verify attendance records are marked as "completed"
3. Ensure basic_salary and hourly_rate are configured
4. Review citizenship_status for EPF eligibility

---

**Last Updated:** December 2025
