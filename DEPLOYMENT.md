# 🚀 Deployment Guide: MI-NES Payroll System

Status: **READY FOR DEPLOYMENT** ✅

Your application is now configured for production. Here is how to deploy it safely.

## 1. Pre-Deployment Check
- [x] **Database Credentials Secured**: We moved hardcoded passwords to `.env`.
- [x] **UI Polished**: Staff and HR dashboards are using the premium glassmorphism theme.
- [x] **Environment Variables**: `.env` file created for local testing.

## 2. Recommended Hosting
Since this is a custom PHP application, we recommend **Railway** or **Heroku** or a standard **VPS (Ubuntu/Nginx)**.

### Option A: Render (Free & Recommended) ☁️
**Best for:** Free hosting with continuous deployment from GitHub.

1.  **Sign Up:** Go to [render.com](https://render.com) and sign up with GitHub.
2.  **New Web Service:** Click "New +" -> "Web Service".
3.  **Connect Repo:** Select `DFadlisha/payroll-system`.
4.  **Configure:**
    *   **Name:** `mi-nes-payroll` (or similar)
    *   **Runtime:** `Docker` (It will automatically detect the Dockerfile I just created)
    *   **Region:** Singapore (nearest to Malaysia)
    *   **Instance Type:** Free
5.  **Environment Variables:**
    *   Click "Advanced" or wait for creation, then go to "Environment" tab.
    *   Add the following keys/values:
        - `DB_HOST`: (Your Supabase Host)
        - `DB_PORT`: `6543` (Transaction Pooler) or `5432`
        - `DB_USER`: (Your Supabase User)
        - `DB_PASS`: (Your Supabase Password)
        - `DB_NAME`: `postgres`
        - `APP_ENV`: `production`
6.  **Deploy:** Click "Create Web Service".

*Note: The free tier on Render spins down after 15 minutes of inactivity. The first request after a break might take 30-60 seconds.*

### Option B: Railway (Paid / Trial) 🚅
**Best for:** Production performance, no spin-downs.
1. Connect your GitHub repository to Railway.
2. Add a **New Service** from GitHub.
3. In "Variables", add the same DB credentials as above.
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
