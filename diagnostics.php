&lt;?php
/**
 * Diagnostic Page for Zeabur Deployment
 * This page helps diagnose 502 Bad Gateway errors
 * Access: https://mi-nes.zeabur.app/diagnostics.php
 */

header('Content-Type: text/html; charset=utf-8');

echo '&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;Zeabur Diagnostics&lt;/title&gt;
    &lt;style&gt;
        body { font-family: monospace; background: #1a1a1a; color: #0f0; padding: 20px; }
        .section { background: #2a2a2a; padding: 15px; margin: 15px 0; border-radius: 8px; }
        .ok { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        h2 { color: #00f0ff; border-bottom: 2px solid #00f0ff; padding-bottom: 5px; }
        pre { background: #000; padding: 10px; overflow-x: auto; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;';

echo '&lt;h1&gt;🔍 Zeabur Deployment Diagnostics&lt;/h1&gt;';

// 1. Check PHP Version
echo '&lt;div class="section"&gt;';
echo '&lt;h2&gt;1. PHP Environment&lt;/h2&gt;';
echo '&lt;p&gt;&lt;strong&gt;PHP Version:&lt;/strong&gt; ' . phpversion() . '&lt;/p&gt;';
echo '&lt;p&gt;&lt;strong&gt;Server Software:&lt;/strong&gt; ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '&lt;/p&gt;';
echo '&lt;p&gt;&lt;strong&gt;Server Port:&lt;/strong&gt; ' . ($_SERVER['SERVER_PORT'] ?? 'Unknown') . '&lt;/p&gt;';
echo '&lt;p&gt;&lt;strong&gt;Document Root:&lt;/strong&gt; ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . '&lt;/p&gt;';
echo '&lt;/div&gt;';

// 2. Check Environment Variables
echo '&lt;div class="section"&gt;';
echo '&lt;h2&gt;2. Environment Variables (Database)&lt;/h2&gt;';

$requiredVars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
$allSet = true;

foreach ($requiredVars as $var) {
    $value = getenv($var);
    $isset = $value !== false &amp;&amp; !empty($value);
    
    if (!$isset) {
        $allSet = false;
    }
    
    $class = $isset ? 'ok' : 'error';
    $status = $isset ? '✓ SET' : '✗ MISSING';
    
    // Mask password
    if ($var === 'DB_PASS' &amp;&amp; $isset) {
        $displayValue = str_repeat('*', strlen($value));
    } else {
        $displayValue = $isset ? $value : 'NOT SET';
    }
    
    echo "&lt;p class='$class'&gt;&lt;strong&gt;$var:&lt;/strong&gt; $displayValue ($status)&lt;/p&gt;";
}

if (!$allSet) {
    echo '&lt;p class="error"&gt;⚠️ MISSING VARIABLES! Set them in Zeabur Dashboard → Variables&lt;/p&gt;';
} else {
    echo '&lt;p class="ok"&gt;✓ All environment variables are set&lt;/p&gt;';
}

echo '&lt;/div&gt;';

// 3. Check Database Connection
echo '&lt;div class="section"&gt;';
echo '&lt;h2&gt;3. Database Connection Test&lt;/h2&gt;';

try {
    require_once __DIR__ . '/config/environment.php';
    require_once __DIR__ . '/config/database.php';
    
    $conn = getConnection();
    
    if ($conn) {
        echo '&lt;p class="ok"&gt;✓ Database connection successful!&lt;/p&gt;';
        
        // Test query
        $stmt = $conn-&gt;query("SELECT version()");
        $version = $stmt-&gt;fetchColumn();
        echo "&lt;p class='ok'&gt;PostgreSQL Version: $version&lt;/p&gt;";
        
        // Check if companies table exists
        $stmt = $conn-&gt;query("SELECT COUNT(*) FROM companies");
        $count = $stmt-&gt;fetchColumn();
        echo "&lt;p class='ok'&gt;✓ Companies table exists ($count companies found)&lt;/p&gt;";
        
    } else {
        echo '&lt;p class="error"&gt;✗ Database connection failed&lt;/p&gt;';
    }
    
} catch (Exception $e) {
    echo '&lt;p class="error"&gt;✗ Database Error: ' . htmlspecialchars($e-&gt;getMessage()) . '&lt;/p&gt;';
    echo '&lt;pre class="error"&gt;' . htmlspecialchars($e-&gt;getTraceAsString()) . '&lt;/pre&gt;';
}

echo '&lt;/div&gt;';

// 4. Check File Permissions
echo '&lt;div class="section"&gt;';
echo '&lt;h2&gt;4. File System Check&lt;/h2&gt;';

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
        echo "&lt;p class='ok'&gt;✓ $file (permissions: $perms)&lt;/p&gt;";
    } else {
        echo "&lt;p class='error'&gt;✗ $file NOT FOUND&lt;/p&gt;";
    }
}

echo '&lt;/div&gt;';

// 5. Check PHP Extensions
echo '&lt;div class="section"&gt;';
echo '&lt;h2&gt;5. Required PHP Extensions&lt;/h2&gt;';

$requiredExtensions = ['pdo', 'pdo_pgsql', 'pgsql', 'zip', 'opcache'];

foreach ($requiredExtensions as $ext) {
    $loaded = extension_loaded($ext);
    $class = $loaded ? 'ok' : 'error';
    $status = $loaded ? '✓ LOADED' : '✗ MISSING';
    echo "&lt;p class='$class'&gt;$ext: $status&lt;/p&gt;";
}

echo '&lt;/div&gt;';

// 6. Port detection
echo '&lt;div class="section"&gt;';
echo '&lt;h2&gt;6. Port Configuration&lt;/h2&gt;';
$port = getenv('PORT') ?: '80';
$zeaburPort = getenv('ZEABUR_PORT') ?: 'Not set';
echo "&lt;p&gt;&lt;strong&gt;PORT env var:&lt;/strong&gt; $port&lt;/p&gt;";
echo "&lt;p&gt;&lt;strong&gt;ZEABUR_PORT env var:&lt;/strong&gt; $zeaburPort&lt;/p&gt;";
echo "&lt;p&gt;&lt;strong&gt;Server is listening on port:&lt;/strong&gt; " . ($_SERVER['SERVER_PORT'] ?? 'Unknown') . "&lt;/p&gt;";
echo '&lt;/div&gt;';

// 7. Recommendations
echo '&lt;div class="section"&gt;';
echo '&lt;h2&gt;7. ✅ Next Steps&lt;/h2&gt;';

if (!$allSet) {
    echo '&lt;p class="warning"&gt;⚠️ Fix missing environment variables first!&lt;/p&gt;';
    echo '&lt;ol&gt;';
    echo '&lt;li&gt;Go to Zeabur Dashboard → Your Service → Variables&lt;/li&gt;';
    echo '&lt;li&gt;Add these variables:&lt;ul&gt;';
    echo '&lt;li&gt;DB_HOST = db.aahaznqptohmkdiqpjnx.supabase.co&lt;/li&gt;';
    echo '&lt;li&gt;DB_PORT = 6543&lt;/li&gt;';
    echo '&lt;li&gt;DB_NAME = postgres&lt;/li&gt;';
    echo '&lt;li&gt;DB_USER = postgres&lt;/li&gt;';
    echo '&lt;li&gt;DB_PASS = [your_supabase_database_password]&lt;/li&gt;';
    echo '&lt;/ul&gt;&lt;/li&gt;';
    echo '&lt;li&gt;Redeploy the service&lt;/li&gt;';
    echo '&lt;li&gt;Visit this page again to verify&lt;/li&gt;';
    echo '&lt;/ol&gt;';
} else {
    echo '&lt;p class="ok"&gt;✓ All checks passed! Try accessing the login page now:&lt;/p&gt;';
    echo '&lt;p&gt;&lt;a href="/auth/login.php" style="color: #00f0ff;"&gt;→ Go to Login Page&lt;/a&gt;&lt;/p&gt;';
}

echo '&lt;/div&gt;';

echo '&lt;/body&gt;&lt;/html&gt;';
