-- ============================================
-- DATABASE MIGRATION: Full Payroll System
-- MI-NES Payroll System
-- Run this SQL in your Supabase SQL Editor
-- ============================================

-- ============================================
-- ATTENDANCE TABLE UPDATES
-- ============================================
ALTER TABLE attendance 
ADD COLUMN IF NOT EXISTS ot_hours DECIMAL(5,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS ot_sunday_hours DECIMAL(5,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS ot_public_hours DECIMAL(5,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS project_hours DECIMAL(5,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS extra_shifts INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS late_minutes INTEGER DEFAULT 0;

-- ============================================
-- PAYROLL TABLE UPDATES
-- ============================================
ALTER TABLE payroll 
ADD COLUMN IF NOT EXISTS ot_hours DECIMAL(5,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS ot_allowance DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS ot_sunday_hours DECIMAL(5,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS ot_sunday_allowance DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS ot_public_hours DECIMAL(5,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS ot_public_allowance DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS project_hours DECIMAL(5,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS project_allowance DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS extra_shifts INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS shift_allowance DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS attendance_bonus DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS late_minutes INTEGER DEFAULT 0,
ADD COLUMN IF NOT EXISTS late_deduction DECIMAL(10,2) DEFAULT 0;

-- ============================================
-- USERS TABLE UPDATES
-- ============================================
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS internship_months INTEGER DEFAULT NULL,
ADD COLUMN IF NOT EXISTS epf_number VARCHAR(50) DEFAULT NULL;

-- Add comment for EPF tracking
COMMENT ON COLUMN users.epf_number IS 'EPF/KWSP number - Required for Staff and Part-Time employees';

-- ============================================
-- RATE REFERENCE (from payroll_rates table):
-- ============================================
-- BASIC_STAFF: RM 1,700.00 (Default full-time basic salary)
-- BASIC_INTERN: RM 800.00 (Default internship allowance)
-- RATE_OT_NORMAL: RM 10.00 (Overtime per hour - 1.5x equivalent)
-- RATE_OT_SUNDAY: RM 12.50 (Sunday overtime per hour - 2.0x equivalent)
-- RATE_OT_PUBLIC: RM 20.00 (Public holiday overtime per hour)
-- RATE_PROJECT: RM 15.00 (Bonus per project completed)
-- RATE_SHIFT: RM 10.00 (Allowance per extra shift)
-- RATE_ATTENDANCE: RM 5.00 (Good attendance bonus per day)
-- RATE_LATE: RM 1.00 (Late deduction per minute)
-- ============================================

-- ============================================
-- DAILY RATES (hardcoded):
-- ============================================
-- Part-Time: RM 70.83 per day
-- Intern: RM 33.33 per day
-- ============================================

-- ============================================
-- LEAVE POLICY:
-- ============================================
-- Full-Time Staff: 14 days Annual Leave, 14 days Medical Leave
-- Part-Time: 14 days Annual Leave, 14 days Medical Leave
-- Intern: NRL (Need Replacement Leave) = internship_months days
--         e.g., 3 months internship = 3 days NRL
-- ============================================

-- ============================================
-- PAYROLL FORMULA:
-- ============================================
-- GROSS SALARY = Basic Salary 
--              + OT Normal Allowance (ot_hours × RM10)
--              + OT Sunday Allowance (ot_sunday_hours × RM12.50)
--              + OT Public Allowance (ot_public_hours × RM20)
--              + Project Allowance (project_hours × RM15)
--              + Shift Allowance (extra_shifts × RM10)
--              + Attendance Bonus (days_worked × RM5)
--
-- DEDUCTIONS = EPF Employee (11%)
--            + SOCSO Employee (0.5%, max RM39.35)
--            + EIS Employee (0.2%)
--            + Late Deduction (late_minutes × RM1)
--
-- NET SALARY = GROSS SALARY - DEDUCTIONS
-- ============================================

-- Verify the changes
SELECT 'attendance' as table_name, column_name, data_type 
FROM information_schema.columns 
WHERE table_schema = 'public' AND table_name = 'attendance' 
AND column_name IN ('ot_hours', 'ot_sunday_hours', 'ot_public_hours', 'project_hours', 'extra_shifts', 'late_minutes')
UNION ALL
SELECT 'payroll' as table_name, column_name, data_type 
FROM information_schema.columns 
WHERE table_schema = 'public' AND table_name = 'payroll' 
AND column_name IN ('ot_hours', 'ot_allowance', 'ot_sunday_hours', 'ot_sunday_allowance', 'ot_public_hours', 'ot_public_allowance', 
                    'project_hours', 'project_allowance', 'extra_shifts', 'shift_allowance', 'attendance_bonus', 'late_minutes', 'late_deduction')
UNION ALL
SELECT 'users' as table_name, column_name, data_type 
FROM information_schema.columns 
WHERE table_schema = 'public' AND table_name = 'users' AND column_name = 'internship_months';
