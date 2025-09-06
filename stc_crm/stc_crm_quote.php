<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );
require_once( "./stc_config.php" );

	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman78";
	$option		= "NONE";
	$uid		= "NONE";
	$cid		= "NONE";
	$cnm		= "NONE";
	$own		= "NONE";
	$DAYS		= "1";

	$PUP					= "NONE";
	$DEL					= "NONE";
	$PUPR					= "NONE";
	$DELR					= "NONE";
	$COST					= "NONE";
	$QTY					= "NONE";

	$TARP					= "NONE";
	$THE_NOTE				= "NONE";
	$NOTETYPE				= "NONE";
	$EXT_NOTE				= "NONE";
	$ZONE					= "NONE";
	$ZONE1					= "NONE";
	$ZONE2					= "NONE";
	
	$ORIGIN					= "NONE";
	$ORIGNAME				= "NONE";
	$ORIGADDR1				= "NONE";
	$ORIGCITY				= "NONE";
	$ORIGPROV				= "NONE";
	
	$DESTINATION			= "NONE";
	$DESTNAME				= "NONE";
	$DESTADDR1				= "NONE";
	$DESTCITY				= "NONE";
	$DESTPROV				= "NONE";
	$FSC					= "NONE";
	
	$CALLER					= "NONE";
	$MANUAL					= "NONE";
	$PALLETS				= "NONE";
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
	$RATEP					= "NONE";
	$RATEU					= "NONE";
	$GOODFOR				= "NONE";
	$CONTACT				= "NONE";
	$EMAIL					= "NONE";
	$FB						= "NONE";

	$RANGE					= "NONE";
	$RANGE1					= "50";
	$RANGE2					= "50";
	$COMMODITY				= "NONE";
	$BUSINESS_PHONE			= "NONE";
	$BUSINESS_PHONE_EXT		= "NONE";
	$FAX_PHONE				= "NONE";
	$SITEID					= "NONE";
	$COMPANYID				= "NONE";
	$OP_CODE				= "NONE";
	$WH						= "NONE";
	$DIST					= "NONE";
	$MM						= "NONE";
	
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
		} else if( $key == "DEL" ) {
			$DEL = $value;

		} else if( $key == "PUPR" ) {
			$PUPR = $value;
		} else if( $key == "DELR" ) {
			$DELR = $value;

		} else if( $key == "THE_NOTE" ) {
			$THE_NOTE = $value;
		} else if( $key == "NOTETYPE" ) {
			$NOTETYPE = $value;
		} else if( $key == "EXT_NOTE" ) {
			$EXT_NOTE = $value;
		} else if( $key == "TARP" ) {
			$TARP = $value;
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

		} else if( $key == "ORIGIN" ) {
			$ORIGIN = $value;
		} else if( $key == "ORIGNAME" ) {
			$ORIGNAME = $value;
		} else if( $key == "ORIGADDR1" ) {
			$ORIGADDR1 = $value;
		} else if( $key == "ORIGCITY" ) {
			$ORIGCITY = $value;
		} else if( $key == "ORIGPROV" ) {
			$ORIGPROV = $value;

		} else if( $key == "DESTINATION" ) {
			$DESTINATION = $value;
		} else if( $key == "DESTNAME" ) {
			$DESTNAME = $value;
		} else if( $key == "DESTADDR1" ) {
			$DESTADDR1 = $value;
		} else if( $key == "DESTCITY" ) {
			$DESTCITY = $value;
		} else if( $key == "DESTPROV" ) {
			$DESTPROV = $value;
			
		} else if( $key == "FSC" ) {
			$FSC = $value;
		} else if( $key == "CALLER" ) {
			$CALLER = $value;
		} else if( $key == "MANUAL" ) {
			$MANUAL = $value;
		} else if( $key == "PALLETS" ) {
			$PALLETS = $value;
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
		} else if( $key == "RATEP" ) {
			$RATEP = $value;
		} else if( $key == "RATEU" ) {
			$RATEU = $value;
		} else if( $key == "GOODFOR" ) {
			$GOODFOR = $value;
		} else if( $key == "CONTACT" ) {
			$CONTACT = $value;
		} else if( $key == "EMAIL" ) {
			$EMAIL = $value;
		} else if( $key == "FB" ) {
			$FB = $value;
		} else if( $key == "RANGE" ) {
			$RANGE = $value;
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
		} else if( $key == "OP_CODE" ) {
			$OP_CODE = $value;
		} else if( $key == "WH" ) {
			$WH = $value;
		} else if( $key == "DIST" ) {
			$DIST = $value;
		} else if( $key == "MM" ) {
			$MM = $value;
		} else if( $key == "COMPANYID" ) {
			$COMPANYID = $value;
		} else if( $key == "COST" ) {
			$COST = $value;
		} else if( $key == "QTY" ) {
			$QTY = $value;
		} else if( $key == "PROFILE" ) {
			$stc_profiling = true;
		}
	}

function insert_into_odrstat( $dlid, $ins_date, $updated_by, $start_zone, 
	$status, $stat_comment, $debug ) {
	global $stc_database,$stc_schema;

	// PREVENT KEY VIOL, AND ALLOW NEW RECORD'S FIELDS TO TAKE OVER
	$query_string = "DELETE FROM ODRSTAT
    	WHERE ORDER_ID = ".$dlid."
		AND CHANGED = '".$ins_date."'
		AND STATUS_CODE = '".$status."'";

	if( $debug ) echo "<p>using query_string = $query_string</p>";
	
	$response0 = send_odbc_query( $query_string, $stc_database, $debug );

	// Get a unique ID for ODRSTAT
	$query_string = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_ODRSTAT_ID')";

	if( $debug ) echo "<p>using query_string = $query_string</p>";
	
	$response1 = send_odbc_query( $query_string, $stc_database, $debug );

	if( $response1 ) {
		$nextid = $response1[0]["NEXTID"];
		if( $debug ) echo "<p>NEXTID is $nextid</p>";
		
		// Prepare Select
		$query_string = "INSERT INTO ODRSTAT ( 
			ORDER_ID, CHANGED, STATUS_CODE, STAT_COMMENT, UPDATED_BY,
			TRIP_NUMBER, LEG_ID, ZONE_ID, INS_DATE, ID )
		VALUES ( 
			".$dlid.", '".$ins_date."', '".$status."', '".$stat_comment."', '".$updated_by."',
			0, 0, '".$start_zone."', '".$ins_date."', ".$nextid." )";
	
		if( $debug ) echo "<p>using query_string = $query_string</p>";
	
		$response2 = send_odbc_query( $query_string, $stc_database, $debug );
					
		if( is_array($response2) ) {
			if( $debug ) {
				echo "<pre>";
				var_dump($response2);
				echo "</pre>";
			}
			return true;
		} else {
			if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
		}
	} else {
		if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
	}
	
	return false;
}

function fetch_client_info( $cid, $debug ) {
	global $stc_database;

	// Prepare Select
	$query_string = "SELECT CLIENT_ID, NAME, UNIT, ADDRESS_1, ADDRESS_2, CITY,  PROVINCE,
	POSTAL_CODE, COUNTRY, BUSINESS_PHONE, BUSINESS_PHONE_EXT, CONTACT, EMAIL,
	CLIENT_IS_CALLER,CLIENT_IS_SHIPPER, CLIENT_IS_CONSIGNEE,CLIENT_IS_BILL_TO,
	DEFAULT_DELIVERY_Z,ROUTING_CODE, CREDIT_HOLD, CURRENCY_CODE,  COMMENTS, FAX_PHONE,
	EMAIL_ADDRESS,OPEN_TIME, CLOSE_TIME, APPT_REQ, CUSTOMER_SINCE, REQUESTED_EQUIPMEN,
	POP_UP_NOTES, BILL_CUSTOMER, CSA_NUMBER, FAX_PHONE, BUSINESS_CELL,
	EMAIL_ADDRESS, GMT_OFFSET, DST_APPLIES, CREDIT_STATUS, PAYMENT_OPTIONS,
	CUSTOMS_BROKER_VENDOR, AGENT, SALES_REP, IS_INACTIVE,SPOT_TRAILER 
	FROM CLIENT WHERE CLIENT_ID = '".$cid."' AND IS_INACTIVE='False'";
	
	if( $debug ) echo "<p>using query_string = $query_string</p>";

	$response = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( is_array($response) ) {
		if( $debug ) echo "<p>Success</p>";
		return $response;
	} else {
		if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
		return false;
	}
}

function fetch_cdesc( $COMMODITY, $debug ) {
	global $stc_database;

	// Prepare Select
	$query_string = "SELECT COMMODITY_CODE, SHORT_DESCRIPTION
						FROM CMODTY
						WHERE COMMODITY_CODE = '".$COMMODITY."'";
	
	if( $debug ) echo "<p>using query_string = $query_string</p>";

	$response = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( is_array($response) && count($response) == 1 ) {
		if( $debug ) echo "<p>Success</p>";
		return $response[0]['SHORT_DESCRIPTION'];
	} else {
		if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
		return "";
	}
}

function fetch_entity_id( $company_id ) {
	global $stc_database;

	// Prepare Select
	$query_string = "SELECT ORG_ENTITY_ID
		FROM COMPANY_INFO_SRC
		WHERE COMPANY_INFO_ID = ".$company_id;
	
	if( $debug ) echo "<p>using query_string = $query_string</p>";

	$response = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( is_array($response) && count($response) == 1 ) {
		if( $debug ) echo "<p>Success</p>";
		return $response[0]['ORG_ENTITY_ID'];
	} else {
		if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
		return "";
	}
}

function insert_into_tlorder( $cid, $ins_date, $updated_by, $start_zone, $end_zone,
	$ORIGIN, $ORIGNAME, $ORIGADDR1, $ORIGCITY, $ORIGPROV, 
	$DESTINATION, $DESTNAME, $DESTADDR1, $DESTCITY, $DESTPROV,
	$pick_up_by, $deliver_by, $pick_up_by_req, $deliver_by_req,
	$site_id, $company_id, $tarp, 
	$svc_level, $req_eq, $declared, $contact, $email, $OP_CODE, $debug ) {
	global $stc_database, $stc_schema;
	
	// Get a unique DETAIL_LINE_ID
	$query_string = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_DETAIL_LINE_ID')";

	if( $debug ) echo "<p>using query_string = $query_string</p>";
	
	$response1 = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( is_array($cid) ) {
		$client = [[
			'NAME' => $cid['CALLNAME'],
			'ADDRESS_1' => $cid['CALLADDR1'],
			'ADDRESS_2' => '',
			'CITY' => $cid['CALLCITY'],
			'PROVINCE' => $cid['CALLPROV'],
			'POSTAL_CODE' => $cid['CALLPC'],
			'COUNTRY' => 'US',
			'BUSINESS_PHONE' => '',
			'BUSINESS_PHONE_EXT' => '',
		]];
	} else
		$client = fetch_client_info( $cid, $debug );
	
	if( ! empty($email) )
		$client[0]['EMAIL_ADDRESS'] = $email;
	if( ! empty($contact) )
		$client[0]['CONTACT'] = $contact;
	
	$org_entity_id = fetch_entity_id( $company_id );

	if( is_array($response1) && is_array($client) ) {
		$dlid = $response1[0]["NEXTID"];
		if( $debug ) echo "<p>DETAIL_LINE_ID is $dlid</p>";
	
		$pup = $pick_up_by == "NONE" ? "'".$ins_date."'" : "'".$pick_up_by." 08:00:00'";
		$pupe = $pick_up_by == "NONE" ? "'".$ins_date."'" : "'".$pick_up_by." 17:00:00'";
		$del = $deliver_by == "NONE" ? "'".date('Y-m-d H:i:s', strtotime( $ins_date." + 1 year"))."'" : "'".$deliver_by." 08:00:00'";
		$dele = $deliver_by == "NONE" ? "'".date('Y-m-d H:i:s', strtotime( $ins_date." + 1 year"))."'" : "'".$deliver_by." 17:00:00'";
		
		// Prepare Select
		$query_string = "INSERT INTO TLORDER (DETAIL_LINE_ID, PARENT_ORDER, BILL_NUMBER, 
			CUSTOMER, CALLNAME, CALLADDR1, CALLADDR2, CALLCITY, CALLPROV, CALLPC, CALLCOUNTRY, 
			CALLPHONE, CALLCONTACT, ORIGIN, DESTINATION,
			ORIGNAME, ORIGADDR1, ORIGCITY, ORIGPROV, ORIGPC,
			DESTNAME, DESTADDR1, DESTCITY, DESTPROV, DESTPC,
			
			CREATED_TIME, CREATED_BY, CURRENT_ZONE, CHARGES, XCHARGES, CURRENT_STATUS, PALLETS, 
			PIECES, LENGTH_1, CUBE, WEIGHT, DISTANCE, BILL_TO, PICK_UP_BY, PICK_UP_BY_END, 
			DELIVER_BY, DELIVER_BY_END, 
			DANGEROUS_GOODS, START_ZONE, END_ZONE, REQUESTED_EQUIPMEN, EXTRA_STOPS, NO_STOPS,
			
			BILL_TO_CODE, BILL_TO_NAME, SERVICE_LEVEL, AREA, VOLUME, TIME_1, TOTAL_CHARGES,
			TAX_1, TAX_2, TEMP_CONTROLLED, MASTER_ORDER, NEXT_TERMINAL_ZONE, ROUTE_DESIGNATION,
			SITE_ID, CURRENCY_CODE, MANUAL_GL, INT_PAYABLE_AMT, COD_AMOUNT, CASH_COLLECT, NO_CHARGE,
			
			DOCUMENT_TYPE, TX_TYPE, BILL_NUMBER_KEY, DISTANCE_UNITS, PICK_UP_APPT_REQ,
			PICK_UP_APPT_MADE, DELIVERY_APPT_REQ, DELIVERY_APPT_MADE, OR_APPLY_TO,
			OR_SPLIT, TARP, CALLPHONEEXT, SLM_OVERRIDE, AUTO_ASSIGN_STMTS,
			
			HIGH_VALUE, OP_CODE, ORIG_SPOT_TRAILER, PICKUP_AT_SPOT_TRAILER,
			DEST_SPOT_TRAILER, CARE_OF_SPOT_TRAILER, INVOICE_TYPE, PREPAID_COLLECT,
			MANUAL_MILEAGE, CALLEMAIL, COMPANY_ID, ORG_ENTITY_ID, MODIFIED_TIME, APPROVED, DECLARED_VALUE
			)
		
			VALUES ( ".$dlid.", 0, 'NA',
				".(is_array($cid) ? "'146935'" : "'".$cid."'").",
				'".str_replace("'", "''", $client[0]['NAME'])."', '".str_replace("'", "''", $client[0]['ADDRESS_1'])."', '".str_replace("'", "''", $client[0]['ADDRESS_2'])."', 
				'".str_replace("'", "''", $client[0]['CITY'])."', '".$client[0]['PROVINCE']."', '".$client[0]['POSTAL_CODE']."', 
				'".$client[0]['COUNTRY']."', '".$client[0]['BUSINESS_PHONE']."', '".str_replace("'", "''", (isset($contact) && $contact <> '' ? $contact : $client[0]['CONTACT']))."',
				".
				($ORIGIN <> 'NONE' ? "'".str_replace("'", "''", $ORIGIN)."'" : 'NULL').", ".
				($DESTINATION <> 'NONE' ? "'".str_replace("'", "''", $DESTINATION)."'" : 'NULL').", ".

				($ORIGNAME <> 'NONE' ? "'".str_replace("'", "''", $ORIGNAME)."'" : 'NULL').", ".
				($ORIGADDR1 <> 'NONE' ? "'".str_replace("'", "''", $ORIGADDR1)."'" : 'NULL').", ".
				($ORIGCITY <> 'NONE' ? "'".str_replace("'", "''", $ORIGCITY)."'" : 'NULL').", ".
				($ORIGPROV <> 'NONE' ? "'".str_replace("'", "''", $ORIGPROV)."'" : 'NULL').
				", '".$start_zone."',".
				
				($DESTNAME <> 'NONE' ? "'".str_replace("'", "''", $DESTNAME)."'" : 'NULL').", ".
				($DESTADDR1 <> 'NONE' ? "'".str_replace("'", "''", $DESTADDR1)."'" : 'NULL').", ".
				($DESTCITY <> 'NONE' ? "'".str_replace("'", "''", $DESTCITY)."'" : 'NULL').", ".
				($DESTPROV <> 'NONE' ? "'".str_replace("'", "''", $DESTPROV)."'" : 'NULL').",
				'".$end_zone."',
			
				'".$ins_date."', '".str_replace("'", "''", $updated_by)."', '".$start_zone."', 0, 0, 'ENTRY', 0,
				0, 0, 0, 0, 0, 'C', ".$pup.", ".$pupe.",
				 ".$del.", ".$dele.",
				'False', '".$start_zone."', '".$end_zone."', ".
				($req_eq <> 'NONE' ? "'".$req_eq."'" : 'NULL').",
				'False', 0,
				".(is_array($cid) ? "'146935'" : "'".$cid."'").", '".str_replace("'", "''", $client[0]['NAME'])."', ".
				($svc_level <> 'NONE' ? "'".$svc_level."'" : 'NULL').",
				0, 0, 0, 0,
				0, 0, 'False', 0, '', '',
				'".$site_id."', 'USD', 'False', 0, 0, 'False', 'False',
				
				'INVOICE', 'INVOICE', 'NA        ".$dlid."', 'MI', '".($pick_up_by_req <> 'NONE' ? 'True' : 'False')."',
				'False', '".($deliver_by_req <> 'NONE' ? 'True' : 'False')."', 'False', 'N',
				'False', '".($tarp=='on' ? 'True' : 'False')."', '".$client[0]['BUSINESS_PHONE_EXT']."', 'True', 'False',
				
				'False', '".$OP_CODE."', 'False', 'False',
				'False', 'False', 'FREIGHT', 'NA',
				'False', '".(isset($email) && $email <> '' ? $email : $client[0]['EMAIL_ADDRESS'])."', 
				".$company_id.", ".$org_entity_id.", '".$ins_date."',
				'True', ".($declared <> "NONE" ? $declared : 0 )."			
			 )";
	
		if( $debug ) echo "<p>using query_string = $query_string</p>";
	
		$response2 = send_odbc_query( $query_string, $stc_database, $debug );
					
		if( is_array($response2) ) {
			if( $debug ) {
				echo "<pre>";
				var_dump($response2);
				echo "</pre>";
				
				$query_string = "SELECT COMPANY_ID, SITE_ID FROM TLORDER WHERE DETAIL_LINE_ID=".$dlid;

				if( $debug ) echo "<p>using query_string = $query_string</p>";
			
				$response3 = send_odbc_query( $query_string, $stc_database, $debug );
				echo "<pre>";
				var_dump($company_id, $site_id, $response3);
				echo "</pre>";

			}
			return $dlid;
		} else {
			if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
		}
	} else {
		if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
	}
	
	return false;
}

function insert_into_tldtl( $dlid, $commodity, $PALLETS, $pieces, $length, $width, 
	$DIST, $height, $weight, $pieces_units, $length_units, $width_units, $height_units, $weight_units,
	$rate, $ratep, $rateu, $sub_cost, $debug ) {
	global $stc_database, $stc_schema;

	// Get a unique SEQUENCE
	$query_string = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_TLDTL_SEQ')";

	if( $debug ) echo "<p>using query_string = $query_string</p>";
	
	$response1 = send_odbc_query( $query_string, $stc_database, $debug );
	
	$cdesc = fetch_cdesc( $commodity, $debug );
	
	if( is_array($response1) ) {
		$seq = $response1[0]["NEXTID"];
		if( $debug ) echo "<p>SEQUENCE is $seq</p>";
		
		// Prepare Select
		$query_string = "INSERT INTO TLDTL(ORDER_ID, SEQUENCE, COMMODITY, 
			PALLETS, PIECES, PIECES_UNITS, WEIGHT, WEIGHT_UNITS, LENGTH_1, LENGTH_UNITS,
			DISTANCE, DISTANCE_UNITS,
			HEIGHT, HEIGHT_UNITS, WIDTH, WIDTH_UNITS, DESCRIPTION, RATE, RATE_PER, RATE_UNITS,
			SUB_COST, COST, MANUAL_RATE  )

			VALUES( ".$dlid.", ".$seq.",'".$commodity."',
			".$PALLETS.", ".$pieces.", '".$pieces_units."', ".$weight.", '".$weight_units."', ".$length.", '".$length_units."',
			".number_format($DIST, 0, ".", "") .", 'MI',
			".$height.", '".$height_units."', ".$width.", '".$width_units."',  '".str_replace("'", "''", $cdesc)."', ".$rate.", ".$ratep.",'".$rateu."',".$sub_cost.", ".$sub_cost.", 'True' )";
	
		if( $debug ) echo "<p>using query_string = $query_string</p>";
	
		$response2 = send_odbc_query( $query_string, $stc_database, $debug );
					
		if( is_array($response2) ) {
			if( $debug ) {
				echo "<pre>";
				var_dump($response2);
				echo "</pre>";
			}
			return $seq;
		} else {
			if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
		}
	} else {
		if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
	}
	
	return false;
}

function insert_into_tldtl2( $dlid, $pallets, $time, $duration, $rate, $cdesc, $debug ) {
	global $stc_database, $stc_schema;

	// Get a unique SEQUENCE
	$query_string = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_TLDTL_SEQ')";

	if( $debug ) echo "<p>".__FUNCTION__." using query_string = $query_string</p>";
	
	$response1 = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( is_array($response1) ) {
		$seq = $response1[0]["NEXTID"];
		if( $debug ) echo "<p>SEQUENCE is $seq</p>";
		// Prepare Select
		$query_string = "INSERT INTO TLDTL(ORDER_ID, SEQUENCE, TIME_UNITS, TIME_1, RATE, RATE_PER, RATE_UNITS,
			PALLETS, DESCRIPTION, SUB_COST, MANUAL_RATE  )

			VALUES( ".$dlid.", ".$seq.", '".$time."', $duration, ".$rate.", 1, 'PLT',
			".$pallets.", '".str_replace("'", "''", $cdesc)."', ".($pallets * $rate).", 'True' )";
	
		if( $debug ) echo "<p>".__FUNCTION__." using query_string = $query_string</p>";
	
		$response2 = send_odbc_query( $query_string, $stc_database, $debug );
					
		if( is_array($response2) ) {
			if( $debug ) {
				echo "<pre>";
				var_dump($response2);
				echo "</pre>";
			}
			return $seq;
		} else {
			if( $debug ) echo "<p>".__FUNCTION__." Error - send_odbc_query 2 failed. $last_odbc_error</p>";
		}
	} else {
		if( $debug ) echo "<p>".__FUNCTION__." Error - send_odbc_query 1 failed. $last_odbc_error</p>";
	}
	
	return false;
}

function insert_into_notes( $dlid, $note_type, $ins_date, $the_note, $debug ) {
	global $stc_database, $stc_schema;
	
	$the_note = str_replace("'", "''", $the_note);
		
	// Prepare Select
	$query_string = "DELETE
		FROM NOTES
		WHERE  LTRIM(RTRIM(CHAR(".$dlid."))) = ID_KEY
		AND PROG_TABLE = 'TLORDER'
		AND NOTE_TYPE = '".$note_type."'";

	if( $debug ) echo "<p>using query_string = $query_string</p>";

	$response = send_odbc_query( $query_string, $stc_database, $debug );
				
	// Prepare Select
	$query_string = "INSERT INTO NOTES( ID_KEY, MODIFIED_TIME, NOTE_TYPE, PROG_TABLE,
		THE_NOTE, THE_NOTE_RTF  )

		VALUES( LTRIM(RTRIM(CHAR(".$dlid."))), '".$ins_date."', '".$note_type."', 'TLORDER',
		'".$the_note."', BLOB('".$the_note."') )";

	if( $debug ) echo "<p>using query_string = $query_string</p>";

	$response = send_odbc_query( $query_string, $stc_database, $debug );
				
	if( is_array($response) ) {
		if( $debug ) {
			echo "<pre>";
			var_dump($response);
			echo "</pre>";
		}
		return true;
	} else {
		if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
	}
	
	return false;
}

function post_quote( $dlid, $caller, $commodity, $ins_date, $goodfor, $updated_by, $uses, $debug ) {
	global $stc_database, $stc_schema;

	// Get a unique SEQUENCE
	$query_string = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_QUOTE')";

	if( $debug ) echo "<p>using query_string = $query_string</p>";
	
	$response1 = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( is_array($response1) ) {
		$quote_number = $response1[0]["NEXTID"];
		$qbill = 'Q'.$quote_number;
		if( $debug ) echo "<p>QUOTE is $quote_number</p>";
		
		$exp_date = date('Y-m-d H:i:s', time() + ($goodfor * 24 * 60 * 60));
		
		// Prepare Select
		$query_string = "INSERT INTO QUOTE (QUOTE_ID, CLIENT_NUMBER, QUOTE_NUMBER, ORIGIN, DESTINATION,
		 COMMODITY, FLAT_RATE, TIMES_USED, NUMBER_OF_USES, EFFECTIVE_DATE, EXPIRY_DATE ) 
		 
		 VALUES( ".$dlid.", '".$caller."', ".$quote_number.", 'QUOTE', 'QUOTE',
		 '".$commodity."', 0, 0, ".$uses.", '".$ins_date."', '".$exp_date."' )";
	
		if( $debug ) echo "<p>using query_string = $query_string</p>";
	
		$response2 = send_odbc_query( $query_string, $stc_database, $debug );
					
		if( is_array($response2) ) {

			// Prepare Select
			$query_string = "UPDATE TLORDER 
			SET BILL_NUMBER = '".$qbill."', CURRENT_STATUS = 'QUOTE' 
			WHERE DETAIL_LINE_ID = ".$dlid;
		
			if( $debug ) echo "<p>using query_string = $query_string</p>";
		
			$response3 = send_odbc_query( $query_string, $stc_database, $debug );
						
			if( is_array($response3) ) {
			
				// Prepare Select
				$query_string = "INSERT INTO ODRSTAT 
					(ORDER_ID, STATUS_CODE, CHANGED, STAT_COMMENT, UPDATED_BY) 
					VALUES (".$dlid.", 'QUOTE', '".$ins_date."', 'Posted in Webtools', '".$updated_by."')";
			
				if( $debug ) echo "<p>using query_string = $query_string</p>";
			
				$response4 = send_odbc_query( $query_string, $stc_database, $debug );
							
				if( is_array($response4) ) {
					// Prepare Select
					$query_string = "CALL ".$stc_schema.".UPDATE_TLORDER_SUMMARY( ".$dlid." )";
				
					if( $debug ) echo "<p>using query_string = $query_string</p>";
				
					$response5 = send_odbc_query( $query_string, $stc_database, $debug );
								
					if( is_array($response4) ) {
						if( $debug ) echo "<p>Done posting $qbill</p>";
						return $qbill;
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 5 failed. $last_odbc_error</p>";
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query 4 failed. $last_odbc_error</p>";
				}
			} else {
				if( $debug ) echo "<p>Error - send_odbc_query 3 failed. $last_odbc_error</p>";
			}
		} else {
			if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
		}
	} else {
		if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
	}
	
	return false;
}

//! SCR# 824 - Insert a custom data field
function insert_custom_data( $id, $key, $value, $debug ) {
	global $stc_database, $stc_schema;
	$result = false;
	
	if( $debug ) {
		echo "<pre>".__FUNCTION__.": id, key, value\n";
		var_dump($id, $key, $value);
		echo "</pre>";
	}
	
	// First, get the control type, such as DATE-ONLY
	
	$query_string1 = "SELECT CONTROL_TYPE
		FROM CUSTOM_DEFS WHERE CUSTDEF_ID = $id
		FOR READ ONLY
		WITH UR";

	if( $debug ) {
		echo "<p>".__FUNCTION__.": using query_string1 = </p>
		<pre>";
		var_dump($query_string1);
		echo "</pre>";
	}
	
	$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
	
	if( $debug ) {
		echo "<p>".__FUNCTION__.": response1 = </p>
		<pre>";
		var_dump($response1);
		echo "</pre>";
	}
	
	$control_type = (is_array($response1) && count($response1) == 1 && 
		is_array($response1[0]) && ! empty($response1[0]['CONTROL_TYPE'])) ?
		$response1[0]['CONTROL_TYPE'] : 'TEXT';
	
	$field = $control_type == 'DATE-ONLY' ? 'DATE' : 'DATA';

	// CUSTOM_DATA.DATA field max length 255
	if( strlen($value) > 255 )
		$value = substr($value,0,255);
	
	// Check if it already exists
	$query_string2 = "SELECT SRC_TABLE_KEY_INT
		FROM CUSTOM_DATA
		WHERE SRC_TABLE_KEY_INT = $key
		AND CUSTDEF_ID = $id";
	
	if( $debug ) {
		echo "<p>".__FUNCTION__.": using query_string2 = </p>
		<pre>";
		var_dump($query_string2);
		echo "</pre>";
	}
	
	$response2 = send_odbc_query( $query_string2, $stc_database, $debug );
	
	if( $debug ) {
		echo "<p>".__FUNCTION__.": response1 = </p>
		<pre>";
		var_dump($response2);
		echo "</pre>";
	}
	
	if( is_array($response2) && count($response2) > 0 &&
		$response2[0]["SRC_TABLE_KEY_INT"] == $key ) {
		$query_string3 = "UPDATE CUSTOM_DATA
			SET $field = '".$value."'
			WHERE SRC_TABLE_KEY_INT = $key
			AND CUSTDEF_ID = $id";
	} else {
		$query_string3 = "INSERT INTO CUSTOM_DATA(SRC_TABLE_KEY, SRC_TABLE_KEY_INT, $field, CUSTDEF_ID)
			VALUES( '".$key."', ".$key.", '".$value."', $id )";
	}
	
	if( $debug ) {
		echo "<p>".__FUNCTION__.": using query_string3 = </p>
		<pre>";
		var_dump($query_string3);
		echo "</pre>";
	}
	
	$response3 = send_odbc_query( $query_string3, $stc_database, $debug );
	
	if( $debug ) {
		echo "<p>".__FUNCTION__.": response3 = </p>
		<pre>";
		var_dump($response3);
		echo "</pre>";
	}
	
	if( is_array($response3) ) {
		if( $debug ) echo "<p>CHANGED</p>";
		else $result = "CHANGED";
	} else {
		if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
		else $result = "NOT OK: send_odbc_query failed: " . $last_odbc_error;
	}
	
	return $result;
}

function days_diff( $d1, $d2 ) {
	$t1 = strtotime($d1);
	$t2 = strtotime($d2);
	$datediff = $t2 - $t1;
	
	return round($datediff / (60 * 60 * 24));
}

function insert_acc_charges( $dlid, $code, $qty, $rate, $cdesc, $debug ) {
	global $stc_database, $stc_schema;
	$result = false;
	
	if( $debug ) {
			echo "<p>".__FUNCTION__." entry, dlid=$dlid, code=$code, qty=$qty, rate=$rate,".
				"cdesc=$cdesc</p>";
	}
	
	// Get a unique ID for ACHARGE_TLORDER
	$query_string = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_ACT_ID')";

	if( $debug ) {
		echo "<p>".__FUNCTION__." using query_string = </p>
		<pre>";
		var_dump($query_string);
		echo "</pre>";
	}
	
	$response1 = send_odbc_query( $query_string, $stc_database, $debug );

	if( $response1 ) {
		$nextid = $response1[0]["NEXTID"];
		if( $debug ) echo "<p>NEXTID is $nextid</p>";
		// True -> False
		$query_string = "INSERT INTO ACHARGE_TLORDER(
			ACT_ID, DETAIL_LINE_ID, ACODE_ID, REQUESTED_CODE, IS_MANUAL,
			QUANTITY, ACTUAL_QUANTITY, RATE, CHARGE_AMOUNT, AUTO_ASSIGNED,
			NOTATION )
			VALUES(
			$nextid, $dlid, '".$code."', '".$code."', 'False',
			$qty, $qty, $rate, ".($qty * $rate).", 'False',
			'".$cdesc."')";
          
		if( $debug ) {
			echo "<p>".__FUNCTION__." using query_string = </p>
			<pre>";
			var_dump($query_string);
			echo "</pre>";
		}
	
		$response2 = send_odbc_query( $query_string, $stc_database, $debug );
		if( is_array($response2) ) {
			$result = $nextid;
		}
	}
	
	return $result;
}


	if( $stc_profiling || $debug ) {
		$timer = new stc_timer();
		$timer->start();
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

			case 'COMPANIES':	// !COMPANIES - List of companies, for multi-company
				// Prepare Select
				$query_string = "SELECT C.COMPANY_INFO_ID, C.NAME
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

			case 'OPCODES':	// !OPCODES - List of OP CODES
				// Prepare Select
				$query_string = "SELECT DISTINCT OP_CODE
					FROM OPERATION_CODES
					WHERE CUBE_TO_WEIGHT = '1'
					ORDER BY OP_CODE ASC
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

			case 'ZONES':	// !ZONES - List of zones that match first digits or first part of description
				// Validate fields
				if( $ZONE == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT DISTINCT ZONE_ID, SHORT_DESCRIPTION
					FROM (SELECT DISTINCT ZONE_ID, SHORT_DESCRIPTION
						FROM CLIENT, ZONE
						WHERE ((CLIENT.NAME LIKE '".$ZONE."%'
								OR CLIENT.POSTAL_CODE LIKE '".$ZONE."%')
							AND CLIENT.POSTAL_CODE = ZONE_ID)
						OR ZONE_ID LIKE '".$ZONE."%'
					    OR SHORT_DESCRIPTION LIKE '".$ZONE."%')
					    
					    FETCH FIRST 50 ROWS ONLY
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

			case 'AVGRATE':	// !AVGRATE - Average rate
				// Validate fields
				if( $cid == "NONE" || $ZONE1 == "NONE" || $ZONE2 == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT START_ZONE, END_ZONE, 
						COUNT(*) AS COUNT_LOADS, AVG(CHARGES) AS AVG_CHARGE
						FROM TLORDER T
						WHERE T.BILL_TO_CODE = '".$cid."'
						AND START_ZONE = '".$ZONE1."'
						AND END_ZONE = '".$ZONE2."'
						AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
						and t.extra_stops <> 'Child'
						AND T.CURRENT_STATUS NOT IN ('CANCL', 'QUOTE')
						AND T.CREATED_TIME = (
						  SELECT MAX(J.CREATED_TIME) FROM
						  TLORDER J
						  WHERE T.BILL_NUMBER = J.BILL_NUMBER
						  AND J.DOCUMENT_TYPE IN ('INVOICE','REBILL'))
						GROUP BY START_ZONE, END_ZONE
						ORDER BY START_ZONE ASC, END_ZONE ASC
						FOR READ ONLY
						WITH UR";
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response1 = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response1) ) {
				
						// Prepare Select
						$query_string = "SELECT COUNT(*) AS COUNT_LOADS, 
							AVG(CASE WHEN LENGTH_1 > 0 THEN ROUND(CHARGES/LENGTH_1,2) ELSE 0 END) AS CHARGE_FOOT,
							AVG(CASE WHEN WEIGHT > 0 THEN ROUND(CHARGES/WEIGHT*1000,2) ELSE 0 END) AS CHARGE_LB

							FROM TLORDER T
							WHERE T.BILL_TO_CODE = '".$cid."'
							AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
							and t.extra_stops <> 'Child'
							AND T.CURRENT_STATUS NOT IN ('CANCL', 'QUOTE')
							AND T.CREATED_TIME = (
								SELECT MAX(J.CREATED_TIME) FROM
								TLORDER J
								WHERE T.BILL_NUMBER = J.BILL_NUMBER
								AND J.DOCUMENT_TYPE IN ('INVOICE','REBILL'))
							FOR READ ONLY
							WITH UR";
						
						if( $debug ) echo "<p>using query_string = $query_string</p>";
				
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( is_array($response2) ) {
							$response = array();
							$response['DIRECT'] = $response1;
							$response['ALL'] = $response2;
						
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
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}

				break;

			case 'LIST':	// !LIST - List of quotes for a client
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT Q.QUOTE_ID, Q.QUOTE_NUMBER, T.CREATED_BY, 
						Q.EFFECTIVE_DATE, Q.EXPIRY_DATE, Q.ORIGIN, Q.DESTINATION,
						Q.COMMODITY, Q.FLAT_RATE, Q.NUMBER_OF_USES, Q.TIMES_USED,
						T.BILL_DATE
						FROM QUOTE Q, TLORDER T
						WHERE Q.CLIENT_NUMBER = '".$cid."'
						AND 'Q' || Q.QUOTE_NUMBER = T.BILL_NUMBER
						AND T.CURRENT_STATUS <> 'CANCL'
						ORDER BY Q.QUOTE_NUMBER ASC
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

			case 'GETDLID':	// !GETDLID - Map BILL_NUMBER to DETAIL_LINE_ID
				// Validate fields
				if( $FB == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT DETAIL_LINE_ID
						FROM TLORDER
						WHERE BILL_NUMBER = '".$FB."'
						AND BILL_NUMBER LIKE 'Q%'
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

			case 'DUE':	// !DUE - List of quotes due to expire soon
				// Prepare Select
				$query_string = "SELECT Q.CLIENT_NUMBER, C.NAME, C.SALES_REP, 
					T.START_ZONE,
					T.END_ZONE, T.CURRENT_ZONE, T.DESTADDR1, T.DESTCITY,
					".$stc_schema.".GET_ZONE_DESC(T.START_ZONE) START_ZDESC,
					".$stc_schema.".GET_ZONE_DESC(T.END_ZONE) END_ZDESC,
					".$stc_schema.".GET_ZONE_DESC(T.CURRENT_ZONE) CURRENT_ZDESC,
					Q.QUOTE_ID, 
					'Q' || Q.QUOTE_NUMBER AS QBILL, T.CREATED_BY,
					(SELECT J.FULLNAME
					FROM ST_CMS_USERS J
					WHERE T.CREATED_BY = J.USERID) FULLNAME,
					(SELECT J.EMAIL
					FROM ST_CMS_USERS J
					WHERE T.CREATED_BY = J.USERID) EMAIL,
					(SELECT K.FULLNAME
					FROM ST_CMS_USERS K
					WHERE T.CREATED_BY = K.USERID) C_FULLNAME,
					(SELECT K.EMAIL
					FROM ST_CMS_USERS K
					WHERE T.CREATED_BY = K.USERID) C_EMAIL,
					Q.EFFECTIVE_DATE, DATE(Q.EXPIRY_DATE) AS EXPIRY_DATE, Q.ORIGIN, Q.DESTINATION,
					Q.COMMODITY, Q.FLAT_RATE, Q.NUMBER_OF_USES, Q.TIMES_USED, T.BILL_DATE
					
					FROM QUOTE Q, TLORDER T, CLIENT C
					WHERE 'Q' || Q.QUOTE_NUMBER = T.BILL_NUMBER
					AND Q.CLIENT_NUMBER = C.CLIENT_ID
					AND Q.TIMES_USED = 0
					AND T.CURRENT_STATUS <> 'CANCL'
					AND DATE(Q.EXPIRY_DATE) BETWEEN CURRENT DATE AND CURRENT DATE + 5 DAYS
					".($uid <> "NONE" ? "AND c.SALES_REP = '".$uid."'" : "")."
					".($cid <> "NONE" ? "AND Q.CLIENT_NUMBER = '".$cid."'" : "")."
					ORDER BY Q.EFFECTIVE_DATE DESC
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

			case 'NOTSENT':	// !NOTSENT - List of quotes not sent recently
				// Prepare Select
				$query_string = "SELECT Q.CLIENT_NUMBER, C.NAME, C.SALES_REP, 
					T.START_ZONE,
					T.END_ZONE, T.CURRENT_ZONE, T.DESTADDR1, T.DESTCITY,
					".$stc_schema.".GET_ZONE_DESC(T.START_ZONE) START_ZDESC,
					".$stc_schema.".GET_ZONE_DESC(T.END_ZONE) END_ZDESC,
					".$stc_schema.".GET_ZONE_DESC(T.CURRENT_ZONE) CURRENT_ZDESC,
					Q.QUOTE_ID, 
					'Q' || Q.QUOTE_NUMBER AS QBILL, T.CREATED_BY,
					(SELECT J.FULLNAME
					FROM ST_CMS_USERS J
					WHERE T.CREATED_BY = J.USERID) FULLNAME,
					(SELECT J.EMAIL
					FROM ST_CMS_USERS J
					WHERE T.CREATED_BY = J.USERID) EMAIL,
					(SELECT K.FULLNAME
					FROM ST_CMS_USERS K
					WHERE T.CREATED_BY = K.USERID) C_FULLNAME,
					(SELECT K.EMAIL
					FROM ST_CMS_USERS K
					WHERE T.CREATED_BY = K.USERID) C_EMAIL,
					Q.EFFECTIVE_DATE, DATE(Q.EXPIRY_DATE) AS EXPIRY_DATE, Q.ORIGIN, Q.DESTINATION,
					Q.COMMODITY, Q.FLAT_RATE, Q.NUMBER_OF_USES, Q.TIMES_USED
					
					FROM QUOTE Q, TLORDER T, CLIENT C
					WHERE 'Q' || Q.QUOTE_NUMBER = T.BILL_NUMBER
					AND Q.CLIENT_NUMBER = C.CLIENT_ID
					AND Q.TIMES_USED = 0
					AND T.CURRENT_STATUS <> 'CANCL'
					AND T.BILL_DATE IS NULL
					AND DATE(Q.EFFECTIVE_DATE) BETWEEN CURRENT DATE - 10 DAYS AND CURRENT DATE - 1 DAY
					".($uid <> "NONE" ? "AND c.SALES_REP = '".$uid."'" : "")."
					".($cid <> "NONE" ? "AND Q.CLIENT_NUMBER = '".$cid."'" : "")."
					ORDER BY Q.EFFECTIVE_DATE DESC
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

			case 'COPIES':	// !COPIES - List of copies of a quote
				// Validate fields
				if( $FB == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT TRIM(BILL_NUMBER) AS BILL_NUMBER
						FROM TLORDER T
						WHERE T.DLID_QUOTE = (SELECT DETAIL_LINE_ID
							FROM TLORDER Q
							WHERE Q.BILL_NUMBER = '".$FB."')
							OR T.COPY_DLID = (SELECT DETAIL_LINE_ID
							FROM TLORDER C
							WHERE C.BILL_NUMBER = '".$FB."')
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

			case 'ZDESC':	// !ZDESC - Return description for a zone
				// Validate fields
				if( $ZONE == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT ZONE_ID, SHORT_DESCRIPTION
						FROM ZONE
					    WHERE ZONE_ID = '".$ZONE."'";
					
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

			case 'POPZONES':	// !POPZONES - Given a client, the most popular zones based on past bills
				// Prepare Select
				$query_string = "SELECT START_ZONE, SHORT_DESCRIPTION AS START_ZONE_DESC, NUM
					FROM
					(SELECT DISTINCT START_ZONE, COUNT(*) NUM
					FROM TLORDER
					".($cid != "NONE" ? "WHERE BILL_TO_CODE = '".$cid."'" : "")."
					GROUP BY START_ZONE
					ORDER BY 2 DESC
					FETCH FIRST 10 ROWS ONLY), ZONE
					WHERE START_ZONE = ZONE_ID
					ORDER BY NUM DESC";
				
				if( $debug ) echo "<p>using query_string = $query_string</p>";
		
				$response1 = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response1) ) {
					// Prepare Select
					$query_string = "SELECT END_ZONE, SHORT_DESCRIPTION AS END_ZONE_DESC, NUM
						FROM
						(SELECT DISTINCT END_ZONE, COUNT(*) NUM
						FROM TLORDER
						".($cid != "NONE" ? "WHERE BILL_TO_CODE = '".$cid."'" : "")."
						GROUP BY END_ZONE
						ORDER BY 2 DESC
						FETCH FIRST 10 ROWS ONLY), ZONE
						WHERE END_ZONE = ZONE_ID
						ORDER BY NUM DESC";
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response2 = send_odbc_query( $query_string, $stc_database, $debug );
				
					if( is_array($response2) ) {
						$response = array();
						$response['START_ZONE'] = $response1;
						$response['END_ZONE'] = $response2;
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
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}

				break;

			case 'CLIENTS':		// !CLIENTS - List of clients that match first characters of name or id
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT CLIENT_ID, NAME, CITY, PROVINCE
						FROM CLIENT 
						WHERE IS_INACTIVE = 'False' 
						AND ( CLIENT_ID LIKE '".$cid."%' OR NAME LIKE '".$cid."%' )
						ORDER BY CLIENT_ID";
						
						// CLIENT_IS_CALLER='True' AND 
					
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

			case 'CLIENT':		// !CLIENT - Fetch details for client
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					// TM 2013 missing: ALLOW_ALL_CMDTYS
					$query_string = "SELECT CLIENT_ID, NAME, UNIT, ADDRESS_1, ADDRESS_2, CITY,
						PROVINCE, POSTAL_CODE, COUNTRY, BUSINESS_PHONE, BUSINESS_PHONE_EXT, CONTACT, 
						EMAIL, CLIENT_IS_CALLER,CLIENT_IS_SHIPPER, CLIENT_IS_CONSIGNEE,CLIENT_IS_BILL_TO,
						DEFAULT_DELIVERY_Z,ROUTING_CODE, CREDIT_HOLD, CURRENCY_CODE,  COMMENTS, 
						FAX_PHONE, EMAIL_ADDRESS,OPEN_TIME, CLOSE_TIME, APPT_REQ, CUSTOMER_SINCE, 
						REQUESTED_EQUIPMEN, POP_UP_NOTES, BILL_CUSTOMER, CSA_NUMBER, 
						FAX_PHONE, BUSINESS_CELL, EMAIL_ADDRESS, GMT_OFFSET, DST_APPLIES, CREDIT_STATUS,
						PAYMENT_OPTIONS, CUSTOMS_BROKER_VENDOR, AGENT, SALES_REP, IS_INACTIVE,
						SPOT_TRAILER 
						FROM CLIENT 
						WHERE CLIENT_ID = '".$cid."' AND IS_INACTIVE='False'
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

			case 'POPCMDTY':		// !POPCMDTY - Top 20 commodities for a given client, or for all clients
				if( $cid <> "NONE" ) {
					// Prepare Select
					$query_string = "SELECT COMMODITY, SHORT_DESCRIPTION, NUM
						FROM
						(SELECT DISTINCT D.COMMODITY, COUNT(*) NUM
						FROM TLORDER T, TLDTL D
						WHERE T.BILL_TO_CODE = '".$cid."'
						AND T.DETAIL_LINE_ID = D.ORDER_ID
						AND COALESCE(D.COMMODITY, '') <> ''
						AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
						and t.extra_stops <> 'Child'
						AND T.CURRENT_STATUS <> 'CANCL'
						GROUP BY D.COMMODITY
						ORDER BY 2 DESC
						FETCH FIRST 20 ROWS ONLY), CMODTY
						WHERE COMMODITY = COMMODITY_CODE
						ORDER BY NUM DESC";
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response1 = send_odbc_query( $query_string, $stc_database, $debug );
				}

				$query_string = "SELECT COMMODITY, SHORT_DESCRIPTION, NUM
					FROM
					(SELECT DISTINCT D.COMMODITY, COUNT(*) NUM
					FROM TLORDER T, TLDTL D
					WHERE COALESCE(T.BILL_TO_CODE, '') <> ''
					AND T.DETAIL_LINE_ID = D.ORDER_ID
					AND COALESCE(D.COMMODITY, '') <> ''
					AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
					and t.extra_stops <> 'Child'
					AND T.CURRENT_STATUS <> 'CANCL'
					GROUP BY D.COMMODITY
					ORDER BY 2 DESC
					FETCH FIRST 20 ROWS ONLY), CMODTY
					WHERE COMMODITY = COMMODITY_CODE
					ORDER BY NUM DESC";
				
				if( $debug ) echo "<p>using query_string = $query_string</p>";
		
				$response2 = send_odbc_query( $query_string, $stc_database, $debug );
				
				$response = array();
				if( is_array($response1) ) $response['CLIENT'] = $response1;
				if( is_array($response2) ) $response['ALL'] = $response2;
				
				if( $debug ) {
					echo "<pre>";
					var_dump($response);
					echo "</pre>";
				} else {
					echo encryptData(json_encode( $response ));
				}

				break;

			case 'CMDTYS':	// !CMDTYS - List of commodities that match first characters
				// Validate fields
				if( $COMMODITY == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT COMMODITY_CODE, SHORT_DESCRIPTION
						FROM CMODTY
						WHERE COMMODITY_CODE LIKE '".$COMMODITY."%'";
					
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

			case 'CDESC':	// !CDESC - Return description for a commodity
				// Validate fields
				if( $COMMODITY == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT COMMODITY_CODE, SHORT_DESCRIPTION
						FROM CMODTY
						WHERE COMMODITY_CODE = '".$COMMODITY."'";
					
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

			case 'UNITS':	// !UNITS - length & weight units
				$query_string = "SELECT UNIT_SYMBOL, NAME, TRIM(BASE_UNIT) BASE_UNIT, CONVERSION_FACTOR
					FROM UNIT
					WHERE UNIT_TYPE = 'L'
					ORDER BY CONVERSION_FACTOR ASC
					FOR READ ONLY
					WITH UR";
				
				if( $debug ) echo "<p>using query_string = $query_string</p>";
		
				$response1 = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response1) ) {
					$query_string = "SELECT UNIT_SYMBOL, NAME, TRIM(BASE_UNIT) BASE_UNIT, CONVERSION_FACTOR
						FROM UNIT
						WHERE UNIT_TYPE = 'W'
						ORDER BY CONVERSION_FACTOR ASC
						FOR READ ONLY
						WITH UR";
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response2 = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response2) ) {
						$query_string = "SELECT UNIT_SYMBOL, NAME, TRIM(BASE_UNIT) BASE_UNIT, CONVERSION_FACTOR
							FROM UNIT
							WHERE UNIT_TYPE = 'I'
							ORDER BY CONVERSION_FACTOR ASC
							FOR READ ONLY
							WITH UR";
						
						if( $debug ) echo "<p>using query_string = $query_string</p>";
				
						$response3 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( is_array($response2) ) {
							$response = array();
							$response['LENGTH'] = $response1;
							$response['WEIGHT'] = $response2;
							$response['ITEM'] = $response3;
							if( $debug ) {
								echo "<pre>";
								var_dump($response);
								echo "</pre>";
							} else {
								echo encryptData(json_encode( $response ));
							}
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query 3 failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: send_odbc_query 3 failed: " . $last_odbc_error);
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query 2 failed: " . $last_odbc_error);
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query 1 failed: " . $last_odbc_error);
				}

				break;

			case 'REQSVC':	// !REQSVC - Requested equipment and service levels available
				$query_string = "SELECT CODE, CODEDESC
					FROM EQCLASS
					ORDER BY CODEDESC
					FOR READ ONLY
					WITH UR";
				
				if( $debug ) echo "<p>using query_string = $query_string</p>";
		
				$response1 = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response1) ) {
					$query_string = "SELECT CODE, CODEDESC 
						FROM SVCLEVEL
						ORDER BY CODE
						FOR READ ONLY
						WITH UR";
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response2 = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response2) ) {
						$response = array();
						$response['REQ'] = $response1;
						$response['SVC'] = $response2;
						if( $cid <> "NONE" ) {
							$query_string = "SELECT REQUESTED_EQUIPMEN 
								FROM CLIENT
								WHERE CLIENT_ID = '".$cid."'
								FOR READ ONLY
								WITH UR";
							
							if( $debug ) echo "<p>using query_string = $query_string</p>";
					
							$response3 = send_odbc_query( $query_string, $stc_database, $debug );
							
							if( is_array($response2) ) {
								$response['REQ_DEFAULT'] = $response3[0]['REQUESTED_EQUIPMEN'];
							}
						}
						if( $SITEID <> "NONE" ) {
							$query_string = "SELECT SERVICE_LEVEL 
								FROM SITE
								WHERE SITE_ID = '".$SITEID."'
								FOR READ ONLY
								WITH UR";
							
							if( $debug ) echo "<p>using query_string = $query_string</p>";
					
							$response4 = send_odbc_query( $query_string, $stc_database, $debug );
							
							if( is_array($response4) ) {
								$response['SVC_DEFAULT'] = $response4[0]['SERVICE_LEVEL'];
							}
						} else {
							$response['SVC_DEFAULT'] = 'TRUCKLOAD';
						}
					
					

						
						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query 2 failed: " . $last_odbc_error);
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query 1 failed: " . $last_odbc_error);
				}

				break;

			case 'CIDS':	// !CIDS - List of CLIENTS
				$dbconn = new stc_db( $stc_database, $debug );
				
				if( $dbconn ) {
					$query_string = "SELECT DISTINCT p.CLIENTID, C.NAME
						FROM PHONE p, PHONENOTES n, CLIENT c
						WHERE p.PHONEID = n.PHONEID
						AND p.CLIENTID = c.CLIENT_ID
						AND PHONEDATE > CURRENT DATE - ".$DAYS." DAYS
						".($uid <> "NONE" ? "AND c.SALES_REP = '".$uid."'" : "")."
						UNION
						SELECT DISTINCT Q.CLIENT_NUMBER AS CLIENTID, C.NAME
						FROM QUOTE Q, CLIENT C
						WHERE Q.CLIENT_NUMBER = C.CLIENT_ID
						AND Q.EFFECTIVE_DATE > CURRENT DATE - ".$DAYS." DAYS
						".($uid <> "NONE" ? "AND C.SALES_REP = '".$uid."'" : "")."
						UNION
						SELECT DISTINCT T.BILL_TO_CODE AS CLIENTID, C.NAME
						FROM TLORDER T, CLIENT C
						WHERE T.CREATED_TIME > CURRENT DATE - ".$DAYS." DAYS
						AND T.BILL_TO_CODE = C.CLIENT_ID
						AND CURRENT_STATUS <> 'CANCL'
						AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
						AND EXTRA_STOPS <> 'Child'
						AND BILL_NUMBER NOT LIKE 'Q%'
						AND SITE_ID IN ('SITE1', 'SITE4')
						AND DOCUMENT_TYPE = 'INVOICE'
						".($uid <> "NONE" ? "AND C.SALES_REP = '".$uid."'" : "")."
						ORDER BY CLIENTID ASC
						FOR READ ONLY
						WITH UR";
					
					$response = $dbconn->get_multiple_rows( $query_string );
					
					if( is_array($response) ) {
						echo ok_response( 'CIDS', $response, $dbconn, $timer, $debug );
					} else {
						echo error_response( "NOT OK", 'get_multiple_rows failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'stc_db failed: ' . $last_odbc_error, $debug );
				}

				break;

			case 'CALLS':	// !CALLS - List of calls(notes)
				$dbconn = new stc_db( $stc_database, $debug );
				
				if( $dbconn ) {
					$query_string = "SELECT p.CLIENTID, c.NAME, c.SALES_REP, n.USER_ID, 
					(SELECT S.NAME FROM SALESREP S
					WHERE S.ID = c.SALES_REP ) AS REP_NAME,
					(SELECT S.NAME FROM SALESREP S
					WHERE S.ID = n.USER_ID ) AS USER_NAME,
					n.PHONEID, PHONEDATE, TITLE,
						NOTETYPE, THE_NOTE
						FROM PHONE p, PHONENOTES n, CLIENT c
						WHERE p.PHONEID = n.PHONEID
						AND p.CLIENTID = c.CLIENT_ID
						AND DATE(PHONEDATE) > CURRENT DATE - ".$DAYS." DAYS
						".($uid <> "NONE" ? "AND c.SALES_REP = '".$uid."'" : "")."
						".($cid <> "NONE" ? "AND p.CLIENTID = '".$cid."'" : "")."
						".($NOTETYPE <> "NONE" ? "AND NOTETYPE = '".$NOTETYPE."'" : "")."
						ORDER BY PHONEDATE DESC
						FOR READ ONLY
						WITH UR";
				
					$response = $dbconn->get_multiple_rows( $query_string );
					
					if( is_array($response) ) {
						echo ok_response( 'CALLS', $response, $dbconn, $timer, $debug );
					} else {
						echo error_response( "NOT OK", 'get_multiple_rows failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'stc_db failed: ' . $last_odbc_error, $debug );
				}

				break;

			case 'CHIST':	// !CHIST - Histogram of calls(notes)
				$dbconn = new stc_db( $stc_database, $debug );
				
				if( $dbconn ) {
					$query_string = "WITH RAW AS 
						(SELECT PHONEDATE, NOTETYPE
						FROM PHONE P, PHONENOTES N, CLIENT C
						WHERE P.PHONEID = N.PHONEID
						AND P.CLIENTID = C.CLIENT_ID
						AND PHONEDATE > CURRENT DATE - 6 MONTHS
						".($uid <> "NONE" ? "AND c.SALES_REP = '".$uid."'" : "")."
						".($cid <> "NONE" ? "AND p.CLIENTID = '".$cid."'" : "")."
						ORDER BY PHONEDATE ASC),
						
						ALL_BY_WEEK AS
						(SELECT YEAR(PHONEDATE) YR, WEEK(PHONEDATE) WK, 
							(SELECT COUNT(*)
								FROM CLIENT
								WHERE YEAR(ADDITION_DATE) < YEAR(MIN(PHONEDATE))
								OR (YEAR(ADDITION_DATE) = YEAR(MIN(PHONEDATE))
								AND WEEK(ADDITION_DATE) <= WEEK(MIN(PHONEDATE)))) AS CLIENTS,
							(SELECT COUNT(*)
								FROM CLIENT
								WHERE YEAR(ADDITION_DATE) = YEAR(MIN(PHONEDATE))
								AND WEEK(ADDITION_DATE) = WEEK(MIN(PHONEDATE))) AS GAIN,
							(SELECT COUNT(*)
								FROM CLIENT
								WHERE YEAR(ADDITION_DATE) < YEAR(MIN(PHONEDATE))
								OR (YEAR(ADDITION_DATE) = YEAR(MIN(PHONEDATE))
								AND WEEK(ADDITION_DATE) <= WEEK(MIN(PHONEDATE)))
								AND IS_INACTIVE = 'True') AS INACTIVE,
							COUNT(*) AS CALLS,
							(SELECT COUNT(*)
								FROM CLIENT
								WHERE YEAR(MODIFIED_DATE) = YEAR(MIN(PHONEDATE))
								AND WEEK(MODIFIED_DATE) = WEEK(MIN(PHONEDATE))
								AND NOT (YEAR(ADDITION_DATE) = YEAR(MIN(PHONEDATE))
								AND WEEK(ADDITION_DATE) = WEEK(MIN(PHONEDATE)))
								) AS MODS

						FROM RAW
						GROUP BY YEAR(PHONEDATE), WEEK(PHONEDATE)
						ORDER BY YEAR(PHONEDATE) ASC, WEEK(PHONEDATE) ASC),
						
						LEADS_BY_WEEK AS
						(SELECT YEAR(PHONEDATE) YR, WEEK(PHONEDATE) WK, COUNT(*) AS LEADS
						FROM RAW
						WHERE NOTETYPE = 'LEAD'
						GROUP BY YEAR(PHONEDATE), WEEK(PHONEDATE)
						ORDER BY YEAR(PHONEDATE) ASC, WEEK(PHONEDATE) ASC),
						
						FUP_BY_WEEK AS
						(SELECT YEAR(PHONEDATE) YR, WEEK(PHONEDATE) WK, COUNT(*) AS FUP
						FROM RAW
						WHERE NOTETYPE = 'L-FOLLOW'
						GROUP BY YEAR(PHONEDATE), WEEK(PHONEDATE)
						ORDER BY YEAR(PHONEDATE) ASC, WEEK(PHONEDATE) ASC)
						
						SELECT ALL_BY_WEEK.YR, ALL_BY_WEEK.WK, CALLS, CLIENTS, GAIN, INACTIVE, MODS, LEADS, FUP
						FROM ALL_BY_WEEK
						LEFT OUTER JOIN LEADS_BY_WEEK
						ON ALL_BY_WEEK.YR = LEADS_BY_WEEK.YR
						AND ALL_BY_WEEK.WK = LEADS_BY_WEEK.WK
						LEFT OUTER JOIN FUP_BY_WEEK
						ON ALL_BY_WEEK.YR = FUP_BY_WEEK.YR
						AND ALL_BY_WEEK.WK = FUP_BY_WEEK.WK
						ORDER BY ALL_BY_WEEK.YR ASC, ALL_BY_WEEK.WK ASC

						FOR READ ONLY
						WITH UR";
				
					$response = $dbconn->get_multiple_rows( $query_string );
					
					if( is_array($response) ) {
						echo ok_response( 'CHIST', $response, $dbconn, $timer, $debug );
					} else {
						echo error_response( "NOT OK", 'get_multiple_rows failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'stc_db failed: ' . $last_odbc_error, $debug );
				}

				break;

			case 'QUOTES':	// !QUOTES - List of quotes
				$dbconn = new stc_db( $stc_database, $debug );
				
				if( $dbconn ) {
					$query_string = "SELECT T.BILL_NUMBER, Q.CLIENT_NUMBER, C.NAME, C.SALES_REP, T.CREATED_BY,
						(SELECT S.NAME FROM SALESREP S
						WHERE S.ID = C.SALES_REP) AS REP_NAME,
						(SELECT DATA
							FROM CUSTOM_DATA
							WHERE CUSTDEF_ID = ".$stc_custom_client_account_rep_id."
							AND CLIENT_ID = SRC_TABLE_KEY) AS ACCOUNT_REP,
						(SELECT S.NAME FROM SALESREP S
						WHERE S.ID = T.CREATED_BY) AS USER_NAME,
						T.OP_CODE, T.START_ZONE,
						T.END_ZONE, T.CURRENT_ZONE, T.DESTADDR1, T.DESTCITY,
						".$stc_schema.".GET_ZONE_DESC(T.START_ZONE) START_ZDESC,
						".$stc_schema.".GET_ZONE_DESC(T.END_ZONE) END_ZDESC,
						".$stc_schema.".GET_ZONE_DESC(T.CURRENT_ZONE) CURRENT_ZDESC,			
						Q.QUOTE_ID, 
						'Q' || Q.QUOTE_NUMBER AS QBILL, T.CREATED_BY, 
						Q.EFFECTIVE_DATE, DATE(Q.EXPIRY_DATE) EXPIRY_DATE, Q.ORIGIN, Q.DESTINATION,
						Q.COMMODITY, Q.FLAT_RATE, Q.NUMBER_OF_USES, Q.TIMES_USED, T.BILL_DATE
						FROM QUOTE Q, TLORDER T, CLIENT C
						WHERE 'Q' || Q.QUOTE_NUMBER = T.BILL_NUMBER
						AND T.CURRENT_STATUS <> 'CANCL'
						AND Q.CLIENT_NUMBER = C.CLIENT_ID
						AND Q.EFFECTIVE_DATE > CURRENT DATE - ".$DAYS." DAYS
						".($uid <> "NONE" ? "AND c.SALES_REP = '".$uid."'" : "")."
						".($cid <> "NONE" ? "AND Q.CLIENT_NUMBER = '".$cid."'" : "")."
						ORDER BY T.BILL_NUMBER ASC, Q.EFFECTIVE_DATE DESC
						FOR READ ONLY
						WITH UR";
				
					$response = $dbconn->get_multiple_rows( $query_string );
					
					if( is_array($response) ) {
						echo ok_response( 'QUOTES', $response, $dbconn, $timer, $debug );
					} else {
						echo error_response( "NOT OK", 'get_multiple_rows failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'stc_db failed: ' . $last_odbc_error, $debug );
				}

				break;

			case 'EXPIRED':	// !EXPIRED - List of recently expired quotes
				$dbconn = new stc_db( $stc_database, $debug );
				
				if( $dbconn ) {
					$query_string = "SELECT Q.CLIENT_NUMBER, C.NAME, C.SALES_REP, T.CREATED_BY,
						(SELECT S.NAME FROM SALESREP S
						WHERE S.ID = C.SALES_REP) AS REP_NAME,			
						(SELECT S.NAME FROM SALESREP S
						WHERE S.ID = T.CREATED_BY) AS USER_NAME,			
						T.START_ZONE,
						T.END_ZONE, T.CURRENT_ZONE, T.DESTADDR1, T.DESTCITY,
						".$stc_schema.".GET_ZONE_DESC(T.START_ZONE) START_ZDESC,
						".$stc_schema.".GET_ZONE_DESC(T.END_ZONE) END_ZDESC,
						".$stc_schema.".GET_ZONE_DESC(T.CURRENT_ZONE) CURRENT_ZDESC,			
						Q.QUOTE_ID, 
						'Q' || Q.QUOTE_NUMBER AS QBILL, T.CREATED_BY, 
						Q.EFFECTIVE_DATE, Q.EXPIRY_DATE, Q.ORIGIN, Q.DESTINATION,
						Q.COMMODITY, Q.FLAT_RATE, Q.NUMBER_OF_USES, Q.TIMES_USED, T.BILL_DATE
						FROM QUOTE Q, TLORDER T, CLIENT C
						WHERE 'Q' || Q.QUOTE_NUMBER = T.BILL_NUMBER
						AND T.CURRENT_STATUS <> 'CANCL'
						AND Q.CLIENT_NUMBER = C.CLIENT_ID
						AND Q.TIMES_USED = 0
						AND Q.EXPIRY_DATE > CURRENT DATE - ".$DAYS." DAYS
						AND Q.EXPIRY_DATE <= CURRENT DATE
						".($uid <> "NONE" ? "AND c.SALES_REP = '".$uid."'" : "")."
						".($cid <> "NONE" ? "AND Q.CLIENT_NUMBER = '".$cid."'" : "")."
						ORDER BY Q.EFFECTIVE_DATE DESC
						FOR READ ONLY
						WITH UR";
					
					$response = $dbconn->get_multiple_rows( $query_string );
					
					if( is_array($response) ) {
						echo ok_response( 'EXPIRED', $response, $dbconn, $timer, $debug );
					} else {
						echo error_response( "NOT OK", 'get_multiple_rows failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'stc_db failed: ' . $last_odbc_error, $debug );
				}

				break;

			case 'BILLS':	// !BILLS - List of bills
				$dbconn = new stc_db( $stc_database, $debug );
				
				if( $dbconn ) {
					$query_string = "SELECT TRIM(T.BILL_NUMBER) AS BILL_NUMBER, T.BILL_TO_CODE, T.BILL_TO_NAME,
						C.NAME, C.SALES_REP, T.CREATED_BY,
						(SELECT S.NAME FROM SALESREP S
						WHERE S.ID = C.SALES_REP) AS REP_NAME,	
						(SELECT S.NAME FROM SALESREP S
						WHERE S.ID = T.CREATED_BY) AS USER_NAME,			
						T.CREATED_TIME, T.CURRENT_STATUS, T.COMMODITY,
						OP_CODE, TOTAL_CHARGES, SITE_ID, 
						(SELECT S.SITE_NAME FROM SITE S WHERE S.SITE_ID = T.SITE_ID),
						T.CURRENCY_CODE, T.DOCUMENT_TYPE
						FROM TLORDER T, CLIENT C
						WHERE T.CREATED_TIME > CURRENT DATE - ".$DAYS." DAYS
						AND T.BILL_TO_CODE = C.CLIENT_ID
						AND CURRENT_STATUS <> 'CANCL'
						AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
						AND EXTRA_STOPS <> 'Child'
						AND BILL_NUMBER NOT LIKE 'Q%'
						AND DOCUMENT_TYPE = 'INVOICE'
						AND T.DLID_QUOTE IS NOT NULL
						".($uid <> "NONE" ? "AND C.SALES_REP = '".$uid."'" : "")."
						".($cid <> "NONE" ? "AND T.BILL_TO_CODE = '".$cid."'" : "")."
						ORDER BY T.CREATED_TIME DESC
						FOR READ ONLY
						WITH UR";
				
					$response = $dbconn->get_multiple_rows( $query_string );
					
					if( is_array($response) ) {
						echo ok_response( 'BILLS', $response, $dbconn, $timer, $debug );
					} else {
						echo error_response( "NOT OK", 'get_multiple_rows failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'stc_db failed: ' . $last_odbc_error, $debug );
				}

				break;

			case 'NEWBILLS':	// !NEWBILLS - List of bills recently created and not emailed
				$query_string = "SELECT TRIM(T.BILL_NUMBER) AS BILL_NUMBER, T.DETAIL_LINE_ID,
					LTRIM( (SELECT Q.BILL_NUMBER FROM TLORDER Q
							WHERE Q.DETAIL_LINE_ID = T.DLID_QUOTE) ) AS QBILL,
					T.BILL_TO_CODE, T.BILL_TO_NAME,
					C.NAME, C.SALES_REP, T.CREATED_BY,
					(SELECT S.NAME FROM SALESREP S
						WHERE S.ID = C.SALES_REP) AS REP_NAME,	
					(SELECT S.EMAIL FROM ST_CMS_USERS S
						WHERE S.USERID = C.SALES_REP) AS REP_EMAIL,	
					(SELECT S.NAME FROM SALESREP S
						WHERE S.ID = T.CREATED_BY) AS USER_NAME,			
					(SELECT S.EMAIL FROM ST_CMS_USERS S
						WHERE S.USERID = T.CREATED_BY) AS USER_EMAIL,	
					(SELECT NAME FROM VENDOR 
						WHERE T.DISPATCH_AGENT = VENDOR_ID) AGENT_NAME,
					(SELECT S.EMAIL FROM ST_CMS_USERS S, VENDOR V
						WHERE T.DISPATCH_AGENT = V.VENDOR_ID
						AND UPPER(V.NAME) = UPPER(S.FULLNAME) ) AS AGENT_EMAIL,	
					T.CREATED_TIME, T.CURRENT_STATUS, T.COMMODITY,
					OP_CODE, TOTAL_CHARGES, SITE_ID, 
					(SELECT S.SITE_NAME FROM SITE S WHERE S.SITE_ID = T.SITE_ID),
					T.CURRENCY_CODE, T.DOCUMENT_TYPE
					FROM TLORDER T, CLIENT C
					WHERE T.CREATED_TIME > CURRENT DATE - ".$DAYS." DAYS
					AND NOT EXISTS (SELECT O.CHANGED
						FROM ODRSTAT O
						WHERE O.ORDER_ID = T.DETAIL_LINE_ID
						AND O.STATUS_CODE = 'EMAILED')
					AND T.BILL_TO_CODE = C.CLIENT_ID
					AND CURRENT_STATUS <> 'CANCL'
					AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
					AND EXTRA_STOPS <> 'Child'
					AND BILL_NUMBER NOT LIKE 'Q%'
					AND DOCUMENT_TYPE = 'INVOICE'
					AND T.DLID_QUOTE IS NOT NULL
					".($uid <> "NONE" ? "AND C.SALES_REP = '".$uid."'" : "")."
					".($cid <> "NONE" ? "AND T.BILL_TO_CODE = '".$cid."'" : "")."
					ORDER BY T.CREATED_TIME DESC
					FOR READ ONLY
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

			case 'EMAILED':	// !EMAILED - Set ODRSTAT to indicate we emailed a report on this bill
				// Validate fields
				if( ($cid == "NONE" && $FB == "NONE") || $uid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
					
					if( $cid == "NONE" ) {
						$query_string = "SELECT BILL_NUMBER, DETAIL_LINE_ID
							FROM TLORDER
							WHERE BILL_NUMBER = '".$FB."'
							FOR READ ONLY
							WITH UR";
							
						$response = send_odbc_query( $query_string, $stc_database, $debug );
				
						if( is_array($response) && count($response) == 1 && ! empty($response[0]['DETAIL_LINE_ID'])) {
							$cid = $response[0]['DETAIL_LINE_ID'];
						} else {
							if( $debug ) echo "<p>Error - EMAILED failed, $FB not found</p>";
							else echo encryptData("NOT OK: EMAILED failed: , $FB not found");
						}	
					}
					
					if( $cid != "NONE") {
						$ins_date = date('Y-m-d H:i:s');
	
						$ret = insert_into_odrstat( $cid, $ins_date, $uid, 
							'', 'EMAILED', 'Report sent to '.
								($CALLER == 'NONE' ? 'recipient' : $CALLER), $debug );		
				
						if( isset($ret) && $ret ) {
							if( $debug ) echo "<p>DONE: ODRSTAT updated</p>";
							else echo encryptData("DONE");							
						} else {
							if( $debug ) echo "<p>Error - insert_into_odrstat failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: insert_into_odrstat failed: " . $last_odbc_error);
						}
					}
				}

				break;

			case 'SENDEXP':	// !SENDEXP - Check if we can send email regarding expiring quotes
				$query_string = "SELECT MODIFIED_DATE
					FROM VENDOR
					WHERE VENDOR_ID = 'STCQUOTES'
					FOR READ ONLY
					WITH UR";
				
				if( $debug ) echo "<p>using query_string = $query_string</p>";
		
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response) ) {
					if( count($response) == 0 ) {
						$query_string = "INSERT INTO VENDOR(VENDOR_ID, NAME, MODIFIED_DATE, IS_INACTIVE)
							VALUES('STCQUOTES', 'STC QUOTES', CURRENT TIMESTAMP, 'True')";
						
						if( $debug ) echo "<p>using query_string = $query_string</p>";
				
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						if( is_array($response2) ) {
							if( $debug ) echo "<p>DOIT: created vendor</p>";
							else echo encryptData("DOIT");
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query2 failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: send_odbc_query2 failed: " . $last_odbc_error);
						}
					} else {
						$mod_date = $response[0]['MODIFIED_DATE'];
						if( strtotime($mod_date) < strtotime(date('Y-m-d')) ) {
							$query_string = "UPDATE VENDOR
								SET MODIFIED_DATE = CURRENT DATE
								WHERE VENDOR_ID = 'STCQUOTES'";
							
							if( $debug ) echo "<p>using query_string = $query_string</p>";
					
							$response3 = send_odbc_query( $query_string, $stc_database, $debug );
							if( is_array($response3) ) {
								if( $debug ) echo "<p>DOIT: updated date</p>";
								else echo encryptData("DOIT");
							} else {
								if( $debug ) echo "<p>Error - send_odbc_query3 failed. $last_odbc_error</p>";
								else echo encryptData("NOT OK: send_odbc_query3 failed: " . $last_odbc_error);
							}
						} else {
							if( $debug ) echo "<p>NOT OK: Already done today</p>";
							else echo encryptData("NOT OK: Already done today");
						}					
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}

				break;

			case 'GETBD':	// !GETBD - Get BILL_DATE for a quote
				// Validate fields
				if( $FB == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT BILL_DATE 
						FROM TLORDER 
						WHERE BILL_NUMBER = '".$FB."'";
					
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

			case 'SETBD':	// !SETBD - Set BILL_DATE to now, for a quote
				// Validate fields
				if( $FB == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "UPDATE TLORDER
						SET BILL_DATE = CURRENT TIMESTAMP
						WHERE BILL_NUMBER = '".$FB."'";
					
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

			case 'QUOTE':	// !QUOTE - Post a quote
				if( $debug ) echo "<p>QUOTE: COMPANYID=$COMPANYID SITEID=$SITEID CALLER=$CALLER 
					CONTACT=$CONTACT EMAIL=$EMAIL
					ZONE1=$ZONE1 ZONE2=$ZONE2 DESTADDR1=$DESTADDR1 DESTCITY=$DESTCITY
					COMMODITY=$COMMODITY PIECES=$PIECES 
					WEIGHT=$WEIGHT LENGTH=$LENGTH WIDTH=$WIDTH HEIGHT=$HEIGHT 
					WTU=$WTU LNU=$LNU WIU=$WIU HTU=$HTU SVC=$SVC REQ=$REQ
					DECLARED=$DECLARED RATE=$RATE GOODFOR=$GOODFOR
					uid=$uid</p>";
				// Validate fields
				if( $COMPANYID == "NONE" || $uid == "NONE" || $ZONE1 == "NONE" || $ZONE2 == "NONE" ||
					$COMMODITY == "NONE" || $PUP == "NONE" || $DEL == "NONE" || $SITEID == "NONE" || 
					$CALLER == "NONE" || $PIECES == "NONE" || $WEIGHT == "NONE" || $LENGTH == "NONE" ||
					$WIDTH == "NONE" || $HEIGHT == "NONE" || $DECLARED == "NONE" || $RATE == "NONE" ||
					$WTU == "NONE" || $LNU == "NONE" || $WIU == "NONE" || $HTU == "NONE" ||
					$SVC == "NONE" || $REQ == "NONE" ||
					$CONTACT == "NONE" || $EMAIL == "NONE" || $GOODFOR == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					
					
					$ins_date = date('Y-m-d H:i:s', strtotime('now - 1 hour'));
			
					$dlid = insert_into_tlorder( $CALLER, $ins_date, $uid, $ZONE1, $ZONE2, 
								$DESTADDR1, $DESTCITY, $PUP, $DEL, $SITEID, $COMPANYID, $TARP, 
								$SVC, $REQ, $DECLARED, $CONTACT, $EMAIL, $debug );
					
					if( isset($dlid) && $dlid ) {
						if( $THE_NOTE <> "NONE" )
							insert_into_notes( $dlid, $stc_internal_note_type, $ins_date, $THE_NOTE, $debug );
						if( $EXT_NOTE <> "NONE" )
							insert_into_notes( $dlid, $stc_external_note_type, $ins_date, $EXT_NOTE, $debug );
					
						$ret = insert_into_odrstat( $dlid, $ins_date, $uid, $ZONE1, 'ENTRY',
							'Created in Webtools - http://www.strongtoweronline.com', $debug );
						
						if( isset($ret) && $ret ) {
							// TLDTL
							$ret2 = insert_into_tldtl( $dlid, $COMMODITY, $REQ, $PIECES, $LENGTH, $WIDTH, 
	$HEIGHT, $WEIGHT, 'PC', $LNU, $WIU, $HTU, $WTU, $RATE, $debug );

							if( isset($ret2) && $ret2 ) {
								// post quote.
								$qbill = post_quote( $dlid, $CALLER, $COMMODITY, $ins_date, $GOODFOR, $uid, $USES, $debug );
								if( isset($qbill) && $qbill ) {
									$response = array();
									$response['QUOTE'] = $qbill;
									$response['DLID'] = $dlid;
									
									if( $debug ) {
										echo "<pre>";
										var_dump($response);
										echo "</pre>";
										
									} else {
										echo encryptData(json_encode( $response ));
									}
						
								} else {
									if( $debug ) echo "<p>Error - post_quote failed. $last_odbc_error</p>";
									else echo encryptData("NOT OK: post_quote failed: " . $last_odbc_error);
								}
							} else {
								if( $debug ) echo "<p>Error - insert_into_tldtl failed. $last_odbc_error</p>";
								else echo encryptData("NOT OK: insert_into_tldtl failed: " . $last_odbc_error);
							}
						} else {
							if( $debug ) echo "<p>Error - insert_into_odrstat failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: insert_into_odrstat failed: " . $last_odbc_error);
						}

					} else {
						if( $debug ) echo "<p>Error - insert_into_tlorder failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: insert_into_tlorder failed: " . $last_odbc_error);
					}
				}

				break;

			case 'QUOTE1':	// !QUOTE1 - Create a NA bill
				if( $debug ) echo "<p>QUOTE1: COMPANYID=$COMPANYID SITEID=$SITEID CALLER=$CALLER 
					CONTACT=$CONTACT EMAIL=$EMAIL
					ZONE1=$ZONE1 ZONE2=$ZONE2 ORIGADDR1=$ORIGADDR1 ORIGCITY=$ORIGCITY ORIGPROV=$ORIGPROV DESTADDR1=$DESTADDR1 DESTCITY=$DESTCITY DESTPROV=$DESTPROV
					SVC=$SVC REQ=$REQ
					uid=$uid</p>";
				// Validate fields
				if( $COMPANYID == "NONE" || $uid == "NONE" || $ZONE1 == "NONE" || $ZONE2 == "NONE" ||
					$SITEID == "NONE" || $OP_CODE == "NONE" ||
					($CALLER == "NONE" && $MANUAL == "NONE") ||
					$CONTACT == "NONE" || $EMAIL == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo error_response( "NOT OK", "Required fields missing or blank.", $debug );
				} else {
				
				//	if( $debug ) echo "<p>Error - testing.</p>";
				//	else echo error_response( "NOT OK", "testing: ", $debug );
				//	die;
					
					
					$ins_date = date('Y-m-d H:i:s', strtotime('now - 1 hour'));
					
					if( $CALLER == "NONE" && $MANUAL != "NONE" ) {
						$CALLER = json_decode($MANUAL, true);
						if( $debug ) {
							echo "<pre>CALLER\n";
							var_dump($CALLER);
							echo "</pre>";
							
						}
					}
			
					$dlid = insert_into_tlorder( $CALLER, $ins_date, $uid, $ZONE1, $ZONE2, 
						$ORIGIN, $ORIGNAME, $ORIGADDR1, $ORIGCITY, $ORIGPROV, 
						$DESTINATION, $DESTNAME, $DESTADDR1, $DESTCITY, $DESTPROV, 
						$PUP, $DEL, $PUPR, $DELR, $SITEID, $COMPANYID, $TARP,
						$SVC, $REQ, $DECLARED, $CONTACT, $EMAIL, $OP_CODE, $debug );
					
					if( isset($dlid) && $dlid ) {
						/* May use later
						if( $THE_NOTE <> "NONE" )
							insert_into_notes( $dlid, $stc_internal_note_type, $ins_date, $THE_NOTE, $debug );
						if( $EXT_NOTE <> "NONE" )
							insert_into_notes( $dlid, $stc_external_note_type, $ins_date, $EXT_NOTE, $debug );
						*/
					
						$ret = insert_into_odrstat( $dlid, $ins_date, $uid, $ZONE1, 'ENTRY',
							'Created in Webtools - http://www.strongtoweronline.com', $debug );
						
						if( isset($ret) && $ret ) {
							
							//! SCR# 824 - Insert custom data fields
							if( $WH != "NONE" ) {
								// Encoded JSON array of CD_x => value, where x is the id
								$fields = json_decode($WH, true);
								if( $debug ) {
									echo "<pre>dlid, OP_CODE, fields:\n";
									var_dump($dlid, $OP_CODE, $fields);
									echo "</pre>";
								}
								
								if( $OP_CODE == 'WAREHOUSE' ) {
									if( $debug ) {
										echo "<pre>Before insert_custom_data:\n";
										var_dump( $dlid, $OP_CODE, $fields['CD_77'] );
										echo "</pre>";
									}
																		
									if( ! empty($fields['CD_77']) ) insert_custom_data( 77, $dlid, $fields['CD_77'], $debug );
									if( ! empty($fields['CD_78']) ) insert_custom_data( 78, $dlid, $fields['CD_78'], $debug );
									if( ! empty($fields['CD_79']) ) insert_custom_data( 79, $dlid, $fields['CD_79'], $debug );
									
									$rate = $fields['RATE'];
									$pallets = $fields['PALLETS'];
									$palin = $fields['PALIN'];
									$palout = $fields['PALOUT'];
									
									$days = days_diff( $fields['CD_77'], $fields['CD_78'] ) + 1;
									if( $fields['CD_79'] == 'Daily' ) {
										$tm = 'DAY';
										$dur = $days;
									} else if( $fields['CD_79'] == 'Weekly' ) {
										$tm = 'WK';
										$dur = round($days / 7, 0, PHP_ROUND_HALF_UP);
									} else {
										$tm = 'M';
										$dur = round($days / 30, 0, PHP_ROUND_HALF_UP);
									}
									
									if( $debug ) {
										echo "<pre>rate, pallets, palin, palout, tm, dur:\n";
										var_dump($rate, $pallets, $palin, $palout, $tm, $dur);
										echo "</pre>";
									}

									insert_into_tldtl2( $dlid, $pallets, $tm, $dur, ($dur * $rate), 
										'Warehouse quote ('.$dur.' '.$tm.' X '.$rate.')', $debug );
										
									insert_acc_charges( $dlid, 'STORAGEWH', $pallets, $palin, 'Per Pallet Cost In:', $debug );
									insert_acc_charges( $dlid, 'STORAGEWH', $pallets, $palout, 'Per Pallet Cost Out:', $debug );
									
								} else if( $OP_CODE == 'LEASE_RENT' ) {
									if( ! empty($fields['CD_80']) ) insert_custom_data( 80, $dlid, $fields['CD_80'], $debug );
									if( ! empty($fields['CD_82']) ) insert_custom_data( 82, $dlid, $fields['CD_82'], $debug );
									$rate = $fields['RATE'];
									$delivery = $fields['LSDELIVERY'];
									$pickup = $fields['LSPICKUP'];

									if( $debug ) {
										echo "<pre>rate, delivery, pickup:\n";
										var_dump($rate, $delivery, $pickup);
										echo "</pre>";
									}
									
									if( $fields['CD_82'] == 'Weekly' ) {
										$tm = 'WK';
									} else {
										$tm = 'M';
									}
									
									insert_into_tldtl2( $dlid, 1, $tm, 1, $rate, 
										'Lease/Rent quote (per '.$tm.')', $debug );
									
									insert_acc_charges( $dlid, 'TRL LEASE', 1, $delivery, 'Delivery Charges:', $debug );
									insert_acc_charges( $dlid, 'TRL LEASE', 1, $pickup, 'Pick-Up Charges:', $debug );
								}
								
							}
							
							$query_string = "CALL ".$stc_schema.".UPDATE_TLORDER_SUMMARY( $dlid )";
									
							if( $debug ) echo "<p>using query_string = $query_string</p>";
							
							$response3 = send_odbc_query( $query_string, $stc_database, $debug );
							
							$query_string = "CALL ".$stc_schema.".SAVE_TLORDER( $dlid )";
									
							if( $debug ) echo "<p>using query_string = $query_string</p>";
							
							$response3 = send_odbc_query( $query_string, $stc_database, $debug );
							

							$response = array();
							$response['OUTCOME'] = 'OK';
							$response['DLID'] = $dlid;
							
							if( $debug ) {
								echo "<pre>";
								var_dump($response);
								echo "</pre>";
								
							} else {
								echo encryptData(json_encode( $response ));
							}
						
						} else {
							if( $debug ) echo "<p>Error - insert_into_odrstat failed. $last_odbc_error</p>";
							else echo error_response( "NOT OK", "insert_into_odrstat failed: " . $last_odbc_error, $debug );
						}

					} else {
						if( $debug ) echo "<p>Error - insert_into_tlorder failed. $last_odbc_error</p>";
						else echo error_response( "NOT OK", "insert_into_tlorder failed: " . $last_odbc_error, $debug );
					}
				}

				break;

			case 'QUOTE1A':		// !QUOTE1A - Fetch details from step 1
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT T.BILL_NUMBER, T.DETAIL_LINE_ID, T.CURRENT_STATUS,
						".$stc_schema.".GET_STATUS_DESC(CURRENT_STATUS) STATUS_DESC,
						T.START_ZONE, T.END_ZONE, T.CURRENT_ZONE, 
						".$stc_schema.".GET_ZONE_DESC(T.START_ZONE) START_ZDESC,
						".$stc_schema.".GET_ZONE_DESC(T.END_ZONE) END_ZDESC,
						".$stc_schema.".GET_ZONE_DESC(T.CURRENT_ZONE) CURRENT_ZDESC,
						T.CUSTOMER, T.CALLNAME, T.CALLADDR1, T.CALLADDR2, T.CALLCITY, T.CALLPROV,
						T.CALLPC, T.CALLCOUNTRY, T.CALLPHONE, T.CALLCONTACT, T.CALLPHONEEXT,
						T.CALLEMAIL,
						T.ORIGNAME, T.ORIGADDR1, T.ORIGCITY, T.ORIGPROV,
						T.DESTNAME, T.DESTADDR1, T.DESTCITY, T.DESTPROV,
						T.COMPANY_ID, (SELECT C.NAME AS COMPANY_NAME 
							FROM COMPANY_INFO_SRC C WHERE C.COMPANY_INFO_ID  = T.COMPANY_ID),
						T.SITE_ID, (SELECT S.SITE_NAME FROM SITE S WHERE S.SITE_ID = T.SITE_ID),
						T.COMMODITY,
						T.PICK_UP_BY, T.DELIVER_BY, T.PICK_UP_APPT_REQ, T.DELIVERY_APPT_REQ,
						T.ORIGIN, T.ORIGNAME, T.DESTINATION, 
						T.DESTNAME, T.BILL_TO_CODE, T.BILL_TO_NAME, T.DECLARED_VALUE, 
						T.DISTANCE, T.DISTANCE_UNITS, T.MANUAL_MILEAGE,
						T.CREATED_BY, T.CREATED_TIME, T.CURRENCY_CODE,
						T.SERVICE_LEVEL, T.REQUESTED_EQUIPMEN,
						T.PIECES, T.LENGTH_1, T.WEIGHT, 
						(SELECT DAYS(EXPIRY_DATE) - DAYS(CURRENT DATE)
								FROM QUOTE
								WHERE QUOTE_ID = T.DETAIL_LINE_ID) AS GOOD_FOR,
						(SELECT CAST(THE_NOTE AS VARCHAR(6000))
								FROM NOTES
								WHERE  LTRIM(RTRIM(CHAR(T.DETAIL_LINE_ID))) = ID_KEY
								AND PROG_TABLE = 'TLORDER'
								AND NOTE_TYPE = '".$stc_internal_note_type."')  AS QUOTE_NOTE,

						(SELECT CAST(THE_NOTE AS VARCHAR(6000))
								FROM NOTES
								WHERE  LTRIM(RTRIM(CHAR(T.DETAIL_LINE_ID))) = ID_KEY
								AND PROG_TABLE = 'TLORDER'
								AND NOTE_TYPE = '".$stc_external_note_type."')  AS QUOTE_EXT_NOTE,
						(SELECT COUNT(*) AS DETAIL_ROWS
						FROM TLDTL
						WHERE ORDER_ID = T.DETAIL_LINE_ID),

						(SELECT CODEDESC FROM SVCLEVEL WHERE CODE = T.SERVICE_LEVEL) SVCDESC,
						(SELECT CODEDESC FROM EQCLASS WHERE CODE = T.REQUESTED_EQUIPMEN) REQDESC,
						T.PICK_UP_BY_END, T.DELIVER_BY_END, T.OP_CODE, T.TARP, T.CHARGES, T.TOTAL_CHARGES,
						(SELECT Q.NUMBER_OF_USES FROM QUOTE Q
							WHERE 'Q' || Q.QUOTE_NUMBER = T.BILL_NUMBER)
						FROM TLORDER T
						WHERE DETAIL_LINE_ID = ".$cid."
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

			case 'QUOTE2B':		// !QUOTE2B - Fetch detail lines
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT ORDER_ID, SEQUENCE, REQUESTED_EQUIPMEN, COMMODITY, 
						RATE, RATE_UNITS, RATE_PER, DISTANCE,
						PALLETS, PIECES, PIECES_UNITS, WEIGHT, WEIGHT_UNITS, LENGTH_1, LENGTH_UNITS, 
						HEIGHT, HEIGHT_UNITS, WIDTH, WIDTH_UNITS, DESCRIPTION, SUB_COST, MANUAL_RATE
						FROM TLDTL
						WHERE ORDER_ID = ".$cid."
						".($SEQ <> "NONE" ? "AND SEQUENCE = ".$SEQ : "")."
						ORDER BY ORDER_ID,SEQUENCE
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

			case 'ADDDTL':		// !ADDDTL - Add TLDTL Commodity
				if( $debug ) echo "<p>ADDDTL: cid=$cid COMMODITY=$COMMODITY PALLETS=$PALLETS PIECES=$PIECES DIST=$DIST
					WEIGHT=$WEIGHT LENGTH=$LENGTH WIDTH=$WIDTH HEIGHT=$HEIGHT 
					ITU=$ITU WTU=$WTU LNU=$LNU WIU=$WIU HTU=$HTU
					RATE=$RATE RATEP=$RATEP RATEU=$RATEU</p>";
				// Validate fields
				if( $cid == "NONE" || $COST == "NONE" ||
					$COMMODITY == "NONE" || $RATE == "NONE" ) {
					
				//	$PIECES == "NONE" || $WEIGHT == "NONE" || $LENGTH == "NONE" ||
				//	$WIDTH == "NONE" || $HEIGHT == "NONE" || $RATE == "NONE" ||
				//	$ITU == "NONE" || $WTU == "NONE" || $LNU == "NONE" || $WIU == "NONE" || $HTU == "NONE" ||
				//	$REQ == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo error_response( "NOT OK", "Required fields missing or blank.", $debug );
				} else {
					if( $PALLETS == '' ) $PALLETS = 'NULL';
					if( $PIECES == '' ) $PIECES = 'NULL';
					if( $WEIGHT == '' ) $WEIGHT = 'NULL';
					if( $LENGTH == '' ) $LENGTH = 'NULL';
					if( $WIDTH == '' ) $WIDTH = 'NULL';
					if( $HEIGHT == '' ) $HEIGHT = 'NULL';
					if( $RATE == '' ) $RATE = 'NULL';
				
					// TLDTL
					$ret2 = insert_into_tldtl( $cid, $COMMODITY, $PALLETS, $PIECES, $LENGTH, $WIDTH,
								$DIST, $HEIGHT, $WEIGHT, $ITU, $LNU, $WIU, $HTU, $WTU, $RATE, $RATEP, $RATEU, $COST, $debug );
					
					if( isset($ret2) && $ret2 ) {
						// Prepare Select

						/*$query_string = "CALL ".$stc_schema.".STC_CALC_COST( ".$cid.", ".$ret2." )";
								
						if( $debug ) echo "<p>using query_string = $query_string</p>";
						
						$response4 = send_odbc_query( $query_string, $stc_database, $debug );
						*/

						$query_string = "CALL ".$stc_schema.".UPDATE_TLORDER_SUMMARY( ".$cid." )";
					
						if( $debug ) echo "<p>using query_string = $query_string</p>";
					
						$response3 = send_odbc_query( $query_string, $stc_database, $debug );
						
					
						$response = array();
						$response['OUTCOME'] = 'ADDED';
						$response['DLID'] = $cid;

						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - insert_into_tldtl failed. $last_odbc_error</p>";
						else echo error_response( "NOT OK", "insert_into_tldtl failed: " . $last_odbc_error, $debug );
					}
				}

				break;

			case 'MODDTL':		// !MODDTL - Update TLDTL Commodity
				if( $debug ) echo "<p>MODDTL: cid=$cid SEQ=$SEQ COMMODITY=$COMMODITY PALLETS=$PALLETS  PIECES=$PIECES 
					WEIGHT=$WEIGHT LENGTH=$LENGTH WIDTH=$WIDTH HEIGHT=$HEIGHT 
					ITU=$ITU WTU=$WTU LNU=$LNU WIU=$WIU HTU=$HTU
					RATE=$RATE</p>";
				// Validate fields
				if( $cid == "NONE" || $SEQ == "NONE" || $COST == "NONE" ||
					$PIECES == "NONE" || $WEIGHT == "NONE" || $LENGTH == "NONE" ||
					$WIDTH == "NONE" || $HEIGHT == "NONE" || $RATE == "NONE" ||
					$ITU == "NONE" || $WTU == "NONE" || $LNU == "NONE" || $WIU == "NONE" || 
					$HTU == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo error_response( "NOT OK", "Required fields missing or blank.", $debug );
				} else {
					if( $PALLETS == '' ) $PALLETS = 'NULL';
				
					$cdesc = fetch_cdesc( $COMMODITY, $debug );
					if( $RATE == '' ) $RATE = 'NULL';

				
					// Prepare Select
					$query_string = "UPDATE TLDTL
						SET ".($COMMODITY == "NONE" ? '' : "COMMODITY = '".$COMMODITY."', ")."
						PALLETS = ".$PALLETS.", PIECES = ".$PIECES.", PIECES_UNITS = '".$ITU."', 
						WEIGHT = ".$WEIGHT.", WEIGHT_UNITS = '".$WTU."', 
						LENGTH_1 = ".$LENGTH.", LENGTH_UNITS = '".$LNU."', 
						HEIGHT = ".$HEIGHT.", HEIGHT_UNITS = '".$HTU."', 
						WIDTH = ".$WIDTH.", WIDTH_UNITS = '".$WIU."',
						COST = ".$COST.", SUB_COST = ".$COST.",
						".($COMMODITY == "NONE" ? '' : "DESCRIPTION = '".str_replace("'", "''", $cdesc)."', ")."
						RATE = ".$RATE.",
						RATE_UNITS = '".$RATEU."',
						RATE_PER = ".$RATEP.",
						DISTANCE = ".$DIST.", DISTANCE_UNITS = 'MI'
						WHERE ORDER_ID = ".$cid."
						AND SEQUENCE = ".$SEQ;
				
					if( $debug ) echo "<p>using query_string = $query_string</p>";
				
					$response2 = send_odbc_query( $query_string, $stc_database, $debug );
								
					if( is_array($response2) ) {
						/*$query_string = "CALL ".$stc_schema.".STC_CALC_COST( ".$cid.", ".$SEQ." )";
								
						if( $debug ) echo "<p>using query_string = $query_string</p>";
						
						$response4 = send_odbc_query( $query_string, $stc_database, $debug );
						*/

						// Prepare Select
						$query_string = "CALL ".$stc_schema.".UPDATE_TLORDER_SUMMARY( ".$cid." )";
					
						if( $debug ) echo "<p>using query_string = $query_string</p>";
					
						$response3 = send_odbc_query( $query_string, $stc_database, $debug );
					
						$response = array();
						$response['OUTCOME'] = 'CHANGED';
						$response['DLID'] = $cid;

						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo error_response( "NOT OK", "send_odbc_query failed: " . $last_odbc_error, $debug );
					}
				}

				break;

			case 'DELDTL':		// !DELDTL - Delete TLDTL Commodity
				if( $debug ) echo "<p>DELDTL: cid=$cid SEQ=$SEQ </p>";
				// Validate fields
				if( $cid == "NONE" || $SEQ == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo error_response( "NOT OK", "Required fields missing or blank.", $debug );
				} else {
				
					// Prepare Select
					$query_string = "DELETE FROM TLDTL
						WHERE ORDER_ID = ".$cid."
						AND SEQUENCE = ".$SEQ;
				
					if( $debug ) echo "<p>using query_string = $query_string</p>";
				
					$response2 = send_odbc_query( $query_string, $stc_database, $debug );
								
					if( is_array($response2) ) {
						// Prepare Select
						$query_string = "CALL ".$stc_schema.".UPDATE_TLORDER_SUMMARY( ".$cid." )";
					
						if( $debug ) echo "<p>using query_string = $query_string</p>";
					
						$response3 = send_odbc_query( $query_string, $stc_database, $debug );
					
						$response = array();
						$response['OUTCOME'] = 'DELETED';
						$response['DLID'] = $cid;

						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo error_response( "NOT OK", "send_odbc_query failed: " . $last_odbc_error, $debug );
					}
				}

				break;

			case 'ADDACC':		// !ADDACC - Add Accessorial Charge
				if( $debug ) echo "<p>ADDACC: cid=$cid </p>";
				// Validate fields
				if( $cid == "NONE" || $QTY == "NONE" ||
					$RATEP == "NONE" ) {
					
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo error_response( "NOT OK", "Required fields missing or blank.", $debug );
				} else {
					if( $RATE == '' || $RATE == 'NONE' ) $RATE = 'NULL';
					
					if( $RATEP == 'MISC2' && $THE_NOTE != 'NONE' ) {
						$notation = $THE_NOTE;
					} else {
						$query_string = "SELECT SHORT_DESC
							FROM ACHARGE_CODE ACC
							WHERE  CUSTOM_FLAG='False' 
							AND VENDOR_CODE='False' 
							AND NOT_ACTIVE='False'
							AND  ACODE_ID = '".$RATEP."'
							FOR READ ONLY
							WITH UR";
					
						$response1 = send_odbc_query( $query_string, $stc_database, $debug );
						
						$notation = '';
						if( is_array($response1) && count($response1) > 0 &&
							!empty($response1[0]['SHORT_DESC']))
							$notation = $response1[0]['SHORT_DESC'];
					}
				
					$response2 = insert_acc_charges( $cid, $RATEP, $QTY, $RATE, $notation, $debug );
					
					if( $response2 ) {
						$response = array();
						$response['OUTCOME'] = 'ADDED';
						$response['DLID'] = $cid;
						$response['ACT_ID'] = $response2;

						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - insert_acc_charges failed. $last_odbc_error</p>";
						else echo error_response( "NOT OK", "insert_into_tldtl failed: " . $last_odbc_error, $debug );
					}
				}

				break;

			case 'DELACC':		// !DELACC - Delete Accessorial Charge
				if( $debug ) echo "<p>DELACC: cid=$cid SEQ=$SEQ </p>";
				// Validate fields
				if( $cid == "NONE" || $SEQ == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo error_response( "NOT OK", "Required fields missing or blank.", $debug );
				} else {
				
					// Prepare Select
					$query_string = "DELETE FROM ACHARGE_TLORDER
						WHERE DETAIL_LINE_ID = ".$cid."
						AND ACT_ID = ".$SEQ;
				
					if( $debug ) echo "<p>using query_string = $query_string</p>";
				
					$response2 = send_odbc_query( $query_string, $stc_database, $debug );
								
					if( is_array($response2) ) {
						// Prepare Select
						$query_string = "CALL ".$stc_schema.".UPDATE_TLORDER_SUMMARY( ".$cid." )";
					
						if( $debug ) echo "<p>using query_string = $query_string</p>";
					
						$response3 = send_odbc_query( $query_string, $stc_database, $debug );
					
						$response = array();
						$response['OUTCOME'] = 'DELETED';
						$response['DLID'] = $cid;

						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo error_response( "NOT OK", "send_odbc_query failed: " . $last_odbc_error, $debug );
					}
				}

				break;

			case 'MODACC':		// !MODACC - Update Accessorial Charge
				if( $debug ) echo "<p>MODDTL: cid=$cid SEQ=$SEQ 
					RATE=$RATE RATEP=$RATEP</p>";
				// Validate fields
				if( $cid == "NONE" || $SEQ == "NONE" || $QTY == "NONE" ||
					$RATEP == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo error_response( "NOT OK", "Required fields missing or blank.", $debug );
				} else {
					if( $RATE == '' || $RATE == 'NONE' ) $RATE = 'NULL';
				
					if( $RATEP == 'MISC2' && $THE_NOTE != 'NONE' ) {
						$notation = $THE_NOTE;
					} else {
						$query_string = "SELECT SHORT_DESC
							FROM ACHARGE_CODE ACC
							WHERE  CUSTOM_FLAG='False' 
							AND VENDOR_CODE='False' 
							AND NOT_ACTIVE='False'
							AND  ACODE_ID = '".$RATEP."'
							FOR READ ONLY
							WITH UR";
					
						$response1 = send_odbc_query( $query_string, $stc_database, $debug );
						
						$notation = '';
						if( is_array($response1) && count($response1) > 0 &&
							!empty($response1[0]['SHORT_DESC']))
							$notation = $response1[0]['SHORT_DESC'];
					}

					// Prepare Select
					$query_string = "UPDATE ACHARGE_TLORDER
						SET ACODE_ID = '".$RATEP."', REQUESTED_CODE = '".$RATEP."',
						IS_MANUAL = 'True', QUANTITY = $QTY, ACTUAL_QUANTITY = $QTY,
						RATE = $RATE, CHARGE_AMOUNT = ".($QTY * $RATE).",
						AUTO_ASSIGNED = 'False', NOTATION = '".$notation."'
						
						WHERE DETAIL_LINE_ID = ".$cid."
						AND ACT_ID = ".$SEQ;
				
					if( $debug ) echo "<p>using query_string = $query_string</p>";
				
					$response2 = send_odbc_query( $query_string, $stc_database, $debug );
								
					if( is_array($response2) ) {
						// Prepare Select
						$query_string = "CALL ".$stc_schema.".UPDATE_TLORDER_SUMMARY( ".$cid." )";
					
						if( $debug ) echo "<p>using query_string = $query_string</p>";
					
						$response3 = send_odbc_query( $query_string, $stc_database, $debug );
					
						$response = array();
						$response['OUTCOME'] = 'CHANGED';
						$response['DLID'] = $cid;

						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo error_response( "NOT OK", "send_odbc_query failed: " . $last_odbc_error, $debug );
					}
				}

				break;

			case 'QUOTE3':	// !QUOTE3 - Complete & Post Quote
				if( $debug ) echo "<p>QUOTE3: cid=$cid DECLARED=$DECLARED GOODFOR=$GOODFOR uid=$uid 
					THE_NOTE=$THE_NOTE EXT_NOTE=$EXT_NOTE</p>";
				
				// Validate fields
				if( $cid == "NONE" || $DECLARED == "NONE" || $GOODFOR == "NONE" || $uid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo error_response( "NOT OK", "Required fields missing or blank.", $debug );
				} else {
				
					$query_string = "SELECT *
						FROM QUOTE
						WHERE QUOTE_ID = ".$cid;
				
					if( $debug ) echo "<p>using query_string = $query_string</p>";
				
					$response = send_odbc_query( $query_string, $stc_database, $debug );

					if( is_array($response) && count($response) > 0 ) {
						echo error_response( "NOT OK", "Quote already created.", $debug );
					} else {
					
						$ins_date = date('Y-m-d H:i:s', strtotime('now - 1 hour'));
				
						if( $THE_NOTE <> "NONE" )
							insert_into_notes( $cid, $stc_internal_note_type, $ins_date, $THE_NOTE, $debug );
						if( $EXT_NOTE <> "NONE" )
							insert_into_notes( $cid, $stc_external_note_type, $ins_date, $EXT_NOTE, $debug );
	
						$query_string = "UPDATE TLORDER
							SET DECLARED_VALUE = ".$DECLARED."
							WHERE DETAIL_LINE_ID = ".$cid;
					
						if( $debug ) echo "<p>using query_string = $query_string</p>";
					
						$response1 = send_odbc_query( $query_string, $stc_database, $debug );
									
						if( is_array($response1) ) {
	
							$query_string = "SELECT CUSTOMER, COMMODITY
								FROM TLORDER
								WHERE DETAIL_LINE_ID = ".$cid;
						
							if( $debug ) echo "<p>using query_string = $query_string</p>";
						
							$response2 = send_odbc_query( $query_string, $stc_database, $debug );
										
							if( is_array($response2) ) {
								$CALLER = $response2[0]['CUSTOMER'];
								$COMMODITY = $response2[0]['COMMODITY'];
							
								// post quote.
								$qbill = post_quote( $cid, $CALLER, $COMMODITY, $ins_date, $GOODFOR, $uid, $USES, $debug );

								if( isset($qbill) && $qbill ) {
									$response = array();
									$response['OUTCOME'] = 'OK';
									$response['QUOTE'] = $qbill;
									$response['DLID'] = $dlid;
									$response['CALLER'] = $CALLER;
		
									if( $debug ) {
										echo "<pre>";
										var_dump($response);
										echo "</pre>";
										
									} else {
										echo encryptData(json_encode( $response ));
									}
								
								} else {
									if( $debug ) echo "<p>Error - post_quote failed. $last_odbc_error</p>";
									else echo error_response( "NOT OK", "post_quote failed: " . $last_odbc_error, $debug );
								}
	
							} else {
								if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
								else echo error_response( "NOT OK", "send_odbc_query 2 failed: " . $last_odbc_error, $debug );
							}
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
							else echo error_response( "NOT OK", "send_odbc_query 1 failed: " . $last_odbc_error, $debug );
						}
					}
				}

				break;

			case 'QUOTE3A':	// !QUOTE3A - Update Quote
				if( $debug ) echo "<p>QUOTE3: cid=$cid DECLARED=$DECLARED GOODFOR=$GOODFOR uid=$uid 
					THE_NOTE=$THE_NOTE EXT_NOTE=$EXT_NOTE</p>";
				
				// Validate fields
				if( $cid == "NONE" || $DECLARED == "NONE" || $GOODFOR == "NONE" || $uid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo error_response( "NOT OK", "Required fields missing or blank.", $debug );
				} else {
				
					
					$ins_date = date('Y-m-d H:i:s');
					$exp_date = date('Y-m-d H:i:s', time() + ($GOODFOR * 24 * 60 * 60));

					// Replace note if needed
					if( $THE_NOTE <> "NONE" )
						insert_into_notes( $cid, $stc_internal_note_type, $ins_date, $THE_NOTE, $debug );
					if( $EXT_NOTE <> "NONE" )
						insert_into_notes( $cid, $stc_external_note_type, $ins_date, $EXT_NOTE, $debug );

					$query_string = "UPDATE TLORDER
						SET DECLARED_VALUE = ".$DECLARED."
						WHERE DETAIL_LINE_ID = ".$cid;
				
					if( $debug ) echo "<p>using query_string = $query_string</p>";
				
					$response1 = send_odbc_query( $query_string, $stc_database, $debug );
								
					if( is_array($response1) ) {

						$query_string = "UPDATE QUOTE
							SET EXPIRY_DATE = '".$exp_date."',
								NUMBER_OF_USES = ".$USES."
							WHERE QUOTE_ID = ".$cid;
					
						if( $debug ) echo "<p>using query_string = $query_string</p>";
					
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( is_array($response2) ) {			
							$query_string = "SELECT CUSTOMER, BILL_NUMBER, CURRENT_STATUS, START_ZONE
								FROM TLORDER
								WHERE DETAIL_LINE_ID = ".$cid."
								FOR READ ONLY
								WITH UR";
						
							if( $debug ) echo "<p>using query_string = $query_string</p>";
						
							$response3 = send_odbc_query( $query_string, $stc_database, $debug );
							
							if( is_array($response3) ) {	
								$ret = insert_into_odrstat( $cid, $ins_date, $uid, 
									$response3[0]['START_ZONE'], 
									'CHANGED_QU', 
									"Edited in Webtools", $debug );		
								$response = array();
								$response['OUTCOME'] = 'OK';
								$response['QUOTE'] = $response3[0]['BILL_NUMBER'];
								$response['CALLER'] = $response3[0]['CUSTOMER'];
								$response['DLID'] = $cid;
	
								if( $debug ) {
									echo "<pre>";
									var_dump($response);
									echo "</pre>";
									
								} else {
									echo encryptData(json_encode( $response ));
								}
							} else {
								if( $debug ) echo "<p>Error - send_odbc_query 3 failed. $last_odbc_error</p>";
								else echo error_response( "NOT OK", "send_odbc_query 3 failed: " . $last_odbc_error, $debug );
							}
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
							else echo error_response( "NOT OK", "send_odbc_query 2 failed: " . $last_odbc_error, $debug );
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
						else echo error_response( "NOT OK", "send_odbc_query 1 failed: " . $last_odbc_error, $debug );
					}
				}

				break;

			case 'CANCEL':	// !CANCEL - Cancel a quote
				// Validate fields
				if( $FB == "NONE" || $uid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT DETAIL_LINE_ID, CURRENT_ZONE
						FROM TLORDER
						WHERE BILL_NUMBER = '".$FB."'
						AND CURRENT_STATUS <> 'CANCL'
						FOR READ ONLY
						WITH UR";
					
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) && count($response) == 1 ) {
						$dlid = $response[0]['DETAIL_LINE_ID'];
						$zone1 = $response[0]['CURRENT_ZONE'];
						$cancel_date = date('Y-m-d H:i:s');
						
						$query_string = "UPDATE TLORDER
							SET CURRENT_STATUS = 'CANCL',
							MODIFIED_TIME = '".$cancel_date."'
							WHERE BILL_NUMBER = '".$FB."'
							AND CURRENT_STATUS <> 'CANCL'";
						
						if( $debug ) echo "<p>using query_string = $query_string</p>";
				
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						if( is_array($response2) ) {
							$ret = insert_into_odrstat( $dlid, $cancel_date, $uid, $zone1, 'CANCL',
								'Cancelled in Webtools', $debug );
							if( isset($ret) && $ret ) {
								if( $debug ) echo "<p>DONE: Quote $FB cancelled</p>";
								else echo encryptData("DONE");							
							} else {
								if( $debug ) echo "<p>Error - insert_into_odrstat failed.</p>";
								else echo encryptData("NOT OK: insert_into_odrstat failed." . $last_odbc_error);
							}
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query2 failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: send_odbc_query2 failed: " . $last_odbc_error);
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}

				break;

			case 'GETWHINFO':	// !GETWHINFO - Get info for Warehousing

				$query_string1 = "SELECT CUSTDEF_ID ,LABEL_NAME, CONTROL_TYPE
					FROM CUSTOM_DEFS WHERE CUSTDEF_ID IN (77,78,79)
					ORDER BY CUSTDEF_ID ASC
					FOR READ ONLY
					WITH UR";
			
				if( $debug ) {
					echo "<p>using query_string1 = </p>
					<pre>".
					htmlentities($query_string1).
					"</pre>";
				}
				
				$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
				
				if( $debug ) {
					echo "<pre>Response1\n";
					var_dump($response1);
					echo "</pre>";
				}
					
				if( is_array($response1) ) {
					$query_string2 = "SELECT * FROM CUSTOM_LIST_VALUES
						WHERE CUSTDEF_ID IN (77,78,79)
						FOR READ ONLY
						WITH UR";
				
					if( $debug ) {
						echo "<p>using query_string2 = </p>
						<pre>".
						htmlentities($query_string2).
						"</pre>";
					}
				
					$response2 = send_odbc_query( $query_string2, $stc_database, $debug );
				
					if( $debug ) {
						echo "<pre>Response2\n";
						var_dump($response2);
						echo "</pre>";
					}
					
					if( is_array($response2) ) {
						$response = [
							'VARS' => $response1,
							'ENUM' => $response2
						];

						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						echo error_response( "NOT OK", 'query2 failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'query1 failed: ' . $last_odbc_error, $debug );
				}

				break;

			case 'GETLSINFO':	// !GETLSINFO - Get info for Lease

				$query_string1 = "SELECT CUSTDEF_ID ,LABEL_NAME, CONTROL_TYPE
					FROM CUSTOM_DEFS WHERE CUSTDEF_ID IN (80,82)
					ORDER BY CUSTDEF_ID ASC
					FOR READ ONLY
					WITH UR";
			
				if( $debug ) {
					echo "<p>using query_string1 = </p>
					<pre>".
					htmlentities($query_string1).
					"</pre>";
				}
				
				$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
				
				if( $debug ) {
					echo "<pre>Response1\n";
					var_dump($response1);
					echo "</pre>";
				}
					
				if( is_array($response1) ) {
					$query_string2 = "SELECT * FROM CUSTOM_LIST_VALUES
						WHERE CUSTDEF_ID IN (80,82)
						FOR READ ONLY
						WITH UR";
				
					if( $debug ) {
						echo "<p>using query_string2 = </p>
						<pre>".
						htmlentities($query_string2).
						"</pre>";
					}
				
					$response2 = send_odbc_query( $query_string2, $stc_database, $debug );
				
					if( $debug ) {
						echo "<pre>Response2\n";
						var_dump($response2);
						echo "</pre>";
					}
					
					if( is_array($response2) ) {
						$response = [
							'VARS' => $response1,
							'ENUM' => $response2
						];

						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						echo error_response( "NOT OK", 'query2 failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'query1 failed: ' . $last_odbc_error, $debug );
				}

				break;

			case 'GETCD':	// !GETCD - SCR# 824 - Get custom data fields
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
					$query_string1 = "select CD.LABEL_NAME, CD.CONTROL_TYPE, DATA, DATE
						FROM CUSTOM_DATA D, CUSTOM_DEFS CD
						WHERE D.CUSTDEF_ID = CD.CUSTDEF_ID
						AND D.CUSTDEF_ID IN (".($RANGE == 'NONE' ? '77,78,79,80,82' : $RANGE).")
						AND SRC_TABLE_KEY_INT = $cid
						ORDER BY D.CUSTDEF_ID ASC
						FOR READ ONLY
						WITH UR";
				
					if( $debug ) {
						echo "<p>using query_string1 = </p>
						<pre>".
						htmlentities($query_string1).
						"</pre>";
					}
					
					$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
					
					if( $debug ) {
						echo "<pre>Response1\n";
						var_dump($response1);
						echo "</pre>";
					}
						
					if( is_array($response1) ) {	
						if( $debug ) {
							echo "<pre>";
							var_dump($response1);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response1 ));
						}
					} else {
						echo error_response( "NOT OK", 'query1 failed: ' . $last_odbc_error, $debug );
					}
				}

				break;

			case 'GETACC':	// !GETACC - SCR# 824 - Get Accessorial Charges
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
					$query_string1 = "SELECT ACT_ID, ACODE_ID, NOTATION, QUANTITY, RATE, CHARGE_AMOUNT
						FROM ACHARGE_TLORDER
						WHERE DETAIL_LINE_ID = $cid
						".($SEQ <> "NONE" ? "AND ACT_ID = ".$SEQ : "")."
						FOR READ ONLY
						WITH UR";
				
					if( $debug ) {
						echo "<p>using query_string1 = </p>
						<pre>".
						htmlentities($query_string1).
						"</pre>";
					}
					
					$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
					
					if( $debug ) {
						echo "<pre>Response1\n";
						var_dump($response1);
						echo "</pre>";
					}
						
					if( is_array($response1) ) {	
						if( $debug ) {
							echo "<pre>";
							var_dump($response1);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response1 ));
						}
					} else {
						echo error_response( "NOT OK", 'query1 failed: ' . $last_odbc_error, $debug );
					}
				}

				break;

			case 'SETDIST':	// !SETDIST - SCR# 824 - Set distance / manual miles
				// Validate fields
				if( $cid == "NONE" || $MM == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
					$query_stringp = "SELECT START_ZONE, END_ZONE, MANUAL_MILEAGE
						FROM TLORDER WHERE DETAIL_LINE_ID = $cid
						FOR READ ONLY
						WITH UR";
						
					if( $debug ) {
						echo "<p>using query_stringp = </p>
						<pre>".
						htmlentities($query_stringp).
						"</pre>";
					}
							
					$previous = send_odbc_query( $query_stringp, $stc_database, $debug );
					
					if( $debug ) {
						echo "<pre>previous\n";
						var_dump($previous);
						echo "</pre>";
					}
							
					if( is_array($previous) && count($previous) == 1 ) {
						$prev_mm = $previous[0]['MANUAL_MILEAGE'];
						$start = $previous[0]['START_ZONE'];
						$end = $previous[0]['END_ZONE'];
					}
					
					if( $prev_mm != $MM ||
						($MM == 'True' && $DIST != "NONE") ) {
						
						$query_string1 = "UPDATE TLORDER
							SET MANUAL_MILEAGE = '".$MM."'".
							($MM == 'True' && $DIST != "NONE" ? ", DISTANCE = $DIST" : "" )." 
							WHERE DETAIL_LINE_ID = $cid";
					
						if( $debug ) {
							echo "<p>using query_string1 = </p>
							<pre>".
							htmlentities($query_string1).
							"</pre>";
						}
						
						$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
						
						if( $debug ) {
							echo "<pre>Response1\n";
							var_dump($response1);
							echo "</pre>";
						}
							
						if( is_array($response1) ) {
							if( $MM == 'False' ) {		//! Have to force re-calc
								
								$query_string3 = "UPDATE TLORDER
									SET START_ZONE = '".$end."', END_ZONE = '".$start."'
									WHERE DETAIL_LINE_ID = $cid;
									
									UPDATE TLORDER
									SET START_ZONE = '".$start."', END_ZONE = '".$end."'
									WHERE DETAIL_LINE_ID = $cid;";
								
								if( $debug ) {
									echo "<p>using query_string3 = </p>
									<pre>".
									htmlentities($query_string3).
									"</pre>";
								}
								
								$response3 = send_odbc_query( $query_string3, $stc_database, $debug );
								
								if( $debug ) {
									echo "<pre>Response3\n";
									var_dump($response3);
									echo "</pre>";
								}
							}
						}
						
						if( $debug ) {
							echo "<pre>";
							var_dump($response1);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response1 ));
						}
					} else {
						echo error_response( "NOT OK", 'query1 failed: ' . $last_odbc_error, $debug );
					}
				}

				break;

			case 'RUNITS':	// !RUNITS - List of RATE_UNITS
				$dbconn = new stc_db( $stc_database, $debug );
				
				if( $dbconn ) {
					$query_string = "SELECT DISTINCT RATE_UNITS, NAME
						FROM TLDTL, UNIT
						WHERE COALESCE(RATE_UNITS, '') <> ''
						AND RATE_UNITS = UNIT_SYMBOL
						FOR READ ONLY
						WITH UR";
				
					$response = $dbconn->get_multiple_rows( $query_string );
					
					if( is_array($response) ) {
						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						echo error_response( "NOT OK", 'get_multiple_rows failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'stc_db failed: ' . $last_odbc_error, $debug );
				}

				break;

			case 'ACCCODES':	// !ACCCODES - List of Accessorial Charges
				$dbconn = new stc_db( $stc_database, $debug );
				
				if( $dbconn ) {
					$query_string = "SELECT DISTINCT ACODE_ID, SHORT_DESC
						FROM ACHARGE_CODE ACC
						WHERE  CUSTOM_FLAG='False' 
						AND VENDOR_CODE='False' 
						AND NOT_ACTIVE='False'
						AND USER1 = 'STC'
						
						ORDER BY ACODE_ID ASC, SHORT_DESC ASC
						FOR READ ONLY
						WITH UR";
				
					$response = $dbconn->get_multiple_rows( $query_string );
					
					if( is_array($response) ) {
						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response,  JSON_INVALID_UTF8_IGNORE ));
						}
					} else {
						echo error_response( "NOT OK", 'get_multiple_rows failed: ' . $last_odbc_error, $debug );
					}
				} else {
					echo error_response( "NOT OK", 'stc_db failed: ' . $last_odbc_error, $debug );
				}

				break;

/*
update tlorder
set MANUAL_MILEAGE = 'False', END_ZONE = END_ZONE
where detail_line_id = 418764;

 CALL INS_TLORDER_MILES(N.EXTRA_STOPS, N.DLID_QUOTE, N.DETAIL_LINE_ID, N.BILL_TO_CODE,
      N.START_ZONE, N.END_ZONE, N.PICKUP_TERMINAL, N.DELIVERY_TERMINAL, N.ORIGADDR1, N.ORIGADDR2,
      N.ORIGPC, N.ORIGPROV, N.ORIGCITY, N.DESTADDR1, N.DESTADDR2, N.DESTPC, N.DESTPROV, N.DESTCITY,
      N.MANUAL_MILEAGE);
*/
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

