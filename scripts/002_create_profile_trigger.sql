-- Function to auto-create profile on user signup
create or replace function public.handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  -- Added company_id to profile creation
  insert into public.profiles (id, email, full_name, role, basic_salary, company_id)
  values (
    new.id,
    new.email,
    coalesce(new.raw_user_meta_data->>'full_name', 'New User'),
    coalesce(new.raw_user_meta_data->>'role', 'staff'),
    coalesce((new.raw_user_meta_data->>'basic_salary')::numeric, 0),
    (new.raw_user_meta_data->>'company_id')::uuid
  )
  on conflict (id) do nothing;
  
  return new;
end;
$$;

-- Trigger to call the function
drop trigger if exists on_auth_user_created on auth.users;

create trigger on_auth_user_created
  after insert on auth.users
  for each row
  execute function public.handle_new_user();
