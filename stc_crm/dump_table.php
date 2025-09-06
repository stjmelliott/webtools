<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "odbc-inc.php" );

	$debug = FALSE;
	$password = "";
	$table = "TLORDER";
	$rows = "10";
	$valid_pw = "youdaman17";
	$EXAMINE = "NONE";
	$MATCH = "NONE";
	$MATCHVAL = "NONE";
	$USED = "NONE";
	
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
		} else if( $key == "TABLE" ) {
			$table = $value;
		} else if( $key == "ROWS" ) {
			$rows = $value;
		} else if( $key == "EXAMINE" ) {
			$EXAMINE = $value;
		} else if( $key == "MATCH" ) {
			list($MATCH, $MATCHVAL) = split(':', $value);
		} else if( $key == "USED" ) {
			$USED = $value;
		}
	}
	
	if( $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>DB2 Dump Table</title>
</head>

<body>
<?
	}
	
	if( $password == $valid_pw ) {
		
		//!Get list of tables
		$query_string = "SELECT TABNAME, OWNER 
			FROM SYSCAT.TABLES 
			WHERE TABSCHEMA = '".$stc_schema."'
			ORDER BY TABNAME ASC";
		
		$response = send_odbc_query( $query_string, $stc_database, false );
	
		if( $response ) {
			echo '<form name="form1" method="get" action="dump_table.php" enctype="multipart/form-data">
			<input name="PW" type="hidden" value="'.$password.'" />
			<h3>Table: <select name="TABLE" onchange="form.submit();" >';

			foreach( $response as $row) {
				echo "<option value=\"".$row{'TABNAME'}."\"";
				if( $table == $row{'TABNAME'} )
					echo" selected";
				echo " >".$row{'TABNAME'}."</option>\n";
			}
	
			echo '</select></h3>
			</form>';

		}
		
		//!Get table structure
		$query_string = "SELECT
			SUBSTR(COLNAME, 1, 50) COLNAME,
			SUBSTR(TYPENAME, 1, 40) TYPENAME,
			LENGTH,
			SCALE,
			SUBSTR(DEFAULT, 1, 30) DEFAULT,
			NULLS
			FROM SYSCAT.COLUMNS
			WHERE TABNAME IS NOT NULL
			AND TABSCHEMA = '".$stc_schema."'
			AND TABNAME = '".$table."'
			ORDER BY COLNAME";
		
		$response = send_odbc_query( $query_string, $stc_database, false );
	
		if( $response ) {
			echo "<table border=\"1\">\n";
			$first = true;
			foreach( $response as $row ) {
				if( $first ) {
					echo "<tr>\n";
					foreach( $row as $label => $col ) {
						echo "<th>".$label."</th>\n";
				}
				if( $USED <> "NONE" ) echo "<th>USED</th>";
				echo "</tr>\n";
					$first = false;
				}
				echo "<tr>\n";
				foreach( $row as $label => $col ) {
					echo "<td>".$col."</td>\n";
				}
				if( $USED <> "NONE" )  {
					if( $row['NULLS'] == 'Y' ) {
						$resp = send_odbc_query( "SELECT COUNT(*) USED FROM $table 
							WHERE ".$row['COLNAME']." IS NOT NULL", $stc_database, false );
							
						$bold =  (trim($row['TYPENAME']) == 'VARCHAR' && isset($resp) && $resp[0]['USED'] == 0);
							
						echo "<td align=\"right\">".($bold ? '<strong>' : '').(isset($resp) ? $resp[0]['USED'] : '').($bold ? '</strong>' : '')."</td>";
					} else
						echo "<td>&nbsp;</td>";
				}
				
				echo "</tr>\n";
			}
			echo "</table>\n";
		}
		
		$query_string = "select count(*) from ".$table;
		
		if( $debug ) echo "<p>using query_string = $query_string</p>";
		
		$response1 = send_odbc_query( $query_string, $stc_database, $debug );
		
		if( $response1 ) {
			echo "<pre>";
			var_dump($response1[0]);
			echo "</pre>";
		}
		
		if( $MATCH <> "NONE" ) {
			$query_string = "select *
				from ".$table."
				WHERE $MATCH = '".$MATCHVAL."'";
		} else if( $EXAMINE <> "NONE" ) {
			$query_string = "select $EXAMINE, COUNT(*) FREQ
				from ".$table."
				GROUP BY $EXAMINE
				ORDER BY 2 DESC";
		} else {
			
			$query_string = "select * from ".$table;
			
			if( $table == 'PTLORDER' ) $query_string.= " WHERE ((UP_DATE <> 'True' AND UP_DATE <> 'Done') OR UP_DATE IS NULL) AND WEB_STATUS = 'COMPLETE' AND EXTRA_STOPS<>'Child' ORDER BY DETAIL_LINE_ID";
			
			if( $rows <> "ALL" ) $query_string.= " FETCH FIRST $rows ROWS ONLY";
		}
	
		if( $debug ) echo "<p>using query_string = $query_string</p>";
		
		$response = send_odbc_query( $query_string, $stc_database, $debug );
		
		if( $response ) {
			echo "<pre>";
			var_dump($response);
			echo "</pre>";
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

