-- ============================================
-- NES PAYROLL SYSTEM - FULL DATABASE SCHEMA
-- ============================================
-- This file represents the complete database structure
-- for the NES Payroll System as of December 2025.
-- 
-- TARGET DBMS: PostgreSQL (Supabase)
-- ============================================

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ============================================
-- 1. COMPANIES
-- ============================================
CREATE TABLE IF NOT EXISTS companies (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(255),
    website VARCHAR(255),
    registration_number VARCHAR(50),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================
-- 2. PROFILES (Users)
-- ============================================
-- Links to Supabase Auth via trigger usually, but standalone structure here
CREATE TABLE IF NOT EXISTS profiles (
    id UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
    company_id UUID REFERENCES companies(id),
    email VARCHAR(255) NOT NULL UNIQUE,
    full_name VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'staff' CHECK (role IN ('staff', 'hr', 'leader', 'part_time', 'intern')),
    employment_type VARCHAR(50) DEFAULT 'permanent' CHECK (employment_type IN ('permanent', 'contract', 'part-time', 'intern', 'leader')),
    avatar_url TEXT,
    
    -- Personal & Banking Details
    ic_number VARCHAR(20),
    bank_name VARCHAR(100),
    bank_account_number VARCHAR(50),
    epf_number VARCHAR(50),
    socso_number VARCHAR(50),
    tax_number VARCHAR(50),
    phone VARCHAR(50),
    citizenship_status VARCHAR(20) DEFAULT 'citizen',
    dependents INTEGER DEFAULT 0 CHECK (dependents >= 0),
    
    -- Internship specific
    internship_months INTEGER DEFAULT 0,
    
    -- Salary
    basic_salary NUMERIC(10, 2) DEFAULT 0.00,
    hourly_rate NUMERIC(10, 2) DEFAULT 0.00,
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================
-- 3. WORK LOCATIONS
-- ============================================
CREATE TABLE IF NOT EXISTS work_locations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    company_id UUID NOT NULL REFERENCES companies(id),
    name VARCHAR(255) NOT NULL,
    address TEXT,
    latitude NUMERIC(10, 8),
    longitude NUMERIC(11, 8),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================
-- 4. ATTENDANCE
-- ============================================
CREATE TABLE IF NOT EXISTS attendance (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES profiles(id),
    location_id UUID REFERENCES work_locations(id),
    
    clock_in TIMESTAMP WITH TIME ZONE,
    clock_out TIMESTAMP WITH TIME ZONE,
    
    clock_in_photo TEXT,
    clock_out_photo TEXT,
    clock_in_address TEXT,
    clock_out_address TEXT,
    clock_in_latitude NUMERIC(10, 8),
    clock_in_longitude NUMERIC(11, 8),
    clock_out_latitude NUMERIC(10, 8),
    clock_out_longitude NUMERIC(11, 8),
    
    status VARCHAR(50) DEFAULT 'active', -- active, completed, late, absent
    notes TEXT,
    
    -- Hours Calculation
    total_hours NUMERIC(5, 2) DEFAULT 0,
    regular_hours NUMERIC(5, 2) DEFAULT 0,
    overtime_hours NUMERIC(5, 2) DEFAULT 0, -- Alias for total OT
    
    -- OT Breakdown
    ot_hours NUMERIC(5, 2) DEFAULT 0, -- Normal OT
    ot_sunday_hours NUMERIC(5, 2) DEFAULT 0,
    ot_public_hours NUMERIC(5, 2) DEFAULT 0,
    
    -- Additional Metrics
    late_minutes INTEGER DEFAULT 0,
    project_hours INTEGER DEFAULT 0, -- Used for project count for interns
    extra_shifts INTEGER DEFAULT 0,
    
    -- Verification
    is_verified BOOLEAN DEFAULT FALSE,
    verification_notes TEXT,
    
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================
-- 5. LEAVES
-- ============================================
CREATE TABLE IF NOT EXISTS leaves (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES profiles(id),
    
    -- UPDATED: Added medical and nrl
    leave_type VARCHAR(50) NOT NULL CHECK (leave_type IN ('annual', 'sick', 'medical', 'emergency', 'unpaid', 'nrl', 'compassionate', 'hospitalization', 'maternity', 'paternity', 'other')),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    days INTEGER NOT NULL, -- total_days alias
    total_days INTEGER GENERATED ALWAYS AS (days) STORED, -- Postgres 12+ or just use application logic
    
    reason TEXT,
    status VARCHAR(50) DEFAULT 'pending', -- pending, approved, rejected
    rejection_reason TEXT,
    
    approved_by UUID REFERENCES profiles(id),
    approved_at TIMESTAMP WITH TIME ZONE,
    
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================
-- 6. PAYROLL (Payslips)
-- ============================================
CREATE TABLE IF NOT EXISTS payroll (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES profiles(id),
    
    month INTEGER NOT NULL,
    year INTEGER NOT NULL,
    
    -- Earnings
    basic_salary NUMERIC(10, 2) DEFAULT 0,
    gross_pay NUMERIC(10, 2) DEFAULT 0,
    net_pay NUMERIC(10, 2) DEFAULT 0,
    
    -- Hours Snapshot
    regular_hours NUMERIC(5, 2) DEFAULT 0,
    overtime_hours NUMERIC(5, 2) DEFAULT 0,
    ot_normal_hours NUMERIC(5, 2) DEFAULT 0,
    ot_sunday_hours NUMERIC(5, 2) DEFAULT 0,
    ot_public_hours NUMERIC(5, 2) DEFAULT 0,
    
    -- Amounts
    ot_normal NUMERIC(10, 2) DEFAULT 0,
    ot_sunday NUMERIC(10, 2) DEFAULT 0,
    ot_public NUMERIC(10, 2) DEFAULT 0,
    
    -- Allowances / Bonuses
    project_bonus NUMERIC(10, 2) DEFAULT 0,
    projects_completed INTEGER DEFAULT 0,
    shift_allowance NUMERIC(10, 2) DEFAULT 0,
    extra_shifts INTEGER DEFAULT 0,
    attendance_bonus NUMERIC(10, 2) DEFAULT 0,
    days_good_attendance INTEGER DEFAULT 0,
    
    -- Deductions
    late_deduction NUMERIC(10, 2) DEFAULT 0,
    minutes_late INTEGER DEFAULT 0,
    
    -- Contributions (Deductions/Employer Share)
    epf_employee NUMERIC(10, 2) DEFAULT 0,
    epf_employer NUMERIC(10, 2) DEFAULT 0,
    socso_employee NUMERIC(10, 2) DEFAULT 0,
    socso_employer NUMERIC(10, 2) DEFAULT 0,
    eis_employee NUMERIC(10, 2) DEFAULT 0,
    eis_employer NUMERIC(10, 2) DEFAULT 0,
    pcb_tax NUMERIC(10, 2) DEFAULT 0,
    
    total_allowances NUMERIC(10, 2) DEFAULT 0,
    
    status VARCHAR(50) DEFAULT 'draft', -- draft, finalized, paid
    
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================
-- 7. PAYROLL RATES (Configuration)
-- ============================================
CREATE TABLE IF NOT EXISTS payroll_rates (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    company_id UUID REFERENCES companies(id),
    rate_name VARCHAR(100) NOT NULL, -- RATE_OT_NORMAL, RATE_LATE, etc.
    rate_value NUMERIC(10, 2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(company_id, rate_name)
);

-- ============================================
-- 8. PUBLIC HOLIDAYS
-- ============================================
CREATE TABLE IF NOT EXISTS public_holidays (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    holiday_date DATE NOT NULL UNIQUE,
    holiday_name VARCHAR(255) NOT NULL, -- Renamed from 'name' to match schema.sql
    name VARCHAR(255) GENERATED ALWAYS AS (holiday_name) STORED, -- Alias for code compat if needed
    holiday_type VARCHAR(50) DEFAULT 'national',
    state_code VARCHAR(10),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================
-- 9. PASSWORD RESETS
-- ============================================
CREATE TABLE IF NOT EXISTS password_resets (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES profiles(id),
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 10. NOTIFICATIONS
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES profiles(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    link TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- ============================================
-- 11. INDEXES
-- ============================================
CREATE INDEX IF NOT EXISTS idx_attendance_user_date ON attendance(user_id, clock_in);
CREATE INDEX IF NOT EXISTS idx_payroll_user_period ON payroll(user_id, month, year);
CREATE INDEX IF NOT EXISTS idx_leaves_user_date ON leaves(user_id, start_date);
CREATE INDEX IF NOT EXISTS idx_profiles_role ON profiles(role);
CREATE INDEX IF NOT EXISTS idx_profiles_email ON profiles(email);
