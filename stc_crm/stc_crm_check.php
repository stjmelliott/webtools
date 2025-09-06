<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "odbc-inc.php" );

	$debug = FALSE;
	$password = "";
	$table = "NONE";
	$rows = "ALL";
	$valid_pw = "";
	
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
		}
	}
	
	if( $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>STC Web Tools Installation Check</title>
</head>

<body>
<?php
	}
	
	if( $password == $valid_pw ) {
		
		putenv('DB2CLP=DB20FADE');	// Required for DB2 CLP
		$line = exec('db2 list node directory', $output );
		$line1 = exec('db2level', $output1 );
		echo '<table width="100%" border="1">
		<tr valign="top"><td width="50%"><pre>';
		foreach( $output1 as $o)
			echo $o . "\n";
		echo "</pre><br><pre>";
		foreach( $output as $o)
			echo $o . "\n";
		echo "</pre></td><td>";
		$line2 = exec('db2 list database directory', $output2 );
		echo "<pre>";
		foreach( $output2 as $o)
			echo $o . "\n";
		echo "</pre></td></tr></table>";
		
		echo '<p><a href="dump_table.php?pw=youdaman17&able=TLORDER" target="_blank">View Tables</a></p>';
				
		if( function_exists('mcrypt_encrypt') )
			echo "<p><b>mcrypt_encrypt exists</b> You can enable encryption.</p>";
		else
			echo "<p><b>mcrypt_encrypt does not exist</b> Disable encryption.</p>";

							
		$query_string = "select * from syscat.tables
						WHERE TABNAME IS NOT NULL
						AND TABSCHEMA = '".$stc_schema."'
						AND TABNAME = 'COMPANY_INFO_SRC'";
			
		if( $debug ) echo "<p>using query_string = $query_string</p>";
		
		$response = send_odbc_query( $query_string, $stc_database, $debug );
		
		if( is_array($response) && count($response) == 1 ) {
			echo "<p>COMPANY_INFO_SRC exists.</p>";

			$query_string = "select COMPANY_INFO_ID, NAME from COMPANY_INFO_SRC";
				
			if( $debug ) echo "<p>using query_string = $query_string</p>";
			
			$response = send_odbc_query( $query_string, $stc_database, $debug );
			
			if( is_array($response) && count($response) > 0 ) {
				echo "<p>COMPANY_INFO_SRC contains ".count($response)." entries.</p>";
				echo "<pre>";
				var_dump($response);
				echo "</pre>";
	
			} else {
				echo "<p><b>COMPANY_INFO_SRC contains no entries.</b></p>";
			}

		} else {
			echo "<p><b>COMPANY_INFO_SRC MISSING</b></p>";
		}


		$query_string = "select * from syscat.tables
						WHERE TABNAME IS NOT NULL
						AND TABSCHEMA = '".$stc_schema."'
						AND TABNAME = 'ST_CMS_USERS'";
			
		if( $debug ) echo "<p>using query_string = $query_string</p>";
		
		$response = send_odbc_query( $query_string, $stc_database, $debug );
		
		if( is_array($response) && count($response) == 1 ) {
			echo "<p>ST_CMS_USERS installed.</p>";

			$query_string = "select * from ".$stc_schema.".ST_CMS_USERS";
				
			if( $debug ) echo "<p>using query_string = $query_string</p>";
			
			$response = send_odbc_query( $query_string, $stc_database, $debug );
			
			if( is_array($response) && count($response) > 0 ) {
				echo "<p>ST_CMS_USERS contains ".count($response)." entries.</p>";
				echo "<pre>";
				var_dump($response);
				echo "</pre>";
	
			} else {
				echo "<p><b>ST_CMS_USERS contains no entries.</b></p>
				<p>You need to apply the patch</p>";
			}

		} else {
			echo "<p><b>ST_CMS_USERS MISSING</b></p>
			<p>You need to apply the patch</p>";
		}

		$query_string = "select * from SYSCAT.PROCEDURES
							WHERE PROCNAME IS NOT NULL
							AND PROCSCHEMA = '".$stc_schema."'
						AND PROCNAME = 'CUSTOM_GEN_ID'";
			
		if( $debug ) echo "<p>using query_string = $query_string</p>";
		
		$response = send_odbc_query( $query_string, $stc_database, $debug );
		
		if( is_array($response) && count($response) == 1 ) {
			echo "<p>Procedure CUSTOM_GEN_ID installed.</p>";

		} else {
			echo "<p><b>Procedure CUSTOM_GEN_ID MISSING</b></p>
			<p>You need to apply the patch</p>";
		}

		$query_string = "select * from SYSCAT.FUNCTIONS
							WHERE FUNCNAME IS NOT NULL
							AND FUNCSCHEMA = '".$stc_schema."'
						AND FUNCNAME = 'STC_GET_RATE_PER_MILE'";
			
		if( $debug ) echo "<p>using query_string = $query_string</p>";
		
		$response = send_odbc_query( $query_string, $stc_database, $debug );
		
		if( is_array($response) && count($response) == 1 ) {
			echo "<p>Function STC_GET_RATE_PER_MILE installed.</p>";

		} else {
			echo "<p><b>Function STC_GET_RATE_PER_MILE MISSING</b></p>
			<p>You need to apply the patch</p>";
		}

		echo "<h2>Check for CLIENT.USER fields</h2>";
		for( $c=1; $c<=10; $c++ ) {
			// Prepare Select
			$query_string = "SELECT CLIENT_ID, NAME, USER".$c." FROM CLIENT
			WHERE COALESCE(USER".$c.", '') <> ''
			FETCH FIRST 5 ROWS ONLY";
			
			if( $debug ) echo "<p>using query_string = $query_string</p>";
	
			$response = send_odbc_query( $query_string, $stc_database, $debug );
			
			if( is_array($response) ) {
				if( count($response) > 0 ) {
					echo "<p>USER".$c." is in use.</p>";
					echo "<pre>";
					var_dump($response);
					echo "</pre>";
				} else
					echo "<p>USER".$c." is AVAILABLE.</p>";
			}
			
		}

	} else {
		if( $debug ) echo "<p>Password error.</p>";
	}

	if( $debug ) {
?>
</body>
</html>
<?php
	}
?>

