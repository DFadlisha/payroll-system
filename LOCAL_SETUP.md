# 🚀 Local Development Setup Guide

Complete guide to run the MI-NES Payroll System on your local Windows machine.

---

## 📋 Prerequisites

Before starting, ensure you have:

- ✅ **PHP 8.0 or higher** (with PostgreSQL extensions)
- ✅ **Composer** (PHP dependency manager)
- ✅ **Supabase account** (for database - already set up)
- ✅ **Git** (for version control)

---

## 🎯 Quick Start (Two Options)

### Option 1: PHP Built-in Server (Recommended - Fastest)

This is the easiest way to run locally without installing XAMPP.

#### Step 1: Install PHP (if not installed)

1. **Check if PHP is installed:**
   ```powershell
   php -v
   ```

2. **If not installed, download PHP:**
   - Go to: https://windows.php.net/download/
   - Download **PHP 8.2 (x64 Thread Safe)**
   - Extract to `C:\php`
   - Add `C:\php` to your **System PATH**

3. **Enable PostgreSQL extensions:**
   - Open `C:\php\php.ini` (copy from `php.ini-development` if doesn't exist)
   - Find and uncomment these lines (remove `;`):
     ```ini
     extension=pdo_pgsql
     extension=pgsql
     extension=openssl
     extension=curl
     extension=mbstring
     extension=zip
     ```
   - Save the file

#### Step 2: Install Composer

1. **Check if Composer is installed:**
   ```powershell
   composer -v
   ```

2. **If not installed:**
   - Download from: https://getcomposer.org/download/
   - Run `Composer-Setup.exe`
   - Follow the installer (it will detect your PHP)

#### Step 3: Install Dependencies

Open PowerShell in your project folder:

```powershell
cd "c:\Users\User\Documents\SEM 7\INDUSTRIAL THINGS\NES SOLUTION AND NETWORK SDN BHD\payroll system"
composer install
```

#### Step 4: Verify Environment Configuration

Your `.env` file is already configured! Just verify:

```env
DB_HOST=db.aahaznqptohmkdiqpjnx.supabase.co
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres
DB_PASS=snjIn5DbfYOT1rXe
APP_ENV=development
```

> ⚠️ **Important:** For local development, we use port **5432** (Direct Connection) instead of 6543. This is fine for local use as you have persistent connections.

#### Step 5: Run the Development Server

```powershell
php -S localhost:8000
```

#### Step 6: Open in Browser

Navigate to: **http://localhost:8000**

✅ You should see the MI-NES Payroll login page!

---

### Option 2: XAMPP (Traditional Method)

If you prefer using XAMPP:

#### Step 1: Install XAMPP

1. Download from: https://www.apachefriends.org/
2. Install to default location: `C:\xampp`
3. Run **XAMPP Control Panel**

#### Step 2: Enable PostgreSQL in PHP

1. Open `C:\xampp\php\php.ini`
2. Find and uncomment (remove `;`):
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```
3. Save and restart Apache from XAMPP Control Panel

#### Step 3: Copy Project to XAMPP

1. Copy your project folder to:
   ```
   C:\xampp\htdocs\payroll-system
   ```

   Or create a **symbolic link** (recommended to keep files in original location):
   ```powershell
   # Run PowerShell as Administrator
   New-Item -ItemType SymbolicLink -Path "C:\xampp\htdocs\payroll-system" -Target "c:\Users\User\Documents\SEM 7\INDUSTRIAL THINGS\NES SOLUTION AND NETWORK SDN BHD\payroll system"
   ```

#### Step 4: Install Dependencies

```powershell
cd C:\xampp\htdocs\payroll-system
composer install
```

#### Step 5: Start Apache

1. Open **XAMPP Control Panel**
2. Click **Start** next to Apache
3. Wait for it to turn green

#### Step 6: Access the App

Navigate to: **http://localhost/payroll-system**

---

## 🧪 Testing the Setup

### 1. Test Database Connection

Visit: **http://localhost:8000/diagnostics.php**

This will show you:
- ✅ Database connection status
- ✅ PHP version and extensions
- ✅ Environment configuration
- ✅ File permissions

### 2. Login with Demo Credentials

| Role | Email | Password |
|------|-------|----------|
| **HR Admin** | `admin@nes.com.my` | `password123` |
| **Staff** | `staff@nes.com.my` | `password123` |
| **Intern** | `intern@nes.com.my` | `password123` |

---

## 🔧 Common Issues & Solutions

### Issue 1: "Could not find driver"

**Error:** `could not find driver (pdo_pgsql)`

**Solution:**
1. Make sure you uncommented `extension=pdo_pgsql` in `php.ini`
2. Restart your server (or Apache if using XAMPP)
3. Check: `php -m | findstr pgsql` (should show `pdo_pgsql` and `pgsql`)

---

### Issue 2: "Connection refused"

**Error:** `Connection refused to Supabase`

**Solution:**
1. Check your internet connection (Supabase is cloud-based)
2. Verify `.env` file has correct credentials
3. Try changing `DB_PORT` to `6543` if 5432 doesn't work locally

---

### Issue 3: Composer not found

**Error:** `'composer' is not recognized`

**Solution:**
1. Reinstall Composer from https://getcomposer.org/
2. Make sure it's added to System PATH
3. Restart PowerShell/Command Prompt

---

### Issue 4: Port 8000 already in use

**Error:** `Failed to listen on localhost:8000`

**Solution:**
Use a different port:
```powershell
php -S localhost:8001
```

Or find what's using port 8000:
```powershell
netstat -ano | findstr :8000
```

---

## 📂 Project Structure

```
payroll-system/
├── auth/              # Login, register, logout
├── config/            # Database & environment config
├── database/          # Seed files and migrations
├── hr/                # HR admin pages
├── includes/          # Shared functions, header, footer
├── shared/            # Shared pages (holidays, etc)
├── staff/             # Staff portal pages
├── vendor/            # Composer dependencies (TCPDF)
├── .env               # Environment variables (DO NOT COMMIT)
├── composer.json      # PHP dependencies
├── Dockerfile         # For cloud deployment
└── index.php          # Landing/login page
```

---

## 🚀 Development Workflow

### Daily Development

1. **Start server:**
   ```powershell
   cd "c:\Users\User\Documents\SEM 7\INDUSTRIAL THINGS\NES SOLUTION AND NETWORK SDN BHD\payroll system"
   php -S localhost:8000
   ```

2. **Make changes** to PHP files

3. **Refresh browser** (PHP doesn't need recompilation)

4. **Check logs** if something breaks:
   - Look at `debug.log` in root folder
   - Check server console output

### Before Deploying

1. **Test locally** with `APP_ENV=development`
2. **Commit changes:**
   ```bash
   git add .
   git commit -m "your message"
   git push
   ```
3. **Zeabur auto-deploys** from GitHub

---

## 🛠️ Useful Commands

### Run development server:
```powershell
php -S localhost:8000
```

### Install/Update dependencies:
```powershell
composer install
composer update
```

### Check PHP version & extensions:
```powershell
php -v
php -m
```

### Clear PHP cache (if needed):
```powershell
Remove-Item -Recurse -Force cache/*
```

### Seed holidays manually:
```powershell
php database/run_seed.php 2026
```

---

## 📚 Additional Resources

- **PHP Manual:** https://www.php.net/manual/en/
- **Supabase Docs:** https://supabase.com/docs
- **Composer Docs:** https://getcomposer.org/doc/

---

## ✅ Quick Checklist

Before you start coding, make sure:

- [ ] PHP 8.0+ installed with `pdo_pgsql` extension
- [ ] Composer installed
- [ ] `composer install` completed successfully
- [ ] `.env` file exists with correct Supabase credentials
- [ ] Server running on `localhost:8000`
- [ ] Can access http://localhost:8000 in browser
- [ ] Database connection works (check `/diagnostics.php`)
- [ ] Can login with demo credentials

---

**Happy Coding! 🎉**

If you encounter any issues not covered here, check `debug.log` or run `/diagnostics.php` for detailed error information.
