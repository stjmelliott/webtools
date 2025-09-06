<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );

$log_file = 'log/project44.txt';

function log_event( $message ) {
	$log_file = 'log/doe.txt';
	$exists = file_exists($log_file);

		//var_dump( file_exists($this->log_file), is_writable($this->log_file), is_writable(dirname($this->log_file)) );
		
	if( (file_exists($log_file) && is_writable($log_file)) ||
		is_writable(dirname($log_file)) ) 
		@file_put_contents($log_file, date('m/d/Y h:i:s A').
		" msg=".$message."\n\n", ($exists ? FILE_APPEND : 0) );
	
	if( ! $exists )
		@chmod($log_file, 644);
}


	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman68";
	$valid_pw2	= "NOTmyPassword";
	$option		= "NONE";
	$groups		= "NONE";
	$uid		= "NONE";
	$upw		= "NONE";
	$ufn		= "NONE";
	$uad		= "NONE";
	$uem		= "NONE";
	
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
		} else if( $key == "EPW" ) {
			$password = decryptData($value);
			$password2 = decryptData($value,$stc_initial_key);
		} else if( $key == "OPT" ) {
			$option = $value;
		} else if( $key == "GROUPS" ) {
			$groups = $value;
		} else if( $key == "UID" ) {
			$uid = $value;
		} else if( $key == "UPW" ) {
			$upw = $value;
		} else if( $key == "UFN" ) {
			$ufn = $value;
		} else if( $key == "UAD" ) {
			$uad = $value;
		} else if( $key == "UEM" ) {
			$uem = $value;
		}
	}
	
	if( $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>STC CRM Backend - User Functions</title>
</head>

<body>
<?php
	}
	
	if( $password == $valid_pw ||
		($password2 == $valid_pw2 && strtoupper($option) == 'MYKEY')) {
		
		switch (strtoupper($option)) {
			case 'MYKEY':		//! MYKEY
				if( $debug ) echo "<p>RETURN TODAY'S KEY</p>";
				
				$response = array();
				$response{'TODAYS_KEY'} = $stc_todays_key;
				
				$stc_functions_enc = array();
				foreach( $stc_functions as $fn => $url ) {
					// Replace pw= password with epw= encrypted password
					$myint = preg_match('/^([^\?]*)\?pw=([^\&]*)(.*)$/', $url, $matches );
					$stc_functions_enc{$fn} = $matches[1]."?epw=".encryptData($matches[2]).$matches[3];
				}
				
				$response{'FUNCTIONS'} = $stc_functions_enc;
				
				echo encryptData(json_encode( $response ), $stc_initial_key);
				
				break;
				
			case 'ADD':		//! ADD
				// Validate fields
				if( $uid == "NONE" || $upw == "NONE" || $ufn == "NONE" ||  $groups == "NONE") {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					$query_string = "SELECT * FROM SALESREP WHERE ID = '".$uid."'";
						
					if( $debug ) echo "<p>CHECK SALESREP $uid EXISTS</p>";
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) && count($response) == 1 ) {
						if( $debug ) echo "<p>FOUND SALESREP $uid</p>";

						$query_string = "SELECT * FROM ST_CMS_USERS WHERE USERID = '".$uid."'";
							
						if( $debug ) echo "<p>CHECK SALESREP $uid DOES NOT EXIST IN ST_CMS_USERS</p>";
						
						$response = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( is_array($response) && count($response) == 0 ) {
							if( $debug ) echo "<p>DID NOT FIND SALESREP $uid IN ST_CMS_USERS</p>";

							// Prepare Insert
							$query_string = "INSERT INTO ST_CMS_USERS(USERID, PASSWORD, FULLNAME, EMAIL, GROUPS)
							VALUES('".$uid."','".decryptData($upw)."','".$ufn."','".$uem."','".$groups."')";     
							
							if( $debug ) echo "<p>using query_string = $query_string</p>";
					
							$response1 = send_odbc_query( $query_string, $stc_database, $debug );
							
							if( is_array($response1) ) {
								if( $debug ) echo "<p>ADDED</p>";
								else echo encryptData("ADDED");
							} else {
								if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
								else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
							}
						} else {
							if( $debug ) echo "<p>ERROR SALESREP $uid ALREADY IN IN ST_CMS_USERS</p>";
							else echo encryptData("ERROR SALESREP $uid ALREADY IN IN ST_CMS_USERS");
						}
					} else {
						if( $debug ) echo "<p>MISSING SALESREP $uid</p>";
						else echo encryptData("MISSING - $uid not found in SALESREP table");
					}
				}

				break;
			case 'DEL':		//! DEL
				// Validate fields
				if( $uid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Delete
					$query_string = "DELETE FROM ST_CMS_USERS
					WHERE USERID = '".$uid."'";     
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) ) {
						if( $debug ) echo "<p>DELETED</p>";
						else echo encryptData("DELETED");
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}
				break;
			case 'LIST':		//! LIST
				// Prepare Select
				$query_string = "SELECT U.USERID, U.FULLNAME, U.EMAIL, U.GROUPS, CLIENTS, LANES
				FROM ST_CMS_USERS U
				LEFT OUTER JOIN
				(SELECT R.SALES_REP, COUNT(*) AS CLIENTS
				FROM CLIENT R
				GROUP BY R.SALES_REP) AS R(SALES_REP, CLIENTS)
				ON R.SALES_REP = U.USERID
				LEFT OUTER JOIN
				(SELECT C.SALES_REP, COUNT(T.BILL_NUMBER) AS LANES
				FROM TLORDER T, CLIENT C
				WHERE T.CURRENT_STATUS = 'QUOTE'
				AND T.BILL_TO_CODE = C.CLIENT_ID
				GROUP BY C.SALES_REP) AS P(SALES_REP, LANES)
				ON U.USERID = P.SALES_REP
				ORDER BY 1 ASC
				FOR READ ONLY
				WITH UR";
				
				if( $debug ) echo "<p>using query_string = $query_string</p>";
		
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response) ) {
					if( $debug ) {
						echo "<pre>";
						var_dump($response);
						echo "</pre>";
					} else {
						echo encryptData(json_encode( $response ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
				break;
				
			case 'MOD':		//! MOD
				// Validate fields
				if( $uid == "NONE" || ($upw == "NONE" && $ufn == "NONE" && $uem == "NONE" && $groups == "NONE") ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Update
					$query_string = "UPDATE ST_CMS_USERS ";
					$first = true;
					if( $upw <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." PASSWORD = '".decryptData($upw)."' ";
						$first = false;
					}
					if( $ufn <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." FULLNAME = '".$ufn."' ";
						$first = false;
					}
					if( $uem <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." EMAIL = '".$uem."' ";
						$first = false;
					}
					if( $groups <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." GROUPS = '".$groups."' ";
						$first = false;
					}
					$query_string .= "WHERE USERID = '".$uid."'";     
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) ) {
						if( $debug ) echo "<p>CHANGED</p>";
						else echo encryptData("CHANGED");
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}
				break;
			case 'FETCH':
				// Validate fields
				if( $uid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT USERID, FULLNAME, EMAIL, GROUPS FROM ST_CMS_USERS
					WHERE USERID = '".$uid."'";
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) ) {
						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}

				break;
			case 'AUTH':		//! AUTH
				// Validate fields
				if( $uid == "NONE" || $upw == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT USERID, FULLNAME, EMAIL, GROUPS FROM ST_CMS_USERS
					WHERE USERID = '".decryptData($uid)."' AND PASSWORD = '".decryptData($upw)."'";
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) ) {
						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
							flush();
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
					flush();	// Hopefully get the user moving along...
					
				//	if( $uid == 'ADMINISTRATOR' ) {
				//		$doe = update_fuel( $debug, 'DOE' ); // Update fuel data
				//		$mw = update_fuel( $debug, 'MW' ); // Update fuel data
				//		if( $doe || $mw || $uem != 'NONE' )
				//			refresh_fb( $debug );
				//	}
				}

				break;

			case 'AUTH2':
				// Validate fields
				if( $uid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT USERID, FULLNAME, PASSWORD, EMAIL, GROUPS FROM ST_CMS_USERS
					WHERE USERID = '".$uid."'";
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) ) {
						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}

				break;

			case 'REPS':
				$query_string = "SELECT ID, NAME, CLIENTS, LANES
					FROM SALESREP
					LEFT OUTER JOIN
					(SELECT R.SALES_REP, COUNT(*) AS CLIENTS
					FROM CLIENT R
					GROUP BY R.SALES_REP) AS R(SALES_REP, CLIENTS)
					ON R.SALES_REP = ID
					LEFT OUTER JOIN
					(SELECT C.SALES_REP, COUNT(T.BILL_NUMBER) AS LANES
					FROM TLORDER T, CLIENT C
					WHERE T.CURRENT_STATUS = 'QUOTE'
					AND T.BILL_TO_CODE = C.CLIENT_ID
					GROUP BY C.SALES_REP) AS P(SALES_REP, LANES)
					ON ID = P.SALES_REP
					ORDER BY 1 ASC
					FOR READ ONLY
					WITH UR";
					
				if( $debug ) echo "<p>GET SALES REPS</p>";
				
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response) ) {
					if( $debug ) {
						echo "<pre>";
						var_dump($response);
						echo "</pre>";
					} else {
						echo encryptData(json_encode( $response ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
			
			break;

			default:
				if( $debug ) echo "<p>Error - Invalid Option \"$option\".</p>";
		}
			
	} else {
		if( $debug ) echo "<p>Authentication error.</p>";
	}


	if( $debug ) {
?>
</body>
</html>
<?php
	}
?>