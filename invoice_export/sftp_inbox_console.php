<?php
declare(strict_types=1);

/**
 * SFTP Inbox Console — Diagnostics v5 (clean JSON)
 * - JSON-only diagnostics: /sftp_inbox_console.php?action=diag
 * - No verbose SSH hex logs (keeps UI clean)
 * - Distinguishes connect vs auth failures
 * - Supports key passphrase and password fallback via $INVEXP_CONFIG
 */

ini_set('display_errors', '0');  // never leak HTML in JSON

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';

$creds = $INVEXP_CONFIG['sftp']['test'] ?? [];
$host  = $creds['host'] ?? '';
$port  = (int)($creds['port'] ?? 22);
$user  = $creds['user'] ?? '';
$keyPath = $creds['key_path'] ?? '';
$keyPass = $creds['key_passphrase'] ?? null;
$password = $creds['password'] ?? null;

// capture PHP warnings/fatals
$php_errors = [];
set_error_handler(function($severity, $message, $file, $line) use (&$php_errors) {
    $php_errors[] = ['severity'=>$severity, 'message'=>$message, 'file'=>$file, 'line'=>$line];
    return true;
});

function json_out($arr) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}
function tcp_probe($host, $port, $timeout = 5) {
    $errstr = ''; $errno = 0;
    $s = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($s) { fclose($s); return ['ok'=>true]; }
    return ['ok'=>false, 'errno'=>$errno, 'error'=>$errstr];
}

if (($_GET['action'] ?? '') === 'diag') {
    $res = [
        'ok' => false,
        'steps' => [],
        'pwd' => null,
        'entries' => [],
        'error' => null,
        'errors' => [],
        'serverId' => null,
        'connect_ok' => null,
        'tcp_probe' => tcp_probe($host, $port),
        'php_errors' => [],
    ];

    if (!invexp_phpseclib_available()) {
        $res['error'] = 'phpseclib not available';
        $res['errors'][] = 'Expected autoload at ' . (INVEXP_BASE . DIRECTORY_SEPARATOR . 'phpseclib');
        $res['php_errors'] = $GLOBALS['php_errors'] ?? [];
        json_out($res);
    }

    // we expect v2 API aliases to be present
    if (!class_exists('phpseclib\\Net\\SFTP', true) || !class_exists('phpseclib\\Crypt\\RSA', true)) {
        $res['error'] = 'SFTP/RSA classes unavailable';
        $res['errors'][] = 'Autoloader did not register expected classes';
        $res['php_errors'] = $GLOBALS['php_errors'] ?? [];
        json_out($res);
    }

    $res['steps'][] = 'Loading key: ' . $keyPath;
    $key = null;
    try {
        if ($keyPath && is_file($keyPath)) {
            $pem = @file_get_contents($keyPath);
            if ($pem !== false) {
                $key = new \phpseclib\Crypt\RSA();
                if ($keyPass && method_exists($key, 'setPassword')) { $key->setPassword($keyPass); }
                if (!$key->loadKey($pem)) { $res['steps'][] = 'Key load failed (bad format or passphrase)'; $key = null; }
            } else {
                $res['steps'][] = 'Unable to read key file';
            }
        } else {
            $res['steps'][] = 'Key file not found';
        }
    } catch (\Throwable $e) {
        $res['steps'][] = 'Exception while loading key: ' . $e->getMessage();
    }

    $sftp = new \phpseclib\Net\SFTP($host, $port, 10);
    $res['serverId'] = method_exists($sftp, 'getServerIdentification') ? $sftp->getServerIdentification() : null;
    $res['connect_ok'] = method_exists($sftp, 'isConnected') ? (bool)$sftp->isConnected() : null;

    // 1) key auth
    if ($key) {
        $res['steps'][] = "Connecting to $host:$port as $user (key auth)";
        if ($sftp->login($user, $key)) {
            $res['pwd'] = $sftp->pwd();
            $list = $sftp->nlist('/incoming/generic_ff');
            if ($list !== false) { $res['entries'] = $list; $res['ok'] = true; $res['php_errors'] = $GLOBALS['php_errors'] ?? []; json_out($res); }
            $res['error'] = 'Login OK but listing failed';
            $res['php_errors'] = $GLOBALS['php_errors'] ?? []; json_out($res);
        } else {
            $res['steps'][] = 'Key auth failed';
            $res['error'] = ($res['serverId'] === null || $res['serverId'] === false) ? 'Connection failed (no SSH banner)' : 'Login failed (key)';
        }
    }

    // 2) password fallback
    if (!empty($password)) {
        $res['steps'][] = "Retry as $user with password (fallback)";
        $sftp2 = new \phpseclib\Net\SFTP($host, $port, 10);
        if ($sftp2->login($user, $password)) {
            $res['pwd'] = $sftp2->pwd();
            $list = $sftp2->nlist('/incoming/generic_ff');
            if ($list !== false) { $res['entries'] = $list; $res['ok'] = true; $res['php_errors'] = $GLOBALS['php_errors'] ?? []; json_out($res); }
            $res['error'] = 'Password login OK but listing failed';
            $res['php_errors'] = $GLOBALS['php_errors'] ?? []; json_out($res);
        } else {
            $res['steps'][] = 'Password auth failed';
            $res['error'] = 'Login failed (password)';
        }
    }

    $res['php_errors'] = $GLOBALS['php_errors'] ?? [];
    json_out($res);
}

// ---- HTML UI (uses JSON endpoint above) ----
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>SFTP Inbox Console</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 20px; }
    pre { background: #f6f8fa; padding: 12px; border-radius: 6px; }
    button { padding: 10px 16px; }
    .muted { color: #6c757d; }
  </style>
</head>
<body>
  <h1 class="h4 mb-3">SFTP Inbox Console</h1>
  <p class="muted">Click to run a read-only diagnostic on the SIT inbox (<code>/incoming/generic_ff</code>).</p>
  <button class="btn btn-primary" onclick="runDiag()">Run Diagnostics</button>
  <h3 class="h6 mt-3">Result</h3>
  <pre id="out">(nothing yet)</pre>

<script>
async function runDiag() {
  const out = document.getElementById('out');
  out.textContent = 'Running...';
  try {
    const r = await fetch('?action=diag', {cache:'no-store'});
    const t = await r.text();
    try {
      const j = JSON.parse(t);
      out.textContent = JSON.stringify(j, null, 2);
    } catch (e) {
      out.textContent = "Non-JSON response (raw):\n\n" + t;
    }
  } catch (e) {
    out.textContent = 'Error: ' + e;
  }
}
</script>
</body>
</html>
