# Deployment Guide - MI-NES Payroll System

## Option 1: Railway.app (Recommended - Easiest)

### Step 1: Sign Up
1. Go to [railway.app](https://railway.app)
2. Sign up with GitHub

### Step 2: Create New Project
1. Click "New Project"
2. Select "Deploy from GitHub repo"
3. Choose your `payroll-system` repository

### Step 3: Add Environment Variables
Click on your service → Variables → Add these:

```
DB_HOST=db.aahaznqptohmkdiqpjnx.supabase.co
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres
DB_PASS=ZGdRerSZQfxtoHKs
APP_ENV=production
APP_DEBUG=false
```

### Step 4: Deploy
- Railway will auto-deploy
- You'll get a URL like: `https://your-app.up.railway.app`

**That's it!** Railway handles everything automatically.

---

## Option 2: Vercel (Serverless - Requires Modification)

### Requirements
- Need to convert to serverless functions
- Not recommended for this project (requires major refactoring)

---

## Option 3: InfinityFree (Traditional Hosting - Very Simple)

### Step 1: Sign Up
1. Go to [infinityfree.net](https://infinityfree.net)
2. Create free account

### Step 2: Create Hosting Account
1. Choose subdomain or use custom domain
2. Wait for account activation (instant)

### Step 3: Upload Files
1. Use File Manager or FTP
2. Upload all files to `htdocs` folder
3. Create `.env` file with your Supabase credentials

### Step 4: Configure
- Edit `.env` with your database details
- That's it!

**Pros:** Works exactly like XAMPP
**Cons:** Slower, limited resources

---

## Option 4: Fly.io (Better than Render)

### Step 1: Install Fly CLI
```bash
# Windows (PowerShell)
iwr https://fly.io/install.ps1 -useb | iex
```

### Step 2: Login
```bash
fly auth login
```

### Step 3: Launch App
```bash
cd "c:\Users\User\Documents\SEM 7\INDUSTRIAL THINGS\NES SOLUTION AND NETWORK SDN BHD\payroll system"
fly launch
```

### Step 4: Set Secrets
```bash
fly secrets set DB_HOST=db.aahaznqptohmkdiqpjnx.supabase.co
fly secrets set DB_PORT=5432
fly secrets set DB_USER=postgres
fly secrets set DB_PASS=ZGdRerSZQfxtoHKs
```

### Step 5: Deploy
```bash
fly deploy
```

---

## Comparison Table

| Platform | Ease of Use | Free Tier | Speed | Supabase Compatibility |
|----------|-------------|-----------|-------|------------------------|
| **Railway** | ⭐⭐⭐⭐⭐ | $5/month credit | Fast | ✅ Excellent |
| **Render** | ⭐⭐⭐ | 750 hours/month | Medium | ⚠️ IPv6 Issues |
| **Fly.io** | ⭐⭐⭐⭐ | 3 VMs free | Fast | ✅ Good |
| **InfinityFree** | ⭐⭐⭐⭐⭐ | Unlimited | Slow | ✅ Good |
| **Vercel** | ⭐⭐ | Generous | Very Fast | ⚠️ Requires Refactoring |

---

## Recommended: Switch to Railway

Railway is the easiest and most reliable option for your PHP + Supabase setup.

**Next Steps:**
1. Go to railway.app
2. Connect your GitHub
3. Deploy in 2 minutes
4. Done! ✅
