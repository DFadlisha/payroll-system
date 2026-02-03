<?php
function checkFile($file) {
    $content = file_get_contents($file);
    $len = strlen($content);
    $errors = [];

    // Check for BOM
    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
        $errors[] = "BOM detected at start";
    }

    // Check for whitespace before <?php
    if (!preg_match('/^\s*<\?php/i', $content)) {
        // It might be a template file starting with HTML, which is fine for views but not for logic files.
        // Logic files usually start with <?php
        // We will only flag if it looks like a logic file (in includes, config, auth root)
        if (strpos($file, 'views') === false) {
             // If it doesn't start with <?php, check if it has <?php later
             if (strpos($content, '<?php') !== false) {
                 // Check if there is content before the first <?php
                 $start = strpos($content, '<?php');
                 $pre = substr($content, 0, $start);
                 if (trim($pre) !== '') {
                      // HTML content before PHP - acceptable in some cases, but if it's just whitespace it's bad
                 } else if (strlen($pre) > 0) {
                      $errors[] = "Whitespace detected before <?php";
                 }
             }
        }
    } else {
        // Starts with <?php, check for whitespace before it (excluding BOM if checked above)
        if (preg_match('/^[\s]+<\?php/i', $content)) {
            $errors[] = "Whitespace detected before <?php";
        }
    }

    // Check for whitespace after ?> (only if ?> exists)
    // Note: We recommend removing ?> entirely
    if (substr(trim($content), -2) === '?>') {
        // Check if there is anything after ?>
        $lastTagPos = strrpos($content, '?>');
        $after = substr($content, $lastTagPos + 2);
        if (strlen($after) > 0) {
             $errors[] = "Content/Whitespace detected after closing ?> tag";
        }
    }

    if (!empty($errors)) {
        echo "File: $file\n";
        foreach ($errors as $err) {
            echo "  - $err\n";
        }
    }
}

function scanDirRecursive($dir) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            if ($file !== 'vendor' && $file !== '.git') {
                scanDirRecursive($path);
            }
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            checkFile($path);
        }
    }
}

echo "Scanning for PHP file issues (BOM, Whitespace)...\n";
scanDirRecursive(__DIR__);
echo "Scan complete.\n";
