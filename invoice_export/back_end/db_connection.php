<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';
$config = $INVEXP_CONFIG;
// Set timezone
date_default_timezone_set('America/Chicago');

// DB2 connection credentials
define('DB2_DBNAME', 'M3');
define('DB2_DBUSER', 'tmwin');
define('DB2_DBPASS', 'S0rdf1sh');
define('DB2_HOST', 'localhost');
define('DB2_PORT', '50000');

// Function to return DB2 ODBC connection
function getDB2Connection() {
    $dsn = "DRIVER={IBM DB2 ODBC DRIVER};DATABASE=" . DB2_DBNAME . ";HOSTNAME=" . DB2_HOST . ";PORT=" . DB2_PORT . ";PROTOCOL=TCPIP;";
    $conn = odbc_connect($dsn, DB2_DBUSER, DB2_DBPASS);

    if (!$conn) {
        die("Connection failed: " . odbc_errormsg());
    }

    return $conn;
}
?>
