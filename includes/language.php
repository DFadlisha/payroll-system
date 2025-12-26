<?php
/**
 * ============================================
 * LANGUAGE SYSTEM
 * ============================================
 * Multi-language support (English only enforced)
 * ============================================
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// FORCE ENGLISH
$_SESSION['lang'] = 'en';
$GLOBALS['translations'] = require __DIR__ . '/lang/en.php';

/**
 * Get translation by key
 * @param string $key Translation key (supports dot notation: 'nav.dashboard')
 * @param array $params Optional parameters for string replacement
 * @return string Translated string
 */
function __($key, $params = [])
{
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
 * @return string Always 'en'
 */
function getCurrentLang()
{
    return 'en';
}

/**
 * Get language switcher HTML (Disabled)
 * @return string Empty string
 */
function getLanguageSwitcher()
{
    return '';
}
