<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';
$config = $INVEXP_CONFIG;


use phpseclib\Net\SFTP;
use phpseclib\Crypt\RSA;

// Load credentials
$config = 

// Date and paths
$today = date('Y-m-d');
$exportFile = "C:/trax_invoice_export/SHWN_MDYT_MT_$today.csv";
$remoteFile = "incoming/generic_ff/SHWN_MDYT_MT_$today.csv";
$logDir = INVEXP_LOG_DIR;
$logFile = "$logDir/sftp_upload_log_$today.txt";

if (!is_dir($logDir)) mkdir($logDir, 0777, true);

function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $message\n", FILE_APPEND);
}

function uploadToSFTP($details, $exportFile, $remoteFile) {
    $sftp = new SFTP($details['host'], $details['port']);

    $key = new RSA();
    $key->loadKey(file_get_contents($details['key_path']));

    if (!$sftp->login($details['user'], $key)) {
        logMessage("[{$details['user']}] Login failed to {$details['host']}");
        return false;
    }

    if (!$sftp->put($remoteFile, $exportFile, SFTP::SOURCE_LOCAL_FILE)) {
        logMessage("[{$details['user']}] Upload failed to $remoteFile");
        return false;
    }

    logMessage("[{$details['user']}] Successfully uploaded $exportFile to $remoteFile");
    return true;
}

// Upload to Test and Prod
logMessage("Starting SFTP uploads for $today");
uploadToSFTP($config['sftp']['test'], $exportFile, $remoteFile);
uploadToSFTP($config['sftp']['prod'], $exportFile, $remoteFile);
logMessage("Finished SFTP uploads for $today");
