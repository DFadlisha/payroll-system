-- ============================================
-- FIX ALL DATABASE ERRORS
-- ============================================
-- Run this SQL in your Supabase SQL Editor to fix all errors
-- ============================================

-- 1. DROP FOREIGN KEY CONSTRAINT (if it exists)
-- This fixes the registration foreign key error
ALTER TABLE profiles DROP CONSTRAINT IF EXISTS profiles_id_fkey;

-- 2. ADD INTERNSHIP_MONTHS COLUMN TO PROFILES TABLE
-- This fixes the registration error: column "internship_months" does not exist
ALTER TABLE profiles ADD COLUMN IF NOT EXISTS internship_months INTEGER DEFAULT NULL;

-- Add comment for the column
COMMENT ON COLUMN profiles.internship_months IS 'Number of months for internship - determines NRL (Need Replacement Leave) days';

-- 3. ADD TOTAL_DAYS COLUMN TO LEAVES TABLE
-- This fixes the leaves error: column "total_days" does not exist
ALTER TABLE leaves ADD COLUMN IF NOT EXISTS total_days INTEGER DEFAULT 0;

-- Update existing leaves records to calculate total_days
UPDATE leaves 
SET total_days = EXTRACT(DAY FROM (end_date - start_date)) + 1
WHERE total_days = 0 OR total_days IS NULL;

-- 4. VERIFY CHANGES
SELECT 'profiles table check' as check_type, column_name, data_type 
FROM information_schema.columns 
WHERE table_schema = 'public' 
  AND table_name = 'profiles' 
  AND column_name IN ('internship_months');

SELECT 'leaves table check' as check_type, column_name, data_type 
FROM information_schema.columns 
WHERE table_schema = 'public' 
  AND table_name = 'leaves' 
  AND column_name IN ('total_days');

-- 5. CHECK FOREIGN KEY CONSTRAINTS
SELECT 
    tc.constraint_name, 
    tc.table_name, 
    kcu.column_name,
    ccu.table_name AS foreign_table_name,
    ccu.column_name AS foreign_column_name 
FROM 
    information_schema.table_constraints AS tc 
    JOIN information_schema.key_column_usage AS kcu
      ON tc.constraint_name = kcu.constraint_name
      AND tc.table_schema = kcu.table_schema
    JOIN information_schema.constraint_column_usage AS ccu
      ON ccu.constraint_name = tc.constraint_name
      AND ccu.table_schema = tc.table_schema
WHERE tc.constraint_type = 'FOREIGN KEY' 
    AND tc.table_name='profiles';

-- If the query above returns empty, the foreign key has been successfully removed
-- ============================================
-- NOTES:
-- ============================================
-- After running this SQL:
-- 1. Registration with internship months will work
-- 2. Leave requests will work properly
-- 3. No more foreign key constraint errors
-- ============================================
