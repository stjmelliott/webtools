<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'invoice_export_config.php';
$config = $INVEXP_CONFIG;
// midway_sql_export.php - This script generates the CSV export using the unified SQL
require_once __DIR__ . '/db_connection.php';

$today = date('Y-m-d');
$outputFile = "C:/trax_invoice_export/SHWN_MDYT_MT_$today.csv";
$logDir = INVEXP_LOG_DIR;
$logFile = "$logDir/export_log_$today.txt";
if (!is_dir($logDir)) mkdir($logDir, 0777, true);

function logExport($message) {
    global $logFile;
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $message\n", FILE_APPEND);
}

try {
    $conn = getDB2Connection();  // From db_connection.php

    $sql = <<<SQL
SELECT
    'MDYT'                                  AS "Interline Carrier Code (SCAC)",
    CUSTOMER                                AS "Account Number",
    VARCHAR_FORMAT(BILL_DATE, 'YYYY-MM-DD') AS "Invoice Date",
    BILL_NUMBER                             AS "Invoice Number",
    CAST(TOTAL_CHARGES AS DECIMAL(10,2))    AS "Invoice Amount",
    r.trace_number                          AS "Bill of Lading",
    r.trace_number                          AS "Tracking Number",
    'USD'                                   AS "Billing Currency",
    'CC'                                    AS "Bill Type Ref",
    'Mid-Way Transportation, Inc.'          AS "Remit To - Company",
    'Mid-Way Transportation, Inc.'          AS "Remit To - Name",
    'PO Box 756'                            AS "Remit To - Address 1",
    'Hewitt'                                AS "Remit To - City",
    'TX'                                    AS "Remit To - State/Province",
    '76643'                                 AS "Remit To - Zip/Postal Code",
    'US'                                    AS "Remit To - Country",
    CALLNAME                                AS "Bill To - Company",
    CALLNAME                                AS "Bill To - Name",
    CALLADDR1                               AS "Bill To - Address 1",
    CALLCITY                                AS "Bill To - City",
    CALLPROV                                AS "Bill To - State/Province",
    CALLPC                                  AS "Bill To Zip/Postal Code",
    'US'                                    AS "Bill To - Country",
    ORIGNAME                                AS "Shipper - Company",
    ORIGNAME                                AS "Shipper - Name",
    ORIGADDR1                               AS "Shipper - Address 1",
    ORIGCITY                                AS "Shipper - City",
    ORIGPROV                                AS "Shipper - State/Province",
    ORIGPC                                  AS "Shipper - Zip/Postal Code",
    'US'                                    AS "Shipper - Country",
    DESTNAME                                AS "Consignee - Company",
    DESTNAME                                AS "Consignee - Name",
    DESTADDR1                               AS "Consignee - Address 1",
    DESTCITY                                AS "Consignee - City",
    DESTPROV                                AS "Consignee - State/Province",
    DESTPC                                  AS "Consignee - Zip/Postal Code",
    'US'                                    AS "Consignee - Country",
    SERVICE_LEVEL                           AS "Service Level",
    DATE(ACTUAL_PICKUP)                     AS "Ship Date",
    PIECES                                  AS "Pieces",
    'EA'                                    AS "Package Code Ref",
    CAST(INT(DISTANCE) AS VARCHAR(20))      AS "Miles", -- Changed this line
    CAST(WEIGHT AS DECIMAL(10,2))           AS "Bill Weight",
    CAST(WEIGHT AS DECIMAL(10,2))           AS "Ship Weight",
    'L'                                     AS "Weight Unit of Measure",
    DATE(ACTUAL_DELIVERY)                   AS "Delivery Date",
    CAST(ROLLUP_CHARGES AS DECIMAL(10,2))   AS "Freight Charge",
    CAST(ROLLUP_XCHARGES AS DECIMAL(10,2))  AS "Fuel Surcharge",
    ''                                      AS "Charge Code 1",
    ''                                      AS "Charge Description 1",
    CAST(0.00 AS DECIMAL(10,2))             AS "Charge Amount 1",
    ''                                      AS "Charge Quantity/UOM 1",
    ''                                      AS "Charge Code 2",
    ''                                      AS "Charge Description 2",
    CAST(0.00 AS DECIMAL(10,2))             AS "Charge Amount 2",
    ''                                      AS "Charge Quantity/UOM 2"
FROM TMWIN.TLORDER t
LEFT JOIN TRACE r
    ON r.DETAIL_NUMBER = t.DETAIL_LINE_ID
   AND r.TRACE_TYPE    = 2
WHERE
    t.CURRENT_STATUS IN ('APPRVD','BILLD')
    AND t.CUSTOMER = 'SHERBILLTO'
    AND COALESCE(t.USER10, '') = ''
ORDER BY
    t.BILL_NUMBER;
SQL;

    $stmt = odbc_prepare($conn, $sql);
    if (!$stmt) throw new Exception("Prepare failed: " . odbc_errormsg($conn));

    if (!odbc_execute($stmt)) throw new Exception("Execute failed: " . odbc_errormsg($conn));

    $fp = fopen($outputFile, 'w');
    $headerWritten = false;
    while ($row = odbc_fetch_array($stmt)) {
        if (!$headerWritten) {
            fputcsv($fp, array_keys($row));
            $headerWritten = true;
        }
        fputcsv($fp, $row);
    }
    fclose($fp);
    logExport("Export complete to $outputFile");
    echo json_encode(['status' => 'success', 'file' => $outputFile]);

} catch (Exception $e) {
    logExport("Export failed: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
