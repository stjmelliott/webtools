<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );
require_once( "./stc_config.php" );

	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman75";
	$option		= "NONE";
	$uid		= "NONE";
	$cid		= "NONE";
	$cnm		= "NONE";
	$own		= "NONE";
	$days		= "90";

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
		}
	}
	
	if( $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>STC CRM Backend - HR Functions</title>
</head>

<body>
<?
	}
	
	if( $password == $valid_pw ) {
		
		switch (strtoupper($option)) {

			case 'LIST':  // Show customers with lanes
				
				// Prepare Select
				$query_string = "SELECT APPLICATION_ID, APPLICATION_NUMBER, LAST_NAME, FIRST_NAME, MIDDLE_INITIAL, 
					AKA, ADDRESS_1, ADDRESS_2, CITY, PROVINCE, COUNTRY, POSTAL_CODE, PHONE, FAX, EMAIL, WEB_SUBMITED, 
					CALLED_IN, CUR_DLICENSE_NUMBER, CUR_DLICENSE_JURIS, CUR_DLICENSE_COUNTRY, SIN_NUMBER, BIRTH_DATE, 
					STATUS, CLOSEST_LOCATION, REFERED_BY, JOB_TITLE, APPLICANT_BITMAP
					FROM HR_APPLICATIONS
					
					ORDER BY APPLICATION_ID
					for read only
					with ur";
					// WHERE APPLICATION_ID=2
										
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

