# 🚀 Deployment Guide: MI-NES Payroll System

Status: **READY FOR DEPLOYMENT** ✅

Your application is now configured for production. Here is how to deploy it safely.

## 1. Pre-Deployment Check
- [x] **Database Credentials Secured**: We moved hardcoded passwords to `.env`.
- [x] **UI Polished**: Staff and HR dashboards are using the premium glassmorphism theme.
- [x] **Environment Variables**: `.env` file created for local testing.

## 2. Recommended Hosting
Since this is a custom PHP application, we recommend **Railway** or **Heroku** or a standard **VPS (Ubuntu/Nginx)**.

### Option A: Railway (Fastest) 🚅
1. Connect your GitHub repository to Railway.
2. Add a **PHP** service.
3. In Railway "Variables", add these (copy from your `.env`):
   - `DB_HOST`
   - `DB_PORT` (6543 for transaction pooler recommended on production)
   - `DB_USER`
   - `DB_PASS`
   - `DB_NAME`
4. Set `APP_ENV=production`.

### Option B: Traditional Hosting (cPanel/Hostinger) 🌐
1. Upload all files to `public_html`.
2. Edit `.env` on the server with your production database details.
3. Ensure PHP 8.1+ is enabled.

## 3. Important Notes
- **Do not upload the `.env` file** to public GitHub repositories.
- Use the **Transaction Pooler** (Port 6543) for Supabase in production if you have many users.
- Ensure `https` is forced on your domain.

## 4. Post-Deployment
- Log in as the HR Admin (`admin@nes.com.my` / `password123`) to verify access.
- Test the "Clock In" feature on a mobile device to ensure GPS works (requires HTTPS).
