-- ============================================
-- CONSOLIDATED SCHEMA CHANGES
-- ============================================
-- This file contains all necessary database changes 
-- to bring the Supabase schema up to date.
-- ============================================

-- 1. PUBLIC HOLIDAYS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS public_holidays (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    holiday_date DATE NOT NULL UNIQUE,
    holiday_name VARCHAR(255) NOT NULL,
    holiday_type VARCHAR(50) DEFAULT 'national',
    state_code VARCHAR(10),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_public_holidays_date ON public_holidays(holiday_date);
CREATE INDEX IF NOT EXISTS idx_public_holidays_active ON public_holidays(is_active);

-- Insert 2025 Holidays (IF NOT EXISTS logic handled by UNIQUE constraint on holiday_date usually, 
-- but simpler to use ON CONFLICT DO NOTHING for imports)
INSERT INTO public_holidays (holiday_date, holiday_name, holiday_type) VALUES
('2025-01-01', 'New Year''s Day / Tahun Baru', 'national'),
('2025-01-29', 'Chinese New Year / Tahun Baru Cina', 'national'),
('2025-01-30', 'Chinese New Year (Day 2)', 'national'),
('2025-03-31', 'Hari Raya Aidilfitri', 'national'),
('2025-04-01', 'Hari Raya Aidilfitri (Day 2)', 'national'),
('2025-05-01', 'Labour Day / Hari Pekerja', 'national'),
('2025-06-02', 'Agong''s Birthday', 'national'),
('2025-08-31', 'National Day / Hari Merdeka', 'national'),
('2025-09-16', 'Malaysia Day / Hari Malaysia', 'national'),
('2025-12-25', 'Christmas Day / Hari Krismas', 'national')
ON CONFLICT (holiday_date) DO NOTHING;


-- 2. PASSWORD RESETS TABLE
-- ============================================
CREATE TABLE IF NOT EXISTS password_resets (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_password_resets_token ON password_resets(token);


-- 3. PROFILES TABLE UPDATES
-- ============================================

-- Remove problematic Foreign Key if it exists
DO $$ 
BEGIN 
  IF EXISTS (SELECT 1 FROM information_schema.table_constraints WHERE constraint_name = 'profiles_id_fkey') THEN 
    ALTER TABLE profiles DROP CONSTRAINT profiles_id_fkey; 
  END IF; 
END $$;

-- Add Internship Months
ALTER TABLE profiles 
ADD COLUMN IF NOT EXISTS internship_months INTEGER DEFAULT NULL;

-- Add Dependents Column
ALTER TABLE profiles 
ADD COLUMN IF NOT EXISTS dependents INTEGER DEFAULT 0 CHECK (dependents >= 0 AND dependents <= 10);


-- 4. LEAVES TABLE UPDATES
-- ============================================
ALTER TABLE leaves 
ADD COLUMN IF NOT EXISTS total_days INTEGER DEFAULT 0;

-- Recalculate total_days for existing records
UPDATE leaves 
SET total_days = EXTRACT(DAY FROM (end_date - start_date)) + 1
WHERE total_days = 0 OR total_days IS NULL;


-- ============================================
-- END OF SCHEMA
-- ============================================
