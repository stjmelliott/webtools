<?php
//  from ODBC
$dsn = "M3";  
$user = "db2admin";  
$password = "S0rdf1sh!";  

// Establish ODBC connection to DB2
$conn = odbc_connect($dsn, $user, $password);

if (!$conn) {
    die("Connection failed: " . odbc_errormsg());
}


// Sample query
$sql = "select userid,fullname,groups from tmwin.ST_CMS_USERS";
$result = odbc_exec($conn, $sql);

if ($result) {
    echo "<h3>Data Retrieved Successfully:</h3>";
    while ($row = odbc_fetch_array($result)) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    }
} else {
    echo "Query failed: " . odbc_errormsg();
}

// Close connection
odbc_close($conn);
?>
