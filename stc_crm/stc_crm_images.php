<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );
require_once( "./odbc-inc-pg.php" );
require_once( "./stc_config.php" );

	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman76";
	$option		= "NONE";
	$USERID		= "NONE";
	$cid		= "NONE";
	$cnm		= "NONE";
	$own		= "NONE";
	$days		= "90";
	$FB			= "NONE";

	$LANE					= "NONE";
	$NAME					= "NONE";
	$ZONE1					= "NONE";
	$ZONE2					= "NONE";
	$RANGE1					= "50";
	$RANGE2					= "50";
	$COMMODITY				= "NONE";
	$BUSINESS_PHONE			= "NONE";
	$BUSINESS_PHONE_EXT		= "NONE";
	$FAX_PHONE				= "NONE";
	$EMAIL_ADDRESS			= "NONE";
	$COMPANY_URL			= "NONE";
	$TRIP_NUMBER			= "NONE";
	$EXT					= "NONE";
	$CONTENTS				= "NONE";
	$DOCID					= "NONE";
	$DRIVER					= false;
	$SITE					= "NONE";
	
	if( isset( $_POST{'OPT'}) ) {
		foreach($_POST as $key => $value) {
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
			} else if( $key == "USERID" ) {
				$USERID = $value;
			} else if( $key == "TRIP_NUMBER" ) {
				$TRIP_NUMBER = $value;
			} else if( $key == "EXT" ) {
				$EXT = $value;
			} else if( $key == "CONTENTS" ) {
				$CONTENTS = $value;
			}
		}
		
	} else {
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
			} else if( $key == "USERID" ) {
				$USERID = $value;
			} else if( $key == "LANE" ) {
				$LANE = $value;
			} else if( $key == "CID" ) {
				$cid = $value;
			} else if( $key == "OWN" ) {
				$own = $value;
			} else if( $key == "CLIENT_ID" ) {
				$CLIENT_ID = $value;
			} else if( $key == "NAME" ) {
				$NAME = $value;
			} else if( $key == "ZONE1" ) {
				$ZONE1 = $value;
			} else if( $key == "ZONE2" ) {
				$ZONE2 = $value;
			} else if( $key == "RANGE1" ) {
				$RANGE1 = $value;
			} else if( $key == "RANGE2" ) {
				$RANGE2 = $value;
			} else if( $key == "COMMODITY" ) {
				$COMMODITY = $value;
			} else if( $key == "TRIP_NUMBER" ) {
				$TRIP_NUMBER = $value;
			} else if( $key == "DRIVER" ) {
				$DRIVER = true;
			} else if( $key == "BUSINESS_PHONE" ) {
				$BUSINESS_PHONE = $value;
			} else if( $key == "BUSINESS_PHONE_EXT" ) {
				$BUSINESS_PHONE_EXT = $value;
			} else if( $key == "FAX_PHONE" ) {
				$FAX_PHONE = $value;
			} else if( $key == "EMAIL_ADDRESS" ) {
				$EMAIL_ADDRESS = $value;
			} else if( $key == "COMPANY_URL" ) {
				$COMPANY_URL = $value;
			} else if( $key == "FB" ) {
				$FB = $value;
			} else if( $key == "CONTENTS" ) {
				$CONTENTS = $value;
			} else if( $key == "DOCID" ) {
				$DOCID = $value;
			} else if( $key == "SITE" ) {
				$SITE = $value;
			}
		}
	}
	
	if( $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>STC CRM Backend - Image Functions</title>
</head>

<body>
<?
	}
	
	if( $password == $valid_pw ) {
		
		switch (strtoupper($option)) {

			case 'TRIPS':  // !TRIPS - List of trips
			
				switch( $SITE ) {
					case 'CAINBOUND':
						$match = "AND TRIP.DESTINATION_ZONE = 'TERMCAORG'";
						break;
					case 'WIINBOUND':
						$match = "AND TRIP.DESTINATION_ZONE  = 'TERMWIJVL'";
						break;
					case 'CAOUTBOUND':
						$match = "AND TRIP.ORIGIN_ZONE  = 'TERMCAORG'";
						break;
					case 'WIOUTBOUND':
						$match = "AND TRIP.ORIGIN_ZONE  = 'TERMWIJVL'";
						break;
					case 'ALL':
					default:
						$match = "";
						break;
				}
				
				// Prepare Select
				$query_string = "SELECT TRIP.TRIP_NUMBER, TRIP.STATUS, TRIP.DRIVER,
					(SELECT NAME FROM DRIVER WHERE DRIVER.DRIVER_ID = TRIP.DRIVER) AS DNAME,
					TRIP.POWER_UNIT, TRIP.TRAILER, TRIP.LS_NUM_LEGS NUM_LEGS,
					".$stc_schema.".GET_ZONE_DESC(TRIP.ORIGIN_ZONE) ORIGIN,
					".$stc_schema.".GET_ZONE_DESC(TRIP.DESTINATION_ZONE) DESTINATION,
					(SELECT ROLLUP_COMMODITY
						FROM ITRIPTLO, TLORDER
						WHERE ITRIPTLO.TRIP_NUMBER = TRIP.TRIP_NUMBER
						AND ITRIPTLO.bill_number = TLORDER.bill_number
						fetch first row only) COMMODITY,
					(SELECT SUM(LS_LEG_DIST)
						FROM LEGSUM
						WHERE LS_TRIP_NUMBER = TRIP_NUMBER) DISTANCE,
					(SELECT COUNT(BILL_NUMBER) FROM ITRIPTLO
						WHERE ITRIPTLO.TRIP_NUMBER = TRIP.TRIP_NUMBER) BILLS
					
					FROM TRIP
					WHERE TRIP.STATUS IN ('ASSGN', 'BOOKED', 'DISP')
					AND ACTIVE_REC = 'True'
					AND DELIVER_BY > current_date - 365 days
					".$match;
				
				if( $TRIP_NUMBER <> "NONE" )
					$query_string .= " AND TRIP.TRIP_NUMBER = '".$TRIP_NUMBER."'";
					
				if( $DRIVER )
					$query_string .= "	ORDER BY TRIP.DRIVER, TRIP.TRIP_NUMBER ASC";
				else
					$query_string .= "	ORDER BY TRIP.STATUS ASC, TRIP.TRIP_NUMBER ASC";

				$query_string .= "  for read only
					with ur";
										
				if( $debug ) echo "<p>using query_string = $query_string</p>";
		
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response) ) {
					if( $debug ) echo "<p>uploads_directory = $stc_uploads_directory</p>";
					
					for( $c = 0; $c < count($response); $c++) {
						$found = false;
						foreach ( $stc_image_valid_file_formats as $fmt ) {
							//if( $debug ) echo "<p>check for  ".$stc_uploads_directory.$response[$c]{'TRIP_NUMBER'}.".".$fmt."</p>";
							if( file_exists( $stc_uploads_directory.$response[$c]{'TRIP_NUMBER'}.".".$fmt ) ) {
								$response[$c]{'IMAGE'} = $fmt;
								if( $debug ) echo "<p>found  ".$stc_uploads_directory.$response[$c]{'TRIP_NUMBER'}.".".$fmt."</p>";
								$found = true;
								break;
							}
						}
						
						if( ! $found ) 
							$response[$c]{'IMAGE'} = 'NONE';
							
						if( file_exists( $stc_uploads_directory.$response[$c]{'TRIP_NUMBER'}.'.lck' ) )
							$response[$c]{'LOCKED'} = file_get_contents( $stc_uploads_directory.$response[$c]{'TRIP_NUMBER'}.'.lck' );
						else 
							$response[$c]{'LOCKED'} = 'NONE';
					}
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

			case 'VIEW':  //!VIEW - View image for trip
				
				// Validate fields
				if( $TRIP_NUMBER == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					if( $debug ) echo "<p>Look for image file</p>";	
					$found = false;
					foreach ( $stc_image_valid_file_formats as $fmt ) {
						//if( $debug ) echo "<p>check for  ".$stc_uploads_directory.$TRIP_NUMBER.".".$fmt."</p>";
						if( file_exists( $stc_uploads_directory.$TRIP_NUMBER.".".$fmt ) ) {
							$found_path = $stc_uploads_directory.$TRIP_NUMBER.".".$fmt;
							$found_name = $TRIP_NUMBER.".".$fmt;
							$found_mime = "image/".$fmt;
							if( $debug ) echo "<p>found  ".$found_path."</p>";
							$found = true;
							break;
						}
					}
					
					if( $found ) {
						$contents = file_get_contents($found_path);
  						$base64   = base64_encode($contents);
						$response = array();
						$response{'NAME'} = $found_name;
						$response{'MIME'} = $found_mime;
						$response{'CONTENTS'} = $base64;
						
						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
							
						} else {
							echo encryptData(json_encode( $response ));
						}
						
					} else {
						echo encryptData("NOT FOUND");
					}
				}
				break;
			
			case 'CHECKOUT':  // !CHECKOUT - checkout image for trip
				
				// Validate fields
				if( $TRIP_NUMBER == "NONE" || $USERID == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$lock_path = $stc_uploads_directory.$TRIP_NUMBER.".lck";

					// Check for a lock
					if( file_exists( $lock_path ) ) { // Locked
						$whom = file_get_contents( $lock_path );
						echo error_response( "LOCKED", $whom, $debug );
					} else { // Unlocked
						if( ! file_put_contents( $lock_path, $USERID ) ) {	// Write lock file
							echo error_response( "WRITEFAIL", 'unable to write to lock file', $debug );
						} else {
							if( $debug ) echo "<p>Editing of image for trip ".$TRIP_NUMBER." locked for ".$USERID.".</p>";
						
							if( $debug ) echo "<p>Look for image file</p>";	
							$found = false;
							foreach ( $stc_image_valid_file_formats as $fmt ) {
								//if( $debug ) echo "<p>check for  ".$stc_uploads_directory.$TRIP_NUMBER.".".$fmt."</p>";
								if( file_exists( $stc_uploads_directory.$TRIP_NUMBER.".".$fmt ) ) {
									$found_path = $stc_uploads_directory.$TRIP_NUMBER.".".$fmt;
									$found_name = $TRIP_NUMBER.".".$fmt;
									$found_mime = "image/".$fmt;
									if( $debug ) echo "<p>found  ".$found_path."</p>";
									$found = true;
									break;
								}
							}
							
							if( $found ) {
								$contents = file_get_contents($found_path);
								$base64   = base64_encode($contents);
								$response = array();
								$response{'OUTCOME'} = 'CHECKOUT';
								$response{'NAME'} = $found_name;
								$response{'MIME'} = $found_mime;
								$response{'CONTENTS'} = $base64;
								
								if( $debug ) {
									echo "<pre>";
									var_dump($response);
									echo "</pre>";
									
								} else {
									echo encryptData(json_encode( $response ));
								}
								
							} else {
								echo error_response( "NOT FOUND", 'file not found', $debug );
							}
						} // Lock ok
					} // Unlocked
				} // Validate fields
			
				break;

			case 'UNCHECKOUT':  // !UNCHECKOUT - uncheckout image for trip
				
				// Validate fields
				if( $TRIP_NUMBER == "NONE" || $USERID == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$lock_path = $stc_uploads_directory.$TRIP_NUMBER.".lck";

					if( $debug ) echo "<p>UNCHECKOUT of image for trip ".$TRIP_NUMBER." by ".$USERID.".</p>";
					// Check for a lock
					if( file_exists( $lock_path ) ) { // Locked
						$whom = file_get_contents( $lock_path );
						if( $whom <> $USERID ) {
							echo error_response( "LOCKED", $whom, $debug );
						} else {
							if( ! unlink( $lock_path ) ) {
								echo error_response( "WRITEFAIL", 'unable to delete lock file', $debug );
							} else {
								$response = array();
								$response{'OUTCOME'} = 'UNCHECKOUT';
								if( $debug ) {
									echo "<pre>";
									var_dump($response);
									echo "</pre>";
									
								} else {
									echo encryptData(json_encode( $response ));
								}
							}
						}
					} else {
						echo error_response( "NOTLOCKED", 'image not checked out', $debug );
					}
				} 
			
				break;

			case 'CHECKIN':  // !CHECKIN - checkin image for trip
				
				// Validate fields
				if( $TRIP_NUMBER == "NONE" || $USERID == "NONE" || $EXT == "NONE" || $CONTENTS == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$lock_path = $stc_uploads_directory.$TRIP_NUMBER.".lck";
					$image_path = $stc_uploads_directory.$TRIP_NUMBER.".";
					// possible PHP upload errors
					$errors = array(1 => 'php.ini max file size exceeded', 
									2 => 'html form max file size exceeded', 
									3 => 'file upload was only partial', 
									4 => 'no file was attached');
					
					$fieldname = 'file';

					// Check for a lock
					if( ! file_exists( $lock_path ) ) { // Not locked
							echo error_response( "NOTLOCKED", 'image not checked out', $debug );
					} else {
						$whom = file_get_contents( $lock_path );
						if( $whom <> $USERID ) {
							echo error_response( "LOCKED", $whom, $debug );
						} elseif( ! in_array( $EXT, $stc_image_valid_file_formats ) ) {
							echo error_response( "FORMAT", 'not recognized format', $debug );
						} else {
							$found = false;
							foreach ( $stc_image_valid_file_formats as $fmt ) {
								if( file_exists( $image_path.$fmt ) ) {
									$found_path = $image_path.$fmt;
									$found = true;
									break;
								}
							}
							
							if( ! is_writable($lock_path) )
								echo error_response( "WRITEFAIL", 'lock file unwritable: '.$lock_path, $debug );
							else if( $found && ! is_writable($found_path) )
								echo error_response( "WRITEFAIL", 'image file unwritable: '.$found_path, $debug );
							else
							if( $found && ! unlink($found_path) ) {
								echo error_response( "WRITEFAIL", 'unable to remove previous image: '.$found_path, $debug );
							} else {	
								if( ! file_put_contents( $image_path.$EXT, base64_decode($CONTENTS) ) ) {
									echo error_response( "WRITEFAIL", 'unable to update image file: '.$image_path.$EXT, $debug );
								} else {
									if( ! unlink( $lock_path ) ) {
										echo error_response( "WRITEFAIL", 'unable to delete lock file: '.$lock_path, $debug );
									} else {
										$response = array();
										$response{'OUTCOME'} = 'CHECKIN';
										$response{'PATH'} = $image_path.$EXT;
										if( $debug ) {
											echo "<pre>";
											var_dump($response);
											echo "</pre>";
											
										} else {
											echo encryptData(json_encode( $response ));
										}
									}
																						
								}
								
																															
							}
					
						}
					}



				} 
			
				break;


			case 'GETBOL':  // !GETBOL - Get BOL for a FB
				// Validate fields
				if( $FB == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					$query_string = "SELECT i.det_indexvalue, i.hdr_intdocid, e.pev_date, 
							e.ety_code, e.document_type
						FROM img_pageindex i, v_events e
						WHERE i.det_indexvalue = '".$FB."'
						AND i.hdr_intdocid = e.hdr_intdocid
						AND e.ety_code = 'STORE'
						
						".($DOCID <> "NONE" ? "AND i.hdr_intdocid = '".$DOCID."'" : "");
						// AND e.document_type in ('BOL', 'INV')
						
					// Get a list of tables
					//$query_string = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
											
					if( $debug ) echo "<p>using query_string = $query_string1</p>";
			
					$response = send_pg_odbc_query( $query_string, $stc_pg_odbc_database, $debug );
					
					if( is_array($response) ) {
						for( $c = 0; $c < count($response); $c++ ) {
							$response[$c]{'PATH'} = '\\\\KTL-NEWIMAGING\\KTLTMWImages\\'.
								substr($response[$c]{'hdr_intdocid'},0,2).'\\'.
								substr($response[$c]{'hdr_intdocid'},2,2).'\\'.
								substr($response[$c]{'hdr_intdocid'},4,2).'\\'.
								substr($response[$c]{'hdr_intdocid'},6,2).".tif";

							if( file_exists($response[$c]{'PATH'}) && $DOCID <> "NONE" ) {
								$contents = file_get_contents($response[$c]{'PATH'});
		  						$base64   = base64_encode($contents);
								$response[$c]{'MIME'} = "image/tiff";
								$response[$c]{'CONTENTS'} = $base64;
							}

						}
										
						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
							
						} else {
							echo encryptData(json_encode( $response ));
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

