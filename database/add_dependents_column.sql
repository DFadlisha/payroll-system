-- Add dependents column to profiles table for PCB tax calculation
-- This field tracks number of dependents for Malaysian income tax relief
-- Each dependent provides RM2,000 annual tax relief (max 6 dependents)

ALTER TABLE profiles 
ADD COLUMN IF NOT EXISTS dependents INTEGER DEFAULT 0 CHECK (dependents >= 0 AND dependents <= 10);

COMMENT ON COLUMN profiles.dependents IS 'Number of tax dependents for PCB calculation (Malaysian LHDN tax relief)';
