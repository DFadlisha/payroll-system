Local environment setup
-----------------------

Copy `.env.local.example` to `.env.local` and fill in your Supabase project details:

```
NEXT_PUBLIC_SUPABASE_URL=https://your-project.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=eyJ...your_anon_key
NEXT_PUBLIC_DEV_SUPABASE_REDIRECT_URL=http://localhost:3000/auth/verify
```

After creating `.env.local` restart the dev server:

```powershell
cd 'C:\tmp\payroll'
npm run dev
```

If you don't have a Supabase project yet, you can use the development fallback (only in dev mode) which populates a demo company in the login/signup select so you can try the UI without Supabase.
