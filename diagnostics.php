<?php
/**
 * Diagnostic Page for Zeabur Deployment
 * This page helps diagnose 502 Bad Gateway errors
 * Access: https://mi-nes.zeabur.app/diagnostics.php
 */

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html>
<html>
<head>
    <title>Zeabur Diagnostics</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #0f0; padding: 20px; }
        .section { background: #2a2a2a; padding: 15px; margin: 15px 0; border-radius: 8px; }
        .ok { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        h2 { color: #00f0ff; border-bottom: 2px solid #00f0ff; padding-bottom: 5px; }
        pre { background: #000; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>';

echo '<h1>🔍 Zeabur Deployment Diagnostics</h1>';

// 1. Check PHP Version
echo '<div class="section">';
echo '<h2>1. PHP Environment</h2>';
echo '<p><strong>PHP Version:</strong> ' . phpversion() . '</p>';
echo '<p><strong>Server Software:</strong> ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</p>';
echo '<p><strong>Server Port:</strong> ' . ($_SERVER['SERVER_PORT'] ?? 'Unknown') . '</p>';
echo '<p><strong>Document Root:</strong> ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . '</p>';
echo '</div>';

// 2. Check Environment Variables
echo '<div class="section">';
echo '<h2>2. Environment Variables (Database)</h2>';

$requiredVars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
$allSet = true;

foreach ($requiredVars as $var) {
    $value = getenv($var);
    $isset = $value !== false && !empty($value);
    
    if (!$isset) {
        $allSet = false;
    }
    
    $class = $isset ? 'ok' : 'error';
    $status = $isset ? '✓ SET' : '✗ MISSING';
    
    // Mask password
    if ($var === 'DB_PASS' && $isset) {
        $displayValue = str_repeat('*', strlen($value));
    } else {
        $displayValue = $isset ? $value : 'NOT SET';
    }
    
    echo "<p class='$class'><strong>$var:</strong> $displayValue ($status)</p>";
}

if (!$allSet) {
    echo '<p class="error">⚠️ MISSING VARIABLES! Set them in Zeabur Dashboard → Variables</p>';
} else {
    echo '<p class="ok">✓ All environment variables are set</p>';
}

echo '</div>';

// 3. Check Database Connection
echo '<div class="section">';
echo '<h2>3. Database Connection Test</h2>';

try {
    require_once __DIR__ . '/config/environment.php';
    require_once __DIR__ . '/config/database.php';
    
    $conn = getConnection();
    
    if ($conn) {
        echo '<p class="ok">✓ Database connection successful!</p>';
        
        // Test query
        $stmt = $conn->query("SELECT version()");
        $version = $stmt->fetchColumn();
        echo "<p class='ok'>PostgreSQL Version: $version</p>";
        
        // Check if companies table exists
        $stmt = $conn->query("SELECT COUNT(*) FROM companies");
        $count = $stmt->fetchColumn();
        echo "<p class='ok'>✓ Companies table exists ($count companies found)</p>";
        
    } else {
        echo '<p class="error">✗ Database connection failed</p>';
    }
    
} catch (Exception $e) {
    echo '<p class="error">✗ Database Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre class="error">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}

echo '</div>';

// 4. Check File Permissions
echo '<div class="section">';
echo '<h2>4. File System Check</h2>';

$filesToCheck = [
    '.env',
    'config/database.php',
    'auth/login.php',
    'includes/functions.php',
];

foreach ($filesToCheck as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo "<p class='ok'>✓ $file (permissions: $perms)</p>";
    } else {
        echo "<p class='error'>✗ $file NOT FOUND</p>";
    }
}

echo '</div>';

// 5. Check PHP Extensions
echo '<div class="section">';
echo '<h2>5. Required PHP Extensions</h2>';

$requiredExtensions = ['pdo', 'pdo_pgsql', 'pgsql', 'zip', 'opcache'];

foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $class = $loaded ? 'ok' : 'error';
    $status = $loaded ? '✓ LOADED' : '✗ MISSING';
    echo "<p class='$class'>$ext: $status</p>";
}

echo '</div>';

// 6. Port detection
echo '<div class="section">';
echo '<h2>6. Port Configuration</h2>';
$port = getenv('PORT') ?: '80';
$zeaburPort = getenv('ZEABUR_PORT') ?: 'Not set';
echo "<p><strong>PORT env var:</strong> $port</p>";
echo "<p><strong>ZEABUR_PORT env var:</strong> $zeaburPort</p>";
echo "<p><strong>Server is listening on port:</strong> " . ($_SERVER['SERVER_PORT'] ?? 'Unknown') . "</p>";
echo '</div>';

// 7. Recommendations
echo '<div class="section">';
echo '<h2>7. ✅ Next Steps</h2>';

if (!$allSet) {
    echo '<p class="warning">⚠️ Fix missing environment variables first!</p>';
    echo '<ol>';
    echo '<li>Go to Zeabur Dashboard → Your Service → Variables</li>';
    echo '<li>Add these variables:<ul>';
    echo '<li>DB_HOST = db.aahaznqptohmkdiqpjnx.supabase.co</li>';
    echo '<li>DB_PORT = 6543</li>';
    echo '<li>DB_NAME = postgres</li>';
    echo '<li>DB_USER = postgres</li>';
    echo '<li>DB_PASS = [your_supabase_database_password]</li>';
    echo '</ul></li>';
    echo '<li>Redeploy the service</li>';
    echo '<li>Visit this page again to verify</li>';
    echo '</ol>';
} else {
    echo '<p class="ok">✓ All checks passed! Try accessing the login page now:</p>';
    echo '<p><a href="/auth/login.php" style="color: #00f0ff;">→ Go to Login Page</a></p>';
}

echo '</div>';

echo '</body></html>';
