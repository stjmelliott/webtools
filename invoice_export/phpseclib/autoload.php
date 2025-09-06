<?php
// Robust autoloader for phpseclib v2/v3 (non-Composer)
// Place this file at: invoice_export/phpseclib/autoload.php
// It auto-detects whether class files live under:
//   - invoice_export/phpseclib/Net/SFTP.php            (flat)
//   - invoice_export/phpseclib/phpseclib/Net/SFTP.php  (nested archive)
// and supports both namespaces: phpseclib\* and phpseclib3\*.

spl_autoload_register(function ($class) {
    static $baseDir = null;

    // Determine baseDir once
    if ($baseDir === null) {
        $candidates = [
            __DIR__ . '/',                 // flat: Net/SFTP.php
            __DIR__ . '/phpseclib/',      // nested: phpseclib/Net/SFTP.php
        ];
        foreach ($candidates as $cand) {
            if (is_file($cand . 'Net/SFTP.php') || is_file($cand . 'Net/SFTP.php')) {
                $baseDir = $cand;
                break;
            }
        }
        if ($baseDir === null) {
            // Default to flat
            $baseDir = __DIR__ . '/';
        }
    }

    // Normalize known prefixes (phpseclib v2, v3). v1 had no namespaces.
    $prefixes = ['phpseclib\\', 'phpseclib3\\'];
    foreach ($prefixes as $prefix) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relative = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
            return;
        }
    }

    // Fallback: try raw class as path (legacy)
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
