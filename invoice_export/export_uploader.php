<?php
/**
 * export_uploader.php — Export & Upload console (v3.1)
 * Drop at: C:\clients\midway\webtools\invoice_export\export_uploader.php
 *
 * v3.1:
 *  - Friendlier behavior for SIT where directory listing may be disabled.
 *  - For "List Inbox": if login+chdir OK but nlist() is false, return
 *    "Listing disabled by server (login OK)" with ok=true so the UI stays calm.
 *  - Adds a "Quick Sandbox Check" that only verifies login + chdir (no listing).
 */

@ini_set('display_errors', 0);
date_default_timezone_set('America/Chicago');

// -----------------------------------------------------------------------------
// Paths & Config
// -----------------------------------------------------------------------------
$BASE = __DIR__;
$CONFIG_PATH = $BASE . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';
$LOG_DIR = dirname($BASE) . DIRECTORY_SEPARATOR . 'log'; // ..\log
if (!is_dir($LOG_DIR)) { @mkdir($LOG_DIR, 0777, true); }
$LOG_FILE = $LOG_DIR . DIRECTORY_SEPARATOR . 'uploader_' . date('Ymd_His') . '_' . (function_exists('getmypid')?getmypid():0) . '.log';
$LOG = fopen($LOG_FILE, 'a');

function logl($lvl, $msg, $data = null) {
  global $LOG;
  $row = array('ts'=>date('Y-m-d H:i:s'),'level'=>$lvl,'msg'=>$msg,'data'=>$data);
  if (is_resource($LOG)) { fwrite($LOG, json_encode($row, JSON_UNESCAPED_SLASHES).PHP_EOL); fflush($LOG); }
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function exists($p){ return is_string($p) && $p !== '' && file_exists($p); }

$INVEXP_CONFIG = array();
$CONFIG_LOADED = false;
if (is_file($CONFIG_PATH)) {
  include $CONFIG_PATH;
  if (isset($INVEXP_CONFIG) && is_array($INVEXP_CONFIG)) { $CONFIG_LOADED = true; }
} else {
  logl('ERROR', 'config_missing', array('path'=>$CONFIG_PATH));
}

// Defaults
$exportSql = isset($INVEXP_CONFIG['paths']['export_sql']) ? $INVEXP_CONFIG['paths']['export_sql'] : ($BASE . DIRECTORY_SEPARATOR . 'back_end' . DIRECTORY_SEPARATOR . 'export_script.sql');
$csvDir    = isset($INVEXP_CONFIG['paths']['csv_dir']) ? $INVEXP_CONFIG['paths']['csv_dir'] : 'C:\\trax_invoice_export';

// -----------------------------------------------------------------------------
// phpseclib loader (v2 or legacy)
// -----------------------------------------------------------------------------
function detect_phpseclib_paths($base) {
  $cands = array(
    $base . DIRECTORY_SEPARATOR . 'phpseclib' . DIRECTORY_SEPARATOR . 'autoload.php',
    $base . DIRECTORY_SEPARATOR . 'phpseclib' . DIRECTORY_SEPARATOR . 'bootstrap.php',
    $base . DIRECTORY_SEPARATOR . 'phpseclib' . DIRECTORY_SEPARATOR . 'phpseclib' . DIRECTORY_SEPARATOR . 'bootstrap.php',
    $base . DIRECTORY_SEPARATOR . 'phpseclib' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php',
  );
  $exists = array();
  foreach ($cands as $p) { $exists[$p] = is_file($p) ? 'YES' : 'NO'; }
  return array($cands, $exists);
}
function ensure_phpseclib($base) {
  list($cands, $exists) = detect_phpseclib_paths($base);
  foreach ($cands as $p) { if (is_file($p)) { require_once $p; return array(true, $p, $exists); } }
  return array(false, null, $exists);
}
function get_phpseclib_classes() {
  $classes = array(
    'ssh2' => class_exists('\\phpseclib\\Net\\SSH2') ? '\\phpseclib\\Net\\SSH2' : (class_exists('Net_SSH2') ? 'Net_SSH2' : null),
    'sftp' => class_exists('\\phpseclib\\Net\\SFTP') ? '\\phpseclib\\Net\\SFTP' : (class_exists('Net_SFTP') ? 'Net_SFTP' : null),
    'rsa'  => class_exists('\\phpseclib\\Crypt\\RSA') ? '\\phpseclib\\Crypt\\RSA' : (class_exists('Crypt_RSA') ? 'Crypt_RSA' : null),
  );
  return $classes;
}

// -----------------------------------------------------------------------------
// Helpers
// -----------------------------------------------------------------------------
function run_db2_export($config, $exportSql) {
  $db = isset($config['db2']['db']) ? $config['db2']['db'] : '';
  $user = isset($config['db2']['user']) ? $config['db2']['user'] : '';
  $pass = isset($config['db2']['pass']) ? $config['db2']['pass'] : '';
  $sql  = $exportSql;

  $details = array('db'=>$db,'user'=>$user ? '***' : '(empty)','sql_path'=>$sql,'sql_exists'=>is_file($sql));
  if ($db === '' || $user === '' || $pass === '') {
    logl('ERROR', 'db2_missing_creds', $details);
    return array('ok'=>false,'summary'=>'Missing DB2 credentials in config.','details'=>$details);
  }
  if (!is_file($sql)) {
    logl('ERROR', 'export_sql_missing', $details);
    return array('ok'=>false,'summary'=>'Export SQL script not found: '.$sql,'details'=>$details);
  }

  $quotedSql = '"' . $sql . '"';
  $inner = 'db2 CONNECT TO ' . $db . ' USER ' . $user . ' USING ' . $pass . ' && db2 -tvf ' . $quotedSql . ' && db2 CONNECT RESET';
  $cmd = 'db2cmd /c /w /i "' . $inner . '"';
  logl('INFO', 'db2_run_cmd', array('cmd'=>$cmd));

  $desc = array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w'));
  $pipes = array();
  $proc = @proc_open($cmd, $desc, $pipes);
  if (!is_resource($proc)) {
    logl('ERROR', 'db2_proc_open_failed');
    return array('ok'=>false,'summary'=>'Failed to start db2cmd process','details'=>array('cmd'=>$cmd));
  }
  stream_set_blocking($pipes[1], false);
  stream_set_blocking($pipes[2], false);
  $out = ''; $err = '';
  while (true) {
    $status = proc_get_status($proc);
    $out .= stream_get_contents($pipes[1]);
    $err .= stream_get_contents($pipes[2]);
    if (!$status['running']) { $rc = $status['exitcode']; break; }
    usleep(100000);
  }
  foreach ($pipes as $p) { if (is_resource($p)) fclose($p); }
  @proc_close($proc);

  logl('INFO', 'db2_completed', array('rc'=>$rc, 'out_len'=>strlen($out), 'err_len'=>strlen($err)));
  $ok = ($rc === 0) && (stripos($out, 'DB20000I') !== false);
  $summary = $ok ? 'Full export completed.' : 'Export finished with errors.';
  return array('ok'=>$ok,'summary'=>$summary,'details'=>array('rc'=>$rc,'cmd'=>$cmd,'stdout'=>$out,'stderr'=>$err));
}

function latest_csv($dir) {
  $dir = rtrim($dir, "\\/");
  $best = null; $bestMtime = -1;
  if (!is_dir($dir)) return array(null, 'CSV directory not found: '.$dir);
  $dh = opendir($dir);
  if (!$dh) return array(null, 'Unable to open CSV directory: '.$dir);
  while (($f = readdir($dh)) !== false) {
    if ($f === '.' || $f === '..') continue;
    if (preg_match('/^SHWN_MDYT_MT_\d{4}-\d{2}-\d{2}\.csv$/i', $f)) {
      $p = $dir . DIRECTORY_SEPARATOR . $f;
      $mt = @filemtime($p);
      if ($mt && $mt > $bestMtime) { $best = $p; $bestMtime = $mt; }
    }
  }
  closedir($dh);
  return array($best, $best ? null : 'No matching CSV found in '.$dir);
}

function tcp_probe($host, $port, $timeout=5) {
  $meta = array('host'=>$host,'port'=>$port,'ok'=>false);
  $conn = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, $timeout);
  if ($conn) {
    stream_set_timeout($conn, 1);
    $banner = @fgets($conn, 256);
    fclose($conn);
    $meta['ok'] = true;
    if ($banner !== false) $meta['banner'] = trim($banner);
  } else {
    $meta['errno'] = $errno; $meta['error'] = $errstr;
  }
  return $meta;
}

function load_key_obj($RSAClass, $keyPath, $passphrase, &$diag) {
  if (!$RSAClass || !$keyPath || !is_file($keyPath)) return null;
  $diag['key_path'] = $keyPath;
  $diag['key_exists'] = is_file($keyPath);
  if ($diag['key_exists']) {
    $diag['key_size'] = @filesize($keyPath);
    $diag['key_mtime'] = @date('Y-m-d H:i:s', @filemtime($keyPath));
    $fh = @fopen($keyPath, 'r');
    if ($fh) { $head = @fgets($fh, 160); fclose($fh); $diag['key_head'] = trim((string)$head); }
  }

  $keyObj = new $RSAClass();
  if ($passphrase && method_exists($keyObj, 'setPassword')) { $keyObj->setPassword($passphrase); }
  $keyData = @file_get_contents($keyPath);
  if ($keyData && method_exists($keyObj,'loadKey') && @$keyObj->loadKey($keyData)) {
    $diag['key_loaded'] = true;
    return $keyObj;
  }
  $diag['key_loaded'] = false;
  return null;
}

function sftp_connect_and_login($cfg, &$diag) {
  $need = array('host','user');
  foreach ($need as $k) { if (!isset($cfg[$k]) || trim((string)$cfg[$k]) === '') { return array(false,'Missing SFTP '.$k.' in config'); } }
  $host = $cfg['host']; $port = isset($cfg['port']) ? (int)$cfg['port'] : 22; $user = $cfg['user'];
  $keyPath = isset($cfg['key_path']) ? $cfg['key_path'] : null; $password = isset($cfg['password']) ? $cfg['password'] : null; $passphrase = isset($cfg['key_passphrase']) ? $cfg['key_passphrase'] : null;
  $diag['host']=$host; $diag['port']=$port; $diag['user']=$user; $diag['auth']='(none)';
  $diag['tcp_probe'] = tcp_probe($host, $port);

  list($okLib, $loadedFrom, $existMap) = ensure_phpseclib(__DIR__);
  $diag['phpseclib']=array('loaded'=>$okLib,'path'=>$loadedFrom,'candidates'=>$existMap);
  if (!$okLib) return array(false,'phpseclib not found');

  $classes = get_phpseclib_classes();
  $diag['classes']=$classes;
  if (!$classes['ssh2'] || !$classes['sftp']) return array(false,'phpseclib classes not available');
  $SSHClass = $classes['ssh2']; $SFTPClass = $classes['sftp']; $RSAClass = $classes['rsa'];

  // Prepare key object (if provided)
  $keyObj = load_key_obj($RSAClass, $keyPath, $passphrase, $diag);

  // Try direct SFTP login first
  try {
    $sftp = new $SFTPClass($host, $port, 10);
    if ($keyObj) {
      $diag['auth'] = 'key';
      $ok = @$sftp->login($user, $keyObj);
      if ($ok) {
        if (method_exists($sftp,'getServerIdentification')) { $diag['serverId'] = $sftp->getServerIdentification(); }
        return array(true, array('sftp'=>$sftp,'ssh'=>$sftp,'key'=>$keyObj));
      } else {
        $diag['sftp_key_auth'] = 'failed';
        if (method_exists($sftp,'getLog')) $diag['sftp_log'] = $sftp->getLog();
      }
    }
    if (!$keyObj && $password) {
      $diag['auth'] = 'password';
      $ok = @$sftp->login($user, $password);
      if ($ok) {
        if (method_exists($sftp,'getServerIdentification')) { $diag['serverId'] = $sftp->getServerIdentification(); }
        return array(true, array('sftp'=>$sftp,'ssh'=>$sftp,'key'=>null));
      } else {
        $diag['sftp_password_auth'] = 'failed';
        if (method_exists($sftp,'getLog')) $diag['sftp_log'] = $sftp->getLog();
      }
    }
  } catch (\Throwable $e) {
    $diag['sftp_ctor_ex'] = $e->getMessage();
  }

  // Fallback: SSH first, then SFTP
  try {
    $ssh = new $SSHClass($host, $port, 10);
    $authOk = false;
    if ($keyObj) {
      $diag['auth'] = 'key';
      $authOk = @$ssh->login($user, $keyObj);
      if (!$authOk) {
        $diag['ssh_key_auth'] = 'failed';
        if (method_exists($ssh,'getLog')) $diag['ssh_log'] = $ssh->getLog();
      }
    }
    if (!$authOk && $password) {
      $diag['auth'] = 'password';
      $authOk = @$ssh->login($user, $password);
      if (!$authOk) {
        $diag['ssh_password_auth'] = 'failed';
        if (method_exists($ssh,'getLog')) $diag['ssh_log'] = $ssh->getLog();
      }
    }
    if (!$authOk) return array(false,'Login failed');

    $sftp = new $SFTPClass($host, $port, 10);
    if (!$sftp->login($user, $keyObj ? $keyObj : $password)) {
      if (method_exists($sftp,'getLog')) $diag['sftp_log'] = $sftp->getLog();
      return array(false,'SFTP login failed after SSH auth');
    }
    if (method_exists($ssh,'getServerIdentification')) { $diag['serverId'] = $ssh->getServerIdentification(); }
    return array(true, array('ssh'=>$ssh, 'sftp'=>$sftp, 'key'=>$keyObj));
  } catch (\Throwable $ex) {
    $diag['ex']=$ex->getMessage();
    return array(false,'Exception during SSH/SFTP: '.$ex->getMessage());
  }
}

function sftp_upload($cfg, $localFile) {
  $diag = array('remote_dir'=>isset($cfg['remote_dir'])?$cfg['remote_dir']:'/incoming/generic_ff');
  list($ok, $sess) = sftp_connect_and_login($cfg, $diag);
  if (!$ok) {
    $summary = 'NOT CONFIGURED';
    if (is_string($sess) && stripos($sess,'login failed') !== false) $summary = 'Auth failed (key)';
    return array('ok'=>false,'summary'=>$summary,'details'=>array('error'=>isset($sess)?$sess:'Login failed','diag'=>$diag));
  }

  $sftp = $sess['sftp']; $remoteDir = $diag['remote_dir'];
  if (!$sftp->chdir($remoteDir)) return array('ok'=>false,'summary'=>'Remote directory not accessible','details'=>array('diag'=>$diag,'cwd_failed'=>$remoteDir));

  $remoteFile = basename($localFile);
  // Duplicate detection
  $existing = $sftp->stat($remoteFile);
  if (is_array($existing) && isset($existing['size'])) {
    $same = ((int)$existing['size'] === (int)filesize($localFile));
    if ($same) {
      return array('ok'=>true,'summary'=>'Duplicate (already present)','details'=>array('diag'=>$diag,'remote_file'=>$remoteFile,'existing'=>$existing));
    }
  }

  $okPut = $sftp->put($remoteFile, @file_get_contents($localFile));
  if (!$okPut) {
    return array('ok'=>false,'summary'=>'Upload failed.','details'=>array('diag'=>$diag,'remote_file'=>$remoteFile));
  }

  $stat = $sftp->stat($remoteFile);
  $sizeOk = is_array($stat) && isset($stat['size']) && $stat['size'] == filesize($localFile);
  return array('ok'=>$sizeOk,'summary'=>$sizeOk ? 'Uploaded' : 'Upload size mismatch','details'=>array('diag'=>$diag,'remote_file'=>$remoteFile,'stat'=>$stat));
}

function sftp_list($cfg, $limit=50) {
  $diag = array('remote_dir'=>isset($cfg['remote_dir'])?$cfg['remote_dir']:'/incoming/generic_ff');
  list($ok, $sess) = sftp_connect_and_login($cfg, $diag);
  if (!$ok) {
    $summary = 'NOT CONFIGURED';
    if (is_string($sess) && stripos($sess,'login failed') !== false) $summary = 'Auth failed (key)';
    return array('ok'=>false,'summary'=>$summary,'details'=>array('error'=>isset($sess)?$sess:'Login failed','diag'=>$diag));
  }
  $sftp = $sess['sftp'];
  $remoteDir = $diag['remote_dir'];
  if (!$sftp->chdir($remoteDir)) return array('ok'=>false,'summary'=>'Remote directory not accessible','details'=>array('diag'=>$diag,'cwd_failed'=>$remoteDir));

  // Some SIT endpoints allow CHDIR but disable listing entirely.
  $list = @$sftp->nlist();
  if ($list === false) {
    return array('ok'=>true,'summary'=>'Listing disabled by server (login OK)','files'=>array(), 'details'=>array('diag'=>$diag, 'listing_disabled'=>true));
  }

  $files = array();
  if (is_array($list)) {
    foreach ($list as $f) {
      if ($f === '.' || $f === '..') continue;
      $st = $sftp->stat($f);
      $files[] = array('name'=>$f, 'size'=>isset($st['size'])?$st['size']:null, 'mtime'=>isset($st['mtime'])?date('Y-m-d H:i:s',$st['mtime']):null);
      if (count($files) >= $limit) break;
    }
  }
  return array('ok'=>true,'summary'=>'Listed','files'=>$files,'details'=>array('diag'=>$diag));
}

function preflight_checks($cfg, $exportSql, $csvDir) {
  list($okLib, $loadedFrom, $existMap) = ensure_phpseclib(__DIR__);
  $checks = array(
    array('name'=>'Config loaded','ok'=>isset($cfg) && is_array($cfg),'info'=>''),
    array('name'=>'DB2 credentials','ok'=>!empty($cfg['db2']['db']) && !empty($cfg['db2']['user']) && isset($cfg['db2']['pass']),'info'=>''),
    array('name'=>'export_script.sql exists','ok'=>is_file($exportSql),'info'=>$exportSql),
    array('name'=>'CSV directory exists','ok'=>is_dir($csvDir),'info'=>$csvDir),
    array('name'=>'phpseclib loader found','ok'=>$okLib,'info'=>$loadedFrom ? $loadedFrom : json_encode($existMap)),
    array('name'=>'Sandbox host/user set','ok'=>!empty($cfg['sftp']['sandbox']['host']) && !empty($cfg['sftp']['sandbox']['user']),'info'=>''),
    array('name'=>'Sandbox key exists (if set)','ok'=>empty($cfg['sftp']['sandbox']['key_path']) || is_file($cfg['sftp']['sandbox']['key_path']),'info'=>isset($cfg['sftp']['sandbox']['key_path'])?$cfg['sftp']['sandbox']['key_path']:''),
    array('name'=>'Live host/user set','ok'=>!empty($cfg['sftp']['live']['host']) && !empty($cfg['sftp']['live']['user']),'info'=>''),
  );
  return $checks;
}

// -----------------------------------------------------------------------------
// Controller
// -----------------------------------------------------------------------------
$action = isset($_POST['action']) ? $_POST['action'] : null;
$target = isset($_POST['target']) ? $_POST['target'] : null; // sandbox|live
$result = null;
$endUserSummary = null;

if ($action === 'export') {
  $result = run_db2_export($INVEXP_CONFIG, $exportSql);
  $endUserSummary = $result['ok'] ? 'Full export completed.' : 'Export failed';
} else if ($action === 'upload' && in_array($target, array('sandbox','live'), true)) {
  list($csv, $err) = latest_csv($csvDir);
  if (!$csv) {
    $result = array('ok'=>false,'summary'=>'No CSV found','details'=>array('csv_dir'=>$csvDir,'error'=>$err));
    $endUserSummary = 'No CSV found';
  } else {
    $cfg = isset($INVEXP_CONFIG['sftp'][$target]) ? $INVEXP_CONFIG['sftp'][$target] : array();
    $result = sftp_upload($cfg, $csv);
    $endUserSummary = ($result['summary'] === 'Duplicate (already present)') ? 'Duplicate' : ($result['ok'] ? 'Uploaded' : ($result['summary'] ?: 'Upload failed'));
  }
} else if ($action === 'test' && in_array($target, array('sandbox','live'), true)) {
  $cfg = isset($INVEXP_CONFIG['sftp'][$target]) ? $INVEXP_CONFIG['sftp'][$target] : array();
  $diag = array(); $res = sftp_connect_and_login($cfg, $diag);
  $summary = ($res[0]===true) ? 'Connection OK' : ((is_string($res[1]) && stripos($res[1],'login failed')!==false) ? 'Auth failed (key)' : 'Connection failed');
  $result = array('ok'=>$res[0]===true, 'summary'=>$summary, 'details'=>array('diag'=>$diag, 'message'=>isset($res[1])?$res[1]:null));
  $endUserSummary = $summary;
} else if ($action === 'list' && in_array($target, array('sandbox','live'), true)) {
  $cfg = isset($INVEXP_CONFIG['sftp'][$target]) ? $INVEXP_CONFIG['sftp'][$target] : array();
  $result = sftp_list($cfg, 50);
  // soften the end-user badge for SIT behavior
  if ($result['ok'] && isset($result['details']['listing_disabled']) && $result['details']['listing_disabled']) {
    $endUserSummary = 'Listing disabled (login OK)';
  } else {
    $endUserSummary = $result['ok'] ? 'Listed' : $result['summary'];
  }
} else if ($action === 'quickcheck' && in_array($target, array('sandbox','live'), true)) {
  $cfg = isset($INVEXP_CONFIG['sftp'][$target]) ? $INVEXP_CONFIG['sftp'][$target] : array();
  $diag = array('remote_dir'=>isset($cfg['remote_dir'])?$cfg['remote_dir']:'/incoming/generic_ff');
  list($ok, $sess) = sftp_connect_and_login($cfg, $diag);
  if ($ok) {
    $sftp = $sess['sftp']; $remoteDir = $diag['remote_dir'];
    $okCwd = @$sftp->chdir($remoteDir);
    $result = array('ok'=>$okCwd, 'summary'=>$okCwd ? 'Login + directory OK' : 'Login OK, directory not accessible', 'details'=>array('diag'=>$diag));
  } else {
    $result = array('ok'=>false, 'summary'=>'Auth failed (or blocked)', 'details'=>array('diag'=>$diag, 'message'=>is_string($sess)?$sess:null));
  }
  $endUserSummary = $result['summary'];
}

// Quick info for UI
$quick = array(
  'config_loaded' => $CONFIG_LOADED,
  'config_path' => $CONFIG_PATH,
  'csv_dir' => $csvDir,
  'export_sql' => $exportSql,
  'log_file' => $LOG_FILE,
);
list($latestCsv, $latestErr) = latest_csv($csvDir);

// -----------------------------------------------------------------------------
// UI
// -----------------------------------------------------------------------------
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Midway Export — Export & Upload</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8fafc; }
    .container-narrow { max-width: 980px; }
    .card { border-radius: 16px; }
    .small-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: .85rem; }
    .check-ok { color: #16a34a; }
    .check-bad { color: #dc2626; }
    .table-fixed { table-layout: fixed; }
    .nowrap { white-space: nowrap; }
  </style>
</head>
<body>
<div class="container container-narrow py-4">
  <h1 class="h4 mb-3">Midway Export — Export & Upload</h1>

  <!-- Preflight panel -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
        <div class="h6 mb-0">Preflight</div>
        <div class="small text-muted small-mono">Config: <?php echo h($quick['config_path']); ?></div>
      </div>
      <table class="table table-sm mt-2 mb-0">
        <tbody>
          <?php foreach (preflight_checks($INVEXP_CONFIG, $exportSql, $csvDir) as $chk): ?>
          <tr>
            <td class="nowrap"><?php echo h($chk['name']); ?></td>
            <td class="nowrap"><?php echo $chk['ok'] ? '<span class="check-ok">✔</span>' : '<span class="check-bad">✖</span>'; ?></td>
            <td class="small-mono text-muted"><?php echo h($chk['info']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Latest CSV summary -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="h6 mb-2">Latest CSV</div>
      <?php if ($latestCsv): ?>
        <div class="small-mono">Path: <?php echo h($latestCsv); ?></div>
        <div class="small text-muted small-mono">
          Size: <?php echo h(number_format((float)filesize($latestCsv))); ?> bytes ·
          Modified: <?php echo h(date('Y-m-d H:i:s', filemtime($latestCsv))); ?>
        </div>
      <?php else: ?>
        <div class="text-warning">No SHWN_MDYT_MT_YYYY-MM-DD.csv found in <?php echo h($csvDir); ?>.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Export button -->
  <div class="card mb-3">
    <div class="card-body">
      <form method="post" class="d-flex flex-wrap gap-2">
        <input type="hidden" name="action" value="export">
        <button class="btn btn-primary" type="submit">📤 Run Full Export (LIVE DB)</button>
      </form>
      <div class="mt-3 small text-muted small-mono">
        SQL: <?php echo h($quick['export_sql']); ?> ·
        CSV Dir: <?php echo h($quick['csv_dir']); ?> ·
        Log file: <?php echo h($quick['log_file']); ?>
      </div>
    </div>
  </div>

  <!-- Upload buttons -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <div class="h6">SANDBOX (SIT)</div>
          <form method="post" class="d-flex flex-wrap gap-2">
            <input type="hidden" name="action" value="upload">
            <input type="hidden" name="target" value="sandbox">
            <button class="btn btn-success" type="submit">🧪 Upload to SANDBOX</button>
          </form>
          <form method="post" class="d-flex flex-wrap gap-2 mt-2">
            <input type="hidden" name="action" value="quickcheck">
            <input type="hidden" name="target" value="sandbox">
            <button class="btn btn-outline-secondary" type="submit">✅ Quick Sandbox Check (login + dir)</button>
          </form>
          <form method="post" class="d-flex flex-wrap gap-2 mt-2">
            <input type="hidden" name="action" value="list">
            <input type="hidden" name="target" value="sandbox">
            <button class="btn btn-outline-secondary" type="submit">📂 List Sandbox Inbox (may be disabled)</button>
          </form>
        </div>
        <div class="col-md-6">
          <div class="h6">LIVE (Production)</div>
          <form method="post" class="d-flex flex-wrap gap-2">
            <input type="hidden" name="action" value="upload">
            <input type="hidden" name="target" value="live">
            <button class="btn btn-outline-danger" type="submit">🚚 Upload to LIVE</button>
          </form>
          <form method="post" class="d-flex flex-wrap gap-2 mt-2">
            <input type="hidden" name="action" value="quickcheck">
            <input type="hidden" name="target" value="live">
            <button class="btn btn-outline-secondary" type="submit">✅ Quick Live Check (login + dir)</button>
          </form>
          <form method="post" class="d-flex flex-wrap gap-2 mt-2">
            <input type="hidden" name="action" value="list">
            <input type="hidden" name="target" value="live">
            <button class="btn btn-outline-secondary" type="submit">📂 List Live Inbox</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <?php if ($action): ?>
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between">
        <div class="h6 mb-0">Result</div>
        <span class="badge <?php echo ($result && !empty($result['ok'])) ? 'bg-success' : 'bg-warning text-dark'; ?>">
          <?php echo h($endUserSummary ?: 'ERROR'); ?>
        </span>
      </div>

      <?php if ($action === 'list' && isset($result['files'])): ?>
        <div class="table-responsive mt-3">
          <table class="table table-sm table-striped table-bordered table-fixed align-middle">
            <thead><tr><th>Name</th><th class="nowrap">Size (bytes)</th><th class="nowrap">Modified</th></tr></thead>
            <tbody>
              <?php if (empty($result['files'])): ?>
                <tr><td colspan="3" class="text-muted text-center">No files listed.</td></tr>
              <?php else: foreach ($result['files'] as $f): ?>
                <tr>
                  <td class="small-mono"><?php echo h($f['name']); ?></td>
                  <td class="small-mono"><?php echo h($f['size']); ?></td>
                  <td class="small-mono"><?php echo h($f['mtime']); ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

      <details class="mt-3">
        <summary>Show technical diagnostics</summary>
        <pre class="small-mono bg-light p-2 border rounded" style="max-height: 60vh; overflow:auto;"><?php
          echo h(json_encode(array(
            'quick'=>$quick,
            'action'=>$action,
            'target'=>$target,
            'result'=>$result
          ), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
        ?></pre>
      </details>
    </div>
  </div>
  <?php endif; ?>

  <a class="btn btn-outline-secondary" href="export_all_in_one.php">⬅️ Back to Control Panel</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
