<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';
$config = $INVEXP_CONFIG;
/**
 * ============================================================================
 * SFTP INBOX DIAGNOSTICS (READ-ONLY BY DEFAULT)
 * ============================================================================
 * Purpose:
 *   This page helps you verify your SFTP connection and see what's going on
 *   after you run an export. It mirrors the same connection method your
 *   uploader uses (phpseclib autoloader + namespaced classes), and it reads
 *   credentials directly from "midway_credentials.php". It does not change
 *   any of your existing logic.
 *
 * What it checks (SAFE by default):
 *   1) Connects and logs in to the SFTP server using your exact creds.
 *   2) Shows where you are on the server (pwd/realpath).
 *   3) Tries to move into your inbound directory (absolute + relative).
 *   4) Tries to list files (some sites block listing; that's normal).
 *   5) Always tries to look up a specific file (stat/lstat) so you can confirm
 *      if a particular CSV exists—even when listing is blocked.
 *   6) Shows metadata for the directory itself (useful to prove access).
 *
 * OPTIONAL (still safe unless enabled):
 *   - Re-stat after a short delay (detects if the server "sweeps" files fast).
 *   - Show SSH/SFTP logs and negotiated ciphers (for deep troubleshooting).
 *   - Show server host-key fingerprints (SHA256/MD5).
 *   - Stat multiple filenames in one go (comma-separated list).
 *   - (Off by default) A tiny "write probe": put -> rename -> delete a temp file.
 *     ONLY runs if you explicitly enable it with confirm=YES.
 *
 * --------------------------------------------------------------------------
 * HOW TO USE (examples you can paste into your browser):
 * --------------------------------------------------------------------------
 *   • SIT default:
 *       sftp_inbox_check.php
 *
 *   • PROD:
 *       sftp_inbox_check.php?env=prod
 *
 *   • Check one specific file name:
 *       sftp_inbox_check.php?file=SHWN_MDYT_MT_2025-08-13_143012.csv
 *
 *   • Re-stat after 5 seconds (detect fast "sweep" after upload):
 *       sftp_inbox_check.php?file=SHWN_MDYT_MT_2025-08-13_143012.csv&restat=1&delay=5
 *
 *   • Stat multiple filenames (comma-separated):
 *       sftp_inbox_check.php?statlist=A.csv,B.csv,C.csv
 *
 *   • Override the remote directory (if your inbound path changes):
 *       sftp_inbox_check.php?dir=/incoming/generic_ff_alt
 *
 *   • Show server fingerprints + SSH/SFTP logs (for support tickets):
 *       sftp_inbox_check.php?fingerprint=1&showlogs=1
 *
 *   • (OPTIONAL) Write probe — ONLY if you want to test write/rename/delete:
 *       sftp_inbox_check.php?probe=put&confirm=YES
 *
 * --------------------------------------------------------------------------
 * WHAT THE RESULTS MEAN (quick read):
 * --------------------------------------------------------------------------
 *   • "LOGIN FAILED"  → Wrong key/format/passphrase/user or IP not whitelisted.
 *   • "rawlist: NOT PERMITTED" → Write-only inbox (normal for many B2B hubs).
 *   • "stat(filename): NOT FOUND or blocked" → Either the file isn't there or
 *       the server blocks read-back/stat. If your upload succeeded earlier,
 *       it's often because the intake system moved ("swept") your file already.
 *   • Use unique filenames (add invoice # or timestamp) so you can stat the
 *       exact name immediately after upload.
 *
 * SAFETY:
 *   • This page is read-only unless you explicitly enable the write probe.
 *   • It reuses the same credentials and library wiring as your uploader.
 *   • No changes to your export/upload logic.
 *
 * ============================================================================
 */

date_default_timezone_set('America/Chicago');
header('Content-Type: text/plain');

/* ----------------------------------------------------------------------------
 * 1) Load phpseclib exactly the way your uploader does (NO logic changes)
 *    - autoload.php gives us the namespaced classes we need.
 * -------------------------------------------------------------------------- */

use phpseclib\Net\SFTP;
use phpseclib\Crypt\RSA;

/* ----------------------------------------------------------------------------
 * 2) Load your exact credentials from "midway_credentials.php"
 *    - This file should return the array you showed me (with sftp.test/prod).
 * -------------------------------------------------------------------------- */
$cfg = isset($INVEXP_CONFIG) ? $INVEXP_CONFIG : (isset($config) ? $config : []);
if (!is_array($cfg) || !isset($cfg['sftp'])) {
    echo "ERROR: midway_credentials.php missing 'sftp' array.\n";
    exit(1);
}

/* ----------------------------------------------------------------------------
 * 3) Read input parameters (these DO NOT change any business logic):
 *    - env       : which environment to hit (default 'test' for SIT)
 *    - dir       : which remote directory to check (defaults to your inbound)
 *    - file      : a specific file to stat (defaults to today's export name)
 *    - statlist  : extra filenames to stat (comma-separated)
 *    - restat    : if '1', wait a bit and stat again (detect fast sweep)
 *    - delay     : how many seconds to wait before restat (default 2)
 *    - fingerprint: if '1', show server host-key fingerprints
 *    - showlogs  : if '1', print SSH/SFTP logs (for deep troubleshooting)
 *    - perms     : if '1', include directory permission bits in output
 *    - probe     : if 'put' AND confirm=YES, run safe write probe (optional)
 * -------------------------------------------------------------------------- */
$env   = (isset($_GET['env']) && strtolower($_GET['env']) === 'prod') ? 'prod' : 'test';
$creds = $cfg['sftp'][$env] ?? null;

// Default inbound directory used by your flow; can be overridden via ?dir=
$remoteDir = isset($_GET['dir']) && $_GET['dir'] !== '' ? ltrim($_GET['dir'], '/') : 'incoming/generic_ff';

// Default target filename is today's export name; override via ?file=
$today  = date('Y-m-d');
$target = isset($_GET['file']) && $_GET['file'] !== '' ? basename($_GET['file']) : "SHWN_MDYT_MT_" . $today . ".csv";

// Allow statting multiple filenames at once (?statlist=A.csv,B.csv)
$statlist = [];
if (isset($_GET['statlist']) && $_GET['statlist'] !== '') {
    foreach (explode(',', $_GET['statlist']) as $nm) {
        $nm = trim($nm);
        if ($nm !== '') $statlist[] = basename($nm); // basename() = safety
    }
}

// Optional extras
$doRestat    = isset($_GET['restat']) && $_GET['restat'] == '1';
$restatDelay = isset($_GET['delay']) && is_numeric($_GET['delay']) ? max(1, (int)$_GET['delay']) : 2;
$showLogs    = isset($_GET['showlogs']) && $_GET['showlogs'] == '1';
$showFP      = isset($_GET['fingerprint']) && $_GET['fingerprint'] == '1';
$showPerm    = isset($_GET['perms']) && $_GET['perms'] == '1';
$doProbe     = (isset($_GET['probe']) && $_GET['probe'] === 'put' && isset($_GET['confirm']) && $_GET['confirm'] === 'YES');

/* ----------------------------------------------------------------------------
 * 4) Validate credentials (MUST exist in your config; we don't guess)
 * -------------------------------------------------------------------------- */
if (!$creds || !isset($creds['host'], $creds['port'], $creds['user'], $creds['key_path'])) {
    echo "ERROR: Missing host/port/user/key_path for env '" . $env . "' in midway_credentials.php.\n";
    exit(1);
}

// Unpack creds (verbatim)
$host     = $creds['host'];
$port     = (int)$creds['port'];
$user     = $creds['user'];
$keyPath  = $creds['key_path'];
$keyPass  = isset($creds['key_pass']) ? $creds['key_pass'] : null;  // optional passphrase
$password = isset($creds['password']) ? $creds['password'] : null;  // optional password fallback

/* ----------------------------------------------------------------------------
 * 5) Print a simple report of what we’re about to do (no secrets)
 * -------------------------------------------------------------------------- */
echo "=== SFTP Inbox Diagnostics (" . $env . ") ===\n";
echo "Host:   " . $host . "\n";
echo "Port:   " . $port . "\n";
echo "User:   " . $user . "\n";
echo "Key:    " . $keyPath . "\n";
echo "Dir:    /" . $remoteDir . "\n";
echo "Target: " . $target . "\n";
echo "Restat: " . ($doRestat ? "enabled (" . $restatDelay . "s)" : "disabled") . "\n";
echo "Probe:  " . ($doProbe ? "put/rename/delete (explicitly enabled)" : "OFF (safe)") . "\n";
echo "Logs:   " . ($showLogs ? "ON" : "OFF") . "\n";
echo "FP:     " . ($showFP ? "ON" : "OFF") . "\n";
echo "Perms:  " . ($showPerm ? "ON" : "OFF") . "\n\n";

/* ----------------------------------------------------------------------------
 * 6) Load private key file and authenticate just like your uploader
 *    - If your key requires a passphrase, put it in midway_credentials.php
 *      as 'key_pass' and we'll apply it here.
 *    - If password fallback is present, we try it only if key login fails.
 * -------------------------------------------------------------------------- */
if (!is_file($keyPath)) {
    echo "ERROR: Key file not found at " . $keyPath . "\n";
    exit(2);
}
$pem = @file_get_contents($keyPath);
if ($pem === false) {
    echo "ERROR: Unable to read key file: " . $keyPath . "\n";
    exit(3);
}

// Create the SFTP client and load your key
$sftp = new SFTP($host, $port, 20);
$key  = new RSA();
if (!empty($keyPass)) { $key->setPassword($keyPass); } // apply passphrase if you have one

if (!$key->loadKey($pem)) {
    echo "ERROR: Failed to load key (use OpenSSH/PKCS1 PEM, not .ppk).\n";
    exit(4);
}

// Try key login (same as uploader); then optional password fallback if provided
$authOK = @$sftp->login($user, $key);
if (!$authOK && !empty($password)) {
    $authOK = @$sftp->login($user, $password);
}
if (!$authOK) {
    echo "LOGIN FAILED. Check whitelist/IP, key (and passphrase), or account status for '" . $env . "'.\n";
    exit(5);
}

/* ----------------------------------------------------------------------------
 * 7) (Optional) Show server host-key fingerprints (handy for tickets)
 * -------------------------------------------------------------------------- */
if ($showFP && method_exists($sftp, 'getServerPublicHostKey')) {
    echo "[ Host Key Fingerprints ]\n";
    $hk = $sftp->getServerPublicHostKey();
    if ($hk !== false) {
        $raw = $hk->getPublicKey();
        $sha256 = base64_encode(hash('sha256', $raw, true));
        $md5 = implode(':', str_split(bin2hex(hash('md5', $raw, true)), 2));
        echo "SHA256: " . $sha256 . "\n";
        echo "MD5:    " . $md5 . "\n\n";
    } else {
        echo "Unable to retrieve host key.\n\n";
    }
}

/* ----------------------------------------------------------------------------
 * 8) Bearings: where are we, and where is the inbound folder?
 *    - pwd(): current working directory on the SFTP server
 *    - realpath(): resolves the absolute path of your inbound directory
 * -------------------------------------------------------------------------- */
echo "[ Bearings ]\n";
$pwd1 = $sftp->pwd();
echo "pwd() before chdir: " . ($pwd1 !== false ? $pwd1 : '[unknown]') . "\n";
$rp  = $sftp->realpath("/" . $remoteDir . "");
echo "realpath(/" . $remoteDir . "): " . ($rp !== false ? $rp : '[not resolved]') . "\n\n";

/* ----------------------------------------------------------------------------
 * 9) Try to change into your inbound directory
 *    - First, try absolute path. If that fails, try relative path.
 * -------------------------------------------------------------------------- */
echo "[ chdir Tests ]\n";
$absChdir = $sftp->chdir("/" . $remoteDir . "");
echo "chdir(/" . $remoteDir . "): " . ($absChdir ? "OK" : "FAIL") . "\n";
if (!$absChdir) {
    $relChdir = $sftp->chdir($remoteDir);
    echo "chdir(" . $remoteDir . "): " . ($relChdir ? "OK" : "FAIL") . "\n";
}
echo "pwd() after chdir: " . (($pwd2 = $sftp->pwd()) !== false ? $pwd2 : '[unknown]') . "\n\n";

/* ----------------------------------------------------------------------------
 * 10) Directory listing (many hubs block this on inbound "drop-box" folders)
 *     - We attempt listing both relative ('.') and absolute.
 *     - If blocked, that's normal; move on to stat() checks below.
 * -------------------------------------------------------------------------- */
echo "[ Directory Listing ]\n";
$listRel = $sftp->rawlist('.', true);
echo "rawlist('.')            : " . (($listRel === false) ? "NOT PERMITTED" : "OK") . "\n";
$listAbs = $sftp->rawlist("/" . $remoteDir . "", true);
echo "rawlist(/" . $remoteDir . ")  : " . (($listAbs === false) ? "NOT PERMITTED" : "OK") . "\n";

if (is_array($listRel) && count($listRel) > 0) {
    echo "\nContents ('.'):\n";
    $rows = 0;
    foreach ($listRel as $name => $meta) {
        if ($name === '.' || $name === '..') continue;
        $size  = isset($meta['size'])  ? $meta['size']  : 0;
        $mtime = isset($meta['mtime']) ? date('Y-m-d H:i:s', $meta['mtime']) : '-';
        echo str_pad($size, 12, ' ', STR_PAD_LEFT) . "  " . $mtime . "  " . $name . "\n";
        $rows++;
    }
    if ($rows === 0) echo "[Empty]\n";
} else {
    echo "[No visible contents or listing blocked]\n";
}
echo "\n";

/* ----------------------------------------------------------------------------
 * 11) File probes: try to locate a specific file by name (stat/lstat)
 *     - This works even when listing is blocked (if the server allows stat).
 *     - Use unique filenames when you upload so you can target them here.
 * -------------------------------------------------------------------------- */
echo "[ File Probes ]\n";
echo "stat(" . $target . "): ";
$st = $sftp->stat($target);
if ($st !== false && isset($st['size'])) {
    $mtime = isset($st['mtime']) ? date('Y-m-d H:i:s', $st['mtime']) : '-';
    echo "FOUND  size=" . $st['size'] . "  mtime=" . $mtime . "\n";
} else {
    echo "NOT FOUND or blocked\n";
}

echo "lstat(" . $target . "): ";
$lst = $sftp->lstat($target);
if ($lst !== false && isset($lst['size'])) {
    $mtime2 = isset($lst['mtime']) ? date('Y-m-d H:i:s', $lst['mtime']) : '-';
    echo "FOUND  size=" . $lst['size'] . "  mtime=" . $mtime2 . "\n";
} else {
    echo "NOT FOUND or blocked\n";
}

/* ----------------------------------------------------------------------------
 * 12) Directory metadata: stat('.') tells us the folder's size/mtime
 *     - If perms=1, we also print the permission bits/mode (octal).
 * -------------------------------------------------------------------------- */
echo "stat('.') metadata: ";
$stDot = $sftp->stat('.');
if ($stDot !== false) {
    $sz = isset($stDot['size']) ? $stDot['size'] : 0;
    $mt = isset($stDot['mtime']) ? date('Y-m-d H:i:s', $stDot['mtime']) : '-';
    echo "size=" . $sz . " mtime=" . $mt . "";
    if ($showPerm && isset($stDot['mode'])) {
        $modeOct = decoct($stDot['mode']);
        echo " mode=0" . $modeOct . "";
    }
    echo "\n";
} else {
    echo "not available\n";
}

/* ----------------------------------------------------------------------------
 * 13) Stat additional filenames if you passed a list (?statlist=A.csv,B.csv)
 * -------------------------------------------------------------------------- */
if (!empty($statlist)) {
    echo "\n[ Additional statlist ]\n";
    foreach ($statlist as $nm) {
        echo "stat(" . $nm . "): ";
        $sx = $sftp->stat($nm);
        if ($sx !== false && isset($sx['size'])) {
            $mtx = isset($sx['mtime']) ? date('Y-m-d H:i:s', $sx['mtime']) : '-';
            echo "FOUND size=" . $sx['size'] . " mtime=" . $mtx . "\n";
        } else {
            echo "NOT FOUND or blocked\n";
        }
    }
}

/* ----------------------------------------------------------------------------
 * 14) Optional "re-stat after delay" to detect fast sweep
 *     - If the intake system moves files quickly, the second stat might miss.
 * -------------------------------------------------------------------------- */
if ($doRestat) {
    echo "\n[ Re-Stat After Delay ]\n";
    echo "Sleeping " . $restatDelay . " seconds...\n";
    sleep($restatDelay);
    echo "stat(" . $target . ") again: ";
    $st2 = $sftp->stat($target);
    if ($st2 !== false && isset($st2['size'])) {
        $mtime3 = isset($st2['mtime']) ? date('Y-m-d H:i:s', $st2['mtime']) : '-';
        echo "FOUND  size=" . $st2['size'] . "  mtime=" . $mtime3 . "\n";
    } else {
        echo "NOT FOUND or blocked\n";
    }
}

/* ----------------------------------------------------------------------------
 * 15) OPTIONAL WRITE PROBE (OFF unless you enable ?probe=put&confirm=YES)
 *     - This is a safe way to test write/rename/delete capabilities without
 *       touching your real invoice files. Only runs if you explicitly confirm.
 * -------------------------------------------------------------------------- */
if ($doProbe) {
    echo "\n[ Write Probe ]\n";
    // We use a harmless temp filename so we don't collide with real data.
    $tmpName   = "MWY_DIAG_" . date('Ymd_His') . ".tmp";
    $finalName = $tmpName . ".ok";
    $payload   = "midway-diagnostics " . date('c') . "\n";

    echo "put(" . $tmpName . ") ... ";
    $okPut = $sftp->put($tmpName, $payload);       // try writing a tiny file
    echo ($okPut ? "OK\n" : "FAIL\n");

    echo "rename(" . $tmpName . " -> " . $finalName . ") ... ";
    $okRen = $okPut ? $sftp->rename($tmpName, $finalName) : false;  // test rename
    echo ($okRen ? "OK\n" : "FAIL\n");

    echo "delete(" . $finalName . ") ... ";
    $okDel = $okRen ? $sftp->delete($finalName) : false;            // clean up
    echo ($okDel ? "OK\n" : "FAIL (likely no delete permission)\n");
}

/* ----------------------------------------------------------------------------
 * 16) OPTIONAL: SSH/SFTP negotiated algorithms and logs (for deep dives)
 *     - Useful when support needs ciphers/KEX/MAC info or wire logs.
 * -------------------------------------------------------------------------- */
if ($showLogs) {
    echo "\n[ Negotiated Algorithms ]\n";
    if (method_exists($sftp, 'getAlgorithmsNegotiated')) {
        $alg = $sftp->getAlgorithmsNegotiated();
        if ($alg) {
            foreach ($alg as $k => $v) {
                if (is_array($v)) $v = json_encode($v);
                echo "" . $k . ": " . $v . "\n";
            }
        } else {
            echo "n/a\n";
        }
    } else {
        echo "Method getAlgorithmsNegotiated() not available.\n";
    }

    echo "\n[ SSH Log ]\n";
    if (method_exists($sftp, 'getLog')) {
        echo $sftp->getLog() . "\n";
    } else {
        echo "SSH log not available on this phpseclib build.\n";
    }

    echo "\n[ SSH Errors ]\n";
    if (method_exists($sftp, 'getErrors')) {
        $errs = $sftp->getErrors();
        if (!empty($errs)) { foreach ($errs as $e) echo "- " . $e . "\n"; }
        else echo "[none]\n";
    } else {
        echo "SSH errors not available on this build.\n";
    }

    echo "\n[ SFTP Log ]\n";
    if (method_exists($sftp, 'getSFTPLog')) {
        echo $sftp->getSFTPLog() . "\n";
    } else {
        echo "SFTP log not available on this build.\n";
    }

    echo "\n[ SFTP Errors ]\n";
    if (method_exists($sftp, 'getSFTPErrors')) {
        $serrs = $sftp->getSFTPErrors();
        if (!empty($serrs)) { foreach ($serrs as $e) echo "- " . $e . "\n"; }
        else echo "[none]\n";
    } else {
        echo "SFTP errors not available on this build.\n";
    }
}

/* ----------------------------------------------------------------------------
 * 17) Done
 * -------------------------------------------------------------------------- */
echo "\n[ Done ]\n";
exit(0);