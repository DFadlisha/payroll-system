# 🚀 Zeabur Deployment Guide for MI-NES Payroll System

## 🔍 Troubleshooting 502 Bad Gateway Errors

If you're seeing a **502 Bad Gateway** error, follow these steps:

### Step 1: Access the Diagnostics Page

First, visit the diagnostics page to identify the issue:

```
https://mi-nes.zeabur.app/diagnostics.php
```

This page will check:
- ✅ Environment variables
- ✅ Database connection
- ✅ PHP extensions
- ✅ Port configuration
- ✅ File permissions

### Step 2: Fix Missing Environment Variables

The most common cause of 502 errors is **missing or incorrect environment variables**.

#### In Zeabur Dashboard:

1. Go to your service → **Variables** tab
2. Add these environment variables:

```env
DB_HOST=db.aahaznqptohmkdiqpjnx.supabase.co
DB_PORT=6543
DB_NAME=postgres
DB_USER=postgres
DB_PASS=<your_supabase_database_password>
APP_ENV=production
APP_DEBUG=false
```

⚠️ **CRITICAL**: The `DB_PASS` must be your **Supabase DATABASE password**, NOT your Supabase account login password!

#### How to Get Your Supabase Database Password:

1. Go to [Supabase Dashboard](https://supabase.com/dashboard/project/aahaznqptohmkdiqpjnx/settings/database)
2. Scroll to **Database Settings**
3. If you don't remember your password, click **"Reset Database Password"**
4. Copy the new password and paste it into Zeabur's `DB_PASS` variable

### Step 3: Redeploy

After setting the environment variables:

1. Go to **Deployments** tab in Zeabur
2. Click **"Redeploy"** or push a new commit to trigger redeployment
3. Wait for the deployment to complete
4. Visit the diagnostics page again to verify all checks pass

### Step 4: Verify Port Configuration

Zeabur automatically assigns a port via the `PORT` environment variable. Our Dockerfile is configured to use this automatically.

Check in **Network** tab to see which port your service is listening on. It should match what Zeabur expects.

## 🔧 Common Issues and Solutions

### Issue 1: Database Connection Timeout

**Symptom**: Diagnostics page shows database connection error

**Solution**:
1. Verify your Supabase database is accessible
2. Check if you're using **Transaction Pooler** (port 6543) - recommended for cloud deployments
3. Verify the `DB_HOST` and `DB_PORT` are correct

### Issue 2: Port Mismatch

**Symptom**: Service shows as "running" but returns 502

**Solution**:
1. Ensure the `PORT` environment variable is set by Zeabur (automatic)
2. Check Zeabur logs to see which port Apache is listening on
3. Verify the port matches what Zeabur expects

### Issue 3: Missing PHP Extensions

**Symptom**: Diagnostics page shows missing extensions

**Solution**:
1. The Dockerfile should install all required extensions
2. If any are missing, update the Dockerfile and redeploy

### Issue 4: Application Crashes on Startup

**Symptom**: Service keeps restarting

**Solution**:
1. Check **Logs** tab in Zeabur for error messages
2. Common causes:
   - Syntax error in PHP files
   - Database connection fails and crashes the app
   - Missing files or permissions

## 📊 Monitoring Your Deployment

### Check Logs

In Zeabur Dashboard → **Logs** tab, you should see:

```
Apache/2.4.x configured and listening on port 8080
[timestamp] [core:notice] AH00094: Command line: 'apache2 -D FOREGROUND'
```

### Health Check

The application has a health check endpoint:

```
https://mi-nes.zeabur.app/health.php
```

This should return `OK` with HTTP 200 status.

### Network Tab

In Zeabur Dashboard → **Network** tab:
- Verify the service is listening on the correct port
- Check if the domain is properly mapped

## 🎯 Deployment Checklist

Before deploying, ensure:

- [ ] All environment variables are set in Zeabur
- [ ] Supabase database is accessible
- [ ] Database credentials are correct
- [ ] `.env` file is NOT committed to Git (use `.gitignore`)
- [ ] Dockerfile is up to date
- [ ] All dependencies are in `composer.json`

## 🔐 Security Notes

1. **Never commit `.env` file** - It contains sensitive credentials
2. **Use strong database passwords** - Generate secure passwords
3. **Set `APP_DEBUG=false`** in production - Prevents exposing sensitive error details
4. **Keep dependencies updated** - Run `composer update` regularly

## 📚 Additional Resources

- [Zeabur Documentation](https://zeabur.com/docs)
- [Supabase Documentation](https://supabase.com/docs)
- [PHP Docker Official Image](https://hub.docker.com/_/php)

## 🆘 Still Having Issues?

If you've followed all steps and still see 502 errors:

1. **Check Zeabur Status**: Verify Zeabur services are operational
2. **Contact Support**: Reach out to Zeabur support with:
   - Service name
   - Request ID from the 502 error page
   - Recent deployment logs
   - Results from the diagnostics page

---

**Last Updated**: February 2026  
**Version**: 1.0
