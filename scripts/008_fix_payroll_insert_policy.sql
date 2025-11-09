-- Fix payroll RLS policies to allow HR to insert payroll records

-- Drop the problematic "for all" policy
drop policy if exists "HR can manage payroll in same company" on public.payroll;

-- Create separate policies for different operations

-- HR can insert payroll for employees in their company
create policy "HR can insert payroll in same company"
  on public.payroll for insert
  with check (
    public.is_hr() and 
    user_id in (
      select id from public.profiles where company_id = public.get_user_company_id()
    )
  );

-- HR can update payroll for employees in their company
create policy "HR can update payroll in same company"
  on public.payroll for update
  using (
    public.is_hr() and 
    user_id in (
      select id from public.profiles where company_id = public.get_user_company_id()
    )
  )
  with check (
    public.is_hr() and 
    user_id in (
      select id from public.profiles where company_id = public.get_user_company_id()
    )
  );

-- HR can delete payroll for employees in their company
create policy "HR can delete payroll in same company"
  on public.payroll for delete
  using (
    public.is_hr() and 
    user_id in (
      select id from public.profiles where company_id = public.get_user_company_id()
    )
  );
