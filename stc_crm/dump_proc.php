<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "odbc-inc.php" );

	$debug = FALSE;
	$password = "";
	$proc = "NONE";
	$rows = "ALL";
	$valid_pw = "youdaman88";
	
	foreach($_GET as $key => $value) {
		//echo "<p>", $key, " = ", $value, "</p>";
		$key = strtoupper($key);
		if( $key == "DB" ) {
			$stc_database = $value;
			continue;
		} else if( $key == "DEBUG" ) {
			$debug = TRUE;
		} else if( $key == "PW" ) {
			$password = $value;
		} else if( $key == "PROC" ) {
			$proc = $value;
		} else if( $key == "ROWS" ) {
			$rows = $value;
		}
	}
	
	if( 1 || $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>DB2 Dump Proc - <?php echo $proc; ?></title>
</head>

<body>
<?
	}
	
	if( $password == $valid_pw ) {
		
		$query_string = "SELECT
				  TEXT
				FROM SYSCAT.PROCEDURES P, SYSCAT.PACKAGES K, SYSIBM.SYSDEPENDENCIES DEP
				WHERE DEP.DNAME = P.SPECIFICNAME
				AND DEP.DSCHEMA = P.PROCSCHEMA
				AND K.PKGNAME = DEP.BNAME
				AND DEP.DTYPE = 'F'
				AND PROCSCHEMA = '".$stc_schema."'
				AND PROCNAME = '".$proc."'";
		
		$response = send_odbc_query( $query_string, $stc_database, false );
					
		if( $response ) {
			if( $debug ) {
				echo "<pre>";
				var_dump($response);
				echo "</pre>";
			} else {
				echo "<pre>";
				echo $response[0]['TEXT'];
				echo "</pre>";
			}
		}
	} else {
		if( $debug ) echo "<p>Password error.</p>";
	}

	if( $debug ) {
?>
</body>
</html>
<?	
	}
?>

