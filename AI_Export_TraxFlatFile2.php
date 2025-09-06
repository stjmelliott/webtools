<?php
/**
 * AI_Export_TraxFlatFile.php  (rev-G) - Updated CSV Export with Specific Query
 * ------------------------------------------------------------
 * Mid-Way Transportation – Sherwin-Williams flat-file export (CSV for Excel)
 * ➜ Outputs a single CSV for all BILL_NUMBERs in one file
 * ➜ Automatically flags USER10 when exported
 * ➜ UI: preview + export all pending
 * ------------------------------------------------------------
 */
const DB_HOST = "localhost";
const DB_PORT = 50000;
const DB_NAME = "M3";
const DB_USER = "tmwin";
const DB_PASS = "S0rdf1sh";
const OUT_DIR = "C:/EDI"; // Still using C:/EDI as output directory
const CUSTOMER = "SHERBILLTO";

function dbconn()
{
    // Existing DB connection logic (no changes needed here)
    if (function_exists('db2_connect')) {
        $h = @db2_connect(DB_NAME, DB_USER, DB_PASS);
        if ($h) return ['type'=>'DB2','h'=>$h];
    }
    if (class_exists('PDO')) {
        $dsn = "odbc:Driver={IBM DB2 ODBC DRIVER};DATABASE=".DB_NAME.
               ";HOSTNAME=".DB_HOST.";PORT=".DB_PORT.
               ";PROTOCOL=TCPIP;";
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
            return ['type'=>'PDO','h'=>$pdo];
        } catch (PDOException $e) {}
    }
    if (function_exists('odbc_connect')) {
        $h = @odbc_connect('M3_DSN', DB_USER, DB_PASS, SQL_CUR_USE_ODBC);
        if (!$h) {
            $dsn = "Driver={IBM DB2 ODBC DRIVER};DATABASE=".DB_NAME.
                   ";HOSTNAME=".DB_HOST.";PORT=".DB_PORT.
                   ";PROTOCOL=TCPIP;";
            $h = @odbc_connect($dsn, DB_USER, DB_PASS, SQL_CUR_USE_ODBC);
        }
        if ($h) return ['type'=>'ODBC','h'=>$h];
    }
    throw new RuntimeException("No usable DB2 driver found (db2 / PDO ODBC / classic ODBC). Check your PHP extensions and DSN.");
}

// SQL to list bills that need to be exported
// This remains the same, selecting BILL_NUMBERs based on status and CUSTOMER, and not yet sent
$sqlList = <<<SQL
SELECT BILL_NUMBER
FROM    TLORDER
WHERE   CURRENT_STATUS IN ('APPRVD','BILLD')
  AND   CUSTOMER          = :customer_param
  AND (USER10 IS NULL OR USER10 NOT LIKE '210 SENT%')
ORDER   BY BILL_NUMBER
SQL;

// SQL to fetch the data for the CSV export - NOW USING YOUR EXACT NEW SELECT STATEMENT
$sqlCSV = <<<SQL
SELECT
    BILL_NUMBER                 AS invoice_number,
    DATE(BILL_DATE)             AS invoice_date,
    TOTAL_CHARGES               AS invoice_amount,
    TRACE_NO                    AS tracking_number,
    'PLACEHOLDER'               AS client_code,
    'MDYT'                      AS carrier_code_scac,
    CUSTOMER                    AS account_number,
    'CC'                        AS bill_type_ref,
    'Mid-Way Transportation, Inc.' AS remit_to_company,
    'PO Box 756'                AS remit_to_address_1,
    'Hewitt'                    AS remit_to_city,
    'TX'                        AS remit_to_state_province,
    '76643'                     AS remit_to_zip_postal_code,
    'US'                        AS remit_to_country,
    CALLNAME                    AS bill_to_company,
    CALLADDR1                   AS bill_to_address_1,
    CALLCITY                    AS bill_to_city,
    'US'                        AS bill_to_country,
    ORIGNAME                    AS shipper_company,
    ORIGADDR1                   AS shipper_address_1,
    ORIGCITY                    AS shipper_city,
    ORIGPROV                    AS shipper_state,
    ORIGPC                      AS shipper_zip,
    'US'                        AS shipper_country,
    DESTNAME                    AS consignee_company,
    DESTADDR1                   AS consignee_address_1,
    DESTCITY                    AS consignee_city,
    DESTPROV                    AS consignee_state,
    DESTPC                      AS consignee_zip,
    'US'                        AS consignee_country,
    SERVICE_LEVEL               AS service_level,
    DATE(ACTUAL_PICKUP)         AS ship_date,
    PIECES                      AS pieces,
    'EA'                        AS package_code_ref,
    WEIGHT                      AS bill_weight,
    WEIGHT                      AS ship_weight,
    'X'                         AS volume_unit_of_measure,
    'L'                         AS weight_unit_of_measure,
    BILL_NUMBER_KEY             AS bol_number,
    COMMODITY                   AS freight_description,
    DISTANCE                    AS total_miles,
    CHARGES                     AS freight_charge,
    XCHARGES                    AS other_charges,
    TOTAL_CHARGES               AS total_charge
FROM TMWIN.TLORDER
WHERE BILL_NUMBER = :bill_number_param
SQL;

// SQL to flag the exported BILL_NUMBER (this is the new UPDATE logic)
$sqlFlag = <<<SQL
UPDATE TMWIN.TLORDER
SET USER10='210 SENT: '||VARCHAR_FORMAT(CURRENT TIMESTAMP,'YYYY-MM-DD HH24:MI:SS')
WHERE BILL_NUMBER = :bill_number_param
SQL;

$act = $_GET['act'] ?? '';
if ($act === 'export') {
    echo "<pre>Exporting CSV...\n";
    $db = dbconn();
    $bills = [];

    // Prepare and execute sqlList using parameter for CUSTOMER
    if ($db['type'] === 'PDO') {
        $stmt = $db['h']->prepare($sqlList);
        $stmt->bindParam(':customer_param', CUSTOMER);
        $stmt->execute();
        $bills = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    } else {
        // Fallback for non-PDO connections, injecting parameter directly (less secure)
        $tempSqlList = str_replace(':customer_param', "'".CUSTOMER."'", $sqlList);
        if ($db['type'] === 'DB2') { $s=db2_exec($db['h'],$tempSqlList); while($r=db2_fetch_array($s)) $bills[]=$r[0]; }
        else if ($db['type'] === 'ODBC') { $s=odbc_exec($db['h'],$tempSqlList); while(odbc_fetch_row($s)) $bills[]=odbc_result($s,1); }
    }

    echo "Found ".count($bills)." invoices.\n";
    $rows = []; // This will store all rows for the CSV
    $exportedBillNumbers = []; // To store successfully processed bill numbers for the update

    // Prepare statements for efficiency if using PDO
    $stmtCSV = null;
    $stmtFlag = null;
    if ($db['type'] === 'PDO') {
        $stmtCSV = $db['h']->prepare($sqlCSV);
        $stmtFlag = $db['h']->prepare($sqlFlag);
    }

    foreach($bills as $bn){
        $row = null;
        if ($db['type'] === 'DB2') {
            $s = db2_exec($db['h'], str_replace(':bill_number_param', "'".$bn."'", $sqlCSV));
            $row = db2_fetch_assoc($s);
        } else if ($db['type'] === 'PDO') {
            $stmtCSV->bindParam(':bill_number_param', $bn);
            $stmtCSV->execute();
            $row = $stmtCSV->fetch(PDO::FETCH_ASSOC);
        } else if ($db['type'] === 'ODBC') {
            $stmt_odbc = odbc_exec($db['h'], str_replace(':bill_number_param', "'".$bn."'", $sqlCSV));
            if(odbc_fetch_row($stmt_odbc)){
                $row = [];
                for($i=1;$i<=odbc_num_fields($stmt_odbc);$i++){
                    $row[odbc_field_name($stmt_odbc,$i)] = odbc_result($stmt_odbc,$i);
                }
            }
        }

        if(!$row) {
            echo "Warning: No data found for BILL_NUMBER $bn. Skipping.\n";
            continue;
        }
        $rows[] = $row; // Add the fetched row (as an associative array) to our collection
        $exportedBillNumbers[] = $bn; // Mark for update if segments successfully extracted
    }

    // Now, perform the updates for all successfully extracted bills
    if (!empty($exportedBillNumbers)) {
        echo "Updating USER10 for ".count($exportedBillNumbers)." invoices.\n";
        foreach($exportedBillNumbers as $bn){
            if ($db['type'] === 'DB2') {
                db2_exec($db['h'], str_replace(':bill_number_param', "'".$bn."'", $sqlFlag));
            } else if ($db['type'] === 'PDO') {
                $stmtFlag->bindParam(':bill_number_param', $bn);
                $stmtFlag->execute();
            } else if ($db['type'] === 'ODBC') {
                odbc_exec($db['h'], str_replace(':bill_number_param', "'".$bn."'", $sqlFlag));
            }
        }
    }


    if($rows){
        $ts = date("Ymd_His");
        $outFile = OUT_DIR . "/Trax_Export_".$ts.".csv"; // Keep as .csv extension
        $f = fopen($outFile, 'w');
        // Write CSV header row
        fputcsv($f, array_keys($rows[0]));
        // Write data rows
        foreach($rows as $r) fputcsv($f, array_values($r));
        fclose($f);
        echo "Wrote $outFile\n";
    } else {
        echo "No data to write to file.\n";
    }

    // Handle commit/close
    if($db['type']==='DB2') {
        db2_commit($db['h']);
        db2_close($db['h']);
    } elseif ($db['type'] === 'PDO') {
        $db['h'] = null;
    } elseif ($db['type'] === 'ODBC') {
        odbc_close($db['h']);
    }

    echo "Done.";
    exit;
}
?><!DOCTYPE html><html><head><meta charset="utf-8"><title>Trax Flat‑File Export</title><style>body{font-family:Arial,sans-serif;margin:2rem;}button{padding:.6rem 1.2rem;margin-right:1rem;font-size:1rem;}</style></head><body>
<h2>Trax Flat‑File Export – <?php echo CUSTOMER;?> (<?php echo DB_NAME;?>)</h2>
<form method="get">
    <button name="act" value="preview">Preview Invoices</button>
    <button name="act" value="export">Export CSV &amp; Flag Sent</button>
</form>
<?php
if($act==='preview'){
    try{
        $db=dbconn();
        // Prepare and execute sqlList for preview (same logic as export for bill list)
        if ($db['type'] === 'PDO') {
            $stmt = $db['h']->prepare($sqlList);
            $stmt->bindParam(':customer_param', CUSTOMER);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } else {
            $tempSqlList = str_replace(':customer_param', "'".CUSTOMER."'", $sqlList);
            if ($db['type'] === 'DB2') { $s=db2_exec($db['h'],$tempSqlList); $rows=[]; while($r=db2_fetch_array($s)) $rows[]=$r[0]; }
            else if ($db['type'] === 'ODBC') { $s=odbc_exec($db['h'],$tempSqlList); $rows=[]; while(odbc_fetch_row($s)) $rows[]=odbc_result($s,1); }
        }
        echo '<h3>Invoices ready to send ('.count($rows).')</h3><ul>'; foreach($rows as $b) echo '<li>'.$b.'</li>'; echo '</ul>';
    }catch(Exception $e){ echo '<pre style="color:red">'.$e->getMessage()."</pre>"; }
}
?></body></html>