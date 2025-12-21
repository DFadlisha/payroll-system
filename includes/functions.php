<?php
/**
 * ============================================
 * FUNGSI HELPER (Helper Functions)
 * ============================================
 * Fail ini mengandungi fungsi-fungsi yang
 * sering digunakan dalam sistem.
 * ============================================
 */

// Pastikan session bermula
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
?>
