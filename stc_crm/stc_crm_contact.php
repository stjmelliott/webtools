<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );


	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman70";
	$option		= "NONE";
	$uid		= "NONE";
	$cid		= "NONE";
	$cnm		= "NONE";
	
	$PHONECONTID			= "NONE";	
	$CLIENTID				= "NONE";
	$CONT_NAME				= "NONE";
	$TITLE					= "NONE";
	$CONT_TYPE				= "NONE";
	$PHONENUMBER			= "NONE";
	$MOBILE_NUM				= "NONE";
	$FAX_NUM				= "NONE";
	$EMAIL					= "NONE";
	$COMMUNICATION_ID		= "NONE";
	$WHEN					= "NONE";
	$DAYS					= "NONE";
	
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
		} else if( $key == "CNM" ) {
			$cnm = $value;
		} else if( $key == "PHONECONTID" ) {
			$PHONECONTID = $value;
		} else if( $key == "CLIENTID" ) {
			$CLIENTID = $value;
		} else if( $key == "CONT_NAME" ) {
			$CONT_NAME = $value;
		} else if( $key == "TITLE" ) {
			$TITLE = $value;
		} else if( $key == "CONT_TYPE" ) {
			$CONT_TYPE = $value;
		} else if( $key == "PHONENUMBER" ) {
			$PHONENUMBER = $value;
		} else if( $key == "MOBILE_NUM" ) {
			$MOBILE_NUM = $value;
		} else if( $key == "FAX_NUM" ) {
			$FAX_NUM = $value;
		} else if( $key == "EMAIL" ) {
			$EMAIL = $value;
		} else if( $key == "COMMUNICATION_ID" ) {
			$COMMUNICATION_ID = $value;
		} else if( $key == "WHEN" ) {
			$WHEN = $value;
		} else if( $key == "DAYS" ) {
			$DAYS = $value;
		}
	}
	
	if( $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>STC CRM Backend - Contact Functions</title>
</head>

<body>
<?
	}
	
	if( $password == $valid_pw ) {
		
		switch (strtoupper($option)) {
			case 'ADD':	// !ADD - Add contact to client
				if( $uid == "NONE" || $CLIENTID == "NONE" || $CONT_NAME == "NONE" || $CONT_TYPE == "NONE" || 
					$COMMUNICATION_ID == "NONE" ) {
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
						
						// Get PHONECONTID
						$query_string = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_PHONECONT_ID')";
						 
						if( $debug ) echo "<p>using query_string = $query_string</p>";
						
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( $debug ) echo "<p>response2 ".gettype($response2)."</p>";
	
						if( $response2 ) {
							$nextid = $response2[0]["NEXTID"];
							if( $debug ) echo "<p>PHONECONTID is $nextid</p>";
	
							// Create PHONECONT record
							$query_string = "INSERT INTO PHONECONT (PHONECONTID, PHONEID, CONT_NAME, 
								TITLE, CONT_TYPE, PHONENUMBER, MOBILE_NUM, 
								FAX_NUM, EMAIL, COMMUNICATION_ID) 
								VALUES (".$nextid.",".$phoneid.",'".$CONT_NAME."',
								'".($TITLE=="NONE"?"":$TITLE)."','".$CONT_TYPE."','".
								($PHONENUMBER=="NONE"?"":$PHONENUMBER)."','".($MOBILE_NUM=="NONE"?"":$MOBILE_NUM)."',
								'".($FAX_NUM=="NONE"?"":$FAX_NUM)."','".($EMAIL=="NONE"?"":$EMAIL)."',
								'".$COMMUNICATION_ID."')";
								 
							if( $debug ) echo "<p>using query_string = $query_string</p>";
							
							$response3 = send_odbc_query( $query_string, $stc_database, $debug );
							
							if( $debug ) echo "<p>response3 ".gettype($response3)."</p>";
	
							if( $debug )  {
								echo "<pre>";
								var_dump($response3);
								echo "</pre>";
							}
							if( is_array($response3) ) {
								update_client_date( $uid, $CLIENTID, $debug  );
								if( $debug ) echo "<p>ADDED</p>";
								else echo encryptData("ADDED");
		
							} else {
								if( $debug ) echo "<p>Error - send_odbc_query 3 failed. $last_odbc_error</p>";
								else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
							}
	
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

			case 'DEL':	// !DEL - delete contact from client
				if( $uid == "NONE" || $CLIENTID == "NONE" || $PHONECONTID == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					// Prepare query
					$query_string = "DELETE FROM PHONECONT
						WHERE PHONECONTID = '".$PHONECONTID."'";
					 
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

			case 'MOD':	// !MOD - modify client
				// Validate fields
				if( $uid == "NONE" || $CLIENTID == "NONE" || $PHONECONTID == "NONE" || 
					($PHONEID == "NONE" && $TITLE == "NONE" && $CONT_TYPE == "NONE" && $PHONENUMBER == "NONE" &&
					$MOBILE_NUM == "NONE" && $FAX_NUM == "NONE" && $EMAIL == "NONE" && $COMMUNICATION_ID == "NONE") ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Update
					$query_string = "UPDATE PHONECONT ";
					$first = true;
					if( $CONT_NAME <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." CONT_NAME = '".$CONT_NAME."' ";
						$first = false;
					}
					if( $TITLE <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." TITLE = '".$TITLE."' ";
						$first = false;
					}
					if( $CONT_TYPE <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." CONT_TYPE = '".$CONT_TYPE."' ";
						$first = false;
					}
					if( $PHONENUMBER <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." PHONENUMBER = '".$PHONENUMBER."' ";
						$first = false;
					}
					
					if( $MOBILE_NUM <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." MOBILE_NUM = '".$MOBILE_NUM."' ";
						$first = false;
					}
					if( $FAX_NUM <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." FAX_NUM = '".$FAX_NUM."' ";
						$first = false;
					}
					if( $EMAIL <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." EMAIL = '".$EMAIL."' ";
						$first = false;
					}
					if( $COMMUNICATION_ID <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." COMMUNICATION_ID = '".$COMMUNICATION_ID."' ";
						$first = false;
					}
										
						
					$query_string .= "WHERE PHONECONTID = '".$PHONECONTID."'";     
					
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

			case 'TYPES':	// !TYPES - Get a list of contact types
				// Prepare Select
				//$query_string = "SELECT PHONECATID, DESCRIPTION 
				//	FROM PHONECAT where cattype = 'P'
				//	ORDER BY PHONECATID DESC";
					
				$query_string = "SELECT * FROM PHONECONTTYPE
					ORDER BY CONT_TYPE";
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

			case 'CTYPES':	// !CTYPES - Get a list of preferred communication types
				// Prepare Select
				$query_string = "SELECT DD_ID, DD_DESCRIPTION
					FROM DROPDOWN
					WHERE DD_TYPE='COMMUNICATION TYPE'
					ORDER BY DD_ID, DD_DESCRIPTION";
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

			case 'LIST':	// !LIST - Get a list of contacts for a client
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT PHONECONTID, CONT_NAME, CONT_TYPE, PHONENUMBER, FAX_NUM, MOBILE_NUM,
						c.NUMBERS, TITLE, EMAIL, COMMUNICATION_ID, USE_IN_EMAIL
						FROM PHONE p, PHONECONT c
						WHERE p.CLIENTID = '".$cid."'
						AND p.PHONEID = c.PHONEID
						ORDER BY 2 ASC";
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

			case 'MOVE':	// !MOVE - Move a list of contacts to a different client
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "UPDATE PHONECONT C
						SET C.PHONEID = (SELECT P.PHONEID 
							FROM PHONE P, CLIENT C
							WHERE P.CLIENTID = C.USER2
							AND C.CLIENT_ID = '".$cid."')
						WHERE C.PHONEID = (SELECT P.PHONEID 
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

			case 'FETCH':	//!FETCH - Fetch details for a contact
				// Validate fields
				if( $PHONECONTID == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "select PHONECONTID, PHONEID, CONT_NAME, 
						TITLE, CONT_TYPE, PHONENUMBER, MOBILE_NUM, 
						FAX_NUM, EMAIL, COMMUNICATION_ID
						FROM PHONECONT
						WHERE PHONECONTID = '".$PHONECONTID."'";
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

			case 'REMIND':	// !REMIND - Add callback reminder for client
				if( $CLIENTID == "NONE" || $WHEN == "NONE" ) {
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
												
						$query_string = "UPDATE PHONE
							SET USERDATE1 = DATE('".$WHEN."')
							WHERE CLIENTID = '".$CLIENTID."'";
							 
						if( $debug ) echo "<p>using query_string = $query_string</p>";
						
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( $debug ) echo "<p>response2 ".gettype($response2)."</p>";

						if( $debug )  {
							echo "<pre>";
							var_dump($response2);
							echo "</pre>";
						}
						if( is_array($response2) ) {
							if( $debug ) echo "<p>REMEMBERED</p>";
							else echo encryptData("REMEMBERED");
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

			case 'FORGET':	// !FORGET - Delete callback reminder for client
				if( $CLIENTID == "NONE" ) {
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
												
						$query_string = "UPDATE PHONE
							SET USERDATE1 = NULL
							WHERE CLIENTID = '".$CLIENTID."'";
							 
						if( $debug ) echo "<p>using query_string = $query_string</p>";
						
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( $debug ) echo "<p>response2 ".gettype($response2)."</p>";

						if( $debug )  {
							echo "<pre>";
							var_dump($response2);
							echo "</pre>";
						}
						if( is_array($response2) ) {
							if( $debug ) echo "<p>FORGOT</p>";
							else echo encryptData("FORGOT");
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

			case 'REPEAT':	// !REPEAT - Set number of days to repeat
				if( $CLIENTID == "NONE" || $DAYS == "NONE" ) {
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
												
						$query_string = "UPDATE PHONE
							SET USERNUM1 = ".$DAYS."
							WHERE CLIENTID = '".$CLIENTID."'";
							 
						if( $debug ) echo "<p>using query_string = $query_string</p>";
						
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( $debug ) echo "<p>response2 ".gettype($response2)."</p>";
						
						if( $DAYS > 0 )
							update_due_date( $CLIENTID, $debug  );	// Set due date

						if( $debug )  {
							echo "<pre>";
							var_dump($response2);
							echo "</pre>";
						}
						if( is_array($response2) ) {
							if( $debug ) echo "<p>SET - repeat = ".$DAYS." days</p>";
							else echo encryptData("SET");
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

