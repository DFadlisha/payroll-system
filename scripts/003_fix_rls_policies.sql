-- Drop existing policies that cause infinite recursion
drop policy if exists "HR can view all profiles" on public.profiles;
drop policy if exists "HR can update profiles" on public.profiles;
drop policy if exists "HR can view all attendance" on public.attendance;
drop policy if exists "HR can view all payroll" on public.payroll;
drop policy if exists "HR can manage payroll" on public.payroll;
drop policy if exists "HR can view all leaves" on public.leaves;
drop policy if exists "HR can update leaves" on public.leaves;

-- Create a security definer function to check if user is HR
-- This function bypasses RLS and prevents infinite recursion
create or replace function public.is_hr()
returns boolean
language plpgsql
security definer
set search_path = public
stable
as $$
begin
  return exists (
    select 1
    from public.profiles
    where id = auth.uid()
    and role = 'hr'
  );
end;
$$;

-- Recreate profiles policies using the security definer function
create policy "HR can view all profiles"
  on public.profiles for select
  using (public.is_hr());

create policy "HR can update profiles"
  on public.profiles for update
  using (public.is_hr());

-- Recreate attendance policies using the security definer function
create policy "HR can view all attendance"
  on public.attendance for select
  using (public.is_hr());

-- Recreate payroll policies using the security definer function
create policy "HR can view all payroll"
  on public.payroll for select
  using (public.is_hr());

create policy "HR can manage payroll"
  on public.payroll for all
  using (public.is_hr());

-- Recreate leaves policies using the security definer function
create policy "HR can view all leaves"
  on public.leaves for select
  using (public.is_hr());

create policy "HR can update leaves"
  on public.leaves for update
  using (public.is_hr());
