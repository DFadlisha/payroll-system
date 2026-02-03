<?php
$files = [
    __DIR__ . '/config/database.php',
    __DIR__ . '/includes/functions.php',
    __DIR__ . '/config/environment.php',
    __DIR__ . '/includes/header.php',
    __DIR__ . '/auth/login.php',
];

foreach ($files as $file) {
    echo "Checking " . basename($file) . "...\n";
    $content = file_get_contents($file);
    
    // Check BOM
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        echo "[FAIL] BOM detected.\n";
    }
    
    // Check Start
    if (!preg_match('/^\s*<\?php/i', $content)) {
        echo "[INFO] Does not start withstandard <?php tag.\n";
    } else {
        if (preg_match('/^[\s]+<\?php/i', $content)) {
             echo "[FAIL] Whitespace before <?php.\n";
        }
    }
    
    // Check End
    $trimmed = trim($content);
    if (substr($trimmed, -2) === '?>') {
         echo "[WARN] Closing ?> tag found.\n";
         // Check if there is anything after it
         $lastPos = strrpos($content, '?>');
         $after = substr($content, $lastPos + 2);
         if (strlen($after) > 0) {
              echo "[FAIL] Content/Whitespace after closing ?> tag. Hex: " . bin2hex($after) . "\n";
         }
    }
}
echo "Done.\n";
