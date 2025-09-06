<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );

function get_client_id( $NAME, $PROVINCE, $CITY, $debug ) {
	global $stc_database, $stc_schema;
	$dbconn = new stc_db( $stc_database, $debug );
	
	$return_value = false;
	
	// Check App Config
	$query_string = "SELECT THE_VALUE FROM CONFIG
		WHERE PROG_NAME = 'PROFILE.EXE'
		AND COMPANY_ID = 1
		AND THE_OPTION = 'Auto Gen Client key'
		FOR READ ONLY
		WITH UR";
	
	if( $debug ) echo "<p>".__FUNCTION__.": using query_string = $query_string</p>";

	$response = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( $debug ) {
		echo "<pre>response\n";
		var_dump($response);
		echo "</pre>";
	}
	
	if( is_array($response) && count($response) == 1 && 
		$response[0]["THE_VALUE"] == "True") {	// We need to generate a client ID
		
		// Check that the custom code is installed
		$query_string = "select * from SYSCAT.PROCEDURES
							WHERE PROCNAME IS NOT NULL
							AND PROCSCHEMA = '".$stc_schema."'
						AND PROCNAME = 'CUSTOM_GET_CLIENT_ID'
						FOR READ ONLY
						WITH UR";
			
		if( $debug ) echo "<p>".__FUNCTION__.": using query_string = $query_string</p>";
		
		$response = send_odbc_query( $query_string, $stc_database, $debug );
		
		if( $debug ) {
			echo "<pre>response\n";
			var_dump($response);
			echo "</pre>";
		}
	
		if( is_array($response) && count($response) == 1 ) {

			// Generate Client ID 
			$query_string = "CALL ".$stc_schema.".CUSTOM_GET_CLIENT_ID( '".$NAME."', '".$PROVINCE."', '".$CITY."' )";
	
			if( $debug ) echo "<p>".__FUNCTION__.": using query_string = $query_string</p>";
	
			$response1 = send_odbc_query( $query_string, $stc_database, $debug );
			
			if( $debug ) {
				echo "<pre>response1\n";
				var_dump($response1);
				echo "</pre>";
			}
	
			if( is_array($response1) && count($response1) == 1 && 
				isset($response1[0]["CLIENT_ID"]) && $response1[0]["CLIENT_ID"] <> '' ) {
				
				$return_value = $response1[0]["CLIENT_ID"];
			}
		}
	}
	
	if( $debug ) echo "<p>".__FUNCTION__.": return $return_value</p>";
	return $return_value;
}

function set_ccat( $cid, $CCAT, $id, $debug ) {
	global $stc_database,$stc_schema;

	$return_value = false;

	// Prepare Select
	$query_string = "SELECT DATA
		FROM CUSTOM_DATA
		WHERE SRC_TABLE_KEY = '".$cid."'
		AND CUSTDEF_ID = ".$id;
	
	if( $debug ) echo "<p>using query_string = $query_string</p>";
	
	$response = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( is_array($response) ) {
		if( count( $response ) == 0 )
			$query_string = "INSERT INTO CUSTOM_DATA(SRC_TABLE_KEY, DATA, CUSTDEF_ID)
				VALUES( '".$cid."', '".$CCAT."', ".$id." )";
		else
			$query_string = "UPDATE CUSTOM_DATA
				SET DATA = '".$CCAT."'
				WHERE SRC_TABLE_KEY = '".$cid."'
				AND CUSTDEF_ID = ".$id;
			
		if( $debug ) echo "<p>using query_string = $query_string</p>";
	
		$response1 = send_odbc_query( $query_string, $stc_database, $debug );
		
		if( is_array($response1) ) {
			$return_value = "CHANGED";
		} else {
			$return_value = "NOT OK: send_odbc_query failed2: " . $last_odbc_error;
		}
	} else {
		$return_value = "NOT OK: send_odbc_query failed1: " . $last_odbc_error;
	}
	
	return $return_value;
}

function set_ctype( $cid, $CTYPE, $debug ) {
	global $stc_database,$stc_schema, $stc_custom_client_type_id;

	$return_value = false;

	// Prepare Select
	$query_string = "SELECT DATA
		FROM CUSTOM_DATA
		WHERE SRC_TABLE_KEY = '".$cid."'
		AND CUSTDEF_ID = ".$stc_custom_client_type_id;
	
	if( $debug ) echo "<p>using query_string = $query_string</p>";
	
	$response = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( is_array($response) ) {
		if( count( $response ) == 0 )
			$query_string = "INSERT INTO CUSTOM_DATA(SRC_TABLE_KEY, DATA, CUSTDEF_ID)
				VALUES( '".$cid."', '".$CTYPE."', ".$stc_custom_client_type_id." )";
		else
			$query_string = "UPDATE CUSTOM_DATA
				SET DATA = '".$CTYPE."'
				WHERE SRC_TABLE_KEY = '".$cid."'
				AND CUSTDEF_ID = ".$stc_custom_client_type_id;
			
		if( $debug ) echo "<p>using query_string = $query_string</p>";
	
		$response1 = send_odbc_query( $query_string, $stc_database, $debug );
		
		if( is_array($response1) ) {
			$return_value = "CHANGED";
		} else {
			$return_value = "NOT OK: send_odbc_query failed2: " . $last_odbc_error;
		}
	} else {
		$return_value = "NOT OK: send_odbc_query failed1: " . $last_odbc_error;
	}
	
	return $return_value;
}


	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman69";
	$option		= "NONE";
	$uid		= "NONE";
	$cid		= "NONE";
	$cnm		= "NONE";
	$own		= "NONE";
	$M1			= "NONE";
	$Y1			= "NONE";
	$M2			= "NONE";
	$Y2			= "NONE";
	$SORT		= "NONE";
	$DAYS		= "1";
	$SITE		= "NONE";
	$MANNING	= "NONE";

	$CLIENT_ID				= "NONE";
	$NAME					= "NONE";
	$COMMENTS				= "NONE";
	$ADDRESS_1				= "NONE";
	$ADDRESS_2				= "NONE";
	$CITY					= "NONE";
	$PROVINCE				= "NONE";
	$POSTAL_CODE			= "NONE";
	$BUSINESS_PHONE			= "NONE";
	$BUSINESS_PHONE_EXT		= "NONE";
	$FAX_PHONE				= "NONE";
	$EMAIL_ADDRESS			= "NONE";
	$COMPANY_URL			= "NONE";
	$COMPANY				= "NONE";
	$TOP					= "NONE";
	$NOGROUP				= false;
	$CCAT					= "NONE";
	$CTYPE					= "NONE";
	$HREP					= "NONE";
	$GROUP					= "NONE";
	$CCALLER				= "NONE";
	$CSHIPPER				= "NONE";
	$CCONS					= "NONE";
	$CBILLTO				= "NONE";
	$FORCE					= "NONE";
	
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
		} else if( $key == "DAYS" ) {
			$DAYS = $value;
		} else if( $key == "CID" ) {
			$cid = $value;
		} else if( $key == "CNM" ) {
			$cnm = $value;
		} else if( $key == "OWN" ) {
			$own = $value;
		} else if( $key == "CLIENT_ID" ) {
			$CLIENT_ID = $value;
		} else if( $key == "NAME" ) {
			$NAME = $value;
		} else if( $key == "COMMENTS" ) {
			$COMMENTS = $value;
		} else if( $key == "ADDRESS_1" ) {
			$ADDRESS_1 = $value;
		} else if( $key == "ADDRESS_2" ) {
			$ADDRESS_2 = $value;
		} else if( $key == "CITY" ) {
			$CITY = $value;
		} else if( $key == "PROVINCE" ) {
			$PROVINCE = $value;
		} else if( $key == "POSTAL_CODE" ) {
			$POSTAL_CODE = $value;
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
		} else if( $key == "COMPANY" ) {
			$COMPANY = $value;
		} else if( $key == "TOP" ) {
			$TOP = $value;
		} else if( $key == "CCAT" ) {
			$CCAT = $value;
		} else if( $key == "NOGROUP" ) {
			$NOGROUP = true;
		} else if( $key == "M1" ) {
			$M1 = $value;
		} else if( $key == "Y1" ) {
			$Y1 = $value;
		} else if( $key == "M2" ) {
			$M2 = $value;
		} else if( $key == "Y2" ) {
			$Y2 = $value;
		} else if( $key == "SORT" ) {
			$SORT = $value;
		} else if( $key == "CTYPE" ) {
			$CTYPE = $value;
		} else if( $key == "SITE" ) {
			$SITE = $value;
		} else if( $key == "HREP" ) {
			$HREP = $value;
		} else if( $key == "GROUP" ) {
			$GROUP = $value;
		} else if( $key == "CCALLER" ) {
			$CCALLER = $value;
		} else if( $key == "CSHIPPER" ) {
			$CSHIPPER = $value;
		} else if( $key == "CCONS" ) {
			$CCONS = $value;
		} else if( $key == "CBILLTO" ) {
			$CBILLTO = $value;
		} else if( $key == "FORCE" ) {
			$FORCE = $value;
		} else if( $key == "MANNING" ) {
			$MANNING = $value;
		}
	}
	
	if( $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>STC CRM Backend - Client Functions</title>
</head>

<body>
<?
	}
	
	if( $password == $valid_pw ) {
		
		switch (strtoupper($option)) {
			case 'EXISTS':	// !EXISTS - Check if client exists
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "select CLIENT_ID
					FROM CLIENT WHERE CLIENT_ID = '".$cid."'";
					 
					if( $debug ) echo "<p>using query_string = $query_string</p>";
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( $response ) {
						if( $debug ) echo "<p>EXISTS</p>";
						else echo encryptData("EXISTS");
					} else {
						if( $debug ) echo "<p>AVAILABLE</p>";
						else echo encryptData("AVAILABLE");
					}
				}

				break;

			case 'GETID':	// !GETID - Find client id
				// Validate fields
				if( $NAME == "NONE" || $POSTAL_CODE == "NONE" || $ADDRESS_1 == "NONE" || $BUSINESS_PHONE == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$dbconn = new stc_db( $stc_database, $debug );
				
					// Prepare Select
					$query_string = "SELECT CLIENT_ID FROM CLIENT
						WHERE IS_INACTIVE = 'False' AND
						((NAME LIKE '".$dbconn->escape($NAME)."%'
						AND POSTAL_CODE = '".$POSTAL_CODE."')
			            OR
			            (RIGHT(BUSINESS_PHONE,8) = RIGHT('".$BUSINESS_PHONE."',8)
			            AND SUBSTR(ADDRESS_1, 1, 6) = SUBSTR('".$ADDRESS_1."', 1, 6)
			            AND SUBSTR(NAME, 1, 5) = SUBSTR('".$NAME."', 1, 5)
			            AND POSTAL_CODE = '".$POSTAL_CODE."'))
			            ORDER BY CLIENT_ID ASC
			            FETCH FIRST 1 ROWS ONLY
			            FOR READ ONLY
			            WITH UR";
		//Scott, can the program cross-check. Phone,  first six characters of address line 1, first 5 characters of Name,  and zip?

					if( $debug ) echo "<p>using query_string = $query_string</p>";
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) && count($response) == 1 &&
						! empty($response[0]['CLIENT_ID']) ) {
						if( $debug ) echo "<p>".$response[0]['CLIENT_ID']."</p>";
						else echo encryptData(json_encode( $response[0]['CLIENT_ID'] ));
;
					} else {
						if( $debug ) echo "<p>NOTFOUND</p>";
						else echo encryptData("NOTFOUND");
					}
				}

				break;

			case 'ADD':	// !ADD - Add a client
				
					if( $FORCE == 'NONE' ) {
						// Search for matching clients, to avoid duplicates
						$query_string = "SELECT CLIENT_ID, NAME, ADDRESS_1, CITY, PROVINCE, 
							POSTAL_CODE, BUSINESS_PHONE
							FROM CLIENT
							WHERE SUBSTR(NAME, 1, MIN(10, CHARACTER_LENGTH(NAME, OCTETS)) ) = 
								SUBSTR('".$NAME."', 1, MIN(10, CHARACTER_LENGTH('".$NAME."', OCTETS)) )
							OR SUBSTR(ADDRESS_1, 1, MIN(10, CHARACTER_LENGTH(ADDRESS_1, OCTETS)) ) = 
								SUBSTR('".$ADDRESS_1."', 1, MIN(10, CHARACTER_LENGTH('".$ADDRESS_1."', OCTETS)) )
							OR POSTAL_CODE = '".$POSTAL_CODE."'
							OR BUSINESS_PHONE = '".$BUSINESS_PHONE."'
							FOR READ ONLY
							WITH UR";
	
						if( $debug ) {
							echo "<p>using query_string = </p>
							<pre>";
							var_dump($query_string);
							echo "</pre>";
						}
	
						$response1 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( is_array($response1) && count($response1) > 0) {
							$response = array();
							$response{'OUTCOME'} = 'MATCH';
							$response{'MATCHES'} = $response1;
	
							if( $debug ) {
								echo "<pre>";
								var_dump($response);
								echo "</pre>";
							} else {
								echo encryptData(json_encode( $response ));
							}
							break;
						}
					}
					//if( $debug ) break;
				
				if( $CLIENT_ID == "NONE" ) {	// If the system auto generates Client IDs
					$response = get_client_id( $NAME, $PROVINCE, $CITY, $debug);
					
					if( $response ) $CLIENT_ID = $response;				
				}
				if( $debug ) {
					echo "<pre>";
					var_dump($uid,$CLIENT_ID,$NAME,$ADDRESS_1,$CITY,$PROVINCE,$POSTAL_CODE,$BUSINESS_PHONE );
					echo "</pre>";
				}
				if( $uid == "NONE" || $CLIENT_ID == "NONE" || $NAME == "NONE" || $ADDRESS_1 == "NONE" || 
					$CITY == "NONE" || $PROVINCE == "NONE" || $POSTAL_CODE == "NONE" || 
					$BUSINESS_PHONE == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo error_response( "NOT OK", 'Required field missing or blank.', $debug );
				} else {
					// Manning uses VENDOR table for sales rep
					$query_string1a = "select VENDOR_ID from vendor
						WHERE IS_INACTIVE = 'False'
						AND VENDOR_TYPE = 'A'
						AND USER1 = '".$uid."'";
					
					if( $debug ) {
						echo "<p>using query_string1a = </p>
						<pre>";
						var_dump($query_string1a);
						echo "</pre>";
					}
					
					$response1a = send_odbc_query( $query_string1a, $stc_database, $debug );
					
					$REP_ID = "";
					if( is_array($response1a) && count($response1a) == 1 &&
						! empty($response1a[0]["VENDOR_ID"]))
						$REP_ID = $response1a[0]["VENDOR_ID"];
					
					// Changed USD to BRO, 'N' to NULL for Hutt
					$query_string = "INSERT INTO CLIENT(CLIENT_ID,NAME,ADDRESS_1,ADDRESS_2,CITY,PROVINCE,POSTAL_CODE,BUSINESS_PHONE,
BUSINESS_PHONE_EXT,FAX_PHONE,BUSINESS_CELL,ADDITION_DATE,CURRENCY_CODE,RATE_SCHEDULE,
ADDRESS_STYLE,DEFAULT_DELIVERY_Z,OPEN_TIME,CLOSE_TIME,STATUS_1,STATUS_2,STATUS_3,

COMMENTS,COMMENTS_RTF,PREFERRED_DRIVER,CUSTOMS_BROKER,EDI_DISPATCH_9,CUSTOMER_SINCE,
GST,DEFAULT_COMMODITY,CONTACT,BILL_CUSTOMER,CREDIT_HOLD,
TRACE_NO,DISCOUNT,CBTAX_1,CBTAX_2,DIST_CODE,EXPORT_CODE,USER_ID,TOTAL_OWED,CREDIT_LIMIT,

ROUTING_CODE,PAY_DISC_DAYS,PAY_DISC_PERC,PAY_TERMS,PAY_DAYS,COD_TYPE,COD_FACTOR,
COD_MINIMUM,COD_MAXIMUM,COLLECTOR,LANGUAGE,SALES_REP,SOLVABILITY,MILEAGE_PROFILE,
DR_CASH_COLLECT,CLIENT_IS_CALLER,CLIENT_IS_SHIPPER,CLIENT_IS_CONSIGNEE,CLIENT_IS_BILL_TO,

POD_REQUIRED,STATEMENT_FREQ,STATEMENT_DELIVERY,EMAIL_ADDRESS,NUM_COPIES,AGENT,AGENT_PERCENTAGE,
AGENT_MAXIMUM,COL_CALL_BACK,OUTBOUND_CUSTOMS_BROKER,CUSTOMER_GROUP,SEPRIORITY,SEREMARKS,
DEFAULT_UOM,POSLAT,POSLONG,EDI_CUSTOMER,REQUESTED_EQUIPMEN,APPT_REQ,WEB_ENABLED,

USER1,USER2,USER3,USER4,USER5,USER6,USER7,USER8,USER9,USER10,TRACE_POPUP,CUBE_TO_WEIGHT,
MANUAL_RATE,POP_UP_NOTES,PCS_PER_HOUR,FC_CHARGE,FC_GROUP,FC_OPTION,FC_RATE,MODIFIED_BY,
MODIFIED_DATE,CUBE_640,CONS_TYPE,DRPAY_TYPE,FLIP_ORIGIN,FLIP_ORIGIN_CODE,FLIP_DEST,

FLIP_DEST_CODE,IS_STEAMSHIP,IS_RAILYARD,IS_PORT,CSA_NUMBER,CARE_OF_DELIVERY,
RADIUS_LOQ,RADIUS_HIQ,LATSEC,LONSEC,LATLO_LOQ,LATHI_LOQ,LONLO_LOQ,LONHI_LOQ,LATLO_HIQ,
LATHI_HIQ,LONLO_HIQ,LONHI_HIQ,TRIP_NOTIFY_FAX_ENABLED,TRIP_NOTIFY_FAX,TRIP_NOTIFY_EMAIL_ENABLED,

TRIP_NOTIFY_EMAIL,IS_RM_CUSTOMER,RM_PART_MARKUP,RM_LABOUR_MARKUP,RM_NO_TAX1,RM_NO_TAX2,
RATE_CLIENT_ID,GMT_OFFSET,DST_APPLIES,SUITE,FLOOR,DOCKNUM,STAIRS,ELEVATOR,DOCK,SITESURVEY,
VEHICLEREST,EMAIL,WS_O214_ASTRING,WS_O214_TRACENUM,WS_O214_URL,WS_O214_CTRACENUM,

WS_O204_AUTOSEND,WS_O204_DLL,WS_O204_PORT,WS_O204_SERVICE,WS_O204_WSDL,UNIT,IS_INACTIVE,
AUTO_ASSIGN_STMTS,AUTO_ASSIGN1,AUTO_ASSIGN2,AUTO_ASSIGN3,ADDRESS_STATUS,REQ_CD_RELEASE,
WS_O204_ASTRING,WS_O204_AUTO_POST,WS_O204_AUTO_CALLER,RATE_CLIENT_MIN1,RATE_CLIENT_MIN2,

RATE_CLIENT_DISC,RATE_CLIENT_ACC_CH,RATE_AGGREGATE,WS_O214_DLL,DETENTION_NO_WARNING,
DETENTION_WARNING_OPTION,DETENTION_ALT_EMAIL,DETENTION_ALT_FAX,CARE_OF_PICKUP,VELTION_CLIENT_ID,
IS_TERMINAL,IS_TRAILER_POOL,ACE_ID,FAST_ID,DUNS_ID,IRS_TAX_ID,SCAC_ID,FILER_ID,FIRMS_ID,
CUSTOMS_ASSIGNED,CREDIT_STATUS,PAYMENT_OPTIONS,SPOT_TRAILER,DEFAULT_TERMINAL,DEFAULT_TERMINAL_ZONE,
SECOUNT4,SECOUNT3,SECOUNT2,SECOUNT1,CLAIMS_OUTSTANDING,FB_AAPPRVD,COMPANY_URL,

CARRIER_RATE_CONS,CUSTOMS_BROKER_VENDOR,PREPAID_COLLECT,EXCLUDE_CLOSEDDAYS_DETENTION,
EXCLUDE_CLOSEDDAYS_FREETIME,CW_BREAK_POINT,PARTS_MARKUP_CAT,LABOUR_MARKUP_CAT,WEIGHT_TO_CUBE,
MAP_BOOK,RATE_METRICS_BASE,COUNTRY,ALT_TAX1,ALT_TAX2,ALT_TAX_JUR,RATE_TO_OVERRIDE)

VALUES('".$CLIENT_ID."','".$NAME."','".$ADDRESS_1."','".($ADDRESS_2="NONE"?"":$ADDRESS_2)."','".$CITY."','".$PROVINCE."', '".$POSTAL_CODE."','".$BUSINESS_PHONE."',
'".($BUSINESS_PHONE_EXT="NONE"?"":$BUSINESS_PHONE_EXT)."','".($FAX_PHONE="NONE"?"":$FAX_PHONE)."',
'','".date("Y-m-d H:i:s.000000")."','USD','',NULL,'57401',NULL,NULL,'','','',

".($COMMENTS <> "NONE" ? "'".$COMMENTS."'" : "NULL").",".($COMMENTS <> "NONE" ? "BLOB('".$COMMENTS."')" : "NULL").",'','','','".date("Y-m-d H:i:s.000000")."',
'False','','','','False',
'',NULL,'False','False','','','".$uid."',0,0,

'1',0,0,'Net 30',0,'',NULL,
NULL,NULL,'','E','".$REP_ID."','','DEFAULT',
'False','".($CCALLER=='on'? "True" : "False")."','".($CSHIPPER=='on'? "True" : "False")."','".($CCONS=='on'? "True" : "False")."','".($CBILLTO=='on'? "True" : "False")."',

'False','M','M','".($EMAIL_ADDRESS="NONE"?"":$EMAIL_ADDRESS)."',1,'',0,
0,NULL,'','',0,'',
'','','','False','','False','False',

NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'','False',0,
'False','False',NULL,'False','',2,0,'".$uid."',
'".date("Y-m-d H:i:s.000000")."','False','DEFAULT','','False','','False',

'','False','False','False','','',
NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,
NULL,NULL,NULL,'True','','True',

'','True',0,0,'False','False',
'','','False','','','','','','','',
'','','','','','',

'False','','','','','','False',
'False','','',NULL,'','False',
'','False','False','','',

'','','False','','False',
'EMail','','','','',
'False','False','','','','','','','',
'','CREDIT','Default','False','','',
0,0,0,0,0,'False',
'".($COMPANY_URL="NONE"?"":$COMPANY_URL)."','False','','NA','True','False',640,'','',0,NULL,
'False','','False','False',NULL,'False')";
											
				
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string);
						echo "</pre>";
					}
					
					$response1 = send_odbc_query( $query_string, $stc_database, $debug );
		
					if( $debug ) echo "<p>response1 ".gettype($response1)."</p>";
					
					if( is_array($response1) ) {
						if( $debug ) echo "<p>CLIENT ADDED</p>";

						if( $stc_custom_client_categories && $CCAT <> "NONE" ) {
							set_ccat( $CLIENT_ID, $stc_custom_client_categories_id, $CCAT, $debug );
						}
						if( $stc_custom_client_types && $CTYPE <> "NONE" ) {
							set_ctype( $CLIENT_ID, $CTYPE, $debug );
						}
						$response = array();
						$response{'OUTCOME'} = 'ADDED';
						$response{'CLIENT_ID'} = $CLIENT_ID;
						
						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
									
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo error_response( "NOT OK", 'send_odbc_query 1 failed: ' . $last_odbc_error, $debug );
					}
					
					
				}

				break;

			case 'MOD':	// !MOD - Modify a client
				// Validate fields
				if( $uid == "NONE" || $CLIENT_ID == "NONE" || 
					($NAME == "NONE" && $ADDRESS_1 == "NONE" && $ADDRESS_2 == "NONE" && $CITY == "NONE" &&
					$PROVINCE == "NONE" && $POSTAL_CODE == "NONE" && $BUSINESS_PHONE == "NONE" &&
					$BUSINESS_PHONE_EXT == "NONE" &&
					$FAX_PHONE == "NONE" && $EMAIL_ADDRESS == "NONE" && $COMPANY_URL == "NONE") ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Update
					$query_string = "UPDATE CLIENT ";
					$first = true;
					if( $NAME <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." NAME = '".$NAME."' ";
						$first = false;
					}
					if( $COMMENTS <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." COMMENTS = '".$COMMENTS."',  COMMENTS_RTF = BLOB('".$COMMENTS."') ";
						$first = false;
					}
					if( $ADDRESS_1 <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." ADDRESS_1 = '".$ADDRESS_1."' ";
						$first = false;
					}
					if( $ADDRESS_2 <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." ADDRESS_2 = '".$ADDRESS_2."' ";
						$first = false;
					}
					if( $CITY <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." CITY = '".$CITY."' ";
						$first = false;
					}
					
					if( $PROVINCE <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." PROVINCE = '".$PROVINCE."' ";
						$first = false;
					}
					if( $POSTAL_CODE <> "" ) {
						$query_string .= ($first ? "SET":",")." POSTAL_CODE = '".$POSTAL_CODE."' ";
						$first = false;
					}
					if( $BUSINESS_PHONE <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." BUSINESS_PHONE = '".$BUSINESS_PHONE."' ";
						$first = false;
					}
					if( $BUSINESS_PHONE_EXT <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." BUSINESS_PHONE_EXT = '".$BUSINESS_PHONE_EXT."' ";
						$first = false;
					}
					
					if( $FAX_PHONE <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." FAX_PHONE = '".$FAX_PHONE."' ";
						$first = false;
					}
					if( $EMAIL_ADDRESS <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." EMAIL_ADDRESS = '".$EMAIL_ADDRESS."' ";
						$first = false;
					}
					if( $COMPANY_URL <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." COMPANY_URL = '".$COMPANY_URL."' ";
						$first = false;
					}
					if( $CCALLER <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." CLIENT_IS_CALLER = '".($CCALLER=='on'? "True" : "False")."' ";
						$first = false;
					}
					if( $CSHIPPER <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." CLIENT_IS_SHIPPER = '".($CSHIPPER=='on'? "True" : "False")."' ";
						$first = false;
					}
					if( $CCONS <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." CLIENT_IS_CONSIGNEE = '".($CCONS=='on'? "True" : "False")."' ";
						$first = false;
					}
					if( $CBILLTO <> "NONE" ) {
						$query_string .= ($first ? "SET":",")." CLIENT_IS_BILL_TO = '".($CBILLTO=='on'? "True" : "False")."' ";
						$first = false;
					}
					
					$query_string .= ",	MODIFIED_BY = '".$uid."',
						MODIFIED_DATE = '".date("Y-m-d H:i:s.000000")."'";
						
					$query_string .= "WHERE CLIENT_ID = '".$CLIENT_ID."'";     
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) ) {
						if( $debug ) echo "<p>CHANGED CLIENT</p>";
						
						// Prepare Update
						$query_string = "UPDATE PHONE ";
						$first = true;
						if( $NAME <> "NONE" ) {
							$query_string .= ($first ? "SET":",")." COMPANYNAME = '".$NAME."' ";
							$first = false;
						}
						if( $ADDRESS_1 <> "NONE" ) {
							$query_string .= ($first ? "SET":",")." ADDRESS_1 = '".$ADDRESS_1."' ";
							$first = false;
						}
						if( $ADDRESS_2 <> "NONE" ) {
							$query_string .= ($first ? "SET":",")." ADDRESS_2 = '".$ADDRESS_2."' ";
							$first = false;
						}
						if( $CITY <> "NONE" ) {
							$query_string .= ($first ? "SET":",")." CITY = '".$CITY."' ";
							$first = false;
						}
						
						if( $PROVINCE <> "NONE" ) {
							$query_string .= ($first ? "SET":",")." PROVINCE = '".$PROVINCE."' ";
							$first = false;
						}
						if( $POSTAL_CODE <> "NONE" ) {
							$query_string .= ($first ? "SET":",")." POSTAL_CODE = '".$POSTAL_CODE."' ";
							$first = false;
						}
						if( $BUSINESS_PHONE <> "NONE" ) {
							$query_string .= ($first ? "SET":",")." MASTERPHONENUMBER = '".$BUSINESS_PHONE."' ";
							$first = false;
						}
						if( $BUSINESS_PHONE_EXT <> "NONE" ) {
							$query_string .= ($first ? "SET":",")." BUSINESS_PHONE_EXT = '".$BUSINESS_PHONE_EXT."' ";
							$first = false;
						}
						
						
						$query_string .= "WHERE CLIENTID = '".$CLIENT_ID."'";     
						
						if( $debug ) echo "<p>using query_string = $query_string</p>";
				
						$response = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( is_array($response) ) {
							if( $stc_custom_client_categories && $CCAT <> "NONE" ) {
								set_ccat( $CLIENT_ID, $stc_custom_client_categories_id, $CCAT, $debug );
							}
							if( $stc_custom_client_types && $CTYPE <> "NONE" ) {
								set_ctype( $CLIENT_ID, $CTYPE, $debug );
							}

							if( $debug ) echo "<p>CHANGED PHONE</p>";
							
							else echo encryptData("CHANGED");
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
						}

					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}
				break;

			case 'LIST':	// !LIST - List of clients
				// Validate fields
				if( $uid == "NONE" && $GROUP == "NONE") {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					if( strtoupper($SORT) == 'MODIFIED' ) {
						$sort_str = 'MODIFIED_DATE DESC';
					} else if( strtoupper($SORT) == 'DUE' ) {
						$sort_str = 'REMINDER_DATE ASC';
					} else {
						$sort_str = 'NAME ASC';
					}
				
					if( $stc_custom_client_types && $CTYPE <> "NONE" ) {
						if( $CTYPE == "Blank" ) $type_match = "WHERE CTYPE IS NULL";
						else $type_match = "WHERE CTYPE = '".$CTYPE."'";					
					} else
						$type_match = "";

					$query_string = "select CLIENT_ID, CUSTOMER_GROUP, IS_INACTIVE, USER2, 
						ALT_NAME, REMINDER_DATE,
						MODIFIED_DATE, NAME, 
						ADDRESS_1, ADDRESS_2, CITY, PROVINCE, POSTAL_CODE, BUSINESS_PHONE,
						BUSINESS_PHONE_EXT, FAX_PHONE, EMAIL_ADDRESS, COMPANY_URL, SALES_REP, ACCOUNT_REP,
						CCAT, CTYPE, PD, USER_ID, TITLE, NOTETYPE, THE_NOTE
						
						FROM
						(select CLIENT_ID, CUSTOMER_GROUP, IS_INACTIVE, USER2, 
						(SELECT X.NAME FROM CLIENT X WHERE X.CLIENT_ID = CLIENT.USER2) AS ALT_NAME,
						(SELECT DATE(USERDATE1) FROM PHONE
							WHERE PHONE.CLIENTID = CLIENT_ID
							FETCH FIRST ROW ONLY) AS REMINDER_DATE,
						MODIFIED_DATE, NAME,
						ADDRESS_1, ADDRESS_2, CITY, PROVINCE, POSTAL_CODE, BUSINESS_PHONE,
						BUSINESS_PHONE_EXT, FAX_PHONE, EMAIL_ADDRESS, COMPANY_URL, SALES_REP,
						(SELECT DATA
							FROM CUSTOM_DATA
							WHERE CUSTDEF_ID = ".$stc_custom_client_account_rep_id."
							AND CLIENT_ID = SRC_TABLE_KEY) AS ACCOUNT_REP,
						".($stc_custom_client_categories ? "(SELECT DATA
							FROM CUSTOM_DATA
							WHERE CUSTDEF_ID = ".$stc_custom_client_categories_id."
							AND CLIENT_ID = SRC_TABLE_KEY)" : "'NONE'")." AS CCAT,
						".($stc_custom_client_types ? "(SELECT DATA
							FROM CUSTOM_DATA
							WHERE CUSTDEF_ID = ".$stc_custom_client_type_id."
							AND CLIENT_ID = SRC_TABLE_KEY)" : "'NONE'")." AS CTYPE
						
						FROM CLIENT
						".($GROUP <> "NONE" ? "WHERE CUSTOMER_GROUP = '".$GROUP."'" : 
							"WHERE SALES_REP = '".$uid."' OR
							UPPER((SELECT DATA
							FROM CUSTOM_DATA
							WHERE CUSTDEF_ID = ".$stc_custom_client_account_rep_id."
							AND CLIENT_ID = SRC_TABLE_KEY)) = '".$uid."'")."
						)
						LEFT OUTER JOIN
						
						(SELECT CLIENTID as CID, PID, PD, n.USER_ID, n.TITLE,
						n.NOTETYPE, n.THE_NOTE
						FROM
						(SELECT p.CLIENTID, n.PHONEID AS PID, MAX(PHONEDATE) PD
						FROM PHONE p, PHONENOTES n
						WHERE p.PHONEID = n.PHONEID
						GROUP BY p.CLIENTID, n.PHONEID
						ORDER BY PD DESC), PHONENOTES n
						WHERE PID = n.PHONEID
						AND PD = n.PHONEDATE)
						
						ON CID = CLIENT_ID
						".$type_match."

						ORDER BY ".$sort_str."
						WITH UR";
					
						if( $debug ) {
							echo "<p>using query_string = </p>
							<pre>";
							var_dump($query_string);
							echo "</pre>";
						}
			
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

			case 'RECENT':	// !RECENT - List clients recently updated
				// Prepare Select
				$query_string = "select CLIENT_ID, MODIFIED_DATE, NAME,
					ADDRESS_1, ADDRESS_2, CITY, PROVINCE, POSTAL_CODE, BUSINESS_PHONE,
					BUSINESS_PHONE_EXT, FAX_PHONE, EMAIL_ADDRESS, COMPANY_URL, SALES_REP,
					CCAT, CTYPE, PD, USER_ID, TITLE, NOTETYPE, THE_NOTE
					
					FROM
					(select CLIENT_ID, MODIFIED_DATE, NAME, 
					ADDRESS_1, ADDRESS_2, CITY, PROVINCE, POSTAL_CODE, BUSINESS_PHONE,
					BUSINESS_PHONE_EXT, FAX_PHONE, EMAIL_ADDRESS, COMPANY_URL, SALES_REP,
					".($stc_custom_client_categories ? "(SELECT DATA
						FROM CUSTOM_DATA
						WHERE CUSTDEF_ID = ".$stc_custom_client_categories_id."
						AND CLIENT_ID = SRC_TABLE_KEY)" : "'NONE'")." AS CCAT,
					".($stc_custom_client_types ? "(SELECT DATA
						FROM CUSTOM_DATA
						WHERE CUSTDEF_ID = ".$stc_custom_client_type_id."
						AND CLIENT_ID = SRC_TABLE_KEY)" : "'NONE'")." AS CTYPE
					FROM CLIENT
					WHERE MODIFIED_DATE > CURRENT DATE - ".$DAYS." DAYS
					".($uid <> "NONE" ? "AND SALES_REP = '".$uid."'" : "")."
					ORDER BY MODIFIED_DATE DESC)
						
					LEFT OUTER JOIN
					
					(SELECT CLIENTID as CID, PID, PD, n.USER_ID, n.TITLE,
					n.NOTETYPE, n.THE_NOTE
					FROM
					(SELECT p.CLIENTID, n.PHONEID AS PID, MAX(PHONEDATE) PD
					FROM PHONE p, PHONENOTES n
					WHERE p.PHONEID = n.PHONEID
					GROUP BY p.CLIENTID, n.PHONEID
					ORDER BY PD DESC), PHONENOTES n
					WHERE PID = n.PHONEID
					AND PD = n.PHONEDATE)
					
					ON CID = CLIENT_ID
					ORDER BY MODIFIED_DATE DESC
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

			case 'SEARCH':	// !SEARCH - Search for a client
				// Validate fields
				if( $cnm == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "select CLIENT_ID, NAME,
						ADDRESS_1, ADDRESS_2, CITY, PROVINCE, POSTAL_CODE, BUSINESS_PHONE, BUSINESS_PHONE_EXT,
						FAX_PHONE, EMAIL_ADDRESS, COMPANY_URL, SALES_REP,
						".($stc_custom_client_categories ? "(SELECT DATA
							FROM CUSTOM_DATA
							WHERE CUSTDEF_ID = ".$stc_custom_client_categories_id."
							AND CLIENT_ID = SRC_TABLE_KEY)" : "'NONE'")." AS CCAT
						FROM CLIENT
						WHERE (NAME LIKE '%".$cnm."%' OR CLIENT_ID LIKE '%".$cnm."%')
						".($uid <> "NONE" ? "AND COALESCE(SALES_REP, '') <> '".$uid."'" : "")."
						
						ORDER BY 2 ASC
						WITH UR";

// hangs						
//(SELECT MAX(BILL_DATE)     FROM TLORDER WHERE BILL_TO_CODE = CLIENT_ID AND INTERFACE_STATUS_F > 0 AND TOTAL_CHARGES <> 0) AS LastBillDate

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

			case 'OWN':	// !OWN - Own a client
				// Validate fields
				if( $uid == "NONE" || $own == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$clients = explode(' ', $own);
					//echo "<pre>";
					//var_dump($own, $clients);
					//echo "</pre>";
					foreach($clients as &$client) {
						$client = "'".$client."'";
					}
					$own2 = "IN (".join(",", $clients).")";
					//echo "<p>own2 $own2</p>";
					
					// Prepare Select
					$query_string = "UPDATE CLIENT
						SET SALES_REP = '".$uid."',
						MODIFIED_BY = '".$uid."',
						MODIFIED_DATE = '".date("Y-m-d H:i:s.000000")."'
						WHERE CLIENT_ID ".$own2;

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

			case 'DISOWN':	// !DISOWN - Disown a client
				// Validate fields
				if( $own == "NONE" || $uid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$clients = explode(' ', $own);
					//echo "<pre>";
					//var_dump($own, $clients);
					//echo "</pre>";
					foreach($clients as &$client) {
						$client = "'".$client."'";
					}
					$own2 = "IN (".join(",", $clients).")";
					//echo "<p>own2 $own2</p>";
					
					// Prepare Select
					$query_string = "UPDATE CLIENT
						SET SALES_REP = '',
						MODIFIED_BY = '".$uid."',
						MODIFIED_DATE = '".date("Y-m-d H:i:s.000000")."'
						WHERE CLIENT_ID ".$own2;

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

			case 'FETCH':	// !FETCH - Fetch client data
				
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
				//	fix_duplicate_phone( $cid, $debug );
				
					// Prepare Select
					$query_string = "select CLIENT_ID, CUSTOMER_GROUP, IS_INACTIVE, USER2, 
						(SELECT X.NAME FROM CLIENT X WHERE X.CLIENT_ID = CLIENT.USER2) AS ALT_NAME,
						MODIFIED_DATE, NAME, 
						ADDRESS_1, ADDRESS_2, CITY, PROVINCE, POSTAL_CODE, BUSINESS_PHONE,
						BUSINESS_PHONE_EXT, FAX_PHONE, EMAIL_ADDRESS, COMPANY_URL, SALES_REP,
						(SELECT DATA
							FROM CUSTOM_DATA
							WHERE CUSTDEF_ID = ".$stc_custom_client_account_rep_id."
							AND CLIENT_ID = SRC_TABLE_KEY) AS ACCOUNT_REP,
						RTRIM(CLIENT_IS_CALLER) AS CLIENT_IS_CALLER, 
						RTRIM(CLIENT_IS_SHIPPER) AS CLIENT_IS_SHIPPER, 
						RTRIM(CLIENT_IS_CONSIGNEE) AS CLIENT_IS_CONSIGNEE, 
						RTRIM(CLIENT_IS_BILL_TO) AS CLIENT_IS_BILL_TO,
						(SELECT DATE(USERDATE1) FROM PHONE
							WHERE PHONE.CLIENTID = CLIENT_ID
							fetch first 1 row only) AS REMINDER_DATE,
						(SELECT CAST(COALESCE(USERNUM1, 0) AS INTEGER) FROM PHONE
							WHERE PHONE.CLIENTID = CLIENT_ID
							fetch first 1 row only) AS REPEAT_DAYS,
						CAST(COMMENTS AS VARCHAR(6000)) AS COMMENTS,
						(SELECT COUNT(*)
							FROM QUOTE Q, TLORDER T
							WHERE Q.CLIENT_NUMBER = '".$cid."'
							AND 'Q' || Q.QUOTE_NUMBER = T.BILL_NUMBER
							AND T.CURRENT_STATUS <> 'CANCL'
							AND Q.EFFECTIVE_DATE > CURRENT DATE - 30 DAYS) AS QUOTES,
						
						(select ROUND(COALESCE(avg(billing),0),2) AVEBILL
							FROM
							(SELECT RTRIM(RTRIM(CHAR(YEAR(T.BILL_DATE))) || '-' || CHAR(MONTH(T.BILL_DATE))) MNTH,
							 ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING
							FROM TLORDER T
							WHERE (T.INTERFACE_STATUS_F >= 0 OR T.INTERFACE_STATUS_F IS NULL)
							AND T.BILL_DATE BETWEEN current timestamp - 1 year AND CURRENT TIMESTAMP
							AND (T.BILL_TO_CODE = '".$cid."' OR T.CUSTOMER = '".$cid."')
							GROUP BY YEAR(BILL_DATE), MONTH(BILL_DATE)
							ORDER BY YEAR(BILL_DATE), MONTH(BILL_DATE))) as AVEBILL,
						".($stc_custom_client_categories ? "(SELECT DATA
							FROM CUSTOM_DATA
							WHERE CUSTDEF_ID = ".$stc_custom_client_categories_id."
							AND CLIENT_ID = SRC_TABLE_KEY)" : "'NONE'")." AS CCAT,
						".($stc_custom_client_types ? "(SELECT DATA
							FROM CUSTOM_DATA
							WHERE CUSTDEF_ID = ".$stc_custom_client_type_id."
							AND CLIENT_ID = SRC_TABLE_KEY)" : "'NONE'")." AS CTYPE
						
						FROM CLIENT
						WHERE CLIENT_ID = '".$cid."'
						WITH UR";

					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string);
						echo "</pre>";
					}
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) && count($response) > 0 ) {
						if( $debug ) {
							echo "<pre>response\n";
							var_dump($response);
							echo "</pre>";
						}
						// Prepare Select
						$query_string = "SELECT BILL_TO_CODE, CUSTOMER, CALLCONTACT, CALLPHONE, CALLEMAIL, 
							MAX(DATE(CREATED_TIME)) MDATE
							FROM TLORDER
							WHERE BILL_TO_CODE = '".$cid."'
							AND CALLCONTACT <> ''
							GROUP BY BILL_TO_CODE, CUSTOMER, CALLCONTACT, CALLPHONE, CALLEMAIL
							ORDER BY BILL_TO_CODE, CUSTOMER, CALLCONTACT, CALLPHONE, CALLEMAIL
							WITH UR";
						if( $debug ) echo "<p>using query_string = $query_string</p>";
				
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( $debug ) {
							echo "<pre>response2\n";
							var_dump($response2);
							echo "</pre>";
						}
						if( is_array($response2) && count($response2) > 0 ) {
							$response['CALLERS'] = $response2;
						}
					
						if( $debug ) {
							echo "<pre>return response\n";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response,  JSON_INVALID_UTF8_IGNORE ));
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}

				break;

			case 'AR':	// !AR - Client AR info
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = 'with collections(client, name, state, phone, ext, contact, email, call_back, inactive,  collector,
                 fax, since, currency, status, hold, options, owed, limit, terms, pay_days, sales_rep, solvability) as
 (select client_id, name, province, business_phone, business_phone_ext,
      contact, coalesce(email_address,email), date(col_call_back), is_inactive,
       cast(coalesce(collector,user_id) as varchar(10)), fax_phone, date(customer_since), currency_code, credit_status,
       credit_hold, payment_options, coalesce(round(total_Owed,0),0), coalesce(credit_limit,0), pay_terms, pay_days,
       cast(sales_rep as varchar(10)), solvability
   from client where client_id = \''.$cid.'\')
select status "Status", hold  "Hold?", options "PmtOptions", limit "CreditLimit",
 (SELECT COUNT(*)           FROM TLORDER WHERE BILL_TO_CODE = CLIENT AND INTERFACE_STATUS_F IS NULL AND TOTAL_CHARGES <> 0) AS "OpenBills",
 round(coalesce((SELECT SUM(TOTAL_CHARGES) FROM TLORDER WHERE BILL_TO_CODE = CLIENT AND INTERFACE_STATUS_F IS NULL),0),2) AS "PendingAmt",
 (SELECT MAX(BILL_DATE)     FROM TLORDER WHERE BILL_TO_CODE = CLIENT AND INTERFACE_STATUS_F > 0 AND TOTAL_CHARGES <> 0) AS "LastBillDate",

 Owed as "ARTotal",
 coalesce(round((select sum(bal_amt) from ar_sum where client_id = client and AGING_date > current timestamp - 29 days),2),0) as "Current",
 coalesce(round((select sum(bal_amt) from ar_sum where client_id = client and AGING_date between current timestamp - 59 days and current timestamp - 30 days),2),0) as "Over30",
 coalesce(round((select sum(bal_amt) from ar_sum where client_id = client and AGING_date between current timestamp - 89 days and current timestamp - 60 days),2),0) as "Over60",
 coalesce(round((select sum(bal_amt) from ar_sum where client_id = client and AGING_date between current timestamp - 119 days and current timestamp - 90 days),2),0) as "Over90",
 coalesce(round((select sum(bal_amt) from ar_sum where client_id = client and AGING_date       < current timestamp - 120 days),2),0) as "Over120",
 CASE WHEN  owed = 0 THEN 0 ELSE ROUND(
 coalesce(round((select sum(bal_amt) from ar_sum where client_id = client and AGING_date < current timestamp - 60 days),2),0) * 100 / OWED,0)  end AS "PercOver60Days",
 CASE WHEN limit = 0 THEN 0 ELSE ROUND(coalesce(round(Owed,2),0) * 100 / limit,0) END AS "PercCreditUsed",
 case when limit = 0 then 0 else ROUND(
  ((coalesce((SELECT SUM(TOTAL_CHARGES) FROM TLORDER WHERE BILL_TO_CODE = CLIENT AND INTERFACE_STATUS_F IS NULL),0) + Owed)  * 100) / limit,0) end AS "PercCreditUsedWithPending",

state "State", phone "Phone", ext "Ext", contact "Contact", email "Email", call_back "CallBack", inactive "Active",  collector "Collector", fax "Fax", since "Since", currency "Currency",
terms "PayTerms", pay_days "PmtDays", sales_rep "SalesRep", solvability "Solvability"
from collections
with ur';
					
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

			case 'BILLING':	// !BILLING - Get average monthly billing 
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT RTRIM(RTRIM(CHAR(YEAR(T.BILL_DATE))) || '-' || CHAR(MONTH(T.BILL_DATE))) MNTH, ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING
					FROM TLORDER T
					WHERE (T.INTERFACE_STATUS_F IS NOT NULL)
					AND T.BILL_DATE BETWEEN current timestamp - 1 year AND CURRENT TIMESTAMP
					AND T.BILL_TO_CODE = '".$cid."'
					GROUP BY YEAR(BILL_DATE), MONTH(BILL_DATE)
					ORDER BY YEAR(BILL_DATE), MONTH(BILL_DATE)
					WITH UR"; // OR T.CUSTOMER = '".$cid."'
					
					//AND (T.ORIGIN = '".$cid."' OR T.DESTINATION = '".$cid."' OR T.BILL_TO_CODE = '".$cid."' OR T.CUSTOMER = '".$cid."')
					
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string);
						echo "</pre>";
					}
			
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

			case 'GETOPTS':	// !GETOPTS - Get certain client options from app config
				// Prepare Select
				$query_string = "SELECT THE_OPTION, THE_VALUE, OPTION_HINT, DEFAULT_VALUE FROM CONFIG
					WHERE PROG_NAME = 'PROFILE.EXE'
					AND COMPANY_ID = 1
					AND THE_OPTION IN ('Auto Gen Client key',
					'Bill-To by Default',
					'Caller by Default',
					'Consignee by Default',
					'CreditStatus',
					'Shipper by Default')
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

			case 'GETID':	// !GETID - Auto generate a CLIENT_ID
				// Validate fields
				if( $NAME == "NONE" || $PROVINCE == "NONE" || $CITY == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					$response = get_client_id( $NAME, $PROVINCE, $CITY, $debug);
										
					if( $response ) {
						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - get_client_id failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: get_client_id failed: " . $last_odbc_error);
					}
				}

				break;

			case 'YEARS':	// !YEARS - Get annual billing for a company
				// Prepare Select
				$query_string = "SELECT ROUND(COALESCE(SUM(TOTAL_CHARGES),0),0) BILLING
					FROM TLORDER T
					WHERE (T.INTERFACE_STATUS_F >= 0 OR T.INTERFACE_STATUS_F IS NULL)
					AND T.CURRENT_STATUS NOT IN ('CANCL', 'QUOTE')
					AND DOCUMENT_TYPE = 'INVOICE'
					AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
					AND EXTRA_STOPS <> 'Child'
					AND BILL_NUMBER NOT LIKE 'Q%'
					". ($COMPANY <> "NONE" ? "AND T.COMPANY_ID = ".$COMPANY : "")."
					AND T.BILL_DATE BETWEEN current timestamp - 1 year AND CURRENT TIMESTAMP
					ORDER BY BILLING DESC
					
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

			case 'TOP20':	// !TOP20 - Get list of top 20 clients
				if( $debug ) echo "<p>TOP20, M1=$M1 Y1=$Y1 M2=$M2 Y2=$Y2
					CCAT=$CCAT CTYPE=$CTYPE HREP=$HREP GROUP=$GROUP
					COMPANY=$COMPANY TOP=$TOP NOGROUP=".($NOGROUP ? "True" : "False")."</p>";
								
				$has_groups = false;		
				// Prepare Select
				$query_string = "SELECT DISTINCT COALESCE(C.CUSTOMER_GROUP, '') CUSTOMER_GROUP
					FROM CLIENT C
					WITH UR";
				
				if( $debug ) echo "<p>using query_string = $query_string</p>";
		
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response) && count($response) > 1 ) {
					if( $debug ) echo "<p>CUSTOMER_GROUP is used</p>";
					$has_groups = true;
				} else {
					if( $debug ) echo "<p>CUSTOMER_GROUP not used</p>";
				}
				
				if( $stc_override_groups ) $has_groups = false;
				
				if( $has_groups && ! $NOGROUP )
					$group_code = "COALESCE((SELECT MAIN_ACCOUNT
						FROM CLIENT_SUBACCT
						WHERE C.CLIENT_ID = CLIENT_SUBACCT.CLIENT_ID), C.CLIENT_ID)";
				else
					$group_code = "C.CLIENT_ID";
					
				if( $stc_custom_client_categories && $CCAT <> "NONE" )
					$cat_match = "AND (SELECT DATA
						FROM CUSTOM_DATA
						WHERE C.CLIENT_ID = SRC_TABLE_KEY
						AND CUSTDEF_ID = ".$stc_custom_client_categories_id.") = '".$CCAT."'";
				else
					$cat_match = "";

				if( $stc_custom_client_types && $CTYPE <> "NONE" )
					$type_match = "AND (SELECT DATA
						FROM CUSTOM_DATA
						WHERE C.CLIENT_ID = SRC_TABLE_KEY
						AND CUSTDEF_ID = ".$stc_custom_client_type_id.") = '".$CTYPE."'";
				else
					$type_match = "";

				if( $GROUP <> "NONE" )
					$group_match = "AND C.CUSTOMER_GROUP = '".$GROUP."'";
				else
					$group_match = "";
					
				if( $COMPANY <> "NONE" )
					$company_match = "AND T.COMPANY_ID = ".$COMPANY;
				else
					$company_match = "";
					
				if( $HREP <> "NONE" )
					$hrep_match = "AND C.SALES_REP = '".$HREP."'";
				else
					$hrep_match = "";
					
			//	$date_match = 'COALESCE(T.BILL_DATE, T.ACTUAL_DELIVERY, T.COMPLETED)';
				$date_match = 'T.BILL_DATE';
					
				if( $SITE <> "NONE" ) {
				//	$site_match = "AND T.SITE_ID = '".$SITE."'";
					
					$site_match = "AND T.SITE_ID IN ('".implode('\', \'', explode('|',$SITE))."')";
					
					switch( $SITE ) {
						case 'SITE1':	// JVL Transport
							$site_match2 = "AND QB_COMPANY = 'QT' AND SUBSTR(QB_INV_NUMBER,1,1) IN ('5', '6')";
							break;
						case 'SITE4':	// ORG Transport
							$site_match2 = "AND QB_COMPANY = 'QT' AND SUBSTR(QB_INV_NUMBER,1,1) IN ('7', '8')";
							break;
						case 'SITE2':	// JVL Logistics
							$site_match2 = "AND QB_COMPANY = 'QL' AND SUBSTR(QB_INV_NUMBER,1,1) = '2'";
							break;
						case 'SITE5':	// ORG Logistics
							$site_match2 = "AND QB_COMPANY = 'QL' AND SUBSTR(QB_INV_NUMBER,1,1) = '5'";
							break;
						default:
							$site_match2 = "";
					}
				} else {
					$site_match = "";
					$site_match2 = "";
				}
				
				// OR T.INTERFACE_STATUS_F IS NULL

				// Prepare Select
				$query_string = "WITH MY_TLORDER AS
					(SELECT ".$group_code." AS CUSTOMER, BILL_DATE, ACTUAL_DELIVERY,
						(SELECT MAX(O.CHANGED)
						FROM ODRSTAT O
						WHERE O.ORDER_ID = T.DETAIL_LINE_ID
						AND O.STATUS_CODE = 'COMPLETE') COMPLETED,
					T.INTERFACE_STATUS_F, TOTAL_CHARGES, T.BILL_TO_CODE, 
					C.NAME, C.SALES_REP
					FROM TLORDER T, CLIENT C
					WHERE COALESCE(T.BILL_TO_CODE,T.CUSTOMER) = C.CLIENT_ID
					AND T.CURRENT_STATUS NOT IN ('CANCL', 'QUOTE')
					AND T.DOCUMENT_TYPE IN ('INVOICE','REBILL')
					AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
					AND T.BILL_DATE > CURRENT_DATE - 5 YEARS
					
					-- Omit consolidated bills
					AND BILLCONS_ID IS NULL
					
					AND T.EXTRA_STOPS <> 'Child'
					AND T.BILL_NUMBER NOT LIKE 'Q%'
					AND T.CREATED_TIME = (
						SELECT MAX(J.CREATED_TIME) FROM
						TLORDER J
						WHERE T.BILL_NUMBER = J.BILL_NUMBER
						AND J.DOCUMENT_TYPE IN ('INVOICE','REBILL'))
					".($MANNING == "NONE" ? "AND COALESCE(T.BILL_TO_CODE,T.CUSTOMER) != '51684'" : "")."
					".$company_match."
					".$site_match." 
					".$hrep_match." 
					".$type_match." 
					".$cat_match."
					".$group_match." )				
				
					SELECT CUSTOMER, COALESCE( C.CUSTOMER_GROUP, '') AS CGROUP, 
					BILLING,
					".($stc_use_historical_data ? "(BILLING + COALESCE(BILLINGH,0))" : "BILLING")."
					AS BILLINGT, BILLING0, BILLINGP, BILLINGP2, BILLING1, BILLING2, BILLING3, 
					BILLING1P, BILLING2P,
					BILLING4, ".($stc_use_historical_data ? "BILLINGH,":"")." C.NAME, C.SALES_REP, 
					".($stc_custom_client_categories ? "CCAT" : "'NONE' AS CCAT")."
					FROM (
					
					SELECT T.CUSTOMER,
					ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING
					FROM MY_TLORDER T
					WHERE T.INTERFACE_STATUS_F >= 0
					AND ".$date_match." BETWEEN 
						DATE(YEAR(CURRENT DATE - 11 MONTHS) || '-' || MONTH(CURRENT DATE - 11 MONTHS) || '-01') 
						AND CURRENT TIMESTAMP
					GROUP BY T.CUSTOMER
					ORDER BY BILLING DESC
					fetch first ". ($TOP <> "NONE" ? $TOP : "20")." rows only)
					
					LEFT JOIN (	-- BILLING0 = this year
						SELECT T.CUSTOMER CUSTOMER0,
						ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING0
						FROM MY_TLORDER T
						WHERE T.INTERFACE_STATUS_F >= 0
						AND YEAR(".$date_match.") = YEAR(CURRENT DATE)
						GROUP BY T.CUSTOMER
						ORDER BY BILLING0 DESC
						fetch first ". ($TOP <> "NONE" ? ($TOP*3) : "60")." rows only)
					ON CUSTOMER = CUSTOMER0

					LEFT JOIN (	-- BILLINGP = previous year
						SELECT T.CUSTOMER CUSTOMERP,
						ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLINGP
						FROM MY_TLORDER T
						WHERE T.INTERFACE_STATUS_F >= 0
						AND YEAR(".$date_match.") = YEAR(CURRENT DATE) - 1
					--	AND ".$date_match." < CURRENT DATE - 1 YEAR
						
			
				--		AND ".$date_match." BETWEEN
				--		  DATE(YEAR(CURRENT DATE) - 1, 1, 1) AND
				--		  DATE(YEAR(CURRENT DATE) - 1, MONTH(CURRENT DATE), DAY(CURRENT DATE))
						
				--		AND YEAR(".$date_match.") = YEAR(CURRENT DATE) - 1
				--		AND (MONTH(".$date_match.") < MONTH(CURRENT DATE)
				--		OR (MONTH(".$date_match.") = MONTH(CURRENT DATE)
				--			AND DAYOFMONTH(".$date_match.") <= DAYOFMONTH(CURRENT DATE)
				--			)
						GROUP BY T.CUSTOMER
						ORDER BY BILLINGP DESC
						fetch first ". ($TOP <> "NONE" ? ($TOP*3) : "60")." rows only)
					ON CUSTOMER = CUSTOMERP

					LEFT JOIN (	-- BILLINGP2 = previous year
						SELECT T.CUSTOMER CUSTOMERP2,
						ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLINGP2
						FROM MY_TLORDER T
						WHERE T.INTERFACE_STATUS_F >= 0
						AND YEAR(".$date_match.") = YEAR(CURRENT DATE) - 1
						AND ".$date_match." < CURRENT DATE - 1 YEAR
						
			
						GROUP BY T.CUSTOMER
						ORDER BY BILLINGP2 DESC
						fetch first ". ($TOP <> "NONE" ? ($TOP*3) : "60")." rows only)
					ON CUSTOMER = CUSTOMERP2

					LEFT JOIN (	-- BILLING1 = 2 months back
						SELECT T.CUSTOMER CUSTOMER1,
						ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING1
						FROM MY_TLORDER T
						WHERE T.INTERFACE_STATUS_F >= 0
						AND MONTH(".$date_match.") = ".$M1."
						AND YEAR(".$date_match.") = ".$Y1."
						GROUP BY T.CUSTOMER
						ORDER BY BILLING1 DESC
						fetch first ". ($TOP <> "NONE" ? ($TOP*3) : "60")." rows only)
					ON CUSTOMER = CUSTOMER1
					
					LEFT JOIN (	-- BILLING1P = 2 months - 1 year back
						SELECT T.CUSTOMER CUSTOMER1P,
						ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING1P
						FROM MY_TLORDER T
						WHERE T.INTERFACE_STATUS_F >= 0
						AND MONTH(".$date_match.") = ".$M1."
						AND YEAR(".$date_match.") = ".($Y1 - 1)."
						GROUP BY T.CUSTOMER
						ORDER BY BILLING1P DESC
						fetch first ". ($TOP <> "NONE" ? ($TOP*3) : "60")." rows only)
					ON CUSTOMER = CUSTOMER1P
					
					LEFT JOIN (	-- BILLING2 previous month
						SELECT T.CUSTOMER CUSTOMER2,
						ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING2
						FROM MY_TLORDER T
						WHERE T.INTERFACE_STATUS_F >= 0
						AND MONTH(".$date_match.") = ".$M2."
						AND YEAR(".$date_match.") = ".$Y2."
						GROUP BY T.CUSTOMER
						ORDER BY BILLING2 DESC
						fetch first ". ($TOP <> "NONE" ? ($TOP*3) : "60")." rows only)
					ON CUSTOMER = CUSTOMER2
					
					LEFT JOIN (	-- BILLING2P previous month - 1 year back
						SELECT T.CUSTOMER CUSTOMER2P,
						ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING2P
						FROM MY_TLORDER T
						WHERE T.INTERFACE_STATUS_F >= 0
						AND MONTH(".$date_match.") = ".$M2."
						AND YEAR(".$date_match.") = ".($Y2 - 1)."
						GROUP BY T.CUSTOMER
						ORDER BY BILLING2P DESC
						fetch first ". ($TOP <> "NONE" ? ($TOP*3) : "60")." rows only)
					ON CUSTOMER = CUSTOMER2P
					
					LEFT JOIN (	-- BILLING3 = last 30 days
						SELECT T.CUSTOMER CUSTOMER3,
						ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING3
						FROM MY_TLORDER T
						WHERE T.INTERFACE_STATUS_F >= 0
						AND ".$date_match." BETWEEN current timestamp - 30 days AND CURRENT TIMESTAMP
						GROUP BY T.CUSTOMER
						ORDER BY BILLING3 DESC
						fetch first ". ($TOP <> "NONE" ? ($TOP*3) : "60")." rows only)
					ON CUSTOMER = CUSTOMER3
					
					LEFT JOIN (	-- BILLING4 = Current month
						SELECT T.CUSTOMER CUSTOMER4,
						ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING4
						FROM MY_TLORDER T
						WHERE T.INTERFACE_STATUS_F >= 0
						AND MONTH(".$date_match.") = MONTH(CURRENT TIMESTAMP)
						AND YEAR(".$date_match.") = YEAR(CURRENT TIMESTAMP)
						GROUP BY T.CUSTOMER
						ORDER BY BILLING4 DESC
						fetch first ". ($TOP <> "NONE" ? ($TOP*3) : "60")." rows only)
					ON CUSTOMER = CUSTOMER4
					
					".($stc_use_historical_data ? "
					LEFT JOIN (
					SELECT ".$group_code." CUSTOMER5
					FROM KAISER_QB_INVOICES, CLIENT C
					WHERE QB_TMCLIENT = C.CLIENT_ID
					". ($COMPANY == "1" ? "AND QB_COMPANY = 'QT'" :
					 	($COMPANY == "3" ? "AND QB_COMPANY = 'QL'" : ""))."
					".$site_match2."
					".$hrep_match." 
					".$type_match." 
					".$cat_match."
					AND QB_INV_DATE BETWEEN 
						DATE(YEAR(CURRENT DATE - 11 MONTHS) || '-' || MONTH(CURRENT DATE - 11 MONTHS) || '-01')
						AND CURRENT TIMESTAMP
					GROUP BY ".$group_code."
					ORDER BY BILLINGH DESC
					fetch first ". ($TOP <> "NONE" ? $TOP : "20")." rows only)
					ON CUSTOMER = CUSTOMER5" : "")."
					
					LEFT JOIN CLIENT C
					ON CUSTOMER = C.CLIENT_ID
					".($stc_custom_client_categories ? "LEFT JOIN (
						SELECT SRC_TABLE_KEY, DATA AS CCAT
						FROM CUSTOM_DATA
						WHERE CUSTDEF_ID = ".$stc_custom_client_categories_id.") 
					ON CUSTOMER = SRC_TABLE_KEY":"")."

					ORDER BY ".($stc_use_historical_data ? "BILLING+COALESCE(BILLINGH,0)" : "BILLING")." DESC
					WITH UR";
										
				
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string);
					echo "</pre>";
				}
			
		
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

			case 'TOP20DRILL':	// !TOP20DRILL - Drill down
				if( $debug ) echo "<p>TOP20, M1=$M1 Y1=$Y1 M2=$M2 Y2=$Y2
					CCAT=$CCAT CTYPE=$CTYPE HREP=$HREP GROUP=$GROUP
					COMPANY=$COMPANY TOP=$TOP NOGROUP=".($NOGROUP ? "True" : "False")."</p>";
								
				$has_groups = false;		
				// Prepare Select
				$query_string = "SELECT DISTINCT COALESCE(C.CUSTOMER_GROUP, '') CUSTOMER_GROUP
					FROM CLIENT C
					WITH UR";
				
				if( $debug ) echo "<p>using query_string = $query_string</p>";
		
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response) && count($response) > 1 ) {
					if( $debug ) echo "<p>CUSTOMER_GROUP is used</p>";
					$has_groups = true;
				} else {
					if( $debug ) echo "<p>CUSTOMER_GROUP not used</p>";
				}
				
				if( $stc_override_groups ) $has_groups = false;
				
				if( $has_groups && ! $NOGROUP )
					$group_code = "COALESCE((SELECT MAIN_ACCOUNT
						FROM CLIENT_SUBACCT
						WHERE C.CLIENT_ID = CLIENT_SUBACCT.CLIENT_ID), C.CLIENT_ID)";
				else
					$group_code = "C.CLIENT_ID";
					
				if( $stc_custom_client_categories && $CCAT <> "NONE" )
					$cat_match = "AND (SELECT DATA
						FROM CUSTOM_DATA
						WHERE C.CLIENT_ID = SRC_TABLE_KEY
						AND CUSTDEF_ID = ".$stc_custom_client_categories_id.") = '".$CCAT."'";
				else
					$cat_match = "";

				if( $stc_custom_client_types && $CTYPE <> "NONE" )
					$type_match = "AND (SELECT DATA
						FROM CUSTOM_DATA
						WHERE C.CLIENT_ID = SRC_TABLE_KEY
						AND CUSTDEF_ID = ".$stc_custom_client_type_id.") = '".$CTYPE."'";
				else
					$type_match = "";

				if( $GROUP <> "NONE" )
					$group_match = "AND C.CUSTOMER_GROUP = '".$GROUP."'";
				else
					$group_match = "";
					
				if( $COMPANY <> "NONE" )
					$company_match = "AND T.COMPANY_ID = ".$COMPANY;
				else
					$company_match = "";
					
				if( $HREP <> "NONE" )
					$hrep_match = "AND C.SALES_REP = '".$HREP."'";
				else
					$hrep_match = "";
					
			//	$date_match = 'COALESCE(T.BILL_DATE, T.ACTUAL_DELIVERY, T.COMPLETED)';
				$date_match = 'T.BILL_DATE';
					
				if( $SITE <> "NONE" ) {
				//	$site_match = "AND T.SITE_ID = '".$SITE."'";
					
					$site_match = "AND T.SITE_ID IN ('".implode('\', \'', explode('|',$SITE))."')";
					
					switch( $SITE ) {
						case 'SITE1':	// JVL Transport
							$site_match2 = "AND QB_COMPANY = 'QT' AND SUBSTR(QB_INV_NUMBER,1,1) IN ('5', '6')";
							break;
						case 'SITE4':	// ORG Transport
							$site_match2 = "AND QB_COMPANY = 'QT' AND SUBSTR(QB_INV_NUMBER,1,1) IN ('7', '8')";
							break;
						case 'SITE2':	// JVL Logistics
							$site_match2 = "AND QB_COMPANY = 'QL' AND SUBSTR(QB_INV_NUMBER,1,1) = '2'";
							break;
						case 'SITE5':	// ORG Logistics
							$site_match2 = "AND QB_COMPANY = 'QL' AND SUBSTR(QB_INV_NUMBER,1,1) = '5'";
							break;
						default:
							$site_match2 = "";
					}
				} else {
					$site_match = "";
					$site_match2 = "";
				}
				
				// OR T.INTERFACE_STATUS_F IS NULL

				// Prepare Select
				$query_string = "WITH MY_TLORDER AS
					(SELECT ".$group_code." AS CUSTOMER, BILL_NUMBER, BILL_DATE, ACTUAL_DELIVERY,
						(SELECT MAX(O.CHANGED)
						FROM ODRSTAT O
						WHERE O.ORDER_ID = T.DETAIL_LINE_ID
						AND O.STATUS_CODE = 'COMPLETE') COMPLETED,
					T.INTERFACE_STATUS_F, TOTAL_CHARGES, T.BILL_TO_CODE, 
					C.NAME, C.SALES_REP
					FROM TLORDER T, CLIENT C
					WHERE COALESCE(T.BILL_TO_CODE,T.CUSTOMER) = C.CLIENT_ID
					AND T.CURRENT_STATUS NOT IN ('CANCL', 'QUOTE')
					AND T.DOCUMENT_TYPE IN ('INVOICE','REBILL')
					AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
					AND T.BILL_DATE > CURRENT_DATE - 5 YEARS
					
					-- Omit consolidated bills
					AND BILLCONS_ID IS NULL
					
					AND T.EXTRA_STOPS <> 'Child'
					AND T.BILL_NUMBER NOT LIKE 'Q%'
					AND T.CREATED_TIME = (
						SELECT MAX(J.CREATED_TIME) FROM
						TLORDER J
						WHERE T.BILL_NUMBER = J.BILL_NUMBER
						AND J.DOCUMENT_TYPE IN ('INVOICE','REBILL'))
					".($MANNING == "NONE" ? "AND COALESCE(T.BILL_TO_CODE,T.CUSTOMER) != '51684'" : "")."
					".($CLIENT_ID == "NONE" ? "" :
						"AND ".$group_code." = '".$CLIENT_ID."'")."
					".$company_match."
					".$site_match." 
					".$hrep_match." 
					".$type_match." 
					".$cat_match."
					".$group_match." )				
				
				SELECT T.CUSTOMER, T.BILL_TO_CODE,
					(SELECT NAME FROM CLIENT C
						WHERE CUSTOMER = C.CLIENT_ID) AS NAME,
					T.BILL_DATE, TRIM(T.BILL_NUMBER) AS BILL_NUMBER,
					DECIMAL(TOTAL_CHARGES, 8, 2) AS TOTAL_CHARGES 
					FROM MY_TLORDER T
					WHERE T.INTERFACE_STATUS_F >= 0
					AND YEAR(".$date_match.") = YEAR(CURRENT DATE)
					ORDER BY T.BILL_NUMBER ASC
				WITH UR";
										
				
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string);
					echo "</pre>";
				}
			
		
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

			case 'GROUP':	// !GROUP - Get info on a group of clients
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					
					if( $stc_custom_client_categories && $CCAT <> "NONE" )
						$cat_match = "AND (SELECT DATA
							FROM CUSTOM_DATA
							WHERE C.CLIENT_ID = SRC_TABLE_KEY
							AND CUSTDEF_ID = 70) = '".$CCAT."'";
					else
						$cat_match = "";

					// Prepare Select
					$query_string = "SELECT C.CLIENT_ID, C.NAME, C.SALES_REP,
						(SELECT ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING
							FROM TLORDER T
							WHERE (T.INTERFACE_STATUS_F >= 0 OR T.INTERFACE_STATUS_F IS NULL)
							". ($COMPANY <> "NONE" ? "AND T.COMPANY_ID = ".$COMPANY : "")."
							AND T.BILL_DATE BETWEEN current timestamp - 1 year AND CURRENT TIMESTAMP
							AND COALESCE(T.BILL_TO_CODE,T.CUSTOMER) = C.CLIENT_ID) BILLING,
							".($stc_custom_client_categories ? "(SELECT DATA
						FROM CUSTOM_DATA
						WHERE C.CLIENT_ID = SRC_TABLE_KEY
						AND CUSTDEF_ID = ".$stc_custom_client_categories_id.")" : "'NONE'")." AS CCAT
						FROM CLIENT C
						WHERE C.CUSTOMER_GROUP = '".$cid."'
						".$cat_match."
						ORDER BY C.CLIENT_ID
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
				}

				break;

			case 'COMPANIES':	// !COMPANIES - Get a list of companies and their annual billing
				// Prepare Select
				$query_string = "SELECT C.COMPANY_INFO_ID, C.NAME,
				
					(SELECT ROUND(COALESCE(SUM(TOTAL_CHARGES),0),2) BILLING
					FROM TLORDER T
					WHERE (T.INTERFACE_STATUS_F >= 0 OR T.INTERFACE_STATUS_F IS NULL)
					AND T.COMPANY_ID = C.COMPANY_INFO_ID
					AND T.BILL_DATE BETWEEN current timestamp - 1 year AND CURRENT TIMESTAMP
					ORDER BY BILLING DESC) AS BILLING
					
					FROM COMPANY_INFO_SRC C
					WHERE C.STATUS = 'Active'
					ORDER BY C.COMPANY_INFO_ID
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

			case 'SITES':	// !SITES - List of sites
				// Prepare Select
				$query_string = "SELECT SITE_ID, SITE_NAME, COMPANY_ID, SERVICE_LEVEL
					FROM SITE
					".($cid <> "NONE" ? "WHERE COMPANY_ID = ".$cid : "")."
					ORDER BY SITE_ID
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

			case 'REPS':  // !REPS - Sales Reps
				$dbconn = new stc_db( $stc_database, $debug );
				
				if( $dbconn ) {
					$query_string = "select DISTINCT C.SALES_REP 
						from CLIENT C
						WHERE COALESCE(C.SALES_REP, '') <> ''
						for read only
						with ur";
																	
					$response = $dbconn->get_multiple_rows( $query_string );
					
					if( is_array($response) ) {
						echo ok_response( 'REPS', $response, $dbconn, $timer, $debug );
					} else {
						echo error_response( "NOT OK", 'get_multiple_rows failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'stc_db failed: ' . $last_odbc_error, $debug );
				}

				break;
				
			case 'AREPS':  // !AREPS - Account Reps
				$dbconn = new stc_db( $stc_database, $debug );
				
				if( $dbconn ) {
					$query_string = "SELECT CUST_VALUE
						FROM CUSTOM_LIST_VALUES
						WHERE CUSTDEF_ID =  85
						for read only
						with ur";
																	
					$response = $dbconn->get_multiple_rows( $query_string );
					
					if( is_array($response) ) {
						echo ok_response( 'AREPS', $response, $dbconn, $timer, $debug );
					} else {
						echo error_response( "NOT OK", 'get_multiple_rows failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'stc_db failed: ' . $last_odbc_error, $debug );
				}

				break;
				
			case 'SETAREP':	// !SETAREP - set Account Rep
				// Validate fields
				if( $cid == "NONE" || $own == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {

					$response = set_ccat( $own, $cid, $stc_custom_client_account_rep_id, $debug );
					
					if( $debug ) echo "<p>".$response."</p>";
					else echo encryptData($response);
				}

				break;

			case 'SETCCAT':	// !SETCCAT - Set a client category
				// Validate fields
				if( $cid == "NONE" || $CCAT == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} elseif( ! $stc_custom_client_categories ) {
					if( $debug ) echo "<p>Error - Client categories not enabled.</p>";
					else echo encryptData("NOT OK: Client categories not enabled.");
				} else {
					$response = set_ccat( $cid, $stc_custom_client_categories_id, $CCAT, $debug );
					
					if( $debug ) echo "<p>".$response."</p>";
					else echo encryptData($response);
				}

				break;

			case 'SETCTYPE':	// !SETCTYPE - Set a client type
				// Validate fields
				if( $cid == "NONE" || $CTYPE == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} elseif( ! $stc_custom_client_types ) {
					if( $debug ) echo "<p>Error - Client types not enabled.</p>";
					else echo encryptData("NOT OK: Client types not enabled.");
				} else {
					$response = set_ctype( $cid, $CTYPE, $debug );
					
					if( $debug ) echo "<p>".$response."</p>";
					else echo encryptData($response);
				}

				break;

			case 'CCAT':  // !CCAT - Client categories
				if( ! $stc_custom_client_categories ) {
					if( $debug ) echo "<p>Error - Client categories not enabled.</p>";
					else echo encryptData("NOT OK: Client categories not enabled.");
				} else {
				
					$query_string1 = "select CUST_VALUE 
						from CUSTOM_LIST_VALUES
						where custdef_id = 70
						for read only
						with ur";
											
					if( $debug ) echo "<p>using query_string = $query_string1</p>";
			
					$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
					
					if( is_array($response1) ) {
						
						if( $debug ) {
							echo "<pre>";
							var_dump($response1);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response1 ));
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}

				break;
				
			case 'DUPS':	// !DUPS - Find duplicates
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					// Prepare Select
					$query_string = "SELECT C.CLIENT_ID, C.NAME, C.ADDRESS_1, C.CITY, C.PROVINCE,
						C.POSTAL_CODE, C.BUSINESS_PHONE, 
						(SELECT COUNT(*) FROM TLORDER
							WHERE CUSTOMER = C.CLIENT_ID
							OR BILL_TO_CODE = C.CLIENT_ID) AS BILLS,
						(SELECT COUNT(*) FROM AR_SUM A
							WHERE A.CLIENT_ID = C.CLIENT_ID) AS AR,
						C.IS_INACTIVE, C.USER2
						FROM CLIENT C, CLIENT D
						WHERE (SUBSTR(C.NAME, 1, MIN(10, CHARACTER_LENGTH(C.NAME, OCTETS)) ) =
							SUBSTR(D.NAME, 1, MIN(10, CHARACTER_LENGTH(D.NAME, OCTETS)) )
						OR SUBSTR(C.ADDRESS_1, 1, MIN(10, CHARACTER_LENGTH(C.ADDRESS_1, OCTETS)) ) =
							SUBSTR(D.ADDRESS_1, 1, MIN(10, CHARACTER_LENGTH(D.ADDRESS_1, OCTETS)) )
						OR C.POSTAL_CODE = D.POSTAL_CODE
						OR C.BUSINESS_PHONE = D.BUSINESS_PHONE)
						
						AND D.CLIENT_ID = '".$cid."'
						ORDER BY C.NAME, C.ADDRESS_1, C.CITY, C.PROVINCE,
						C.POSTAL_CODE, C.BUSINESS_PHONE

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
				}

				break;

			case 'ALLDUPS':	// !ALLDUPS - Find duplicates
				// Prepare Select
				$query_string = "SELECT NAME, CLIENT_ID, ADDRESS_1, CITY, PROVINCE,
					POSTAL_CODE, BUSINESS_PHONE, 
					(SELECT COUNT(*) FROM TLORDER
						WHERE CUSTOMER = C.CLIENT_ID
						OR BILL_TO_CODE = C.CLIENT_ID) AS BILLS,
					(SELECT COUNT(*) FROM AR_SUM A
						WHERE A.CLIENT_ID = C.CLIENT_ID) AS AR,
					IS_INACTIVE, USER2
					FROM CLIENT C
					where exists (
					SELECT D.CLIENT_ID
					FROM CLIENT D
					WHERE C.CLIENT_ID <> D.CLIENT_ID
					AND C.POSTAL_CODE = D.POSTAL_CODE
					AND C.BUSINESS_PHONE = D.BUSINESS_PHONE
					AND D.POSTAL_CODE <> ''
					AND D.BUSINESS_PHONE <> ''
					AND D.IS_INACTIVE = 'False')
					AND C.IS_INACTIVE = 'False'
					ORDER BY POSTAL_CODE, BUSINESS_PHONE
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

			case 'DUE':	// !DUE - How many due/overdue
				// Validate fields
				if( $uid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					// Prepare Select
					$query_string = "SELECT OVERDUE, TODAY, TOMORROW
						FROM
						(SELECT COUNT(*) OVERDUE
						FROM CLIENT
						WHERE SALES_REP = '".$uid."'
						AND (SELECT DATE(USERDATE1) FROM PHONE
							WHERE PHONE.CLIENTID = CLIENT_ID
							FETCH FIRST ROW ONLY) < CURRENT DATE)
						,
						(SELECT COUNT(*) TODAY
						FROM CLIENT
						WHERE SALES_REP = '".$uid."'
						AND (SELECT DATE(USERDATE1) FROM PHONE
							WHERE PHONE.CLIENTID = CLIENT_ID
							FETCH FIRST ROW ONLY) = CURRENT DATE)
						,
						(SELECT COUNT(*) TOMORROW
						FROM CLIENT
						WHERE SALES_REP = '".$uid."'
						AND (SELECT DATE(USERDATE1) FROM PHONE
							WHERE PHONE.CLIENTID = CLIENT_ID
							FETCH FIRST ROW ONLY) = CURRENT DATE + 1 DAYS)

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
				}

				break;

			case 'ALLDUE':	// !ALLDUE - Find all due/overdue client calls
				// Prepare Select
				$query_string = "SELECT SALES_REP, 
					(SELECT S.EMAIL FROM ST_CMS_USERS S
						WHERE S.USERID = SALES_REP) AS REP_EMAIL,	
					CLIENT_ID, NAME,
					(SELECT date(MAX(PHONEDATE))
						FROM PHONE p, PHONENOTES n
						WHERE p.PHONEID = n.PHONEID
						and p.CLIENTID = CLIENT_ID) AS LAST_CALL,
					(SELECT COUNT(*)
						FROM QUOTE Q, TLORDER T
						WHERE Q.CLIENT_NUMBER = CLIENT_ID
						AND 'Q' || Q.QUOTE_NUMBER = T.BILL_NUMBER
						AND T.CURRENT_STATUS <> 'CANCL'
						AND Q.TIMES_USED = 0
						AND Q.EXPIRY_DATE > CURRENT DATE) AS OPEN_QUOTES,
					(SELECT DATE(USERDATE1) FROM PHONE
						WHERE PHONE.CLIENTID = CLIENT_ID) AS DUE_DATE
					FROM CLIENT
					WHERE (SELECT DATE(USERDATE1) FROM PHONE
						WHERE PHONE.CLIENTID = CLIENT_ID
						FETCH FIRST ROW ONLY) < CURRENT DATE + 1 day
					ORDER BY 7 ASC
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

