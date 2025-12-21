<?php
/**
 * ============================================
 * LOGOUT
 * ============================================
 * Hapus session dan redirect ke login page.
 * ============================================
 */

session_start();

// Hapus semua session data
$_SESSION = array();

// Hapus session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect ke login page
header('Location: login.php');
exit();
?>
