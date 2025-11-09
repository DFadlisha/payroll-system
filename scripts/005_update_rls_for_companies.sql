-- Drop existing policies that don't consider company_id
drop policy if exists "HR can view all profiles" on public.profiles;
drop policy if exists "HR can update profiles" on public.profiles;
drop policy if exists "HR can view all attendance" on public.attendance;
drop policy if exists "HR can view all payroll" on public.payroll;
drop policy if exists "HR can manage payroll" on public.payroll;
drop policy if exists "HR can view all leaves" on public.leaves;
drop policy if exists "HR can update leaves" on public.leaves;

-- Create helper function to check if user is HR in same company
create or replace function is_hr_same_company(target_user_id uuid)
returns boolean
language plpgsql
security definer
as $$
declare
  current_user_company_id uuid;
  current_user_role text;
  target_user_company_id uuid;
begin
  -- Get current user's company and role
  select company_id, role into current_user_company_id, current_user_role
  from public.profiles
  where id = auth.uid();
  
  -- Get target user's company
  select company_id into target_user_company_id
  from public.profiles
  where id = target_user_id;
  
  -- Return true if current user is HR and in same company as target user
  return current_user_role = 'hr' and current_user_company_id = target_user_company_id;
end;
$$;

-- Updated profiles policies with company filtering
create policy "HR can view profiles in same company"
  on public.profiles for select
  using (
    company_id in (
      select company_id from public.profiles where id = auth.uid() and role = 'hr'
    )
  );

create policy "HR can update profiles in same company"
  on public.profiles for update
  using (
    company_id in (
      select company_id from public.profiles where id = auth.uid() and role = 'hr'
    )
  );

-- Updated attendance policies with company filtering
create policy "HR can view attendance in same company"
  on public.attendance for select
  using (
    is_hr_same_company(user_id)
  );

-- Updated payroll policies with company filtering
create policy "HR can view payroll in same company"
  on public.payroll for select
  using (
    is_hr_same_company(user_id)
  );

create policy "HR can manage payroll in same company"
  on public.payroll for all
  using (
    is_hr_same_company(user_id)
  );

-- Updated leaves policies with company filtering
create policy "HR can view leaves in same company"
  on public.leaves for select
  using (
    is_hr_same_company(user_id)
  );

create policy "HR can update leaves in same company"
  on public.leaves for update
  using (
    is_hr_same_company(user_id)
  );
