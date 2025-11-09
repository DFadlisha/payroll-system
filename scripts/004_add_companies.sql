-- Create companies table
create table if not exists public.companies (
  id uuid primary key default uuid_generate_v4(),
  name text not null unique,
  logo_url text,
  created_at timestamp with time zone default now(),
  updated_at timestamp with time zone default now()
);

-- Add company_id to profiles table
alter table public.profiles add column if not exists company_id uuid references public.companies(id);

-- Create index for company_id
create index if not exists idx_profiles_company_id on public.profiles(company_id);

-- Enable RLS on companies table
alter table public.companies enable row level security;

-- Companies policies - everyone can view companies
create policy "Anyone can view companies"
  on public.companies for select
  using (true);

-- HR can manage their own company
create policy "HR can update own company"
  on public.companies for update
  using (
    exists (
      select 1 from public.profiles
      where id = auth.uid() and role = 'hr' and company_id = companies.id
    )
  );

-- Insert the two companies
insert into public.companies (name, logo_url) values
  ('NES SOLUTION & NETWORK SDN BHD', 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/NES%20LOGO-WETQC40nvjL4MtJMPq9zKxwBNkKcts.jpg'),
  ('MENTARI INFINITI SDN BHD', 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/MENTARI%20INIFINITI%20LOGO-jrCW8er8HIu1lLSXgokAjPfDojbgc9.png')
on conflict (name) do nothing;
