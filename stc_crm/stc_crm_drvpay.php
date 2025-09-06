<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );
require_once( "./stc_config.php" );

	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman79";
	$option		= "NONE";
	$uid		= "NONE";
	$cid		= "NONE";
	$cnm		= "NONE";
	$own		= "NONE";
	$DAYS		= "1";

	$PUP					= "NONE";
	$TARP					= "NONE";
	$THE_NOTE				= "NONE";
	$EXT_NOTE				= "NONE";
	$DEL					= "NONE";
	$ZONE					= "NONE";
	$ZONE1					= "NONE";
	$ZONE2					= "NONE";
	$DESTADDR1				= "NONE";
	$DESTCITY				= "NONE";
	$CALLER					= "NONE";
	$PIECES					= "NONE";
	$WEIGHT					= "NONE";
	$LENGTH					= "NONE";
	$WIDTH					= "NONE";
	$HEIGHT					= "NONE";

	$ITU					= "NONE";
	$WTU					= "NONE";
	$LNU					= "NONE";
	$WIU					= "NONE";
	$HTU					= "NONE";
	$SVC					= "NONE";
	$REQ					= "NONE";
	$SEQ					= "NONE";
	$USES					= "1";

	$DECLARED				= "NONE";
	$RATE					= "NONE";
	$GOODFOR				= "NONE";
	$CONTACT				= "NONE";
	$EMAIL					= "NONE";
	$FB						= "NONE";

	$RANGE1					= "50";
	$RANGE2					= "50";
	$COMMODITY				= "NONE";
	$BUSINESS_PHONE			= "NONE";
	$BUSINESS_PHONE_EXT		= "NONE";
	$FAX_PHONE				= "NONE";
	$SITEID					= "NONE";
	$COMPANYID				= "NONE";
	
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
		} else if( $key == "PUP" ) {
			$PUP = $value;
		} else if( $key == "THE_NOTE" ) {
			$THE_NOTE = $value;
		} else if( $key == "EXT_NOTE" ) {
			$EXT_NOTE = $value;
		} else if( $key == "TARP" ) {
			$TARP = $value;
		} else if( $key == "DEL" ) {
			$DEL = $value;
		} else if( $key == "CID" ) {
			$cid = $value;
		} else if( $key == "OWN" ) {
			$own = $value;
		} else if( $key == "CLIENT_ID" ) {
			$CLIENT_ID = $value;
		} else if( $key == "NAME" ) {
			$NAME = $value;
		} else if( $key == "ZONE" ) {
			$ZONE = $value;
		} else if( $key == "ZONE1" ) {
			$ZONE1 = $value;
		} else if( $key == "ZONE2" ) {
			$ZONE2 = $value;
		} else if( $key == "DESTADDR1" ) {
			$DESTADDR1 = $value;
		} else if( $key == "DESTCITY" ) {
			$DESTCITY = $value;
		} else if( $key == "CALLER" ) {
			$CALLER = $value;
		} else if( $key == "PIECES" ) {
			$PIECES = $value;
		} else if( $key == "WEIGHT" ) {
			$WEIGHT = $value;
		} else if( $key == "LENGTH" ) {
			$LENGTH = $value;
		} else if( $key == "WIDTH" ) {
			$WIDTH = $value;
		} else if( $key == "HEIGHT" ) {
			$HEIGHT = $value;
		} else if( $key == "ITU" ) {
			$ITU = $value;
		} else if( $key == "WTU" ) {
			$WTU = $value;
		} else if( $key == "LNU" ) {
			$LNU = $value;
		} else if( $key == "WIU" ) {
			$WIU = $value;
		} else if( $key == "HTU" ) {
			$HTU = $value;
		} else if( $key == "SVC" ) {
			$SVC = $value;
		} else if( $key == "REQ" ) {
			$REQ = $value;
		} else if( $key == "SEQ" ) {
			$SEQ = $value;
		} else if( $key == "USES" ) {
			$USES = $value;
		} else if( $key == "DECLARED" ) {
			$DECLARED = $value;
		} else if( $key == "RATE" ) {
			$RATE = $value;
		} else if( $key == "GOODFOR" ) {
			$GOODFOR = $value;
		} else if( $key == "CONTACT" ) {
			$CONTACT = $value;
		} else if( $key == "EMAIL" ) {
			$EMAIL = $value;
		} else if( $key == "FB" ) {
			$FB = $value;
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
		} else if( $key == "SITEID" ) {
			$SITEID = $value;
		} else if( $key == "COMPANYID" ) {
			$COMPANYID = $value;
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

			case 'LIST':	// !LIST - List of driver pay
				// Prepare Select
				$query_string = "SELECT *
					FROM DRIVERPAY_AUTH
					WHERE TRIP_NUMBER = 17874
					
					FOR READ ONLY
					WITH UR";
					//WHERE PMT_STATE IN ('U', 'H')
										
				
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

