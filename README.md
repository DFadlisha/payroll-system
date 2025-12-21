# MI-NES Payroll System (PHP + Supabase)

Sistem Pengurusan Gaji untuk NES Solution & Network Sdn Bhd.

## 📋 Keperluan Sistem (System Requirements)

- PHP 7.4 atau lebih tinggi (dengan extension pgsql)
- Supabase Account (database PostgreSQL)
- Web server (Apache/Nginx) atau XAMPP/WAMP/Laragon

## 🚀 Cara Pemasangan (Installation)

### 1. Pasang XAMPP (Recommended for Windows)
Download dan pasang XAMPP dari: https://www.apachefriends.org/

### 2. Enable PostgreSQL Extension

1. Buka `php.ini` dalam folder XAMPP (contoh: `C:\xampp\php\php.ini`)
2. Cari line `;extension=pgsql` dan buang `;` di depan
3. Restart Apache

### 3. Konfigurasi Supabase

Edit fail `config/database.php` dan masukkan maklumat dari Supabase Dashboard:

```php
// Pergi ke: Supabase Dashboard > Settings > Database
define('DB_HOST', 'aws-0-ap-southeast-1.pooler.supabase.com');  // Host
define('DB_PORT', '6543');                                        // Port
define('DB_NAME', 'postgres');                                    // Database name
define('DB_USER', 'postgres.your-project-ref');                   // User
define('DB_PASS', 'your-database-password');                      // Password
```

### 4. Jalankan Aplikasi

1. Copy folder ke dalam `C:\xampp\htdocs\payroll`
2. Buka browser dan pergi ke: http://localhost/payroll

## 👤 Akaun Demo

| Peranan | Email | Password |
|---------|-------|----------|
| HR Admin | admin@nes.com.my | password123 |
| Staff | staff@nes.com.my | password123 |
| Intern | intern@nes.com.my | password123 |

## 📁 Struktur Folder

```
payroll-php/
├── auth/                   # Halaman login & logout
│   ├── login.php
│   └── logout.php
├── config/                 # Konfigurasi
│   └── database.php
├── database/               # SQL schema
│   └── database.sql
├── hr/                     # Halaman HR Admin
│   ├── dashboard.php
│   ├── employees.php
│   ├── attendance.php
│   ├── leaves.php
│   ├── payroll.php
│   └── reports.php
├── includes/               # Fail yang dikongsi
│   ├── functions.php       # Helper functions
│   ├── header.php          # HTML header
│   └── footer.php          # HTML footer
├── staff/                  # Halaman Staff
│   ├── dashboard.php
│   ├── attendance.php      # Clock in/out
│   ├── leaves.php          # Permohonan cuti
│   ├── payslips.php        # Slip gaji
│   └── profile.php         # Kemaskini profil
├── index.php               # Entry point
└── README.md               # Dokumentasi
```

## ✨ Ciri-ciri Utama (Features)

### Untuk HR Admin:
- 👥 Urus pekerja (tambah, edit, padam)
- 📊 Lihat kehadiran pekerja
- 📝 Luluskan/tolak permohonan cuti
- 💰 Jana dan urus gaji bulanan
- 📈 Jana laporan

### Untuk Staff:
- ⏰ Clock in/out
- 📅 Lihat rekod kehadiran
- 🏖️ Mohon cuti
- 💵 Lihat slip gaji
- 👤 Kemaskini profil

## 🔒 Keselamatan (Security)

- Password di-hash menggunakan bcrypt
- Session-based authentication
- Input sanitization
- PDO prepared statements (prevent SQL injection)
- XSS protection dengan htmlspecialchars()

## 🇲🇾 Pengiraan Gaji Malaysia

Sistem ini mengikut kadar potongan Malaysia:

| Jenis | Pekerja | Majikan |
|-------|---------|---------|
| KWSP/EPF | 11% | 12% |
| PERKESO/SOCSO | ~0.5% | ~1.75% |
| EIS | 0.2% | 0.2% |

## 📞 Sokongan (Support)

Jika ada masalah, hubungi:
- Email: support@nes.com.my
- Tel: 03-12345678

## 📜 Lesen (License)

Hak Cipta © 2024 NES Solution & Network Sdn Bhd. Semua hak terpelihara.
