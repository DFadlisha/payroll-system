<?php
/**
 * ============================================
 * LANGUAGE SYSTEM
 * ============================================
 * Multi-language support for English & Malay
 * ============================================
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle language switch
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, ['en', 'ms'])) {
        $_SESSION['lang'] = $lang;
    }
    // Redirect to remove lang parameter from URL
    $redirect = strtok($_SERVER['REQUEST_URI'], '?');
    if (!empty($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $params);
        unset($params['lang']);
        if (!empty($params)) {
            $redirect .= '?' . http_build_query($params);
        }
    }
    header("Location: $redirect");
    exit;
}

// Set default language
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; // Default to English
}

// Load language file
$langCode = $_SESSION['lang'];
$langFile = __DIR__ . "/lang/{$langCode}.php";

if (file_exists($langFile)) {
    $GLOBALS['translations'] = require $langFile;
} else {
    $GLOBALS['translations'] = require __DIR__ . '/lang/en.php';
}

/**
 * Get translation by key
 * @param string $key Translation key (supports dot notation: 'nav.dashboard')
 * @param array $params Optional parameters for string replacement
 * @return string Translated string
 */
function __($key, $params = []) {
    $keys = explode('.', $key);
    $value = $GLOBALS['translations'];
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $key; // Return key if translation not found
        }
    }
    
    // Replace parameters
    if (!empty($params) && is_string($value)) {
        foreach ($params as $param => $replacement) {
            $value = str_replace(":{$param}", $replacement, $value);
        }
    }
    
    return $value;
}

/**
 * Get current language code
 * @return string Language code (en/ms)
 */
function getCurrentLang() {
    return $_SESSION['lang'] ?? 'en';
}

/**
 * Get language switcher HTML
 * @return string HTML for language switcher
 */
function getLanguageSwitcher() {
    $currentLang = getCurrentLang();
    $currentPage = $_SERVER['REQUEST_URI'];
    $separator = strpos($currentPage, '?') !== false ? '&' : '?';
    
    $html = '<div class="dropdown">
        <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-globe me-1"></i>' . ($currentLang === 'en' ? 'EN' : 'BM') . '
        </button>
        <ul class="dropdown-menu dropdown-menu-end">';
    
    $html .= '<li><a class="dropdown-item ' . ($currentLang === 'en' ? 'active' : '') . '" href="?lang=en">
        <span class="me-2">🇬🇧</span> English</a></li>';
    $html .= '<li><a class="dropdown-item ' . ($currentLang === 'ms' ? 'active' : '') . '" href="?lang=ms">
        <span class="me-2">🇲🇾</span> Bahasa Melayu</a></li>';
    
    $html .= '</ul></div>';
    
    return $html;
}
