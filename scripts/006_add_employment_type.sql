-- Add employment_type column to profiles table
alter table public.profiles
add column employment_type text not null default 'permanent'
check (employment_type in ('permanent', 'contract', 'intern'));

-- Add comment
comment on column public.profiles.employment_type is 'Employment type: permanent, contract, or intern. Interns do not have EPF/SOCSO/EIS deductions.';
