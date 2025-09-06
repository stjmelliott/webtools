<?php
/**
 * midway_sftp_upload_sandbox.php
 * Uploads the latest CSV to SIT (/incoming/generic_ff) using phpseclib.
 * Reads creds from invoice_export/config/invoice_export_config.php
 * Compatible with PHP 5.6/7.x
 */
date_default_timezone_set('America/Chicago');
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';
if (!defined('INVEXP_BASE')) { define('INVEXP_BASE', dirname(__DIR__)); }
if (!defined('INVEXP_LOG_DIR')) { define('INVEXP_LOG_DIR', dirname(INVEXP_BASE) . DIRECTORY_SEPARATOR . 'log'); }
function logln($s) { echo $s, "\n"; }
function find_latest_csv() {
    $cands = array('C:/trax_invoice_export/*.csv', INVEXP_BASE . DIRECTORY_SEPARATOR . 'exports' . DIRECTORY_SEPARATOR . '*.csv');
    $best = null; $bestM = -1;
    foreach ($cands as $pat) {
        $matches = glob($pat);
        if (!is_array($matches)) continue;
        foreach ($matches as $p) {
            $m = @filemtime($p);
            if ($m !== false && $m > $bestM) { $bestM = $m; $best = $p; }
        }
    }
    return $best;
}
if (!function_exists('invexp_phpseclib_available') || !invexp_phpseclib_available()) { logln("ERROR: phpseclib not available."); logln("Return code: 2"); exit; }
if (!class_exists('phpseclib\\Net\\SFTP', true) || !class_exists('phpseclib\\Crypt\\RSA', true)) { logln("ERROR: SFTP/RSA classes unavailable."); logln("Return code: 3"); exit; }
$creds = isset($INVEXP_CONFIG['sftp']['test']) ? $INVEXP_CONFIG['sftp']['test'] : array();
$host = isset($creds['host']) ? $creds['host'] : '';
$port = isset($creds['port']) ? (int)$creds['port'] : 22;
$user = isset($creds['user']) ? $creds['user'] : '';
$keyPath = isset($creds['key_path']) ? $creds['key_path'] : '';
$keyPass = isset($creds['key_passphrase']) ? $creds['key_passphrase'] : null;
$password = isset($creds['password']) ? $creds['password'] : null;
if (!$host || !$user) { logln("ERROR: Missing SFTP host/user in config."); logln("Return code: 4"); exit; }
$csv = find_latest_csv();
if (!$csv || !is_file($csv)) { logln("ERROR: No CSV found to upload."); logln("Return code: 5"); exit; }
logln("Local CSV: $csv (" . filesize($csv) . " bytes)");
$key = null;
if ($keyPath) {
    if (!is_file($keyPath)) { logln("ERROR: Key file not found: $keyPath"); logln("Return code: 6"); exit; }
    $pem = @file_get_contents($keyPath);
    if ($pem === false) { logln("ERROR: Unable to read key file: $keyPath"); logln("Return code: 7"); exit; }
    $key = new \phpseclib\Crypt\RSA();
    if ($keyPass) $key->setPassword($keyPass);
    if (!$key->loadKey($pem)) { logln("ERROR: Failed to load private key (bad format or passphrase)."); logln("Return code: 8"); exit; }
    logln("Loaded key: $keyPath");
}
$sftp = new \phpseclib\Net\SFTP($host, $port, 10);
$ok = false;
if ($key) $ok = $sftp->login($user, $key);
if (!$ok && $password) $ok = $sftp->login($user, $password);
if (!$ok) { logln("ERROR: Login failed."); logln("Return code: 9"); exit; }
logln("Connected to $host:$port as $user");
$remoteDir = '/incoming/generic_ff';
$remotePath = $remoteDir . '/' . basename($csv);
if (!$sftp->put($remotePath, $csv, \phpseclib\Net\SFTP::SOURCE_LOCAL_FILE)) {
    logln("ERROR: Upload failed to $remotePath"); logln("Return code: 10"); exit;
}
logln("Upload OK -> $remotePath");
logln("Return code: 0");
