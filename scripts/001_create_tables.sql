-- Enable UUID extension
create extension if not exists "uuid-ossp";

-- Create profiles table (extends auth.users)
create table if not exists public.profiles (
  id uuid primary key references auth.users(id) on delete cascade,
  email text not null,
  full_name text not null,
  role text not null check (role in ('staff', 'hr')),
  epf_number text,
  socso_number text,
  citizenship_status text check (citizenship_status in ('citizen', 'permanent_resident', 'foreigner')),
  basic_salary numeric(10, 2) not null default 0,
  hourly_rate numeric(10, 2),
  created_at timestamp with time zone default now(),
  updated_at timestamp with time zone default now()
);

-- Create attendance table
create table if not exists public.attendance (
  id uuid primary key default uuid_generate_v4(),
  user_id uuid not null references public.profiles(id) on delete cascade,
  clock_in timestamp with time zone not null,
  clock_out timestamp with time zone,
  total_hours numeric(10, 2),
  overtime_hours numeric(10, 2) default 0,
  status text default 'active' check (status in ('active', 'completed')),
  created_at timestamp with time zone default now()
);

-- Create payroll table
create table if not exists public.payroll (
  id uuid primary key default uuid_generate_v4(),
  user_id uuid not null references public.profiles(id) on delete cascade,
  month integer not null,
  year integer not null,
  regular_hours numeric(10, 2) not null default 0,
  overtime_hours numeric(10, 2) not null default 0,
  gross_pay numeric(10, 2) not null default 0,
  epf_employee numeric(10, 2) not null default 0,
  epf_employer numeric(10, 2) not null default 0,
  socso_employee numeric(10, 2) not null default 0,
  socso_employer numeric(10, 2) not null default 0,
  eis_employee numeric(10, 2) not null default 0,
  eis_employer numeric(10, 2) not null default 0,
  net_pay numeric(10, 2) not null default 0,
  status text default 'draft' check (status in ('draft', 'finalized', 'paid')),
  created_at timestamp with time zone default now(),
  updated_at timestamp with time zone default now(),
  unique(user_id, month, year)
);

-- Create leaves table
create table if not exists public.leaves (
  id uuid primary key default uuid_generate_v4(),
  user_id uuid not null references public.profiles(id) on delete cascade,
  leave_type text not null check (leave_type in ('annual', 'sick', 'emergency', 'unpaid')),
  start_date date not null,
  end_date date not null,
  days numeric(3, 1) not null,
  reason text not null,
  status text default 'pending' check (status in ('pending', 'approved', 'rejected')),
  reviewed_by uuid references public.profiles(id),
  reviewed_at timestamp with time zone,
  created_at timestamp with time zone default now()
);

-- Enable Row Level Security
alter table public.profiles enable row level security;
alter table public.attendance enable row level security;
alter table public.payroll enable row level security;
alter table public.leaves enable row level security;

-- Profiles policies
create policy "Users can view own profile"
  on public.profiles for select
  using (auth.uid() = id);

create policy "HR can view all profiles"
  on public.profiles for select
  using (
    exists (
      select 1 from public.profiles
      where id = auth.uid() and role = 'hr'
    )
  );

create policy "HR can update profiles"
  on public.profiles for update
  using (
    exists (
      select 1 from public.profiles
      where id = auth.uid() and role = 'hr'
    )
  );

create policy "Users can update own profile"
  on public.profiles for update
  using (auth.uid() = id);

-- Attendance policies
create policy "Users can view own attendance"
  on public.attendance for select
  using (auth.uid() = user_id);

create policy "HR can view all attendance"
  on public.attendance for select
  using (
    exists (
      select 1 from public.profiles
      where id = auth.uid() and role = 'hr'
    )
  );

create policy "Users can insert own attendance"
  on public.attendance for insert
  with check (auth.uid() = user_id);

create policy "Users can update own attendance"
  on public.attendance for update
  using (auth.uid() = user_id);

-- Payroll policies
create policy "Users can view own payroll"
  on public.payroll for select
  using (auth.uid() = user_id);

create policy "HR can view all payroll"
  on public.payroll for select
  using (
    exists (
      select 1 from public.profiles
      where id = auth.uid() and role = 'hr'
    )
  );

create policy "HR can manage payroll"
  on public.payroll for all
  using (
    exists (
      select 1 from public.profiles
      where id = auth.uid() and role = 'hr'
    )
  );

-- Leaves policies
create policy "Users can view own leaves"
  on public.leaves for select
  using (auth.uid() = user_id);

create policy "HR can view all leaves"
  on public.leaves for select
  using (
    exists (
      select 1 from public.profiles
      where id = auth.uid() and role = 'hr'
    )
  );

create policy "Users can insert own leaves"
  on public.leaves for insert
  with check (auth.uid() = user_id);

create policy "HR can update leaves"
  on public.leaves for update
  using (
    exists (
      select 1 from public.profiles
      where id = auth.uid() and role = 'hr'
    )
  );

-- Create indexes for better performance
create index idx_attendance_user_id on public.attendance(user_id);
create index idx_attendance_clock_in on public.attendance(clock_in);
create index idx_payroll_user_id on public.payroll(user_id);
create index idx_payroll_month_year on public.payroll(month, year);
create index idx_leaves_user_id on public.leaves(user_id);
create index idx_leaves_status on public.leaves(status);
