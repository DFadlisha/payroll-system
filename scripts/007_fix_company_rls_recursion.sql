-- Drop the problematic policies that cause infinite recursion
drop policy if exists "HR can view profiles in same company" on public.profiles;
drop policy if exists "HR can update profiles in same company" on public.profiles;
drop policy if exists "HR can view attendance in same company" on public.attendance;
drop policy if exists "HR can view payroll in same company" on public.payroll;
drop policy if exists "HR can manage payroll in same company" on public.payroll;
drop policy if exists "HR can view leaves in same company" on public.leaves;
drop policy if exists "HR can update leaves in same company" on public.leaves;

-- Drop the old is_hr function
drop function if exists public.is_hr();

-- Drop the problematic is_hr_same_company function
drop function if exists public.is_hr_same_company(uuid);

-- Create a security definer function to get current user's role and company
-- This function bypasses RLS and prevents infinite recursion
create or replace function public.get_user_role_and_company()
returns table(user_role text, user_company_id uuid)
language plpgsql
security definer
set search_path = public
stable
as $$
begin
  return query
  select role, company_id
  from public.profiles
  where id = auth.uid();
end;
$$;

-- Create a helper function to check if user is HR
create or replace function public.is_hr()
returns boolean
language plpgsql
security definer
set search_path = public
stable
as $$
declare
  user_role text;
begin
  select role into user_role
  from public.profiles
  where id = auth.uid();
  
  return user_role = 'hr';
end;
$$;

-- Create a helper function to get current user's company_id
create or replace function public.get_user_company_id()
returns uuid
language plpgsql
security definer
set search_path = public
stable
as $$
declare
  user_company_id uuid;
begin
  select company_id into user_company_id
  from public.profiles
  where id = auth.uid();
  
  return user_company_id;
end;
$$;

-- Recreate profiles policies using security definer functions
create policy "HR can view profiles in same company"
  on public.profiles for select
  using (
    public.is_hr() and company_id = public.get_user_company_id()
  );

create policy "HR can update profiles in same company"
  on public.profiles for update
  using (
    public.is_hr() and company_id = public.get_user_company_id()
  );

-- Recreate attendance policies with company filtering
create policy "HR can view attendance in same company"
  on public.attendance for select
  using (
    public.is_hr() and 
    user_id in (
      select id from public.profiles where company_id = public.get_user_company_id()
    )
  );

-- Recreate payroll policies with company filtering
create policy "HR can view payroll in same company"
  on public.payroll for select
  using (
    public.is_hr() and 
    user_id in (
      select id from public.profiles where company_id = public.get_user_company_id()
    )
  );

create policy "HR can manage payroll in same company"
  on public.payroll for all
  using (
    public.is_hr() and 
    user_id in (
      select id from public.profiles where company_id = public.get_user_company_id()
    )
  );

-- Recreate leaves policies with company filtering
create policy "HR can view leaves in same company"
  on public.leaves for select
  using (
    public.is_hr() and 
    user_id in (
      select id from public.profiles where company_id = public.get_user_company_id()
    )
  );

create policy "HR can update leaves in same company"
  on public.leaves for update
  using (
    public.is_hr() and 
    user_id in (
      select id from public.profiles where company_id = public.get_user_company_id()
    )
  );
