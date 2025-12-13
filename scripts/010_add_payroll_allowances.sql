-- ============================================
-- MI-NES Payroll System: Add Allowances & Rates
-- ============================================
-- This migration adds columns to track various allowances,
-- overtime types, bonuses, and deductions in the payroll table.

-- Add allowance columns to payroll table
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS basic_salary numeric(10, 2) NOT NULL DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS ot_normal_hours numeric(10, 2) DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS ot_sunday_hours numeric(10, 2) DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS ot_public_hours numeric(10, 2) DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS ot_normal numeric(10, 2) DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS ot_sunday numeric(10, 2) DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS ot_public numeric(10, 2) DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS project_bonus numeric(10, 2) DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS projects_completed integer DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS shift_allowance numeric(10, 2) DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS extra_shifts integer DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS attendance_bonus numeric(10, 2) DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS days_good_attendance integer DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS late_deduction numeric(10, 2) DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS minutes_late integer DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS total_allowances numeric(10, 2) DEFAULT 0;
ALTER TABLE public.payroll ADD COLUMN IF NOT EXISTS pcb_tax numeric(10, 2) DEFAULT 0;

-- Create payroll_rates table to store configurable rates
CREATE TABLE IF NOT EXISTS public.payroll_rates (
  id uuid PRIMARY KEY DEFAULT uuid_generate_v4(),
  company_id uuid REFERENCES public.companies(id) ON DELETE CASCADE,
  rate_name text NOT NULL,
  rate_value numeric(10, 2) NOT NULL,
  description text,
  is_active boolean DEFAULT true,
  created_at timestamp with time zone DEFAULT now(),
  updated_at timestamp with time zone DEFAULT now(),
  UNIQUE(company_id, rate_name)
);

-- Enable RLS on payroll_rates
ALTER TABLE public.payroll_rates ENABLE ROW LEVEL SECURITY;

-- Policies for payroll_rates
CREATE POLICY "HR can view company rates"
  ON public.payroll_rates FOR SELECT
  USING (
    EXISTS (
      SELECT 1 FROM public.profiles
      WHERE id = auth.uid() 
      AND role = 'hr'
      AND company_id = payroll_rates.company_id
    )
  );

CREATE POLICY "HR can manage company rates"
  ON public.payroll_rates FOR ALL
  USING (
    EXISTS (
      SELECT 1 FROM public.profiles
      WHERE id = auth.uid() 
      AND role = 'hr'
      AND company_id = payroll_rates.company_id
    )
  );

-- Insert default rates (for companies that don't have custom rates)
-- These match the MI-NES standard rates
INSERT INTO public.payroll_rates (company_id, rate_name, rate_value, description)
SELECT 
  c.id,
  rates.rate_name,
  rates.rate_value,
  rates.description
FROM public.companies c
CROSS JOIN (
  VALUES 
    ('RATE_OT_NORMAL', 10.00, 'Overtime per hour (1.5x equivalent)'),
    ('RATE_OT_SUNDAY', 12.50, 'Sunday overtime per hour (2.0x equivalent)'),
    ('RATE_OT_PUBLIC', 20.00, 'Public holiday overtime per hour'),
    ('RATE_PROJECT', 15.00, 'Bonus per project completed'),
    ('RATE_SHIFT', 10.00, 'Allowance per extra shift'),
    ('RATE_ATTENDANCE', 5.00, 'Good attendance bonus per day'),
    ('RATE_LATE', 1.00, 'Late deduction per minute'),
    ('BASIC_STAFF', 1700.00, 'Default full-time basic salary'),
    ('BASIC_INTERN', 800.00, 'Default internship allowance')
) AS rates(rate_name, rate_value, description)
ON CONFLICT (company_id, rate_name) DO NOTHING;

-- Create index for better performance
CREATE INDEX IF NOT EXISTS idx_payroll_rates_company ON public.payroll_rates(company_id);

-- Add comments for documentation
COMMENT ON TABLE public.payroll_rates IS 'Stores configurable payroll rates per company';
COMMENT ON COLUMN public.payroll.ot_normal IS 'Overtime pay for normal weekday hours (RM10/hr)';
COMMENT ON COLUMN public.payroll.ot_sunday IS 'Overtime pay for Sunday hours (RM12.50/hr)';
COMMENT ON COLUMN public.payroll.ot_public IS 'Overtime pay for public holiday hours (RM20/hr)';
COMMENT ON COLUMN public.payroll.project_bonus IS 'Bonus for completed projects (RM15/project)';
COMMENT ON COLUMN public.payroll.shift_allowance IS 'Allowance for extra shifts (RM10/shift)';
COMMENT ON COLUMN public.payroll.attendance_bonus IS 'Good attendance bonus (RM5/day)';
COMMENT ON COLUMN public.payroll.late_deduction IS 'Deduction for being late (RM1/minute)';
