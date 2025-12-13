-- Add location tracking fields to attendance table
alter table public.attendance
add column clock_in_latitude decimal(10, 8),
add column clock_in_longitude decimal(11, 8),
add column clock_in_address text,
add column clock_out_latitude decimal(10, 8),
add column clock_out_longitude decimal(11, 8),
add column clock_out_address text;

-- Add comments
comment on column public.attendance.clock_in_latitude is 'Latitude coordinate when clocking in';
comment on column public.attendance.clock_in_longitude is 'Longitude coordinate when clocking in';
comment on column public.attendance.clock_in_address is 'Address/location description when clocking in';
comment on column public.attendance.clock_out_latitude is 'Latitude coordinate when clocking out';
comment on column public.attendance.clock_out_longitude is 'Longitude coordinate when clocking out';
comment on column public.attendance.clock_out_address is 'Address/location description when clocking out';

-- Update employment_type to include part-time
alter table public.profiles
drop constraint if exists profiles_employment_type_check;

alter table public.profiles
add constraint profiles_employment_type_check
check (employment_type in ('permanent', 'contract', 'intern', 'part-time'));

-- Update comment
comment on column public.profiles.employment_type is 'Employment type: permanent, contract, intern, or part-time';
