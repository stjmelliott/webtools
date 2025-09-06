<?php
/**
 * AI_Export_TraxFlatFile.php  (rev‑E)
 * ------------------------------------------------------------
 * Mid‑Way Transportation – Sherwin‑Williams flat‑file export
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
const OUT_DIR = "C:/EDI";
const CUSTOMER = "SHERBILLTO";

function dbconn()
{
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

$sqlList = <<<SQL
SELECT BILL_NUMBER
FROM   TLORDER
WHERE  CURRENT_STATUS IN ('APPRVD','BILLD')
  AND  CUSTOMER        = 'SHERBILLTO'
  AND (USER10 IS NULL OR USER10 NOT LIKE '210 SENT%')
ORDER  BY BILL_NUMBER
SQL;

$sqlCSV = fn(string $bill) => "SELECT BILL_NUMBER AS invoice_number, DATE(BILL_DATE) AS invoice_date, TRACE_NO AS pro_number, BILL_NUMBER_KEY AS bol_number, ORIGCITY AS shipper_city, ORIGPROV AS shipper_state, ORIGPC AS shipper_zip, DESTCITY AS consignee_city, DESTPROV AS consignee_state, DESTPC AS consignee_zip, COMMODITY AS freight_description, WEIGHT AS total_weight, DISTANCE AS total_miles, CHARGES AS freight_charge, XCHARGES AS other_charges, TOTAL_CHARGES AS total_charge FROM TLORDER WHERE BILL_NUMBER='".$bill."'";

$sqlFlag = fn(string $bill) => "UPDATE TLORDER SET USER10='210 SENT: '||VARCHAR_FORMAT(CURRENT TIMESTAMP,'YYYY-MM-DD HH24:MI:SS') WHERE BILL_NUMBER='".$bill."'";

$act = $_GET['act'] ?? '';
if ($act === 'export') {
    echo "<pre>Exporting...\n";
    $db = dbconn();
    $bills = [];
    switch ($db['type']) {
        case 'DB2':  $s=db2_exec($db['h'],$sqlList); while($r=db2_fetch_array($s)) $bills[]=$r[0]; break;
        case 'PDO':  $bills=$db['h']->query($sqlList)->fetchAll(PDO::FETCH_COLUMN,0); break;
        case 'ODBC': $s=odbc_exec($db['h'],$sqlList); while(odbc_fetch_row($s)) $bills[]=odbc_result($s,1); break;
    }
    echo "Found ".count($bills)." invoices\n";
    $rows = [];
    foreach($bills as $bn){
        switch($db['type']){
            case 'DB2':  $row=db2_fetch_assoc(db2_exec($db['h'],$sqlCSV($bn))); break;
            case 'PDO':  $row=$db['h']->query($sqlCSV($bn))->fetch(PDO::FETCH_ASSOC); break;
            case 'ODBC': $stmt=odbc_exec($db['h'],$sqlCSV($bn)); $row=[]; if(odbc_fetch_row($stmt)){for($i=1;$i<=odbc_num_fields($stmt);$i++){ $row[odbc_field_name($stmt,$i)] = odbc_result($stmt,$i);} } break;
        }
        if(!$row) continue;
        $rows[] = $row;
        switch($db['type']){
            case 'DB2':  db2_exec($db['h'],$sqlFlag($bn)); break;
            case 'PDO':  $db['h']->exec($sqlFlag($bn)); break;
            case 'ODBC': odbc_exec($db['h'],$sqlFlag($bn)); break;
        }
    }
    if($rows){
        $ts = date("Ymd_His");
        $outFile = OUT_DIR . "/Trax_Export_".$ts.".csv";
        $f = fopen($outFile, 'w');
        fputcsv($f, array_keys($rows[0]));
        foreach($rows as $r) fputcsv($f, array_values($r));
        fclose($f);
        echo "Wrote $outFile\n";
    }
    if($db['type']==='DB2') { db2_commit($db['h']); db2_close($db['h']); }
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
        switch($db['type']){
            case 'DB2':  $s=db2_exec($db['h'],$sqlList); $rows=[]; while($r=db2_fetch_array($s)) $rows[]=$r[0]; break;
            case 'PDO':  $rows=$db['h']->query($sqlList)->fetchAll(PDO::FETCH_COLUMN,0); break;
            case 'ODBC': $s=odbc_exec($db['h'],$sqlList); $rows=[]; while(odbc_fetch_row($s)) $rows[]=odbc_result($s,1); break;
        }
        echo '<h3>Invoices ready to send ('.count($rows).')</h3><ul>'; foreach($rows as $b) echo '<li>'.$b.'</li>'; echo '</ul>';
    }catch(Exception $e){ echo '<pre style="color:red">'.$e->getMessage()."</pre>"; }
}
?></body></html>
