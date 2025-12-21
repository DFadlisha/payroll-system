-- ============================================
-- FIX: Remove Foreign Key Constraint from profiles table
-- ============================================
-- This fixes the registration error:
-- "insert or update on table profiles violates foreign key constraint profiles_id_fkey"
--
-- Run this SQL in your Supabase SQL Editor
-- ============================================

-- Drop the foreign key constraint
ALTER TABLE profiles DROP CONSTRAINT IF EXISTS profiles_id_fkey;

-- Verify the constraint is removed
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

-- If you see any remaining foreign keys above, you're good if they're NOT profiles_id_fkey
-- If the result is empty, the foreign key has been successfully removed
