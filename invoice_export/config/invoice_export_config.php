<?php
/**
 * invoice_export_config.php
 * Place at: C:\clients\midway\webtools\invoice_export\config\invoice_export_config.php
 *
 * This file centralizes paths, DB2 credentials, and SFTP endpoints.
 * It also defines a few compatibility constants used by older pages.
 */

// -----------------------------------------------------------------------------
// Compatibility constants for older scripts
// -----------------------------------------------------------------------------
if (!defined('INVEXP_BASE')) {
  define('INVEXP_BASE', 'C:\clients\midway\webtools\invoice_export');
}
if (!defined('INVEXP_LOG_DIR')) {
  define('INVEXP_LOG_DIR', 'C:\clients\midway\webtools\log');
}
if (!defined('INVEXP_PHP')) {
  // Path to PHP CLI (not required by all pages, handy for scheduled jobs)
  define('INVEXP_PHP', 'C:\xampp\php\php.exe');
}

// -----------------------------------------------------------------------------
// Central configuration array (newer pages read this)
// -----------------------------------------------------------------------------
$INVEXP_CONFIG = [
  'db2' => [
    'db'   => 'M3',
    'user' => 'tmwin',
    'pass' => 'REPLACE_WITH_DB2_PASSWORD', // <-- put the real password here
  ],
  'paths' => [
    // SQL script that builds the SHWN_MDYT_MT_YYYY-MM-DD.csv
    'export_sql'    => INVEXP_BASE . '\back_end\export_script.sql',
    // Directory where the CSV is written
    'csv_dir'       => 'C:\trax_invoice_export',
    // Where phpseclib is located (autoload.php / bootstrap.php inside)
    'phpseclib_dir' => INVEXP_BASE . '\phpseclib',
  ],
  'sftp' => [
    // Sherwin-Williams SIT / Sandbox (read-only test friendly)
    'sandbox' => [
      'host'           => 'b2b.sit.veraction.com',
      'port'           => 8022,
      'user'           => 'mdyt_ff_t',
      // Use a private key for auth (preferred). Leave password empty when using a key
      'key_path'       => 'C:/midway keys/midway_private_test.key',
      'key_passphrase' => '',  // if the key is encrypted
      'password'       => '',  // optional fallback
      'remote_dir'     => '/incoming/generic_ff',
    ],
    // LIVE / Production — fill these in when you are ready
    'live' => [
      'host'           => '',      // e.g. b2b.veraction.com
      'port'           => 22,
      'user'           => '',
      'key_path'       => '',
      'key_passphrase' => '',
      'password'       => '',
      'remote_dir'     => '/incoming/generic_ff',
    ],
  ],
  'ui' => [
    // Turn on/off extra debug panels in some pages
    'debug' => true,
  ],
];

// Nothing to return; pages will include this file and read $INVEXP_CONFIG.
