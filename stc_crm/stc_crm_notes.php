<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );

	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman71";
	$option		= "NONE";
	$uid		= "";
	$cid		= "";
	$PHONEID	= "";
	$PHONEDATE	= "";
	$CLIENTID	= "";
	$TITLE		= "";
	$NOTETYPE	= "";
	$THE_NOTE	= "";

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
		} else if( $key == "OPT" ) {
			$option = $value;
		} else if( $key == "UID" ) {
			$uid = $value;
		} else if( $key == "CID" ) {
			$cid = $value;
		} else if( $key == "PHONEID" ) {
			$PHONEID = $value;
		} else if( $key == "PHONEDATE" ) {
			$PHONEDATE = $value;
		} else if( $key == "CLIENTID" ) {
			$CLIENTID = $value;
		} else if( $key == "TITLE" ) {
			$TITLE = $value;
		} else if( $key == "NOTETYPE" ) {
			$NOTETYPE = $value;
		} else if( $key == "THE_NOTE" ) {
			$THE_NOTE = $value;
		}
	}
	
	if( $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>STC CRM Backend - Notes Functions</title>
</head>

<body>
<?
	}
	
	if( $password == $valid_pw ) {
		
		switch (strtoupper($option)) {
			case 'ADD': //!ADD - Add note
			// PHONEDATE,USER_ID,TITLE,NOTETYPE,THE_NOTE,PHONEPRIVATE
				if( $uid == "" || $CLIENTID == "" || $PHONEDATE == "" || $TITLE == "" || 
					$NOTETYPE == "" || $THE_NOTE == "" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					// Get PHONE ID
					$query_string = "SELECT PHONEID FROM PHONE
								WHERE CLIENTID = '".$CLIENTID."'";
					 
					if( $debug ) echo "<p>using query_string = $query_string</p>";
					
					$response1 = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( $debug ) echo "<p>response1 ".gettype($response1)."</p>";
					if( $debug )  {
						echo "<pre>";
						var_dump($response1);
						echo "</pre>";
					}
					if( ! $response1 ) {
						if( fix_missing_phone( $CLIENTID, $debug ) ) {
							if( $debug ) echo "<p>Fixed, try again.</p>";
							if( $debug ) echo "<p>using query_string = $query_string</p>";
							
							$response1 = send_odbc_query( $query_string, $stc_database, $debug );
							
							if( $debug ) echo "<p>response1 ".gettype($response1)."</p>";
							if( $debug )  {
								echo "<pre>";
								var_dump($response1);
								echo "</pre>";
							}
							
						} else {
							if( $debug ) echo "<p>Could not fix.</p>";
						}
					}

					if( $response1 ) {
						$phoneid = $response1[0]["PHONEID"];
						if( $debug ) echo "<p>PHONEID is $phoneid</p>";
							
						// Create PHONECONT record
						$query_string = "INSERT INTO PHONENOTES (PHONEID,
							PHONEDATE,USER_ID,TITLE,NOTETYPE,THE_NOTE) 
							VALUES (".$phoneid.",'".$PHONEDATE."',
							'".$uid."','".str_replace("'", "''", $TITLE)."','".$NOTETYPE."','".str_replace("'", "''", $THE_NOTE)."')";
							 
						if( $debug ) echo "<p>using query_string = $query_string</p>";
						
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( $debug ) echo "<p>response3 ".gettype($response2)."</p>";

						if( $debug )  {
							echo "<pre>";
							var_dump($response2);
							echo "</pre>";
						}
						if( is_array($response2) ) {
							update_client_date( $uid, $CLIENTID, $debug  );
							update_due_date( $CLIENTID, $debug  );
							if( $debug ) echo "<p>ADDED</p>";
							else echo encryptData("ADDED");	
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}

				break;

			case 'DEL': //!DEL - Delete note
				if( $uid == "" || $CLIENTID == "" || $PHONEID == "" || $PHONEDATE == "" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					// Prepare query
					$query_string = "DELETE FROM PHONENOTES
						WHERE PHONEID = '".$PHONEID."' 
						AND PHONEDATE = TIMESTAMP('".$PHONEDATE."')";
					 
					if( $debug ) echo "<p>using query_string = $query_string</p>";
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) ) {
						update_client_date( $uid, $CLIENTID, $debug  );
						if( $debug ) echo "<p>DELETED</p>";
						else echo encryptData("DELETED");
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}
				break;

			case 'MOD': //!MOD - Modify note
				// Validate fields
				if( $uid == "" || $CLIENTID == "" || $PHONEID == "" || $PHONEDATE == "" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Update
					$query_string = "UPDATE PHONENOTES ";
					$first = true;
					if( $TITLE <> "" ) {
						$query_string .= ($first ? "SET":",")." TITLE = '".str_replace("'", "''", $TITLE)."' ";
						$first = false;
					}
					if( $NOTETYPE <> "" ) {
						$query_string .= ($first ? "SET":",")." NOTETYPE = '".$NOTETYPE."' ";
						$first = false;
					}
					if( $THE_NOTE <> "" ) {
						$query_string .= ($first ? "SET":",")." THE_NOTE = '".str_replace("'", "''", $THE_NOTE)."' ";
						$first = false;
					}										
						
					$query_string .= "WHERE PHONEID = '".$PHONEID."' 
						AND PHONEDATE = TIMESTAMP('".$PHONEDATE."')";     
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) ) {
						update_client_date( $uid, $CLIENTID, $debug  );
						if( $debug ) echo "<p>CHANGED</p>";
						else echo encryptData("CHANGED");
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}

				break;

			case 'TYPES': //!TYPES - Get note types
				// Prepare Select
				$query_string = "SELECT PHONECATID, DESCRIPTION 
					FROM PHONECAT where cattype = 'N'
					ORDER BY PHONECATID DESC";
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

			case 'LIST': //!LIST - List notes for a client
				// Validate fields
				if( $cid == "" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT n.PHONEID, PHONEDATE, n.USER_ID, TITLE,
						NOTETYPE, THE_NOTE, PHONEPRIVATE 
						FROM PHONE p, PHONENOTES n
						WHERE p.PHONEID = n.PHONEID
						AND p.CLIENTID = '".$cid."'
						ORDER BY PHONEDATE DESC";
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

			case 'MOVE':	// !MOVE - Move a list of notes to a different client
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "UPDATE PHONENOTES N
						SET N.PHONEID = (SELECT P.PHONEID 
							FROM PHONE P, CLIENT C
							WHERE P.CLIENTID = C.USER2
							AND C.CLIENT_ID = '".$cid."')
						WHERE N.PHONEID = (SELECT P.PHONEID 
							FROM PHONE P
							WHERE P.CLIENTID = '".$cid."')";
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

			case 'FETCH': //!FETCH - Fetch note
				// Validate fields
				if( $cid == "" || $PHONEDATE == "" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT n.PHONEID, PHONEDATE,USER_ID,TITLE,NOTETYPE,THE_NOTE,PHONEPRIVATE 
						FROM PHONE p, PHONENOTES n
						WHERE p.PHONEID = n.PHONEID
						AND p.CLIENTID = '".$cid."'
						AND PHONEDATE = TIMESTAMP('".$PHONEDATE."')";
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
<?	
	}
?>

