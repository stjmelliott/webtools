<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );

	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman74";
	$option		= "NONE";
	$cid		= "NONE";
	$cnm		= "NONE";
	$own		= "NONE";
	$days		= "90";

	$UNIT_ID				= "NONE";
	$LOCTYPE				= "NONE";
	$ZONE1					= "NONE";
	$ZONE2					= "NONE";
	$YR						= "NONE";
	$MN						= "NONE";
	$TRIP_NUMBER			= "NONE";
	$COMMODITY				= "NONE";
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
		} else if( $key == "UNIT_ID" ) {
			$UNIT_ID = $value;
		} else if( $key == "LOCTYPE" ) {
			$LOCTYPE = $value;
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
		} else if( $key == "YR" ) {
			$YR = $value;
		} else if( $key == "MN" ) {
			$MN = $value;
		} else if( $key == "TRIP_NUMBER" ) {
			$TRIP_NUMBER = $value;
		} else if( $key == "COMMODITY" ) {
			$COMMODITY = $value;
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
<title>STC CRM Backend - RM Functions</title>
</head>

<body>
<?
	}
	
	if( $password == $valid_pw ) {
		
		switch (strtoupper($option)) {

			case 'TRIPS':  // List power units
				
				// Prepare Select
				$query_string = "SELECT TRIP.TRIP_NUMBER, TRIP.STATUS, TRIP.LS_NUM_LEGS NUM_LEGS,
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
					(SELECT COUNT(ITRIPTLO.BILL_NUMBER) FROM ITRIPTLO, TLORDER
						WHERE ITRIPTLO.TRIP_NUMBER = TRIP.TRIP_NUMBER
						AND	ITRIPTLO.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID
						AND TLORDER.extra_stops <> 'Child' ) BILLS
					
					FROM TRIP
					WHERE TRIP.STATUS IN ('ASSGN', 'BOOKED', 'DISP')
					AND ACTIVE_REC = 'True'
					AND DELIVER_BY > current_date - 90 days";
				
				if( $TRIP_NUMBER <> "NONE" )
					$query_string .= " AND TRIP.TRIP_NUMBER = '".$TRIP_NUMBER."'";
					
				$query_string .= "	ORDER BY TRIP.TRIP_NUMBER ASC

					for read only
					with ur";
										
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

			case 'COMM':  // Commodity
				
				// Prepare Select
				$query_string = "SELECT CODEDESC, TEMPERATURE FROM CMMCLASS 
					WHERE CODE = '".$COMMODITY."'";
					  
				$query_string .= " for read only
					with ur";
										
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

			case 'BILLS':  // Frieght Bills
				if( $TRIP_NUMBER <> "NONE" )
					$trip_extra = "
					(CASE WHEN (SELECT MIN(J.TRIP_NUMBER) FROM ITRIPTLO J
						WHERE J.DETAIL_LINE_ID = T.DETAIL_LINE_ID) = $TRIP_NUMBER THEN
						'True' ELSE 'False' END) AS FIRST_TRIP,
					(CASE WHEN (SELECT MAX(J.TRIP_NUMBER) FROM ITRIPTLO J
						WHERE J.DETAIL_LINE_ID = T.DETAIL_LINE_ID) = $TRIP_NUMBER THEN
						'True' ELSE 'False' END) AS LAST_TRIP,
					(SELECT MAX(J.TRIP_NUMBER) FROM ITRIPTLO J
						WHERE J.DETAIL_LINE_ID = T.DETAIL_LINE_ID
						AND J.TRIP_NUMBER < $TRIP_NUMBER) AS PREV_TRIP,
					(SELECT MIN(J.TRIP_NUMBER) FROM ITRIPTLO J
						WHERE J.DETAIL_LINE_ID = T.DETAIL_LINE_ID
						AND J.TRIP_NUMBER > $TRIP_NUMBER) AS NEXT_TRIP,";
				else
					$trip_extra = "";
				
				// Prepare Select
				$query_string = "SELECT T.BILL_NUMBER, T.CURRENT_STATUS, 
					".$stc_schema.".GET_STATUS_DESC(T.CURRENT_STATUS) STATUS_DESC,
					T.BILL_TO_CODE, T.BILL_TO_NAME, T.TOTAL_CHARGES, T.INT_PAYABLE_AMT, 
					T.OP_CODE,
					(SELECT DESCRIPTION
						FROM OPERATION_CODES C
						WHERE C.OP_CODE = T.OP_CODE) AS OP_CODE_DESC,
					T.SITE_ID, T.COMPANY_ID,
					(SELECT S.SITE_NAME FROM SITE S WHERE S.SITE_ID = T.SITE_ID),
					T.CURRENCY_CODE, T.ROUTE_DESIGNATION, T.ROUTE_SEQUENCE,
					T.SERVICE_LEVEL, T.INTERFACE_STATUS_F, T.NEXT_TERMINAL_ZONE,
					T.EXTRA_STOPS, T.APPROVED,
					T.COMMODITY, 
					(SELECT CODEDESC FROM CMMCLASS 
						WHERE CODE = T.COMMODITY) AS COMM_DESC,
					T.START_ZONE, T.END_ZONE, T.CURRENT_ZONE, 
					".$stc_schema.".GET_ZONE_DESC(T.START_ZONE) START_ZDESC,
					".$stc_schema.".GET_ZONE_DESC(T.END_ZONE) END_ZDESC,
					".$stc_schema.".GET_ZONE_DESC(T.CURRENT_ZONE) CURRENT_ZDESC,
					T.ROLLUP_WEIGHT, T.ROLLUP_LENGTH_1,
					".$stc_schema.".TRACE_SUB_IL(T.DETAIL_LINE_ID) INTERLINER_ID,
					".$stc_schema.".TRACE_SUB_IL_NAME(T.DETAIL_LINE_ID) INTERLINER_NAME,
					".$stc_schema.".TRACE_SUB_IL_PHONE(T.DETAIL_LINE_ID) INTERLINER_PHONE,
					".$trip_extra."

					(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = T.START_ZONE) AS START_LAT,
					(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = T.START_ZONE) AS START_LONG,
					(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = T.END_ZONE) AS END_LAT,
					(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = T.END_ZONE) AS END_LONG,
					(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = T.CURRENT_ZONE) AS CURRENT_LAT,
					(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = T.CURRENT_ZONE) AS CURRENT_LONG
					FROM ITRIPTLO I, TLORDER T
					WHERE I.DETAIL_LINE_ID = T.DETAIL_LINE_ID
					
					AND I.TRIP_NUMBER = ".$TRIP_NUMBER;
					// and T.extra_stops <> 'Child'
					  
				$query_string .= " for read only
					with ur";
										
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

			case 'FUEL':  // Maintenance history
				
				// Prepare Select
				$query_string = "SELECT ROUND(MIN(RATE_RFUEL),2) MIN_FUEL, ROUND(AVG(RATE_RFUEL),2) AVG_FUEL,
					ROUND(MAX(RATE_RFUEL),2) MAX_FUEL, COUNT(RATE_RFUEL) DATA_POINTS
					FROM FT_FUEL
					WHERE DATETIME > CURRENT DATE - 7 DAYS
					AND RATE_RFUEL > 0";
					  
				$query_string .= " for read only
					with ur";
										
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

			case 'INT':  // List of Interliners
				
				// Prepare Select
				$query_string = "SELECT INTERLINER_ID,
					(SELECT NAME FROM VENDOR WHERE INTERLINER_ID = VENDOR_ID) NAME,
					COUNT(*) ITEMS, 
					ROUND(SUM(T.TOTAL_CHARGES),0) TOTAL_CHARGES, ROUND(SUM(T.INT_PAYABLE_AMT),0) INT_PAYABLE_AMT,
					ROUND((SUM(T.TOTAL_CHARGES) - SUM(T.INT_PAYABLE_AMT)),0) MARGIN,
					ROUND((SUM(T.TOTAL_CHARGES) - SUM(T.INT_PAYABLE_AMT)) / COUNT(*),0) AVE_MARGIN
					FROM TLORDER T, ORDER_INTERLINER I
					WHERE INTERLINER_ID <> ''
					AND I.DETAIL_LINE_ID = T.DETAIL_LINE_ID
					and t.extra_stops <> 'Child'
					and T.DOCUMENT_TYPE = 'INVOICE'
					AND UPDATED_DATE > CURRENT TIMESTAMP - 1 YEAR
					GROUP by INTERLINER_ID
					ORDER BY 3 DESC
					FETCH FIRST 50 ROWS ONLY
					for read only
					with ur";
										
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

			case 'HRS':  // Get hours for trip, based on last bill
				
				// Prepare Select
				$query_string = "SELECT ILD_TRIP_NUMBER TRIP_NUMBER, PU, DB,
					INT(((DAYS(DB) - DAYS(PU)) * 86400 +
					(MIDNIGHT_SECONDS(DB) - MIDNIGHT_SECONDS(PU))) / 3600) AS HRS,
					DAYS(DB) - DAYS(PU) + 1 AS DYS
					FROM
					(SELECT ILD_TRIP_NUMBER,
					MIN(TLORDER.PICK_UP_BY) PU, MAX(TLORDER.DELIVER_BY) DB
					
					FROM ILEGDTL, LEGSUM, ITRIPTLO, TLORDER
					WHERE ILD_RES_ID = TLORDER.BILL_NUMBER
					and ILD_RES_TYPE = 'F'
					AND ITRIPTLO.TRIP_NUMBER = LEGSUM.LS_TRIP_NUMBER
					AND LS_TRIP_NUMBER = ILD_TRIP_NUMBER
					AND ILD_LEG_ID = LS_LEG_ID
					AND ITRIPTLO.BILL_NUMBER <> 'NA'
					AND LS_TRIP_NUMBER = '".$TRIP_NUMBER."'
					GROUP BY ILD_TRIP_NUMBER)";
					  
				$query_string .= " for read only
					with ur";
										
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

