<?php
set_time_limit(0);
error_reporting(E_ALL & ~E_WARNING);

// no direct access
defined('_FUZZY') or die('Restricted access');

require_once( "../stc_crm/stc_config.php" );

$last_odbc_error = "";

/*
 *	function send_odbc_query	-	query database and return result or FALSE on error
 */
function send_pg_odbc_query( $query_string, $database, $debug = FALSE ) {

	global $stc_pg_odbc_driver, $stc_pg_odbc_user, $stc_pg_odbc_password, $stc_pg_odbc_host;
	global $last_odbc_error;
	
	if( function_exists( "odbc_connect" ) ) {
		if( $debug ) echo "<p>odbc_connect() exists.</p>";
		if( $debug ) echo "<p>Connect to database $database on host $stc_host</p>";
		
		$dsn =  'DRIVER={'.$stc_pg_odbc_driver.'};'.
		'Server='.$stc_pg_odbc_host.';'.
		'Database='.$database.';'.
		'uid='.$stc_pg_odbc_user; //.'; pwd='.$stc_pg_odbc_password;

		if( $debug ) echo "<p>using DSN = $dsn</p>";
		
		$db_conn = odbc_connect($dsn, '', '');
		
		if ($db_conn) {
			if( $debug ) echo "<p>odbc_connect succeeded.</p>";
			
			if( $debug ) echo "<p>using query_string = $query_string</p>";
	
			$res = odbc_prepare($db_conn, $query_string);
			if($res) {
				if( $debug ) echo "<p>odbc_prepare succeeded.</p>";
				
				odbc_setoption($res, 2, 0, 120);
		
				if(odbc_execute($res)) {
					if( $debug ) echo "<p>odbc_execute succeeded. ".odbc_errormsg($db_conn)."</p>";

					$rows = array();
					while( $row = odbc_fetch_array($res) ) {
						array_push($rows, $row);
					}
					
					odbc_close ($db_conn);
					return $rows;
					
				} else {
					$last_odbc_error = odbc_errormsg($db_conn);
					if( $debug ) echo "<p>could not execute statement: $query_string<br>".odbc_errormsg($db_conn)."</p>";
				}
			} else {
				$last_odbc_error = odbc_errormsg($db_conn);
				if( $debug ) echo "<p>could not prepare statement: $query_string<br>".odbc_errormsg($db_conn)."</p>";
			}
			
			odbc_close ($db_conn);
		}
		else {
			$last_odbc_error = odbc_errormsg($db_conn);
			if( $debug ) echo "<p>Connection failed. ".odbc_errormsg()."<br>".odbc_errormsg($db_conn)."</p>";
		}
	
		
	} else {
		$last_odbc_error = "odbc_connect() does not exist.";
		if( $debug ) echo "<p>odbc_connect() does not exist.</p>";
	}
	return false;
}

?>
