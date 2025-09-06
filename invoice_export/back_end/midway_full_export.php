<?php
/**
 * midway_full_export.php
 * Run the DB2 export script only (no SFTP, no USER10 updates here).
 * Prints a clean Return code for the UI to read.
 * Compatible with PHP 5.6/7.x
 */
date_default_timezone_set('America/Chicago');
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';
function println($s=''){ echo $s, "\n"; }
$cfg = isset($INVEXP_CONFIG['db']) ? $INVEXP_CONFIG['db'] : array();
$db   = isset($cfg['name']) ? $cfg['name'] : '';
$user = isset($cfg['user']) ? $cfg['user'] : '';
$pass = isset($cfg['pass']) ? $cfg['pass'] : '';
$script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'back_end' . DIRECTORY_SEPARATOR . 'export_script.sql';
println('[info] Loaded central config: ' . dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php');
println('[' . date('Y-m-d H:i:s') . "] DB2 credentials: db=$db user=$user pass=" . ($pass !== '' ? '***' : '(empty)'));
if (!$db || !$user || $pass === '') {
    println('ERROR: Missing DB credentials from config.');
    println('Return code: 12');
    exit;
}
println('Searching for export_script.sql:');
println('  - ' . $script . ' => ' . (is_file($script) ? 'YES' : 'NO'));
if (!is_file($script)) {
    println('ERROR: export script not found: ' . $script);
    println('Return code: 13');
    exit;
}
$cmd = 'db2cmd /c /w /i "db2 CONNECT TO ' . $db . ' USER ' . $user . ' USING ' . $pass .
       ' && db2 -tvf "' . $script . '" && db2 CONNECT RESET"';
println('Running: ' . $cmd);
$out = array(); $rc = 0;
exec($cmd . ' 2>&1', $out, $rc);
println(implode("\n", $out));
println('');
println('Return code: ' . $rc);
if ($rc === 0) {
    println('');
    println('OK: export_script.sql executed successfully.');
}
