<?php
declare(strict_types=1);
/**
 * invoice_export/config_status.php  (SAFE)
 * Shows whether the config file exists and whether key fields are populated.
 * Masks sensitive values.
 */
header('Content-Type: text/plain; charset=utf-8');

$base = __DIR__;                    // ...\invoice_export
$cfgPath = $base . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';

echo "INVEXP CONFIG STATUS\n";
echo "Base: $base\n";
echo "Config file: $cfgPath\n\n";

if (!is_file($cfgPath)) {
    echo "ERROR: invoice_export_config.php not found.\n";
    exit;
}

require_once $cfgPath;

function mask($s) { if ($s === null || $s === '') return '(empty)'; return '***'; }

echo "Loaded INVEXP_BASE: " . (defined('INVEXP_BASE') ? INVEXP_BASE : '(not defined)') . "\n\n";

// DB block
$db = $INVEXP_CONFIG['db'] ?? [];
echo "[DB]\n";
echo "  name: " . ($db['name'] ?? '(empty)') . "\n";
echo "  user: " . ($db['user'] ?? '(empty)') . "\n";
echo "  pass: " . mask($db['pass'] ?? '') . "\n\n";

// Paths
$paths = $INVEXP_CONFIG['paths'] ?? [];
$exp = $paths['export_sql'] ?? (INVEXP_BASE . DIRECTORY_SEPARATOR . 'back_end' . DIRECTORY_SEPARATOR . 'export_script.sql');
echo "[PATHS]\n";
echo "  export_sql: $exp  => " . (is_file($exp) ? 'FOUND' : 'MISSING') . "\n\n";

// SFTP (test)
$s = $INVEXP_CONFIG['sftp']['test'] ?? [];
echo "[SFTP test]\n";
echo "  host: " . ($s['host'] ?? '(empty)') . "\n";
echo "  port: " . ($s['port'] ?? '(empty)') . "\n";
echo "  user: " . ($s['user'] ?? '(empty)') . "\n";
echo "  key_path: " . ($s['key_path'] ?? '(empty)') . " => " . (isset($s['key_path']) && is_file($s['key_path']) ? 'FOUND' : 'MISSING') . "\n";
echo "  key_passphrase: " . (array_key_exists('key_passphrase', $s) ? mask($s['key_passphrase']) : '(not set)') . "\n";
echo "  password: " . (array_key_exists('password', $s) ? mask($s['password']) : '(not set)') . "\n\n";

echo "Done.\n";
