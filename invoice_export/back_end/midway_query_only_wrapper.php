<?php
/**
 * midway_query_only_wrapper.php  (v3g2 HOTFIX)
 * - Runs back_end/midway_query_only.php via PHP-CLI with a timeout
 * - EMBED-SAFE: if defined('INVEXP_EMBED'), sets $INVEXP_WRAPPER_RESULT instead of exit()
 * - Extensive logging to C:\clients\midway\webtools\log
 */

date_default_timezone_set('America/Chicago');
@set_time_limit(90);

$ORIG = __DIR__ . DIRECTORY_SEPARATOR . 'midway_query_only.php';
$force = isset($_INVEXP_FORCE) ? (bool)$_INVEXP_FORCE : (isset($_GET['force']) && $_GET['force'] === '1');

// --- Config & logger ---------------------------------------------------------
$INVEXP_CONFIG = isset($INVEXP_CONFIG) ? $INVEXP_CONFIG : array();
$configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';
if (is_file($configPath)) { include_once $configPath; if (!isset($INVEXP_CONFIG)) $INVEXP_CONFIG = array(); }

$logDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'log'; // ..\..\log from back_end
$loggerFile = __DIR__ . DIRECTORY_SEPARATOR . 'invexp_logger.php';
if (is_file($loggerFile)) { include_once $loggerFile; }
$LOGGER = class_exists('InvExpLogger') ? new InvExpLogger($logDir, 'preview') : null;
if ($LOGGER && $LOGGER->ok()) { $LOGGER->start('wrapper'); }

function out_json($arr) {
  if (defined('INVEXP_EMBED')) { $GLOBALS['INVEXP_WRAPPER_RESULT'] = $arr; return; }
  header('Content-Type: application/json; charset=utf-8'); echo json_encode($arr); exit;
}
function extract_json($s) {
  if (preg_match('/\{(?:[^{}]|(?R))*\}/s', $s, $m)) {
    $cand = $m[0];
    $j = json_decode($cand, true);
    if (is_array($j)) return $j;
  }
  return null;
}
function csv_preview($csvPath, $maxRows = 1000) {
  $headers = array(); $rows = array(); $totalRows = 0; $sum = 0.0; $amtIdx = null;
  if (!is_file($csvPath)) return array('ok'=>false,'error'=>'CSV not found','csv_path'=>$csvPath);
  $fh = @fopen($csvPath, 'r'); if (!$fh) return array('ok'=>false,'error'=>'Unable to open CSV','csv_path'=>$csvPath);
  $headers = fgetcsv($fh); if (!$headers) $headers = array();
  foreach ($headers as $i=>$h) {
    $name = strtolower(trim((string)$h));
    if (in_array($name, array('invoice amount','invoice_amount','total charges','total_charges'), true)) { $amtIdx = $i; break; }
  }
  while (($row = fgetcsv($fh)) !== false) {
    $totalRows++;
    if ($amtIdx !== null && isset($row[$amtIdx])) {
      $val = preg_replace('/[^0-9.\-]/', '', (string)$row[$amtIdx]);
      if ($val !== '' && is_numeric($val)) { $sum += (float)$val; }
    }
    if ($totalRows <= $maxRows) $rows[] = $row;
  }
  fclose($fh);
  $mtime = @filemtime($csvPath);
  return array(
    'ok'=>true,
    'csv_path'=>$csvPath,
    'csv_mtime'=>$mtime ? date('Y-m-d H:i:s', $mtime) : null,
    'headers'=>$headers,
    'rows'=>$rows,
    'displayed_rows'=>count($rows),
    'total_rows'=>$totalRows,
    'truncated'=>($totalRows > $maxRows),
    'invoice_amount_sum'=>round($sum, 2),
    'invoice_amount_index'=>$amtIdx
  );
}

function find_php_cli($config) {
  $cands = array();
  if (isset($config['php']['cli_path']) && is_string($config['php']['cli_path'])) $cands[] = $config['php']['cli_path'];
  if (defined('PHP_BINARY') && PHP_BINARY) $cands[] = PHP_BINARY;
  $cands = array_merge($cands, array(
    'C:\xampp\php\php.exe',
    'C:\Program Files\PHP\php.exe',
    'C:\Program Files (x86)\PHP\php.exe',
    'C:\php\php.exe',
    'php' // PATH
  ));
  $tried = array();
  foreach ($cands as $p) {
    $p = trim($p, "\"' ");
    $cmd = $p . ' -v';
    $out = array(); $rc = 255;
    @exec($cmd . ' 2>&1', $out, $rc);
    $tried[] = array('path'=>$p, 'rc'=>$rc, 'first_line'=>isset($out[0])?$out[0]:'');
    if ($rc === 0 || stripos(implode("\n",$out),'PHP') !== false) {
      return array('ok'=>true, 'path'=>$p, 'tried'=>$tried);
    }
  }
  return array('ok'=>false, 'path'=>null, 'tried'=>$tried);
}

function run_cli_timeout($phpExe, $script, $timeoutSec=20) {
  $cmd = $phpExe . ' -f ' . escapeshellarg($script);
  $descriptorspec = array(
    0 => array('pipe', 'r'),
    1 => array('pipe', 'w'),
    2 => array('pipe', 'w')
  );
  $pipes = array();
  $proc = @proc_open($cmd, $descriptorspec, $pipes);
  if (!is_resource($proc)) {
    return array('rc'=>255, 'out'=>'', 'err'=>'Failed to start process', 'cmd'=>$cmd, 'timeout'=>false);
  }
  // Non-blocking
  stream_set_blocking($pipes[1], false);
  stream_set_blocking($pipes[2], false);
  $out = ''; $err='';
  $start = microtime(true);
  $timeout = false;
  while (true) {
    $status = proc_get_status($proc);
    $out .= stream_get_contents($pipes[1]);
    $err .= stream_get_contents($pipes[2]);

    if (!$status['running']) {
      $rc = $status['exitcode'];
      break;
    }
    if ((microtime(true) - $start) > $timeoutSec) {
      $timeout = true;
      @proc_terminate($proc);
      $status = proc_get_status($proc);
      $rc = $status['exitcode'];
      break;
    }
    usleep(100000); // 100ms
  }
  foreach ($pipes as $p) { if (is_resource($p)) fclose($p); }
  @proc_close($proc);
  return array('rc'=>$rc, 'out'=>$out, 'err'=>$err, 'cmd'=>$cmd, 'timeout'=>$timeout);
}

// --- Start flow --------------------------------------------------------------
if ($LOGGER && $LOGGER->ok()) $LOGGER->step('config_loaded', array('config_path'=>$configPath, 'has_cli_override'=>isset($INVEXP_CONFIG['php']['cli_path'])));
if (!is_file($ORIG)) {
  if ($LOGGER && $LOGGER->ok()) $LOGGER->error('backend_missing', array('path'=>$ORIG));
  out_json(array('ok'=>false,'error'=>"Backend missing: $ORIG"));
}

$finder = find_php_cli($INVEXP_CONFIG);
if ($LOGGER && $LOGGER->ok()) $LOGGER->step('php_cli_probe', array('tried'=>$finder['tried']));
if (!$finder['ok']) {
  if ($LOGGER && $LOGGER->ok()) $LOGGER->error('php_cli_not_found');
  out_json(array('ok'=>false,'error'=>'PHP CLI not found. Set $INVEXP_CONFIG["php"]["cli_path"] to php.exe.','tried'=>$finder['tried']));
}
$php = $finder['path'];
if ($LOGGER && $LOGGER->ok()) $LOGGER->step('php_cli_selected', array('php'=>$php, 'force'=>$force));

// First run
$r1 = run_cli_timeout($php, $ORIG, 20);
if ($LOGGER && $LOGGER->ok()) $LOGGER->step('cli_run_first', array('cmd'=>$r1['cmd'], 'rc'=>$r1['rc'], 'timeout'=>$r1['timeout'], 'stdout_len'=>strlen($r1['out']), 'stderr_len'=>strlen($r1['err'])));
$j1 = json_decode(trim($r1['out']), true);
if (!is_array($j1)) $j1 = extract_json($r1['out']);

$csv = null;
if (!is_array($j1)) {
  if (preg_match('~([A-Za-z]:\\\\[^\\r\\n]*?\.csv|/[^\\r\\n]*?\.csv)~i', $r1['out'], $m)) {
    $csv = $m[1];
    if ($LOGGER && $LOGGER->ok()) $LOGGER->warn('json_missing_using_csv_regex', array('csv'=>$csv));
  } else {
    if ($LOGGER && $LOGGER->ok()) $LOGGER->error('backend_json_missing', array('stdout_sample'=>substr($r1['out'],0,400), 'stderr_sample'=>substr($r1['err'],0,200)));
    out_json(array('ok'=>false,'error'=>'Backend CLI did not return JSON','php'=>$php,'cmd'=>$r1['cmd'],'rc'=>$r1['rc'],'timeout'=>$r1['timeout'],'stdout'=>$r1['out'],'stderr'=>$r1['err']));
  }
} else {
  $csv = isset($j1['file']) ? $j1['file'] : null;
  if ($LOGGER && $LOGGER->ok()) $LOGGER->step('backend_json_ok', array('file'=>$csv));
}

// Optional refresh
$deleted = false;
if ($force && $csv && is_file($csv)) {
  @unlink($csv);
  $deleted = true;
  if ($LOGGER && $LOGGER->ok()) $LOGGER->warn('deleted_previous_csv', array('csv'=>$csv));
  $r2 = run_cli_timeout($php, $ORIG, 20);
  if ($LOGGER && $LOGGER->ok()) $LOGGER->step('cli_run_second', array('cmd'=>$r2['cmd'], 'rc'=>$r2['rc'], 'timeout'=>$r2['timeout'], 'stdout_len'=>strlen($r2['out']), 'stderr_len'=>strlen($r2['err'])));
  $j2 = json_decode(trim($r2['out']), true);
  if (is_array($j2) && isset($j2['file'])) $csv = $j2['file'];
  else {
    if (preg_match('~([A-Za-z]:\\\\[^\\r\\n]*?\.csv|/[^\\r\\n]*?\.csv)~i', $r2['out'], $m2)) {
      $csv = $m2[1];
      if ($LOGGER && $LOGGER->ok()) $LOGGER->warn('second_json_missing_using_csv_regex', array('csv'=>$csv));
    }
  }
}

if (!$csv) {
  if ($LOGGER && $LOGGER->ok()) $LOGGER->error('csv_path_not_determined');
  out_json(array('ok'=>false,'error'=>'No "file" path determined from backend','php'=>$php,'stdout'=>substr($r1['out'],0,400)));
}

$preview = csv_preview($csv);
$preview['meta'] = array('mode'=>$force?'refresh':'preview','deleted_previous_csv'=>$deleted,'php_used'=>$php,'timeout_sec'=>20);
if ($LOGGER && $LOGGER->ok()) {
  $LOGGER->end('wrapper', $preview['ok'], array(
    'csv'=>$preview['csv_path'],
    'rows_total'=>$preview['total_rows'],
    'rows_displayed'=>$preview['displayed_rows'],
    'sum'=>$preview['invoice_amount_sum'],
    'truncated'=>$preview['truncated']
  ));
}

out_json($preview);
