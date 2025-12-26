<?php
/**
 * ============================================
 * FUNGSI HELPER (Helper Functions)
 * ============================================
 * Fail ini mengandungi fungsi-fungsi yang
 * sering digunakan dalam sistem.
 * ============================================
 */

// Load environment configuration
require_once __DIR__ . '/../config/environment.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session parameters
    $sessionLifetime = Environment::get('SESSION_LIFETIME', 7200); // 2 hours default
    
    ini_set('session.cookie_httponly', 1);  // Prevent JavaScript access
    ini_set('session.use_only_cookies', 1); // Only use cookies, not URL
    ini_set('session.cookie_lifetime', $sessionLifetime);
    
    // Enable secure flag in production (HTTPS only)
    if (Environment::isProduction() || Environment::getBool('SESSION_SECURE', false)) {
        ini_set('session.cookie_secure', 1);
    }
    
    // Prevent session fixation attacks
    ini_set('session.use_strict_mode', 1);
    
    // Use stronger session ID
    ini_set('session.sid_length', 48);
    ini_set('session.sid_bits_per_character', 6);
    
    session_start();
    
    // Regenerate session ID periodically (every 30 minutes)
    if (!isset($_SESSION['session_created'])) {
        $_SESSION['session_created'] = time();
    } elseif (time() - $_SESSION['session_created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['session_created'] = time();
    }
    
    // Check for session hijacking
    if (isset($_SESSION['user_id'])) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $userIP = $_SERVER['REMOTE_ADDR'] ?? '';
        
        if (!isset($_SESSION['user_agent'])) {
            $_SESSION['user_agent'] = $userAgent;
            $_SESSION['user_ip'] = $userIP;
        } elseif ($_SESSION['user_agent'] !== $userAgent) {
            // Potential session hijacking - destroy session
            session_unset();
            session_destroy();
            header('Location: /auth/login.php?error=session_invalid');
            exit();
        }
    }
}

/**
 * Semak sama ada pengguna sudah login
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Semak sama ada pengguna adalah HR
 * @return bool
 */
function isHR() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'hr';
}

/**
 * Semak sama ada pengguna adalah Staff
 * @return bool
 */
function isStaff() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'staff';
}

/**
 * Redirect ke halaman lain
 * @param string $url URL untuk redirect
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Redirect jika tidak login
 * @param string $redirectTo URL untuk redirect jika tidak login
 */
function requireLogin($redirectTo = '/auth/login.php') {
    if (!isLoggedIn()) {
        redirect($redirectTo);
    }
}

/**
 * Redirect jika bukan HR
 * @param string $redirectTo URL untuk redirect jika bukan HR
 */
function requireHR($redirectTo = '/staff/dashboard.php') {
    requireLogin();
    if (!isHR()) {
        redirect($redirectTo);
    }
}

/**
 * Redirect jika bukan Staff
 * @param string $redirectTo URL untuk redirect jika bukan Staff
 */
function requireStaff($redirectTo = '/hr/dashboard.php') {
    requireLogin();
    if (!isStaff()) {
        redirect($redirectTo);
    }
}

/**
 * Sanitize input untuk keselamatan
 * @param string $data Data untuk sanitize
 * @return string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Format tarikh ke format Malaysia
 * @param string $date Tarikh dalam format Y-m-d
 * @return string
 */
function formatDate($date) {
    if (empty($date)) return '-';
    return date('d/m/Y', strtotime($date));
}

/**
 * Format masa ke format 12 jam
 * @param string $time Masa dalam format H:i:s
 * @return string
 */
function formatTime($time) {
    if (empty($time)) return '-';
    return date('h:i A', strtotime($time));
}

/**
 * Format wang (Ringgit Malaysia)
 * @param float $amount Jumlah wang
 * @return string
 */
function formatMoney($amount) {
    return 'RM ' . number_format($amount, 2);
}

/**
 * Format nama bulan dalam Bahasa Malaysia
 * @param int $month Nombor bulan (1-12)
 * @return string
 */
function getMonthName($month) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Mac',
        4 => 'April', 5 => 'Mei', 6 => 'Jun',
        7 => 'Julai', 8 => 'Ogos', 9 => 'September',
        10 => 'Oktober', 11 => 'November', 12 => 'Disember'
    ];
    return $months[$month] ?? '';
}

/**
 * Terjemah jenis cuti ke Bahasa Malaysia
 * @param string $type Jenis cuti dalam English
 * @return string
 */
function getLeaveTypeName($type) {
    $types = [
        'annual' => 'Annual Leave',
        'medical' => 'Medical Leave',
        'emergency' => 'Emergency Leave',
        'unpaid' => 'Unpaid Leave',
        'nrl' => 'NRL (Need Replacement Leave)',
        'other' => 'Other'
    ];
    return $types[$type] ?? $type;
}

/**
 * Terjemah status cuti ke Bahasa Malaysia
 * @param string $status Status cuti
 * @return array dengan nama dan warna badge
 */
function getLeaveStatusBadge($status) {
    $statuses = [
        'pending' => ['name' => 'Pending', 'class' => 'bg-warning'],
        'approved' => ['name' => 'Approved', 'class' => 'bg-success'],
        'rejected' => ['name' => 'Rejected', 'class' => 'bg-danger']
    ];
    return $statuses[$status] ?? ['name' => $status, 'class' => 'bg-secondary'];
}

/**
 * Terjemah jenis pekerjaan (employment_type from Supabase)
 * @param string $type Jenis pekerjaan
 * @return string
 */
function getEmploymentTypeName($type) {
    $types = [
        'permanent' => 'Permanent (Full-Time)',
        'contract' => 'Contract',
        'part-time' => 'Part-Time',
        'intern' => 'Intern'
    ];
    return $types[$type] ?? $type;
}

/**
 * Get role display name (Supabase: hr, staff only)
 * @param string $role Role type
 * @return string
 */
function getRoleName($role) {
    $roles = [
        'hr' => 'HR Admin',
        'staff' => 'Staff'
    ];
    return $roles[$role] ?? $role;
}

/**
 * Set mesej flash untuk paparkan
 * @param string $type Jenis mesej (success, error, warning, info)
 * @param string $message Mesej untuk papar
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Dapatkan mesej flash dan padam
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Papar mesej flash sebagai Bootstrap alert
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = [
            'success' => 'alert-success',
            'error' => 'alert-danger',
            'warning' => 'alert-warning',
            'info' => 'alert-info'
        ];
        $class = $alertClass[$flash['type']] ?? 'alert-info';
        echo "<div class='alert {$class} alert-dismissible fade show' role='alert'>";
        echo htmlspecialchars($flash['message']);
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
        echo "</div>";
    }
}

/**
 * Kira umur dari tarikh lahir
 * @param string $birthdate Tarikh lahir
 * @return int
 */
function calculateAge($birthdate) {
    return date_diff(date_create($birthdate), date_create('today'))->y;
}

/**
 * Dapatkan nama hari dalam Bahasa Malaysia
 * @param string $date Tarikh
 * @return string
 */
function getDayName($date) {
    $days = ['Ahad', 'Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu'];
    return $days[date('w', strtotime($date))];
}

/**
 * Kira bilangan hari bekerja dalam bulan
 * (tidak termasuk Sabtu dan Ahad)
 * @param int $month Bulan
 * @param int $year Tahun
 * @return int
 */
function getWorkingDaysInMonth($month, $year) {
    $workDays = 0;
    $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    
    for ($day = 1; $day <= $totalDays; $day++) {
        $date = sprintf('%d-%02d-%02d', $year, $month, $day);
        $dayOfWeek = date('w', strtotime($date));
        // 0 = Ahad, 6 = Sabtu
        if ($dayOfWeek != 0 && $dayOfWeek != 6) {
            $workDays++;
        }
    }
    
    return $workDays;
}

/**
 * Kira tarikh hari ini
 * @return string
 */
function today() {
    return date('Y-m-d');
}

/**
 * Semak sama ada tarikh hari ini
 * @param string $date Tarikh untuk semak
 * @return bool
 */
function isToday($date) {
    return date('Y-m-d', strtotime($date)) === today();
}

/**
 * Generate random string untuk token
 * @param int $length Panjang string
 * @return string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Validate email format
 * @param string $email Email untuk validate
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Get current user data from session
 * @return array
 */
function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'role' => $_SESSION['role'] ?? null,
        'company_id' => $_SESSION['company_id'] ?? null
    ];
}

/**
 * Check if a date is a public holiday
 * @param string $date Date in Y-m-d format
 * @return bool
 */
function isPublicHoliday($date) {
    try {
        $conn = getConnection();
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM public_holidays 
            WHERE holiday_date = ? AND is_active = TRUE
        ");
        $stmt->execute([$date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && intval($result['count']) > 0) {
                return true;
            }
    } catch (PDOException $e) {
        error_log("Public holiday check error: " . $e->getMessage());
        return false;
    }

        // Fallback: use remote holiday lookup (cached)
        $year = date('Y', strtotime($date));
        $holidays = getMalaysiaHolidays($year);
        if (isset($holidays[$date])) {
            // Optionally cache into DB for future faster checks
            try {
                if (isset($conn)) {
                    $stmt = $conn->prepare(
                        "INSERT INTO public_holidays (holiday_date, name, is_active, created_at) VALUES (?, ?, TRUE, NOW()) ON CONFLICT (holiday_date) DO UPDATE SET name = EXCLUDED.name, is_active = TRUE, updated_at = NOW()"
                    );
                    $stmt->execute([$date, $holidays[$date]]);
                }
            } catch (Exception $e) {
                // ignore caching errors
            }
            return true;
        }

        return false;
}

/**
 * Check if a date is Sunday
 * @param string $date Date in Y-m-d format
 * @return bool
 */
function isSunday($date) {
    return date('w', strtotime($date)) == 0;
}

/**
 * Get overtime rate based on day type
 * Malaysian Labour Law:
 * - Normal day: 1.5x
 * - Rest day (Sunday): 2x
 * - Public holiday: 3x
 * 
 * @param string $date Date in Y-m-d format
 * @param float $hourlyRate Base hourly rate
 * @return array ['rate' => multiplier, 'type' => description, 'hourly_rate' => calculated rate]
 */
function getOvertimeRate($date, $hourlyRate) {
    if (isPublicHoliday($date)) {
        return [
            'rate' => 3.0,
            'type' => 'Public Holiday',
            'hourly_rate' => $hourlyRate * 3.0
        ];
    } elseif (isSunday($date)) {
        return [
            'rate' => 2.0,
            'type' => 'Rest Day (Sunday)',
            'hourly_rate' => $hourlyRate * 2.0
        ];
    } else {
        return [
            'rate' => 1.5,
            'type' => 'Normal Day',
            'hourly_rate' => $hourlyRate * 1.5
        ];
    }
}

/**
 * Calculate PCB (Monthly Tax Deduction) based on LHDN 2024 rates
 * This is a simplified calculation. For production, use official LHDN tables.
 * 
 * @param float $monthlyIncome Monthly gross income
 * @param int $dependents Number of dependents (for tax relief)
 * @return float PCB amount to deduct
 */
function calculatePCB($monthlyIncome, $dependents = 0) {
    // Annual income
    $annualIncome = $monthlyIncome * 12;
    
    // Basic personal relief
    $personalRelief = 9000;
    
    // Dependent relief (RM2,000 per dependent, max 6)
    $dependentRelief = min($dependents, 6) * 2000;
    
    // Total relief
    $totalRelief = $personalRelief + $dependentRelief;
    
    // Chargeable income
    $chargeableIncome = max(0, $annualIncome - $totalRelief);
    
    // Malaysian income tax rates 2024
    $tax = 0;
    
    if ($chargeableIncome <= 5000) {
        $tax = 0;
    } elseif ($chargeableIncome <= 20000) {
        $tax = ($chargeableIncome - 5000) * 0.01;
    } elseif ($chargeableIncome <= 35000) {
        $tax = 150 + ($chargeableIncome - 20000) * 0.03;
    } elseif ($chargeableIncome <= 50000) {
        $tax = 600 + ($chargeableIncome - 35000) * 0.06;
    } elseif ($chargeableIncome <= 70000) {
        $tax = 1500 + ($chargeableIncome - 50000) * 0.11;
    } elseif ($chargeableIncome <= 100000) {
        $tax = 3700 + ($chargeableIncome - 70000) * 0.19;
    } elseif ($chargeableIncome <= 150000) {
        $tax = 9400 + ($chargeableIncome - 100000) * 0.25;
    } elseif ($chargeableIncome <= 250000) {
        $tax = 21900 + ($chargeableIncome - 150000) * 0.26;
    } else {
        $tax = 47900 + ($chargeableIncome - 250000) * 0.28;
    }
    
    // Monthly PCB (divide annual tax by 12)
    $monthlyPCB = $tax / 12;
    
    return round($monthlyPCB, 2);
}

/**
 * Send email using PHP mail() or SMTP
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $htmlBody HTML email body
 * @param string $plainBody Plain text email body (optional)
 * @return bool Success status
 */
function sendEmail($to, $subject, $htmlBody, $plainBody = '') {
    try {
        // Get email configuration from environment
        $mailHost = Environment::get('MAIL_HOST', 'smtp.gmail.com');
        $mailPort = Environment::get('MAIL_PORT', '587');
        $mailUsername = Environment::get('MAIL_USERNAME', '');
        $mailPassword = Environment::get('MAIL_PASSWORD', '');
        $mailFromAddress = Environment::get('MAIL_FROM_ADDRESS', 'noreply@company.com');
        $mailFromName = Environment::get('MAIL_FROM_NAME', 'MI-NES Payroll System');
        
        // If SMTP credentials not configured, use simple PHP mail()
        if (empty($mailUsername) || empty($mailPassword)) {
            $headers = "From: {$mailFromName} <{$mailFromAddress}>\r\n";
            $headers .= "Reply-To: {$mailFromAddress}\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            $result = mail($to, $subject, $htmlBody, $headers);
            
            if (!$result) {
                error_log("Email send failed (PHP mail): {$to} - {$subject}");
            }
            
            return $result;
        }
        
        // Use SMTP with authentication (requires additional library like PHPMailer)
        // For now, fall back to PHP mail()
        $headers = "From: {$mailFromName} <{$mailFromAddress}>\r\n";
        $headers .= "Reply-To: {$mailFromAddress}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $result = mail($to, $subject, $htmlBody, $headers);
        
        if ($result) {
            error_log("Email sent successfully: {$to} - {$subject}");
        } else {
            error_log("Email send failed: {$to} - {$subject}");
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate a secure random token
 * @param int $length Token length (default 32)
 * @return string Random token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Create password reset token
 * @param string $email User email
 * @return array|false Returns token info or false on failure
 */
function createPasswordResetToken($email) {
    try {
        $conn = getConnection();
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT id, full_name FROM profiles WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return false;
        }
        
        // Generate token
        $token = generateSecureToken(32);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Insert token
        $stmt = $conn->prepare("
            INSERT INTO password_resets (user_id, email, token, expires_at)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user['id'], $email, $token, $expiresAt]);
        
        return [
            'token' => $token,
            'email' => $email,
            'full_name' => $user['full_name'],
            'expires_at' => $expiresAt
        ];
        
    } catch (PDOException $e) {
        error_log("Create reset token error: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate password reset token
 * @param string $token Reset token
 * @return array|false Returns user info or false if invalid
 */
function validatePasswordResetToken($token) {
    try {
        $conn = getConnection();
        
        $stmt = $conn->prepare("
            SELECT pr.*, p.email, p.full_name
            FROM password_resets pr
            JOIN profiles p ON pr.user_id = p.id
            WHERE pr.token = ?
            AND pr.expires_at > NOW()
            AND pr.used = FALSE
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $reset ?: false;
        
    } catch (PDOException $e) {
        error_log("Validate reset token error: " . $e->getMessage());
        return false;
    }
}

/**
 * Reset password using token
 * @param string $token Reset token
 * @param string $newPassword New password
 * @return bool Success status
 */
function resetPasswordWithToken($token, $newPassword) {
    try {
        $conn = getConnection();
        
        // Validate token
        $reset = validatePasswordResetToken($token);
        
        if (!$reset) {
            return false;
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Update password
        $stmt = $conn->prepare("
            UPDATE profiles 
            SET password = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$hashedPassword, $reset['user_id']]);
        
        // Mark token as used
        $stmt = $conn->prepare("
            UPDATE password_resets 
            SET used = TRUE
            WHERE token = ?
        ");
        $stmt->execute([$token]);
        
        $conn->commit();
        
        return true;
        
    } catch (PDOException $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        error_log("Reset password error: " . $e->getMessage());
        return false;
    }
}
/**
 * Fetch Malaysia public holidays for a given year (cached locally).
 * Uses Nager.Date public API as fallback and caches results under /cache/holidays.
 * @param int|null $year
 * @param bool $force Refresh cache when true
 * @return array keyed by 'Y-m-d' => holiday name
 */
function getMalaysiaHolidays($year = null, $force = false) {
    $year = $year ? intval($year) : intval(date('Y'));
    $cacheDir = __DIR__ . '/../cache/holidays';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }

    $cacheFile = $cacheDir . "/holidays_MY_{$year}.json";

    if (!$force && file_exists($cacheFile)) {
        $raw = @file_get_contents($cacheFile);
        $data = $raw ? json_decode($raw, true) : null;
        if (is_array($data)) {
            return $data;
        }
    }

    $url = "https://date.nager.at/api/v3/PublicHolidays/{$year}/MY";
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'header' => "User-Agent: MI-NES-Payroll/1.0\r\n"
        ]
    ]);

    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) {
        // If remote fetch failed, return empty array (do not overwrite cache)
        return [];
    }

    $items = json_decode($raw, true);
    if (!is_array($items)) return [];

    $result = [];
    foreach ($items as $item) {
        $d = $item['date'] ?? ($item['Date'] ?? null);
        $name = $item['localName'] ?? $item['name'] ?? '';
        if ($d) {
            $result[$d] = $name;
        }
    }

    // Write cache (best-effort)
    @file_put_contents($cacheFile, json_encode($result));

    return $result;
}

?>
