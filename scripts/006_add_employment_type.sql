-- Add employment_type column to profiles table
alter table public.profiles
add column if not exists employment_type text not null default 'permanent'
check (employment_type in ('permanent', 'part-time', 'contract', 'intern'));

-- If column already exists, update the constraint
alter table public.profiles 
drop constraint if exists profiles_employment_type_check;

alter table public.profiles 
add constraint profiles_employment_type_check 
check (employment_type in ('permanent', 'part-time', 'contract', 'intern'));

-- Add comment
comment on column public.profiles.employment_type is 'Employment type: permanent (full-time), part-time, contract, or intern. Interns do not have EPF/SOCSO/EIS deductions.';
