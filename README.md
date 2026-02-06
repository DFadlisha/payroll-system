# MI-NES Payroll System

**A Modern, Cloud-Ready Payroll Management System for NES Solution & Network Sdn Bhd.**

This system is built with **PHP** (Backend Logic) and **Supabase/PostgreSQL** (Database), designed to be deployed on **Apache/Nginx** web servers (like XAMPP or Render).

---

## ❓ Why Apache? (Architecture Explained)

You might ask: *"Do I need Apache if I have Supabase?"*

**YES, you do.** Here is why:

1.  **Supabase** is your **Database**. It stores the data (employees, attendance, passwords). It is like a "Cabinet" for your files.
2.  **Apache** (or Nginx) is your **Web Server**. It runs the **PHP** code that powers your website. It is like the "Office Manager" that takes user requests, processes them, talks to the Cabinet (Supabase), and shows the result to the user.

**Without Apache, your PHP files (`.php`) cannot run.**

---

## 📋 System Requirements

- **Web Server**: Apache or Nginx (e.g., XAMPP, Laragon, or Render/Heroku)
- **Language**: PHP 7.4 or higher (Extensions required: `pgsql`, `pdo_pgsql`)
- **Database**: Supabase (PostgreSQL)
- **Composer**: For managing PHP dependencies (TCPDF)

---

## 🚀 Installation Guide

### 1. Local Setup (Windows/XAMPP)

1.  **Install XAMPP**: Download from [apachefriends.org](https://www.apachefriends.org/).
2.  **Enable PostgreSQL in PHP**:
    *   Open `C:\xampp\php\php.ini`.
    *   Find `;extension=pdo_pgsql` and `;extension=pgsql`.
    *   Remove the `;` to uncomment them.
    *   Restart Apache.
3.  **Place Files**: Copy the project folder to `C:\xampp\htdocs\payroll-system`.
4.  **Install Dependencies**:
    ```bash
    cd c:\xampp\htdocs\payroll-system
    composer install
    ```
5.  **Configure Environment**:
    *   Rename `.env.example` to `.env`.
    *   Update `DB_HOST`, `DB_USER`, `DB_PASS` with your Supabase credentials.

### 2. Cloud Deployment (Zeabur / Koyeb / Back4App)

1.  **Repository**: Connect your GitHub repository to your chosen cloud provider.
2.  **Runtime**: Ensure **Docker** is selected as the runtime (detected automatically).
3.  **Environment Variables**: Add these in your provider's dashboard:
    *   `DB_HOST`: `db.aahaznqptohmkdiqpjnx.supabase.co`
    *   `DB_PORT`: `6543` (**CRITICAL**: Use 6543 to avoid cloud timeouts)
    *   `DB_NAME`: `postgres`
    *   `DB_USER`: `postgres`
    *   `DB_PASS`: `[YOUR_SUPABASE_PASSWORD]`
    *   `APP_ENV`: `production`

---

## 🧹 Cleanup
I have removed all older redundant guides and utility scripts to keep the project clean for production.

---

## ✨ Features

### 👤 For Staff (Mobile-First Design)
The Staff portal is fully responsive and optimized for mobile devices.
- **Clock In/Out**: GPS-enabled attendance tracking.
- **Payslips**: View and download monthly payslips (PDF).
- **Profile**: Manage details and declare dependents for tax purposes.

### 🏢 For HR Admin
- **Dashboard**: Real-time overview of attendance and pending requests.
- **Payroll Processing**:
    - Automatic calculation of Basic, OT, and Allowances.
    - **Malaysian Statutory Deductions**:
        - **EPF**: 11% (Employee) / 12% (Employer)
        - **SOCSO**: Tiered rates (PERKESO)
        - **EIS**: 0.2%
        - **PCB (MTD)**: 2024 LHDN Progressive Tax Rates (Automated).
- **Reports**: Export payroll summaries and attendance logs to Excel.

---

## 📄 Technical Details

### Overtime (OT) Calculation
The system strictly follows Malaysian Labour Law:
- **Normal Days**: 1.5x Hourly Rate
- **Rest Days (Sunday)**: 2.0x Hourly Rate
- **Public Holidays**: 3.0x Hourly Rate (checked against `public_holidays` table)

### PDF Generation
We use **TCPDF** via Composer to generate professional, Malay-language compatible PDF payslips.
- Library Location: `vendor/tecnickcom/tcpdf`
- Generator Script: `includes/generate_payslip_pdf.php`

### Mobile Implementation
- **Responsive Sidebar**: Automatically hides/shows based on screen width.
- **Touch Targets**: All buttons are optimized for touch (min 44px height).
- **Data Cards**: Tables convert to card views on mobile for better readability.

### Performance Optimizations
- **Holiday API Caching**: The system fetches Malaysian public holidays from Nager.Date API with:
  - **5-second timeout** (reduced from 15s) to prevent blocking
  - **Fast fallback** to hardcoded 2025-2026 holidays if API is slow/unavailable
  - **Smart caching** - only fetches once per year, then stores in database
- **Non-blocking Seeding**: Auto-seeding only triggers when viewing the holidays page for the first time
- **PHP 8.2+ Optimizations**: Uses modern PHP practices (no deprecated functions like `curl_close`)

---

## 🔐 Credentials (Demo)

| Role | Email | Password |
|---|---|---|
| **HR Admin** | `admin@nes.com.my` | `password123` |
| **Staff** | `staff@nes.com.my` | `password123` |
| **Intern** | `intern@nes.com.my` | `password123` |

---

## 🛠 Support

Developed for **NES Solution & Network Sdn Bhd**.
For technical issues, please refer to the `debug.log` file in the root directory.
