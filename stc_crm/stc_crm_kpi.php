<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );
set_time_limit(0);
ini_set('memory_limit', '1024M');

function get_option( $option, $debug ) {
	global $stc_database;

	// Prepare Select
	$query_string = "SELECT * FROM CONFIG
		WHERE PROG_NAME = 'STRONGTOWER.EXE'
		AND THE_OPTION = '".$option."'
		FOR READ ONLY
		WITH UR";
	
	if( $debug ) {
		echo "<p>using query_string = </p>
		<pre>";
		var_dump($query_string);
		echo "</pre>";
	}

	$response = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( is_array($response) && count($response) == 1 ) {
		if( $debug ) echo "<p>Success</p>";
		return str_replace("\'", "'", $response[0]['THE_VALUE']);
	} else {
		if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
		return "";
	}
}

function get_date( $index ) {
	if( $index < -1 )
		$ans = "(DATE(RTRIM(CHAR(YEAR(CURRENT DATE))) || '-' || RTRIM(CHAR(MONTH(CURRENT DATE))) || '-01') ".($index+1)." month - 1 day)";
	else if( $index == -1 )
		$ans = "(DATE(RTRIM(CHAR(YEAR(CURRENT DATE))) || '-' || RTRIM(CHAR(MONTH(CURRENT DATE))) || '-01') - 1 day)";
	else
		$ans = "CURRENT DATE";

	return $ans;
}

function insert_into_odrstat( $dlid, $change_date, $updated_by, $start_zone, 
	$status, $stat_comment, $debug ) {
	global $stc_database,$stc_schema;
	
	date_default_timezone_set("America/Detroit");

	$ins_date = date('Y-m-d H:i:s');


	// PREVENT KEY VIOL, AND ALLOW NEW RECORD'S FIELDS TO TAKE OVER
	//$query_string = "DELETE FROM ODRSTAT
    //	WHERE ORDER_ID = ".$dlid."
	//	AND CHANGED = '".$ins_date."'
	//	AND STATUS_CODE = '".$status."'";

	//if( $debug ) echo "<p>using query_string = $query_string</p>";
	
	//$response0 = send_odbc_query( $query_string, $stc_database, $debug );

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
			".$dlid.", '".$change_date."', '".$status."', LEFT('".$stat_comment."', 80), '".$updated_by."',
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

function get_column_types( $table = 'TLORDER' ) {
	global $stc_database,$stc_schema;
	$result = false;
	
	if( $debug ) echo "<p>get_column_types: $table</p>";
	//!Get table structure
	$query_string = "SELECT
		TRIM(SUBSTR(COLNAME, 1, 50)) COLNAME,
		TRIM(SUBSTR(TYPENAME, 1, 40)) TYPENAME,
		LENGTH,
		SCALE,
		SUBSTR(DEFAULT, 1, 30) DEFAULT,
		NULLS
		FROM SYSCAT.COLUMNS
		WHERE TABNAME IS NOT NULL
		AND TABSCHEMA = '".$stc_schema."'
		AND TABNAME = '".$table."'
		ORDER BY COLNAME";
		
	$response = send_odbc_query( $query_string, $stc_database, false );

	if( is_array($response) && count($response) > 0 ) {
		$result = [];
		foreach( $response as $row ) {
			$result[$row["COLNAME"]] = $row["TYPENAME"];
		}
	}
	
	if( false && $debug ) {
		echo "<pre>get_column_types: coltypes\n";
		var_dump($result);
		echo "</pre>";
	}
	
	return $result;
}

function ins_trace( $dlid, $num, $type, $debug ) {
	global $stc_database, $stc_schema;

	// Get a unique DETAIL_LINE_ID
	$query_string = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_TRACE_ID')";

	if( $debug ) echo "<p>using query_string = $query_string</p>";
	
	$response1 = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( $debug ) {
		echo "<pre>Response1\n";
		var_dump($response1);
		echo "</pre>";
	}

	if( is_array($response1) && count($response1) == 1 && isset($response1[0]["NEXTID"])) {
		$trace_id = $response1[0]["NEXTID"];
		
		$query_string2 = "INSERT INTO TRACE (TRACE_ID, DETAIL_NUMBER, TRACE_NUMBER, TRACE_TYPE)
			VALUES( $trace_id, $dlid, '".$num."', '".$type."')";
		
		if( $debug ) echo "<p>using query_string2 = $query_string2</p>";
		
		$response2 = send_odbc_query( $query_string2, $stc_database, $debug );

		if( $debug ) {
			echo "<pre>Response2\n";
			var_dump($response2);
			echo "</pre>";
		}
	}
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

/**
 * Encode array from latin1 to utf8 recursively
 * @param $dat
 * @return array|string
 */
 function convert_from_latin1_to_utf8_recursively($dat)
   {
      if (is_string($dat)) {
         return utf8_encode($dat);
      } elseif (is_array($dat)) {
         $ret = [];
         foreach ($dat as $i => $d) $ret[ $i ] = convert_from_latin1_to_utf8_recursively($d);
         return $ret;
      } elseif (is_object($dat)) {
         foreach ($dat as $i => $d) $dat->$i = convert_from_latin1_to_utf8_recursively($d);
         return $dat;
      } else {
         return $dat;
      }
   }

	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman77";
	$option		= "NONE";
	$cid		= "NONE";
	$cnm		= "NONE";
	$own		= "NONE";
	$days		= "90";

	$UNIT_ID				= "NONE";
	$CLIENT_ID				= "NONE";
	$LOCTYPE				= "NONE";
	$ZONE1					= "NONE";
	$ZONE2					= "NONE";
	$YR						= "NONE";
	$MN						= "NONE";
	$DT						= "NONE";
	$DT2					= "NONE";
	$TRIP_NUMBER			= "NONE";
	$COMMODITY				= "NONE";
	$COMMENT				= "NONE";
	$BUSINESS_PHONE_EXT		= "NONE";
	$FAX_PHONE				= "NONE";
	$CNM					= "NONE";
	$UID					= "NONE";
	$DLID					= "NONE";
	$THE_OPTION				= "NONE";
	$THE_VALUE				= "NONE";
	$CLASS					= "NONE";
	$FLEET					= "NONE";
	$BROKER					= "NONE";
	$STRICT					= "NONE";
	$VENDOR					= "NONE";
	$VT						= "NONE";
	$FB						= "NONE";
	$CHILD					= "NONE";
	$SHUTTLE				= "BOTH";
	$CARRIER				= "BOTH";
	$TOKEN					= "NONE";
	$SERVER					= "NONE";
	$TTYPE					= "NONE";
	$TYPE					= "BOTH";
	$SITEID					= "NONE";
	$OP_CODE				= "NONE";
	$STATUS					= "NONE";
	$JOBCODE				= "NONE";
	
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
		} else if( $key == "DT" ) {
			$DT = $value;
		} else if( $key == "DT2" ) {
			$DT2 = $value;
		} else if( $key == "TRIP_NUMBER" ) {
			$TRIP_NUMBER = $value;
		} else if( $key == "COMMODITY" ) {
			$COMMODITY = $value;
		} else if( $key == "COMMENT" ) {
			$COMMENT = $value;
		} else if( $key == "BUSINESS_PHONE_EXT" ) {
			$BUSINESS_PHONE_EXT = $value;
		} else if( $key == "FAX_PHONE" ) {
			$FAX_PHONE = $value;
		} else if( $key == "UID" ) {
			$UID = $value;
		} else if( $key == "CNM" ) {
			$CNM = $value;
		} else if( $key == "DLID" ) {
			$DLID = $value;
		} else if( $key == "THE_OPTION" ) {
			$THE_OPTION = $value;
		} else if( $key == "THE_VALUE" ) {
			$THE_VALUE = $value;
		} else if( $key == "CLASS" ) {
			$CLASS = $value;
		} else if( $key == "FLEET" ) {
			$FLEET = $value;
		} else if( $key == "BROKER" ) {
			$BROKER = $value;
		} else if( $key == "STRICT" ) {
			$STRICT = $value;
		} else if( $key == "VENDOR" ) {
			$VENDOR = $value;
		} else if( $key == "VT" ) {
			$VT = $value;
		} else if( $key == "FB" ) {
			$FB = $value;
		} else if( $key == "CHILD" ) {
			$CHILD = "ON";
		} else if( $key == "SHUTTLE" ) {
			$SHUTTLE = $value;
		} else if( $key == "CARRIER" ) {
			$CARRIER = $value;
		} else if( $key == "TYPE" ) {
			$TYPE = $value;
		} else if( $key == "TOKEN" ) {
			$TOKEN = $value;
		} else if( $key == "SERVER" ) {
			$SERVER = $value;
		} else if( $key == "TTYPE" ) {
			$TTYPE = $value;
		} else if( $key == "SITEID" ) {
			$SITEID = $value;
		} else if( $key == "OP_CODE" ) {
			$OP_CODE = $value;
		} else if( $key == "STATUS" ) {
			$STATUS = $value;
		} else if( $key == "JOBCODE" ) {
			$JOBCODE = $value;
		}
	}
	
	if( $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>STC CRM Backend - KPI Functions</title>
</head>

<body>
<?
	}
	
	if( $password == $valid_pw ) {
		
		switch (strtoupper($option)) {

			case 'BRO':  // !BRO - Brokerage Management
			
				// Check the STC function STC_WORKDAYS_PERIOD_SOFAR is installed.
				$query_string = "select * from SYSCAT.FUNCTIONS
									WHERE FUNCNAME IS NOT NULL
									AND FUNCSCHEMA = '".$stc_schema."'
								AND FUNCNAME = 'STC_WORKDAYS_PERIOD_SOFAR'";
					
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string);
					echo "</pre>";
				}
				
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response) && count($response) == 1 ) {
					if( $debug ) echo "<p>Function STC_WORKDAYS_PERIOD_SOFAR installed.</p>";
					if( $DT == 'NONE' )
						$DATE_STR = 'CURRENT DATE';
					else
						$DATE_STR = "DATE('".$DT."')";

					// Prepare Select
					$query_string = "SELECT ".$stc_schema.".STC_PERIOD_NAME( ".$DATE_STR." ) AS CMONTH, ".$stc_schema.".STC_PERIOD_DAY( ".$DATE_STR."  ) AS CDAY,
					".$stc_schema.".STC_PERIOD_DAYS( ".$DATE_STR."  ) AS DAYS_MONTH,
					 CASE WHEN YEAR(CURRENT DATE) = YEAR(".$DATE_STR." )
						AND ".$stc_schema.".STC_ACCOUNTING_PERIOD(CURRENT DATE) =
						".$stc_schema.".STC_ACCOUNTING_PERIOD( ".$DATE_STR."  ) THEN
						".$stc_schema.".STC_WORKDAYS_PERIOD_SOFAR( CURRENT DATE )
					ELSE
						".$stc_schema.".STC_WORKDAYS_IN_PERIOD( ".$DATE_STR."  ) END AS BDAYS,
						".$stc_schema.".STC_WORKDAYS_IN_PERIOD( ".$DATE_STR."  ) AS BDAYS_MONTH
						FROM SYSIBM.SYSDUMMY1";
													
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string);
						echo "</pre>";
					}
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( $debug ) {
						echo "<p>response = </p>
						<pre>";
						var_dump($response);
						echo "</pre>";
					}
			
					if( is_array($response) ) {

					//	$intermodal_ops_codes = get_option( 'Intermodal_OP_Code', $debug );
					//	if( $intermodal_ops_codes == "" ) $intermodal_ops_codes = "'NONE'";

						// Prepare Select - Duncan 4/24 updated not to use USER4
						// Rolled back (TOTAL_CHARGES - INT_PAYABLE_AMT) AS USER4
						$query_string = "WITH MYDATA AS
							(SELECT BILL_DATE, ACTUAL_DELIVERY, DELIVER_BY, DELIVER_BY_END,
							  (SELECT MAX(O.CHANGED)
							  FROM ODRSTAT O
							  WHERE O.ORDER_ID = TLORDER.DETAIL_LINE_ID
							  AND O.STATUS_CODE = 'COMPLETE') COMPLETED,
							TOTAL_CHARGES, (TOTAL_CHARGES - INT_PAYABLE_AMT) AS USER4, SITE_ID, INT_PAYABLE_AMT
							
							FROM TLORDER
							WHERE CREATED_TIME > ".$DATE_STR." - 90 DAYS
							AND CURRENT_STATUS IN( 'BILLD', 'APPRVD', 'COMPLETE')
							AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
							AND BILL_NUMBER <> '0'
							AND UPPER(EXTRA_STOPS) <> 'CHILD'
							AND BILL_NUMBER NOT LIKE 'Q%'
				--			AND CURRENCY_CODE = 'BRO'
							AND EXISTS
							(SELECT INTERLINER_ID
								FROM ORDER_INTERLINER P
								WHERE P.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID)
				--			AND UPPER(OP_CODE) NOT IN (".$intermodal_ops_codes.")
				--			AND NOT (CUSTOMER = 'HOULUDMI' AND USER4 = '17000.00')
							AND CREATED_TIME = (
							  SELECT MAX(CREATED_TIME) FROM
							  TLORDER T
							  WHERE T.BILL_NUMBER = TLORDER.BILL_NUMBER
							  AND DOCUMENT_TYPE IN ('INVOICE','REBILL'))
							  
							-- Manning specific
							AND (SELECT INTERLINER_ID
								FROM ORDER_INTERLINER P
								WHERE P.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID
								ORDER BY ORDER_INTERLINER_ID DESC
								FETCH FIRST 1 ROW ONLY) != '12250'
							)
							
							SELECT DECIMAL( SUM( TOTAL_CHARGES ), 8, 2) REVENUE,
								DECIMAL( SUM( INT_PAYABLE_AMT ), 8, 2) EXPENSES,
								DECIMAL( SUM( TOTAL_CHARGES ), 8, 2) - DECIMAL( SUM( INT_PAYABLE_AMT ), 8, 2) PROFIT,
								DECIMAL( SUM( USER4 ), 8, 2) MARGIN
							FROM MYDATA
							WHERE YEAR( COALESCE(DELIVER_BY, ACTUAL_DELIVERY, COMPLETED) ) = YEAR(".$DATE_STR.")
							AND ".$stc_schema.".STC_ACCOUNTING_PERIOD( COALESCE(DELIVER_BY, ACTUAL_DELIVERY, COMPLETED) ) = ".$stc_schema.".STC_ACCOUNTING_PERIOD(".$DATE_STR.")
							FOR READ ONLY
							WITH UR";
							// AND SITE_ID = 'SITE1'
					//AND NOT LYNX.TRACE_SUB_IL(DETAIL_LINE_ID) IN ('NORNORVA', 'CSXJACFL', 'STROMANE', 'TRIFORIN')
														
						if( $debug ) {
							echo "<p>using query_string = </p>
							<pre>";
							var_dump($query_string);
							echo "</pre>";
						}
				
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						if( $debug ) {
							echo "<p>response2 = </p>
							<pre>";
							var_dump($response2);
							echo "</pre>";
						}
			
						
						if( is_array($response2) ) {
							$response[0]{'RESULTS'} = $response2;
							
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
		
				} else {
					if( $debug ) echo "<p><b>Function STC_WORKDAYS_PERIOD_SOFAR MISSING</b></p>
					<p>You need to apply the patch</p>";
					else echo encryptData("NOT OK: STC_WORKDAYS_PERIOD_SOFAR MISSING");
				}

				break;

			case 'PERIODS':  // !PERIODS - Last 12 months
			
				$now = strtotime('now');
				$prev = strtotime('now - 12 months');
				$y = intval(date('Y', $now));
				$m = intval(date('m', $now));
				
				$response = [];
				for( $mn = $m, $yr = $y; mktime(0,0,0, $mn, 1, $yr) > $prev; $mn-- ) {
					if( $mn < 1 ) {
						$mn += 12;
						$yr--;
					}
					$response[] = [
						'PERIOD' => date('Y-m-d', mktime(0,0,0, $mn, 1, $yr)),
						'LABEL' => date('F Y', mktime(0,0,0, $mn, 1, $yr))
					];
				}

			/*	$query_string = "SELECT START_DATE AS PERIOD,
					YEAR(START_DATE) || ' Period ' || LPAD(ACCOUNTING_PERIOD, 2, '0') AS LABEL
					FROM STC_ACCOUNTING_PERIODS
					WHERE CURRENT DATE > START_DATE
					AND YEAR(START_DATE) >= YEAR(CURRENT DATE) - 2
					ORDER BY START_DATE DESC
				
					FOR READ ONLY
					WITH UR";
					// AND SITE_ID = 'SITE1'
					
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string);
					echo "</pre>";
				}
				
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				*/
				
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

			case 'BILLSB':  // !BILLSB - Details Brokerage Management
			
				/*
				$intermodal_ops_codes = get_option( 'Intermodal_OP_Code', $debug );
				if( $intermodal_ops_codes == "" ) $intermodal_ops_codes = "'NONE'";
				*/
				if( $DT == 'NONE' )
					$DATE_STR = 'CURRENT DATE';
				else
					$DATE_STR = "DATE('".$DT."')";

				// Check the STC function STC_WORKDAYS_MONTH_SOFAR is installed.
				$query_string = "WITH MYDATA AS
					(SELECT TRIM(BILL_NUMBER) AS BILL_NUMBER,
					BILL_TO_NAME,
					BILL_DATE, ACTUAL_DELIVERY, DELIVER_BY, DELIVER_BY_END,
					  (SELECT MAX(O.CHANGED)
					  FROM ODRSTAT O
					  WHERE O.ORDER_ID = TLORDER.DETAIL_LINE_ID
					  AND O.STATUS_CODE = 'COMPLETE') COMPLETED,
					TOTAL_CHARGES,
					INT_PAYABLE_AMT, CURRENCY_CODE,
					".$stc_schema.".TRACE_SUB_IL(DETAIL_LINE_ID) VENDOR,
					(SELECT VENDOR_TYPE FROM VENDOR
					WHERE ".$stc_schema.".TRACE_SUB_IL(DETAIL_LINE_ID) = VENDOR_ID) AS VTYPE,
					OP_CODE, SITE_ID,
					(TLORDER.TOTAL_CHARGES - TLORDER.INT_PAYABLE_AMT) AS USER4,
					CASE WHEN TLORDER.TOTAL_CHARGES > 0 THEN
						(TLORDER.TOTAL_CHARGES - TLORDER.INT_PAYABLE_AMT) /TLORDER.TOTAL_CHARGES * 100
					ELSE 0 END AS USER5
										
					FROM TLORDER
					WHERE CREATED_TIME > ".$DATE_STR." - 90 DAYS
					AND CURRENT_STATUS IN( 'BILLD', 'APPRVD', 'COMPLETE')
					AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
					AND BILL_NUMBER <> '0'
					AND UPPER(EXTRA_STOPS) <> 'CHILD'
					AND BILL_NUMBER NOT LIKE 'Q%'
			--		AND CURRENCY_CODE = 'BRO'
					AND EXISTS
					(SELECT INTERLINER_ID
						FROM ORDER_INTERLINER P
						WHERE P.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID)
			--		AND UPPER(OP_CODE) NOT IN (".$intermodal_ops_codes.")
					AND CREATED_TIME = (
					  SELECT MAX(CREATED_TIME) FROM
					  TLORDER T
					  WHERE T.BILL_NUMBER = TLORDER.BILL_NUMBER
					  AND DOCUMENT_TYPE IN ('INVOICE','REBILL'))

					-- Manning specific
					AND (SELECT INTERLINER_ID
						FROM ORDER_INTERLINER P
						WHERE P.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID
						ORDER BY ORDER_INTERLINER_ID DESC
						FETCH FIRST 1 ROW ONLY) != '12250'
													
					)
					
					SELECT BILL_NUMBER, BILL_TO_NAME, DATE(BILL_DATE) BILL_DATE, DATE(COMPLETED) COMPLETED, DATE(ACTUAL_DELIVERY) ACTUAL_DELIVERY, TOTAL_CHARGES,
					INT_PAYABLE_AMT, CURRENCY_CODE, VENDOR, VTYPE,
					OP_CODE, SITE_ID,
					DECIMAL(USER4, 8, 2) AS MARGIN, DECIMAL(USER5, 8, 2) AS MARGINP
					
					FROM MYDATA
					WHERE YEAR( COALESCE(DELIVER_BY, ACTUAL_DELIVERY, COMPLETED) ) = YEAR(".$DATE_STR.")
					AND ".$stc_schema.".STC_ACCOUNTING_PERIOD( COALESCE(DELIVER_BY, ACTUAL_DELIVERY, COMPLETED) ) = ".$stc_schema.".STC_ACCOUNTING_PERIOD(".$DATE_STR.")
					FOR READ ONLY
					WITH UR";

//					AND DAYS(COALESCE(ACTUAL_DELIVERY, COMPLETED)) <= DAYS(".$DATE_STR.")

					//AND NOT LYNX.TRACE_SUB_IL(DETAIL_LINE_ID) IN ('NORNORVA', 'CSXJACFL', 'STROMANE', 'TRIFORIN')
					//AND NOT (CUSTOMER = 'HOULUDMI' AND USER4 = '17000.00')
					
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

			case 'MONTHBB':  // !MONTHBB - Month Brokerage KPI - By Broker
			
			//	$intermodal_ops_codes = get_option( 'Intermodal_OP_Code', $debug );
			//	if( $intermodal_ops_codes == "" ) $intermodal_ops_codes = "'NONE'";
				
				if( $DT == 'NONE' )
					$DATE_STR = 'CURRENT DATE';
				else
					$DATE_STR = "DATE('".$DT."')";

				$query_string = "WITH MYDATA AS
					(SELECT ACTUAL_PICKUP, ACTUAL_DELIVERY,
					  (SELECT MAX(O.CHANGED)
					  FROM ODRSTAT O
					  WHERE O.ORDER_ID = TLORDER.DETAIL_LINE_ID
					  AND O.STATUS_CODE = 'COMPLETE') COMPLETED,
					  TRIM((SELECT UPDATED_BY
						FROM ODRSTAT O
						WHERE O.ORDER_ID = TLORDER.DETAIL_LINE_ID
						AND O.STATUS_CODE = 'ASSGN'
						FETCH FIRST 1 ROW ONLY)) BROKER,
					  TRIM((SELECT UPDATED_BY
						FROM ODRSTAT O
						WHERE O.ORDER_ID = TLORDER.DETAIL_LINE_ID
						AND O.STATUS_CODE = 'DISP'
						FETCH FIRST 1 ROW ONLY)) BROKER2,
					TOTAL_CHARGES, 
					
					(TLORDER.TOTAL_CHARGES - TLORDER.INT_PAYABLE_AMT) AS USER4,
					CASE WHEN TLORDER.TOTAL_CHARGES > 0 THEN
						(TLORDER.TOTAL_CHARGES - TLORDER.INT_PAYABLE_AMT) /TLORDER.TOTAL_CHARGES * 100
					ELSE 0 END AS USER5,
					
					SITE_ID, BILL_NUMBER, 
					ORIGPROV, DESTPROV, DISTANCE, INT_PAYABLE_AMT,
					
					TLORDER.CHARGES + COALESCE((SELECT SUM(a.CHARGE_AMOUNT)
					FROM ACHARGE_TLORDER a, ACHARGE_CODE c
					WHERE a.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID
					AND a.ACODE_ID = c.ACODE_ID
					AND c.CODE_TYPE IN ('ES', 'FP')),0) AS REVENUE2,
					
					(SELECT SUB_AMOUNT FROM ORDER_INTERLINER o
					WHERE o.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID
					FETCH FIRST 1 ROW ONLY
					) BASE_AMOUNT
					
					FROM TLORDER
					WHERE CREATED_TIME > ".$DATE_STR." - 200 DAYS
					AND CURRENT_STATUS <> 'CANCL'
					AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
					AND BILL_NUMBER <> '0'
					AND UPPER(EXTRA_STOPS) <> 'CHILD'
					AND BILL_NUMBER NOT LIKE 'Q%'
					AND EXISTS
					(SELECT INTERLINER_ID
						FROM ORDER_INTERLINER P
						WHERE P.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID)
			--		AND UPPER(OP_CODE) NOT IN (".$intermodal_ops_codes.")
			--		AND NOT (CUSTOMER = 'HOULUDMI' AND USER4 = '17000.00')
					AND CREATED_TIME = (
					  SELECT MAX(CREATED_TIME) FROM
					  TLORDER T
					  WHERE T.BILL_NUMBER = TLORDER.BILL_NUMBER
					  AND DOCUMENT_TYPE IN ('INVOICE','REBILL'))
					)
					
					SELECT BROKER, COUNT(*) BILLS,
					ROUND( SUM( TOTAL_CHARGES ), 2) REVENUE,
					ROUND( SUM( TOTAL_CHARGES - INT_PAYABLE_AMT ), 2) MARGIN,
					CASE WHEN SUM( TOTAL_CHARGES ) = 0 THEN 0
					ELSE
					ROUND( SUM( TOTAL_CHARGES - INT_PAYABLE_AMT ) / SUM( TOTAL_CHARGES ) * 100, 2) 
					END AS MARGINP
					FROM MYDATA
					WHERE YEAR( COALESCE(ACTUAL_DELIVERY, COMPLETED) ) = YEAR(".$DATE_STR.")
					AND ".$stc_schema.".STC_ACCOUNTING_PERIOD( COALESCE(ACTUAL_DELIVERY, COMPLETED) ) = ".$stc_schema.".STC_ACCOUNTING_PERIOD(".$DATE_STR.")

					GROUP BY BROKER
					ORDER BY BROKER ASC

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

			case 'PREVB':  // !PREVB - Last 12 months - broker
			
			//	$intermodal_ops_codes = get_option( 'Intermodal_OP_Code', $debug );
			//	if( $intermodal_ops_codes == "" ) $intermodal_ops_codes = "'NONE'";
				$DATE_STR = get_date( $DT );

				$query_string = "WITH HUTT_RAW AS
							(SELECT BILL_DATE, ACTUAL_DELIVERY, 
							(SELECT MAX(O.CHANGED)
							  FROM ODRSTAT O
							  WHERE O.ORDER_ID = TLORDER.DETAIL_LINE_ID
							  AND O.STATUS_CODE = 'COMPLETE') COMPLETED,
							  TOTAL_CHARGES, INT_PAYABLE_AMT
							FROM TLORDER
							WHERE CREATED_TIME > ".$DATE_STR." - 400 DAYS
							AND CURRENT_STATUS <> 'CANCL'
							AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
							AND BILL_NUMBER <> '0'
							AND UPPER(EXTRA_STOPS) <> 'CHILD'
							AND BILL_NUMBER NOT LIKE 'Q%'
							AND EXISTS
							(SELECT INTERLINER_ID
								FROM ORDER_INTERLINER P
								WHERE P.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID)
					--		AND UPPER(OP_CODE) NOT IN (".$intermodal_ops_codes.")
					--		AND NOT ".$stc_schema.".TRACE_SUB_IL(DETAIL_LINE_ID) IN ('NORNORVA', 'CSXJACFL', 'STROMANE', 'TRIFORIN')
							AND CREATED_TIME = (
							  SELECT MAX(CREATED_TIME) FROM
							  TLORDER T
							  WHERE T.BILL_NUMBER = TLORDER.BILL_NUMBER
							  AND DOCUMENT_TYPE IN ('INVOICE','REBILL'))
						  ),
					
							HUTT_BROKER AS
							(SELECT ACTUAL_DELIVERY, COMPLETED, TOTAL_CHARGES, INT_PAYABLE_AMT
								FROM HUTT_RAW
								WHERE DATE(COALESCE(ACTUAL_DELIVERY, COMPLETED)) >
								DATE(RTRIM(CHAR(YEAR(".$DATE_STR.")-1)) || '-' || RTRIM(CHAR(MONTH(".$DATE_STR."))) || '-01')
								AND DATE(COALESCE(ACTUAL_DELIVERY, COMPLETED)) <
								DATE(RTRIM(CHAR(YEAR(".$DATE_STR."))) || '-' || RTRIM(CHAR(MONTH(".$DATE_STR."))) || '-01')
							)
					
							SELECT YEAR(COALESCE(ACTUAL_DELIVERY, COMPLETED)) AS BYEAR,
		".$stc_schema.".STC_ACCOUNTING_PERIOD(COALESCE(ACTUAL_DELIVERY, COMPLETED)) AS BMONTH,
		".$stc_schema.".STC_WORKDAYS_IN_PERIOD(COALESCE(ACTUAL_DELIVERY, COMPLETED)) AS BDAYS_MONTH,
								ROUND( SUM( TOTAL_CHARGES ), 2) AS REVENUE,
								ROUND( SUM( INT_PAYABLE_AMT ), 2) AS EXPENSES,
								ROUND( SUM( TOTAL_CHARGES ), 2) - ROUND( SUM( INT_PAYABLE_AMT ), 2) AS PROFIT
							FROM HUTT_BROKER
					
							WHERE YEAR(COALESCE(ACTUAL_DELIVERY, COMPLETED)) >= 2022
					
							GROUP BY YEAR(COALESCE(ACTUAL_DELIVERY, COMPLETED)),
					".$stc_schema.".STC_ACCOUNTING_PERIOD(COALESCE(ACTUAL_DELIVERY, COMPLETED)),
					".$stc_schema.".STC_WORKDAYS_IN_PERIOD(COALESCE(ACTUAL_DELIVERY, COMPLETED))
		
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

			case 'GETOPTS':  // !GETOPTS - Get Options
			
				// Check for STC KPI options installed.
				$query_string1 = "SELECT * FROM CONFIG
					WHERE PROG_NAME = 'STRONGTOWER.EXE'
					FOR READ ONLY
					WITH UR";
					
				if( $debug ) echo "<p>using query_string = $query_string1</p>";
				
				$response = send_odbc_query( $query_string1, $stc_database, $debug );
				
				if( ! is_array($response) || count($response) == 0 ) {
					if( $debug ) echo "<p>STC KPI options missing, install defaults.</p>";

					// Prepare Select
					$query_string2 = "INSERT INTO CONFIG (PROG_NAME, THE_OPTION, OPTION_HINT, THE_VALUE, VALUE_HINT, 
						HIDDEN, COMPANY_ID)
						VALUES ('STRONGTOWER.EXE', 'CHR_Server', 'Choose Server', 'sandbox', 'sandbox or production', 'False', 1),
							('STRONGTOWER.EXE', 'CHR_Sandbox_URL', 'Sandbox URL', 'https://sandbox-api.navisphere.com', 'URL', 'False', 1),
							('STRONGTOWER.EXE', 'CHR_Production_URL', 'Production URL', 'https://api.navisphere.com', 'URL', 'False', 1),
							('STRONGTOWER.EXE', 'CHR_Sandbox_Client_ID', 'Sandbox Client ID', '0oai1zsfwruyCkDTH357', 'text', 'False', 1),
							('STRONGTOWER.EXE', 'CHR_Sandbox_Client_Secret', 'Sandbox Client Secret', 'egeejo14g26jYqPQHyTcD_b-el0UsyUo27SG4l2t', 'text', 'False', 1),
							('STRONGTOWER.EXE', 'CHR_Sandbox_Client_ID', 'Production Client ID', '0oai1ztcauNIEMNPR357', 'text', 'False', 1),
							('STRONGTOWER.EXE', 'CHR_Sandbox_Client_Secret', 'Production Client Secret', 'BeQv4bgUk7D_r-_t4Er5IwlUDBlAvz961ohzWiDB', 'text', 'False', 1),
							('STRONGTOWER.EXE', 'CHR_carrierCode', 'Carrier Code', 'unknown', 'text', 'False', 1)
							('STRONGTOWER.EXE', 'CHR_enabled', 'True/False', 'False', 'True/False', 'False', 1)
							";
													
					if( $debug ) echo "<p>using query_string = $query_string2</p>";
			
					$response1 = send_odbc_query( $query_string2, $stc_database, $debug );
					
					if( is_array($response1) ) {

						// Prepare Select
						$query_string3 = "SELECT * FROM CONFIG
							WHERE PROG_NAME = 'STRONGTOWER.EXE'
							FOR READ ONLY
							WITH UR";
														
						if( $debug ) echo "<p>using query_string = $query_string3</p>";
				
						$response = send_odbc_query( $query_string3, $stc_database, $debug );
						
						if( is_array($response) && count($response) > 0 ) {
							
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
					if( $debug ) {
						echo "<pre>";
						var_dump($response);
						echo "</pre>";
					} else {
						echo encryptData(json_encode( $response ));
					}
				}

				break;

			case 'SETOPT':  // !SETOPT - Update an option
				// Validate fields
				if( $THE_OPTION == "NONE" || $THE_VALUE == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
			
					// Check the STC function STC_WORKDAYS_MONTH_SOFAR is installed.
					$query_string = "UPDATE CONFIG
SET THE_VALUE = '".str_replace("'", "''", $THE_VALUE)."'
WHERE PROG_NAME = 'STRONGTOWER.EXE'
AND THE_OPTION = '".$THE_OPTION."'";

				//	$query_string = "CALL ".$stc_schema.".SET_CONFIG( 'STRONGTOWER.EXE', '".$THE_OPTION."', '".$THE_VALUE."', 'True/False', 'True/False')";
						
					if( $debug ) echo "<p>using query_string = $query_string</p>";
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( $debug ) {
						echo "<pre>response\n";
						var_dump($response, $last_odbc_error);
						echo "</pre>";
					}
					
					if( is_array($response) ) {
						$query_string2 = "SELECT * FROM CONFIG
WHERE PROG_NAME = 'STRONGTOWER.EXE'
AND THE_OPTION = '".$THE_OPTION."'
FOR READ ONLY
WITH UR";
					
					if( $debug ) {
						echo "<p>using query_string2 = </p>
						<pre>";
						var_dump($query_string2);
						echo "</pre>";
					}
					
					$response2 = send_odbc_query( $query_string2, $stc_database, $debug );
				
					if( $debug ) {
						echo "<pre>response2\n";
						var_dump($response2, $last_odbc_error);
						echo "</pre>";
					}
				


							if( $debug ) echo "<p>CHANGED OPTION</p>";							
							else echo encryptData("CHANGED");
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}
				break;

			case 'NEWLANE':  // !NEWLANE - New lane report
							
				$date_part = "= CURRENT DATE";
				if( $DT <> "NONE" ) {
					if( $DT2 <> "NONE" ) 
						$date_part = "BETWEEN DATE('".$DT."') AND DATE('".$DT2."')";
					else
						$date_part = "= DATE('".$DT."')";
				}

				$query_string = "WITH MANNING_DIRECT AS
						(SELECT BILL_NUMBER,
							(SELECT MAX(O.CHANGED)
							FROM ODRSTAT O
							WHERE O.ORDER_ID = TLORDER.DETAIL_LINE_ID
							AND O.STATUS_CODE = 'COMPLETE') COMPLETED,
						BILL_TO_CODE, BILL_TO_NAME,
						ORIGIN, ORIGNAME, ORIGCITY, ORIGPROV,
						DESTINATION, DESTNAME, DESTCITY, DESTPROV,
						ACTUAL_PICKUP, ACTUAL_DELIVERY,
						COALESCE(TIMESTAMPDIFF(16, ACTUAL_DELIVERY - ACTUAL_PICKUP),0) AS DAYS_TRANSIT,
						ROLLUP_PALLETS, ROLLUP_PIECES, ROLLUP_WEIGHT, ROLLUP_LENGTH_1,
						
						COALESCE((SELECT SUM(LENGTH_EST)
							FROM TLDTL
							WHERE ORDER_ID = DETAIL_LINE_ID),0) AS LENGTH_EST,
						
						NO_STOPS,
						
						ROLLUP_CHARGES, ROLLUP_XCHARGES, TOTAL_CHARGES, INT_PAYABLE_AMT,
						
						COALESCE((SELECT SUM(a.CHARGE_AMOUNT)
							FROM ACHARGE_TLORDER a, ACHARGE_CODE c
							WHERE a.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID
							AND a.ACODE_ID = c.ACODE_ID
							AND c.ACC_TYPE = 'FSC'),0) AS FUEL_SURCHARGE,
						
						
						CAST((SELECT LISTAGG(TRIM(V.NAME), ', ')
							FROM ORDER_INTERLINER O, VENDOR V
							WHERE O.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID
							AND V.VENDOR_ID = O.INTERLINER_ID) AS VARCHAR(500)) AS CARRIER_NAME,
							
						COALESCE((SELECT MANUAL_RATE
							FROM TLDTL
							WHERE ORDER_ID = DETAIL_LINE_ID
							AND SUB_COST > 0
							FETCH FIRST 1 ROWS ONLY), 'False') AS MANUAL_RATE
						
						FROM TLORDER
						WHERE CREATED_TIME > CURRENT DATE - 300 DAYS
						AND CURRENT_STATUS <> 'CANCL'
						AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
						AND BILL_NUMBER <> '0'
						AND UPPER(EXTRA_STOPS) <> 'CHILD'
						AND BILL_NUMBER NOT LIKE 'Q%'
						AND CURRENT_STATUS IN ('BILLD', 'COMPLETE', 'APPRVD')
						
						AND CREATED_TIME = (
						  SELECT MAX(CREATED_TIME) FROM
						  TLORDER T
						  WHERE T.BILL_NUMBER = TLORDER.BILL_NUMBER
						  AND DOCUMENT_TYPE IN ('INVOICE','REBILL'))
						)
						
					SELECT TRIM(BILL_NUMBER) AS BILL_NUMBER, 
						BILL_TO_CODE, BILL_TO_NAME,
						ORIGIN, ORIGNAME, ORIGCITY, ORIGPROV,
						DESTINATION, DESTNAME, DESTCITY, DESTPROV,
						ACTUAL_PICKUP, COALESCE(ACTUAL_DELIVERY, COMPLETED) AS ACTUAL_DELIVERY,
						DAYS_TRANSIT,
						ROLLUP_PALLETS, ROLLUP_PIECES, ROLLUP_WEIGHT, ROLLUP_LENGTH_1,
						LENGTH_EST, NO_STOPS,
						ROLLUP_CHARGES, ROLLUP_XCHARGES - FUEL_SURCHARGE AS ROLLUP_XCHARGES,
						TOTAL_CHARGES, INT_PAYABLE_AMT,
						FUEL_SURCHARGE, CARRIER_NAME, MANUAL_RATE,
						
						ROUND(TOTAL_CHARGES - INT_PAYABLE_AMT, 2) AS MARGIN,
						CASE WHEN TOTAL_CHARGES > 0 THEN
							ROUND((TOTAL_CHARGES - INT_PAYABLE_AMT) / TOTAL_CHARGES * 100, 2)
						ELSE 0 END AS MARGINP
					FROM MANNING_DIRECT
					WHERE DATE( COALESCE(ACTUAL_DELIVERY, COMPLETED) ) ".$date_part." 
					ORDER BY BILL_NUMBER ASC			

					FOR READ ONLY
					WITH UR";
					
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string, $stc_database);
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

			case 'PUTTOKEN':  // !PUTTOKEN - Store token for CHR
				// Validate fields
				if( $TOKEN == "NONE" || $TTYPE == "NONE" || $DT == "NONE" || $SERVER == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					// Check if table exists
					$query_string = "SELECT * FROM SYSCAT.TABLES WHERE TABSCHEMA='TMWIN'
						AND TABNAME='STC_CHR_TOKEN'";
								
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string);
						echo "</pre>";
					}
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) && count($response) == 1 ) {
						$query_string2 = "DELETE FROM STC_CHR_TOKEN";
						
						if( $debug ) {
							echo "<p>using query_string2 = </p>
							<pre>";
							var_dump($query_string2);
							echo "</pre>";
						}
						
						$response2 = send_odbc_query( $query_string2, $stc_database, $debug );
					
						if( $debug ) {
							echo "<pre>";
							var_dump($response2);
							echo "</pre>";
						}

						$query_string3 = "INSERT INTO STC_CHR_TOKEN( ACCESS_TOKEN, EXPIRES_IN, TOKEN_TYPE, SERVER)
							VALUES( '".$TOKEN."', '".$DT."', '".$TTYPE."', '".$SERVER."')";
						
						if( $debug ) {
							echo "<p>using query_string3 = </p>
							<pre>";
							var_dump($query_string3);
							echo "</pre>";
						}
						
						$response3 = send_odbc_query( $query_string3, $stc_database, $debug );
					
						if( is_array($response3) ) {
							if( $debug ) {
								echo "<pre>";
								var_dump($response3);
								echo "</pre><p>DONE</p>";
							} else {
								echo encryptData("DONE");
							}
						} else {
							if( $debug ) echo "<p>Error - token EXPIRED</p>";
							else echo encryptData("NOT OK: token EXPIRED");
						}
				
					} else {
						if( $debug ) echo "<p>Error - table STC_CHR_TOKEN is missing</p>";
						else echo encryptData("NOT OK: table STC_CHR_TOKEN is missing");
					}
				}
				
				break;

			case 'GETTOKEN':  // !GETTOKEN - Fetch token for CHR
				// Validate fields
				if( $SERVER == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					// Check if table exists
					$query_string = "SELECT * FROM SYSCAT.TABLES WHERE TABSCHEMA='TMWIN'
						AND TABNAME='STC_CHR_TOKEN'";
								
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string);
						echo "</pre>";
					}
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) && count($response) == 1 ) {
						$query_string2 = "SELECT * FROM STC_CHR_TOKEN
							WHERE EXPIRES_IN > CURRENT_TIMESTAMP
							AND SERVER = '".$SERVER."'";
						
						if( $debug ) {
							echo "<p>using query_string = </p>
							<pre>";
							var_dump($query_string2);
							echo "</pre>";
						}
						
						$response2 = send_odbc_query( $query_string2, $stc_database, $debug );
					
						if( is_array($response2) && count($response2) == 1 ) {
							if( $debug ) {
								echo "<pre>";
								var_dump($response2);
								echo "</pre>";
							} else {
								echo encryptData(json_encode( $response2 ));
							}
						} else {
							if( $debug ) echo "<p>Error - token EXPIRED</p>";
							else echo encryptData("NOT OK: token EXPIRED");
						}
				
					} else {
						if( $debug ) echo "<p>Error - table STC_CHR_TOKEN is missing</p>";
						else echo encryptData("NOT OK: table STC_CHR_TOKEN is missing");
					}
				}
				
				break;

			case 'GETDRIVERS':  // !GETDRIVERS - List of Drivers 
		
				$query_string = "SELECT DRIVER_ID AS ID, CONCAT(CONCAT(FIRST_NAME, ' '), LAST_NAME) AS NAME
					FROM DRIVER
					WHERE ACTIVE_IN_DISP = 'True'
					AND COALESCE(LAST_NAME, '') != ''					

					FOR READ ONLY
					WITH UR";
					
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string, $stc_database);
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

			case 'GETPUNITS':  // !GETPUNITS - List of Tractors 
		
				$query_string = "SELECT UNIT_ID AS ID, CURRENT_ZONE_DESC AS NAME
					FROM PUNIT
					WHERE ACTIVE_WHERE = 'D'
					
					ORDER BY UNIT_ID ASC			

					FOR READ ONLY
					WITH UR";
					
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string, $stc_database);
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

			case 'GETTRAILERS':  // !GETTRAILERS - List of Trailers 
		
				$query_string = "SELECT TRAILER_ID AS ID, LENGTH_1 AS NAME
					FROM TRAILER
					WHERE ACTIVE_IN_DISP = 'True'				
					
					FOR READ ONLY
					WITH UR";
					
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string, $stc_database);
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

			case 'INSTLORDER':  // !INSTLORDER - Insert into TLORDER
		
				// Validate fields
				if( $TOKEN == "NONE" || $CNM == "NONE" || $UID == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$fields = json_decode($TOKEN, true);
					
					//! Get a unique DETAIL_LINE_ID
					$query_string1 = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_DETAIL_LINE_ID')";
						
					if( $debug ) {
						echo "<p>using query_string1 = </p>
						<pre>";
						var_dump($query_string1);
						echo "</pre>";
					}
					
					if( false && $debug ) {
						$response1 = [
							['NEXTID' => '12345678' ]
						];
					} else {
						$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
					}
					
					if( $debug ) {
						echo "<pre>Response1\n";
						var_dump($response1);
						echo "</pre>";
					}
					
					if( is_array($response1) && count($response1) == 1
						&& isset($response1[0]["NEXTID"])
						&& is_array($fields) && count($fields) > 0 ) {
							
						$dlid = $response1[0]["NEXTID"];
						$ins_date = date('Y-m-d H:i:s');
						
						$columns = [
							'DETAIL_LINE_ID',
							'PARENT_ORDER',
							'BILL_NUMBER',
							'BILL_NUMBER_KEY',
							'CREATED_TIME',
						];
						$values = [
							$dlid,
							0,
							"'NA'",
							"'NA        ".$dlid."'",
							"'".$ins_date."'",
						];
						
						//! Populate the bill to client, if needed
						if( isset($fields['BILL_TO_CODE']) && ! isset($fields['BILL_TO_NAME']) ) {
							$client = fetch_client_info( $fields['BILL_TO_CODE'], $debug );
							
							if( is_array($client) ) {
								$fields['CUSTOMER'] = $fields['BILL_TO_CODE'];
								$fields['CALLNAME'] = str_replace("'", "''", $client[0]['NAME']);
								$fields['BILL_TO_NAME'] = str_replace("'", "''", $client[0]['NAME']);
								$fields['CALLADDR1'] = str_replace("'", "''", $client[0]['ADDRESS_1']);
								$fields['CALLADDR2'] = str_replace("'", "''", $client[0]['ADDRESS_2']);
								$fields['CALLCITY'] = str_replace("'", "''", $client[0]['CITY']);
								$fields['CALLPROV'] = str_replace("'", "''", $client[0]['PROVINCE']);
								$fields['CALLPC'] = str_replace("'", "''", $client[0]['POSTAL_CODE']);
								
								$fields['CALLCOUNTRY'] = str_replace("'", "''", $client[0]['COUNTRY']);
								if( ! isset($fields['CALLPHONE']) )
									$fields['CALLPHONE'] = str_replace("'", "''", $client[0]['BUSINESS_PHONE']);
								if( ! isset($fields['CALLCONTACT']) )
									$fields['CALLCONTACT'] = str_replace("'", "''", $client[0]['CONTACT']);
								if( ! isset($fields['CALLCONTACT']) )
									$fields['CALLEMAIL'] = str_replace("'", "''", $client[0]['EMAIL']);
							}
						}

						$coltypes = get_column_types('TLORDER');
						if( false && $debug ) {
							echo "<pre>coltypes\n";
							var_dump($coltypes);
							echo "</pre>";
						}
						
						foreach( $fields as $col => $val ) {
							if( false && $debug ) {
								echo "<pre>col, type, val\n";
								var_dump($col, $coltypes[$col], $val,
								in_array($coltypes[$col], [ 'TIMESTAMP', 'VARCHAR', 'CHARACTER',
								'DATE']));
								echo "</pre>";
							}
							
							$columns[] = $col;
							if( in_array($coltypes[$col], [ 'TIMESTAMP', 'VARCHAR', 'CHARACTER',
								'DATE']) )
								$values[] = "'".$val."'";
							else
								$values[] = $val;
						}
						
						//! INSERT INTO TLORDER
						$query_string2 = "INSERT INTO TLORDER (".implode(',', $columns).")
VALUES(".implode(',', $values).")";
					
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
							//! INSERT INTO TRACE
							ins_trace( $dlid, $CNM, 'K', $debug );
							
							//! INSERT INTO NOTES
							$ins_date = date('Y-m-d H:i:s');
							insert_into_notes( $dlid, 'B', $ins_date, 'Created by Webtools from CH Robinson, load# = '.$CNM, $debug );
							
							//! INSERT INTO ODRSTAT
							insert_into_odrstat( $dlid, $ins_date, $UID, '', 'UPDATES',
								'Created by Webtools from CH Robinson, load# = '.$CNM, $debug );
							
							$response2["DETAIL_LINE_ID"] = $dlid;

							echo encryptData(json_encode( $response2 ));
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: send_odbc_query 2 failed: " . $last_odbc_error);
						}
					
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query 1 failed: " . $last_odbc_error);
					}
				}
				break;

			case 'GETDLID':  // !GETDLID - Fetch DLID for CHR LOAD#
				// Validate fields
				if( $CNM == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					// Find detail number
					$query_string = "SELECT DISTINCT DETAIL_NUMBER 
						FROM TRACE, TLORDER
						WHERE TRACE_NUMBER = '".$CNM."'
						AND DETAIL_NUMBER = DETAIL_LINE_ID
						AND CURRENT_STATUS != 'CANCL'";
					//	AND BILL_NUMBER = 'NA'
						// AND TRACE_TYPE = 'K'		
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string);
						echo "</pre>";
					}
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( $debug ) {
						echo "<pre>Response\n";
						var_dump($response);
						echo "</pre>";
					}
					
					if( is_array($response) && count($response) == 1 &&
						isset($response[0]['DETAIL_NUMBER'])) {
						if( $debug ) {
							echo "<pre>";
							var_dump($response[0]['DETAIL_NUMBER']);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response[0]['DETAIL_NUMBER'] ));
						}
					} else {
						if( $debug ) echo "<p>Error - CHR LOAD# is missing</p>";
						else echo encryptData("NOT OK: CHR LOAD# is missing");
					}
				}
				
				break;

			case 'GETCDLID':  // !GETCDLID - Fetch DLID for CANCELLED CHR LOAD#
				// Validate fields
				if( $CNM == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					// Find detail number
					$query_string = "SELECT DISTINCT DETAIL_NUMBER, TRIM(BILL_NUMBER) AS BILL_NUMBER,
						O.UPDATED_BY, O.CHANGED
						FROM TRACE, TLORDER
			            LEFT JOIN ODRSTAT O
			            ON O.ORDER_ID = TLORDER.DETAIL_LINE_ID
			            AND O.STATUS_CODE = 'CANCL'
						WHERE TRACE_NUMBER = '".$CNM."'
						AND DETAIL_NUMBER = DETAIL_LINE_ID
			            AND CURRENT_STATUS = 'CANCL'
			            ORDER BY O.CHANGED DESC
			            FETCH FIRST 1 ROWS ONLY
			            FOR READ ONLY
			            WITH UR";
					//	AND BILL_NUMBER = 'NA'
						// AND TRACE_TYPE = 'K'		
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string);
						echo "</pre>";
					}
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( $debug ) {
						echo "<pre>Response\n";
						var_dump($response);
						echo "</pre>";
					}
					
					if( is_array($response) && count($response) == 1 ) {
						if( $debug ) {
							echo "<pre>";
							var_dump($response);
							echo "</pre>";
						} else {
							echo encryptData(json_encode( $response ));
						}
					} else {
						if( $debug ) echo "<p>Error - CHR LOAD# is missing</p>";
						else echo encryptData("NOT OK: CHR LOAD# is missing");
					}
				}
				
				break;

			case 'INSNOTES':  // !INSNOTES - Insert notes
				// Validate fields
				if( $DLID == "NONE" || $TOKEN == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$fields = json_decode($TOKEN, true);
					$ins_date = date('Y-m-d H:i:s');
					
					if( is_array($fields) && count($fields) > 0 ) {
						foreach( $fields as $row ) {
							foreach( $row as $note_type => $note_value ) {
								insert_into_notes( $DLID, $note_type, $ins_date, $note_value, $debug );
							}
						}
					}
					
					if( $debug ) echo "<p>CHANGED OPTION</p>";
					else echo encryptData("CHANGED");

				}
				break;
					
			case 'INSTRACE':  // !INSTRACE - Insert trace numbers
				// Validate fields
				if( $DLID == "NONE" || $TOKEN == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$fields = json_decode($TOKEN, true);
					$ins_date = date('Y-m-d H:i:s');
					if( $debug ) {
						echo "<pre>FIELDS\n";
						var_dump($fields);
						echo "</pre>";
					}
					
					if( is_array($fields) && count($fields) > 0 ) {
						foreach( $fields as $row ) {
							foreach( $row as $trace_type => $num ) {
								if( $debug ) {
									echo "<pre>TRACE_TYPE, NUM\n";
									var_dump($trace_type, $num);
									echo "</pre>";
								}
								ins_trace( $DLID, $num, $trace_type, $debug );
							}
						}
					}
					
					if( $debug ) echo "<p>CHANGED OPTION</p>";
					else echo encryptData("CHANGED");

				}
				break;
					
			case 'INSTLDTL':  // !INSTLDTL - Insert TLDTL
				// Validate fields
				if( $DLID == "NONE" || $TOKEN == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$fields = json_decode($TOKEN, true);
					
					$coltypes = get_column_types('TLDTL');
					
					$query_string0 = "DELETE FROM TLDTL WHERE ORDER_ID = $DLID";
				
					if( $debug ) {
						echo "<p>using query_string0 = </p>
						<pre>".
						htmlentities($query_string0).
						"</pre>";
					}
					
					$response0 = send_odbc_query( $query_string0, $stc_database, $debug );
					
					if( $debug ) {
						echo "<pre>Response0\n";
						var_dump($response0);
						echo "</pre>";
					}
				
					foreach( $fields as $tldtl ) { // could be multiple rows
					
						// Get a unique DETAIL_LINE_ID
						$query_string1 = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_TLDTL_SEQ')";
							
						if( $debug ) {
							echo "<p>using query_string1 = </p>
							<pre>";
							var_dump($query_string1);
							echo "</pre>";
						}
						
						if( false && $debug ) {
							$response1 = [
								['NEXTID' => '12345678' ]
							];
						} else {
							$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
						}
						
						if( $debug ) {
							echo "<pre>Response1\n";
							var_dump($response1);
							echo "</pre>";
						}
					
						if( is_array($response1) && count($response1) == 1
							&& isset($response1[0]["NEXTID"]) ) {

							$seq = $response1[0]["NEXTID"];
							
							$columns = [
								'SEQUENCE',
								'ORDER_ID',
							];
							$values = [
								$seq,
								$DLID,
							];
							
							foreach( $tldtl as $col => $val ) {
								if( false && $debug ) {
									echo "<pre>col, type, val\n";
									var_dump($col, $coltypes[$col], $val,
									in_array($coltypes[$col], [ 'TIMESTAMP', 'VARCHAR', 'CHARACTER',
									'DATE']));
									echo "</pre>";
								}
								
								$columns[] = $col;
								if( in_array($coltypes[$col], [ 'TIMESTAMP', 'VARCHAR', 'CHARACTER',
									'DATE']) )
									$values[] = "'".$val."'";
								else
									$values[] = $val;
							}
							
							$query_string2 = "INSERT INTO TLDTL (".implode(',', $columns).")
	VALUES(".implode(',', $values).")";
						
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
						}
					}
										
					if( $debug ) echo "<p>CHANGED OPTION</p>";							
					else echo encryptData("CHANGED");
				}
								
				break;

			case 'UPDTLORDER':  // !UPDTLORDER - Update TLORDER, TLDTL
				// Validate fields
				if( $DLID == "NONE" || $TOKEN == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$fields = json_decode($TOKEN, true);
					if( isset($fields['TLORDER']) && count($fields['TLORDER']) > 0 ) {
						$tlorder = $fields['TLORDER'];
						
						$coltypes = get_column_types('TLORDER');
						$changes = [];
						
						foreach( $tlorder as $col => $val ) {
							if( in_array($coltypes[$col], [ 'TIMESTAMP', 'VARCHAR', 'CHARACTER',
								'DATE']) )
								$changes[] = $col." = '".str_replace("'", "''", $val)."'";
							else
								$changes[] = $col." = ".$val;
						}
						
						$query_string = "UPDATE TLORDER SET ".implode(', ', $changes).
							" WHERE DETAIL_LINE_ID = $DLID";
						
						if( $debug ) {
							echo "<p>using query_string = </p>
							<pre>".
							htmlentities($query_string).
							"</pre>";
						}
						
						$response = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( $debug ) {
							echo "<pre>Response\n";
							var_dump($response);
							echo "</pre>";
						}
						if( ! is_array($response) ) {
							if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: send_odbc_query 1 failed: " . $last_odbc_error);
						}
					}
					
					if( isset($fields['TLDTL']) ) {
						$coltypes = get_column_types('TLDTL');
						
						$query_string0 = "DELETE FROM TLDTL WHERE ORDER_ID = $DLID";
					
						if( $debug ) {
							echo "<p>using query_string0 = </p>
							<pre>".
							htmlentities($query_string0).
							"</pre>";
						}
						
						$response0 = send_odbc_query( $query_string0, $stc_database, $debug );
						
						if( $debug ) {
							echo "<pre>Response0\n";
							var_dump($response0);
							echo "</pre>";
						}
					
						foreach( $fields['TLDTL'] as $tldtl ) { // could be multiple rows
						
							// Get a unique DETAIL_LINE_ID
							$query_string1 = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_TLDTL_SEQ')";
								
							if( $debug ) {
								echo "<p>using query_string1 = </p>
								<pre>";
								var_dump($query_string1);
								echo "</pre>";
							}
							
							if( false && $debug ) {
								$response1 = [
									['NEXTID' => '12345678' ]
								];
							} else {
								$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
							}
							
							if( $debug ) {
								echo "<pre>Response1\n";
								var_dump($response1);
								echo "</pre>";
							}
						
							if( is_array($response1) && count($response1) == 1
								&& isset($response1[0]["NEXTID"]) ) {

								$seq = $response1[0]["NEXTID"];
								
								$columns = [
									'SEQUENCE',
									'ORDER_ID',
								];
								$values = [
									$seq,
									$DLID,
								];
								
								foreach( $tldtl as $col => $val ) {
									if( false && $debug ) {
										echo "<pre>col, type, val\n";
										var_dump($col, $coltypes[$col], $val,
										in_array($coltypes[$col], [ 'TIMESTAMP', 'VARCHAR', 'CHARACTER',
										'DATE']));
										echo "</pre>";
									}
									
									$columns[] = $col;
									if( in_array($coltypes[$col], [ 'TIMESTAMP', 'VARCHAR', 'CHARACTER',
										'DATE']) )
										$values[] = "'".$val."'";
									else
										$values[] = $val;
								}
								
								$query_string2 = "INSERT INTO TLDTL (".implode(',', $columns).")
		VALUES(".implode(',', $values).")";
							
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
							}
						}
					}
					$ins_date = date('Y-m-d H:i:s');
					
					$status = 'UPDATES';
					
					insert_into_odrstat( $DLID, $ins_date, 'WEBTOOLS', '', $status,
						'Updated by Webtools from CH Robinson, load# = '.$CNM, $debug );

					
					if( $debug ) echo "<p>CHANGED OPTION</p>";							
					else echo encryptData("CHANGED");
				}
								
				break;

			case 'POSTTLORDER':  // !POSTTLORDER - POST TLORDER 
		
				if( $DLID == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$query_string1 = "SELECT SITE_ID FROM TLORDER
						WHERE DETAIL_LINE_ID = $DLID";
					
					if( $debug ) {
						echo "<p>using query_string1 = </p>
						<pre>";
						var_dump($query_string1);
						echo "</pre>";
					}
					
					$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
				
					if( $debug ) {
						echo "<pre>response1\n";
						var_dump($response1);
						echo "</pre>";
					}

					if( is_array($response1) && count($response1) == 1 &&
						! empty($response1[0]["SITE_ID"] ) ) {
					
						$query_string2 = "CALL ".$stc_schema.".STC_CUSTOM_POST_FB($DLID, '".$response1[0]["SITE_ID"]."')";
					
						if( $debug ) {
							echo "<p>using query_string2 = </p>
							<pre>";
							var_dump($query_string2);
							echo "</pre>";
						}
						
						$response2 = send_odbc_query( $query_string2, $stc_database, $debug );
						
						if( is_array($response2) ) {
							// Prepare Select
							$query_string3 = "CALL ".$stc_schema.".UPDATE_TLORDER_SUMMARY($DLID)";
						
							if( $debug ) {
								echo "<p>using query_string3 = </p>
								<pre>";
								var_dump($query_string3);
								echo "</pre>";
							}
						
							$response3 = send_odbc_query( $query_string3, $stc_database, $debug );

							if( $debug ) {
								echo "<pre>";
								var_dump(trim($response2[0]["FB_NUMBER"]));
								echo "</pre>";
							} else {
								echo encryptData(json_encode( trim($response2[0]["FB_NUMBER"]) ));
							}
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: send_odbc_query 2 failed: " . $last_odbc_error);
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query 1 failed: " . $last_odbc_error);
					}
				}
				break;

			case 'CANCELFB':  // !CANCELFB - CANCEL A FB 
		
				if( $DLID == "NONE" || $COMMENT == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$ins_date = date('Y-m-d H:i:s');
										
					$status = 'UPDATES';

					//! INSERT INTO ODRSTAT
					insert_into_odrstat( $DLID, $ins_date, 'WEBTOOLS', '', $status,
						$COMMENT, $debug );
					
					$query_string = "CALL ".$stc_schema.".CANCEL_FB($DLID, 'CANCL', '".$COMMENT."', '".$ins_date."', 'WEBTOOLS')";
				
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
							echo "</pre><p>DONE</p>";
						} else {
							echo encryptData("DONE");
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query 1 failed: " . $last_odbc_error);
					}
				}
				break;

			case 'UPDODRSTAT':  // !UPDODRSTAT - Add a comment to ODRSTAT 
		
				if( $DLID == "NONE" || $COMMENT == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$ins_date = date('Y-m-d H:i:s');
										
					$status = 'UPDATES';

					//! INSERT INTO ODRSTAT
					$result = insert_into_odrstat( $DLID, $ins_date, 'WEBTOOLS', '', $status,
						$COMMENT, $debug );
														
					if( $result ) {
						if( $debug ) {
							echo "<p>DONE</p>";
						} else {
							echo encryptData("DONE");
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query 1 failed: " . $last_odbc_error);
					}
				}
				break;

			case 'EXPORTFB':  // !EXPORTFB - EXPORT FB 
		
				// Validate fields $_SESSION['USERID']
				if( $DT == "NONE" || $DT2 == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$filters =[];
					if( $SITEID != 'NONE' ) {
						$filters[] = "SITE_ID = '".$SITEID."'
						";
					}
					if( $OP_CODE != 'NONE' ) {
						$filters[] = "OP_CODE = '".$OP_CODE."'
						";
					}
					if( $STATUS != 'NONE' ) {
						switch( $STATUS ) {
							case 'A':
								$filters[] = "CURRENT_STATUS = 'AVAIL'
								";
								break;
								
							case 'P':
								$filters[] = "CURRENT_STATUS = 'PICKD'
								";
								break;
								
							case 'D':
								$filters[] = "CURRENT_STATUS = 'DELVD'
								";
								break;
								
							case 'B':
								$filters[] = "CURRENT_STATUS IN ('DELVD', 'COMPLETE')
									AND INTERFACE_STATUS_F IS NULL
								";
								break;
							
							case 'C':
								$filters[] = "DOCUMENT_TYPE = 'CREDIT'
								";
								break;
								
							case 'R':
								$filters[] = "DOCUMENT_TYPE = 'REBILL'
								";
								break;
								
							default:
								break;
						}
					}
					
					$filter = implode(' AND ', $filters);
					if( ! empty($filter) ) $filter .= 'AND ';
					
					$query_string = "SELECT TRIM(BILL_NUMBER) AS BILL_NUMBER, BILL_TO_NAME,
							SITE_ID, OP_CODE, CURRENT_STATUS, INTERFACE_STATUS_F,
							DOCUMENT_TYPE,
							ORIGNAME, ORIGCITY, ORIGPROV,
							DESTNAME, DESTCITY, DESTPROV,
							DECIMAL(CHARGES, 8, 2) AS CHARGES,
							DECIMAL(XCHARGES, 8, 2) AS XCHARGES,
							DECIMAL(TOTAL_CHARGES, 8, 2) AS TOTAL_CHARGES

						FROM TLORDER
						WHERE $filter
						CURRENT_STATUS <> 'CANCL'
						AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
						AND BILL_NUMBER <> '0'
						AND EXTRA_STOPS <> 'Child'
						AND BILL_NUMBER NOT LIKE 'Q%'
						AND DATE(COALESCE(BILL_DATE, DELIVER_BY)) BETWEEN DATE('".$DT."') AND DATE('".$DT2."') 

						AND CREATED_TIME = (
							SELECT MAX(CREATED_TIME) FROM TLORDER T
							WHERE T.BILL_NUMBER = TLORDER.BILL_NUMBER
							AND DOCUMENT_TYPE IN ('INVOICE','REBILL'))
						
						ORDER BY 1 ASC
	
						FOR READ ONLY
						WITH UR";
						
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string, $stc_database);
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

			case 'TRLDOT':  // !TRLDOT 
		
				$query_string = "
				SELECT LOCCODE AS \"TRL #\",
					(DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1  + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST) AS \"DOT\",
					to_char(DATE(DATELAST), 'mm/dd/yyyy') AS \"LAST DOT\",
					TRIM(LEFT((SELECT CURRENT_ZONE_DESC FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_MAINT.LOCCODE),30)) AS \"LOCATION\",
					(SELECT CLASS FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_MAINT.LOCCODE) AS \"CLASS\",
					(SELECT STATUS FROM TRAILER WHERE  TRAILER.TRAILER_ID = RM_MAINT.LOCCODE) AS \"STATUS\",
					(SELECT FINAL_DESTINATION_DESC FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_MAINT.LOCCODE) AS \"FINAL DESTINATION\"


				FROM RM_MAINT
				WHERE LOCTYPE = 'TR' AND LOCCODE IN (SELECT TRAILER_ID FROM TRAILER WHERE ACTIVE_IN_DISP = 'True'
				AND NOT(ZONE IN ('MANSTORAGE')) AND NOT( STATUS IN ('LEASEOS','OSAVBL', 'STORAGE','MANPROP')))
				AND JOBCODE IN ('DOT') AND (DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1  + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST)  <=90
				AND NOT(EXISTS(SELECT CLASS FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_MAINT.LOCCODE AND CLASS LIKE 'OS%'))
				AND NOT(EXISTS(SELECT STATUS FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_MAINT.LOCCODE AND TRAILER.STATUS IN ('OUTSERV','SHOP','STORAGEAVL')))
				ORDER BY DOT, LOCCODE WITH UR";
			
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

			case 'TRLMAINT':  // !TRLMAINT 
		
				$query_string = "
				SELECT LOCCODE AS \"TRL #\",
					((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1) AS \"PM\",
					to_char(DATE(DATELAST), 'mm/dd/yyyy') AS \"LAST PM\",
					TRIM(LEFT((SELECT CURRENT_ZONE_DESC FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_MAINT.LOCCODE),30)) AS \"LOCATION\",
					DATEINT AS \"DAYS\",
					(SELECT YEAR FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_MAINT.LOCCODE) AS \"YEAR\",
					(SELECT STATUS FROM TRAILER WHERE  TRAILER.TRAILER_ID = RM_MAINT.LOCCODE) AS \"STATUS\",
					(SELECT USER1 FROM TRAILER WHERE  TRAILER.TRAILER_ID = RM_MAINT.LOCCODE) AS \"LESSOR\",
					(SELECT CLASS FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_MAINT.LOCCODE) AS \"CLASS\"


				FROM RM_MAINT
				WHERE (LOCTYPE = 'TR' AND LOCCODE IN (SELECT TRAILER_ID FROM TRAILER WHERE ACTIVE_IN_DISP = 'True'
				
				AND NOT(ZONE IN ('MANSTORAGE')) AND NOT( STATUS IN ('LEASEOS','OSAVBL', 'STORAGE','MANPROP')))
				AND JOBCODE IN ('MAINT') AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1 )  <= 30 
				AND NOT(EXISTS(SELECT JOBCODE FROM RM_MAINT X WHERE X.LOCCODE = RM_MAINT.LOCCODE AND JOBCODE IN ('DOT') AND
				(DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1  + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST)  <=90))
				AND NOT(EXISTS(SELECT DATA FROM CUSTOM_DATA WHERE CUSTDEF_ID = 74 AND SRC_TABLE_KEY = LOCCODE))
				AND NOT(EXISTS(SELECT CLASS FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_MAINT.LOCCODE AND CLASS LIKE 'OS%')))
				AND NOT(EXISTS(SELECT STATUS FROM TRAILER WHERE  TRAILER.TRAILER_ID = RM_MAINT.LOCCODE AND TRAILER.STATUS IN  ('OUTSERV','SHOP')))
				
				ORDER BY PM, LOCCODE WITH UR
				";
			
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

			case 'TRLCALLIN':  // !TRLCALLIN 
		
				$query_string = "
				SELECT LOCCODE AS \"TRL #\",
					CAST(NOTES as VARCHAR(160)) AS \"CALL IN\",
					NAME AS \"CALLER\",
					to_char(CALLDATE, 'mm/dd/yyyy hh:mm') AS \"DATE\",
					LOCTYPE AS \"TYPE\",
					CALLINID AS \"ID\",
					TRIM(LEFT((SELECT CURRENT_ZONE_DESC FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_CALLIN.LOCCODE),30)) AS \"LOCATION\",
					(SELECT CLASS FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_CALLIN.LOCCODE) AS \"CLASS\",
					(SELECT FINAL_DESTINATION_DESC FROM TRAILER WHERE TRAILER.TRAILER_ID = RM_CALLIN.LOCCODE) AS \"FINAL DESTINATION\"
				FROM RM_CALLIN WHERE LOCTYPE = 'TR' 
				AND DONE = 'False' AND LOCCODE IN (SELECT TRAILER_ID FROM TRAILER WHERE ACTIVE_IN_DISP = 'True'
				AND NOT(ZONE IN ('MANSTORAGE')) AND NOT( STATUS IN ('LEASEDTRL','OSAVBL', 'STORAGE','MANPROP'))) AND WOCOMPLETED_DT IS NULL AND PRIORITY <> 99
				AND NOT(EXISTS(SELECT CLASS FROM TRAILER WHERE TRAILER.TRAILER_ID = LOCCODE AND CLASS LIKE 'OS%'))
				AND NOT(EXISTS(SELECT STATUS FROM TRAILER WHERE  TRAILER.TRAILER_ID = RM_CALLIN.LOCCODE AND TRAILER.STATUS IN  ('OUTSERV','SHOP')))
				
				ORDER BY LOCCODE, CALLDATE DESC WITH UR
				";
			
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

			case 'TRLSTAG':  // !TRLSTAG 
		
				$query_string = "
				SELECT RESCODE AS \"TRL #\",
					REMARKS AS \"SE Remarks\", 
					TRIM(LEFT((SELECT CURRENT_ZONE_DESC FROM TRAILER WHERE TRAILER.TRAILER_ID = SE_DATA.RESCODE),30)) AS \"LOCATION\",
					(SELECT ACTIVE_IN_DISP FROM TRAILER WHERE TRAILER_ID = RESCODE) AS \"ACTIVE\" ,
					(SELECT STATUS FROM TRAILER WHERE TRAILER.TRAILER_ID = SE_DATA.RESCODE) AS \"STATUS\",
					(SELECT USER1 FROM TRAILER WHERE TRAILER.TRAILER_ID = SE_DATA.RESCODE) AS \"LESSOR\",
					(SELECT CLASS FROM TRAILER WHERE TRAILER.TRAILER_ID = SE_DATA.RESCODE) AS \"CLASS\"
				FROM SE_DATA WHERE  SECODE = 'REDTAG' AND STATUS = 'ACTIVE' AND RESTYPE = 'TR'
				AND NOT(EXISTS(SELECT STATUS FROM TRAILER WHERE  TRAILER.TRAILER_ID = SE_DATA.RESCODE AND TRAILER.STATUS IN  ('OUTSERV','SHOP')))
				
				ORDER BY RESCODE
				
				WITH UR
				";
			
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

			case 'TRLTODO':  // !TRLTODO 
		
				$query_string = "
SELECT TRAILER_ID \"TRL #\",
 (SELECT ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1  + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST)) FROM RM_MAINT WHERE LOCCODE = 
TRAILER_ID AND JOBCODE = 'DOT' AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1  + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST))  <=90) \"DOT\", 
 (SELECT DATE(DATELAST) FROM RM_MAINT WHERE LOCCODE = TRAILER_ID AND JOBCODE = 'DOT' AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1  + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST))  <=90) \"LAST DOT\",
 (SELECT ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1) FROM RM_MAINT WHERE LOCCODE = TRAILER_ID AND JOBCODE = 'MAINT' AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1 )  <= 30)  \"PM\" ,
 (SELECT DATE(DATELAST) FROM RM_MAINT WHERE LOCCODE = TRAILER_ID AND JOBCODE = 'MAINT' AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1 )  <= 30) \"LAST PM\",
 (SELECT CAST(NOTES as VARCHAR(60)) FROM RM_CALLIN WHERE LOCTYPE = 'TR' AND  LOCCODE = TRAILER_ID AND DONE = 'False' AND PRIORITY <> 99 AND WOCOMPLETED_DT IS NULL ORDER BY CALLINID DESC FETCH FIRST ROW ONLY) \"CALL IN\",
 (SELECT NAME FROM RM_CALLIN WHERE LOCTYPE = 'TR' AND LOCCODE = TRAILER_ID AND DONE = 'False' AND PRIORITY <> 99 AND WOCOMPLETED_DT IS NULL ORDER BY CALLINID DESC FETCH FIRST ROW ONLY) \"CALLER\",
  (SELECT CALLDATE FROM RM_CALLIN  WHERE LOCTYPE = 'TR' AND LOCCODE = TRAILER_ID AND DONE = 'False' AND PRIORITY <> 99 AND WOCOMPLETED_DT IS NULL ORDER BY CALLINID DESC FETCH FIRST ROW ONLY) \"DATE\",
 LEFT(CURRENT_ZONE_DESC,30) \"LOCATION\",
 CLASS \"CLASS\",
 STATUS \"STATUS\",
 FINAL_DESTINATION_DESC \"FINAL DESTINATION\"
 FROM \"TRAILER\"
 
 WHERE ACTIVE_IN_DISP = 'True'
 AND ZONE IN ('MANNING')

AND NOT( STATUS IN ('LEASEOS','OSAVBL', 'STORAGE','MANPROP','SHOP','LEASEDTRL')) 
 AND NOT(CLASS LIKE 'OS%') AND CLASS IN ('CAB53V','OTR53V','OTR53VNVEN')
 AND NOT(STATUS IN  ('OUTSERV','STORAGEAVL'))
 AND NOT(EXISTS(SELECT DATA FROM CUSTOM_DATA WHERE CUSTDEF_ID = 74 AND SRC_TABLE_KEY = TRAILER_ID))
 AND (EXISTS(SELECT JOBCODE FROM RM_MAINT WHERE  LOCCODE = TRAILER_ID AND ((JOBCODE = 'DOT' AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1  + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST))  <=90 ) OR JOBCODE = 'MAINT'
 AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1 )  <= 30)) OR EXISTS(SELECT CALLDATE FROM RM_CALLIN WHERE LOCCODE = TRAILER_ID AND LOCTYPE = 'TR' AND DONE = 'False' AND PRIORITY <> 99 AND WOCOMPLETED_DT IS NULL)) 
 ORDER BY DOT,PM,TRAILER_ID WITH UR

					for read only
				";
			
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

			case 'TRLTODOSH':  // !TRLTODOSH 
		
				$query_string = "
SELECT TRAILER_ID \"TRL #\",
(SELECT ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1  + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST)) FROM RM_MAINT WHERE LOCCODE = 
TRAILER_ID AND JOBCODE = 'DOT' AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1  + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST))  <=90) \"DOT\", 
 (SELECT DATE(DATELAST) FROM RM_MAINT WHERE LOCCODE = TRAILER_ID AND JOBCODE = 'DOT' AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1  + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST))  <=90) \"LAST DOT\",
(SELECT ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1) FROM RM_MAINT WHERE LOCCODE = TRAILER_ID AND JOBCODE = 'MAINT' AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1 )  <= 30)  \"PM\" ,
(SELECT DATE(DATELAST) FROM RM_MAINT WHERE LOCCODE = TRAILER_ID AND JOBCODE = 'MAINT' AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1 )  <= 30) \"LAST PM\",
(SELECT CAST(NOTES as VARCHAR(60)) FROM RM_CALLIN WHERE LOCTYPE = 'TR' AND  LOCCODE = TRAILER_ID AND DONE = 'False' AND PRIORITY <> 99 AND WOCOMPLETED_DT IS NULL ORDER BY CALLINID DESC FETCH FIRST ROW ONLY) \"CALL IN\",
(SELECT NAME FROM RM_CALLIN WHERE LOCTYPE = 'TR' AND LOCCODE = TRAILER_ID AND DONE = 'False' AND PRIORITY <> 99 AND WOCOMPLETED_DT IS NULL ORDER BY CALLINID DESC FETCH FIRST ROW ONLY) \"CALLER\",
  (SELECT CALLDATE FROM RM_CALLIN  WHERE LOCTYPE = 'TR' AND LOCCODE = TRAILER_ID AND DONE = 'False' AND PRIORITY <> 99 AND WOCOMPLETED_DT IS NULL ORDER BY CALLINID DESC FETCH FIRST ROW ONLY) \"DATE\",
LEFT(CURRENT_ZONE_DESC,30) \"LOCATION\",
CLASS \"CLASS\",
STATUS \"STATUS\",
FINAL_DESTINATION_DESC \"FINAL DESTINATION\"
FROM \"TRAILER\"

 WHERE ACTIVE_IN_DISP = 'True'
AND ZONE IN ('MANNING')
AND NOT( STATUS IN ('LEASEOS','OSAVBL', 'STORAGE','MANPROP','SHOP')) 
 AND NOT(CLASS LIKE 'OS%') AND NOT(CLASS LIKE 'CAB53V')AND NOT(CLASS LIKE 'OTR53V')AND NOT(CLASS LIKE 'OTR53VNVEN')
AND NOT(STATUS IN  ('OUTSERV','STORAGEAVL'))
AND NOT(EXISTS(SELECT DATA FROM CUSTOM_DATA WHERE CUSTDEF_ID = 74 AND SRC_TABLE_KEY = TRAILER_ID))
AND (EXISTS(SELECT JOBCODE FROM RM_MAINT WHERE  LOCCODE = TRAILER_ID AND ((JOBCODE = 'DOT' AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1  + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST))  <=90 ) OR JOBCODE = 'MAINT'
AND ((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE)) - 1 )  <= 30)) OR EXISTS(SELECT CALLDATE FROM RM_CALLIN WHERE LOCCODE = TRAILER_ID AND LOCTYPE = 'TR' AND DONE = 'False' AND PRIORITY <> 99 AND WOCOMPLETED_DT IS NULL)) 
 ORDER BY STATUS,DOT,PM,TRAILER_ID WITH UR

					for read only
				";
			
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

			case 'PUDOT':  // !PUDOT 
		
				$query_string = "
				SELECT RM_MAINT.LOCCODE AS \"PU #\",
					((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE) - 1) + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST) ) AS \"DAYS\" ,
					DATE(DATELAST) AS \"LAST DOT\",
					LEFT((SELECT CURRENT_ZONE_DESC FROM PUNIT WHERE PUNIT.UNIT_ID = RM_MAINT.LOCCODE),20) AS \"LOCATION DESCRIPTION\"
					
					FROM RM_MAINT 
					WHERE
					RM_MAINT.LOCCODE IN (SELECT UNIT_ID FROM PUNIT WHERE ACTIVE_WHERE = 'D' ) AND RM_MAINT.LOCTYPE = 'PU'
					AND RM_MAINT.JOBCODE IN ('DOT')
				--	AND( (DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE) - 1) + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST)) <= 90
				ORDER BY DAYS WITH UR
				";
			
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string);
					echo "</pre>";
				}
				
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				$response = convert_from_latin1_to_utf8_recursively( $response );
				
				if( is_array($response) ) {
							
					if( $debug ) {
						echo "<pre>";
						var_dump($response );
						var_dump(json_encode( $response, JSON_THROW_ON_ERROR, 10 ) );
						echo "</pre>";
					} else {
						echo encryptData(json_encode( $response, JSON_THROW_ON_ERROR, 10 ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
				break;

			case 'PUA':  // !PUA 
		
				$query_string = "
				SELECT RM_MAINT.LOCCODE AS \"PU #\",
					
					INT((DISTLAST + DISTINT - (SELECT DISTMETER FROM RM_MILE WHERE LOCTYPE = 'PU' AND LOCCODE = RM_MAINT.LOCCODE ORDER BY CURDATE DESC FETCH FIRST ROW ONLY) ) ) AS \"PM\",
					TO_CHAR(DATE(DATELAST), 'mm/dd/yyyy') AS \"LAST PM\",
					TRIM(LEFT((SELECT CURRENT_ZONE_DESC FROM PUNIT WHERE PUNIT.UNIT_ID = RM_MAINT.LOCCODE),20)) AS \"LOCATION DESCRIPTION\"
					
					FROM RM_MAINT
					WHERE  RM_MAINT.LOCTYPE = 'PU' AND RM_MAINT.LOCCODE IN (SELECT UNIT_ID FROM PUNIT WHERE ACTIVE_WHERE = 'D' ) AND DISTINT <> 0
					AND RM_MAINT.JOBCODE IN ('A')
					ORDER BY PM
					WITH UR
				";
			
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string);
					echo "</pre>";
				}
				
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				$response = convert_from_latin1_to_utf8_recursively( $response );
				
				if( is_array($response) ) {
							
					if( $debug ) {
						echo "<pre>";
						var_dump($response );
						var_dump(json_encode( $response, JSON_THROW_ON_ERROR, 10 ) );
						echo "</pre>";
					} else {
						echo encryptData(json_encode( $response, JSON_THROW_ON_ERROR, 10 ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
				break;

			case 'PUAUPD':  // !PUAUPD 
				if( $UID == "NONE" || $DT == "NONE" || $TYPE == "NONE" ||
					$JOBCODE == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
		
					$query_string = "
					UPDATE RM_MAINT
					SET DATELAST = '".$DT."'
					WHERE LOCTYPE = '".$TYPE."'
					AND LOCCODE = '".$UID."'
					AND JOBCODE = '".$JOBCODE."'
					";
					
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string);
						echo "</pre>";
					}
					
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

			case 'PUDEFAULT':  // !PUDEFAULT 
		
				$query_string = "
				SELECT UNIT_ID AS \"PU #\", VIN,
					(CASE CLASS WHEN 'CONSLP' THEN 'SLP' WHEN 'CONVDC' THEN 'DAY' WHEN 'ST' THEN 'STR' END) AS \"CLASS\",
					(SELECT DRIVER_ID FROM DRIVER WHERE DEFAULT_PUNIT = UNIT_ID  FETCH FIRST ROW ONLY) AS \"DEFAULT\",
					(SELECT LS_DRIVER FROM LEGSUM WHERE LS_POWER_UNIT = UNIT_ID AND ACTIVE_REC IS NULL AND LS_LEG_STAT IN  ('ACTIVE','PLANNED')  FETCH FIRST ROW ONLY) AS \"DISPATCH\",
					OWNERSHIP_TYPE AS \"RENTED OR LEASED\",
					(SELECT NAME FROM VENDOR WHERE VENDOR_ID = OWNER) AS \"OWNER\",
					CURRENT_ZONE_DESC AS \"LOCATION DESCRIPTION\"
					
					FROM PUNIT
					WHERE OWNER IN ('269','102284','674','100121','100764')
					AND ACTIVE_WHERE = 'D'
					AND NOT(UNIT_ID IN ('118','133'))
					ORDER BY CLASS, UNIT_ID
					WITH UR
				";
			
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string);
					echo "</pre>";
				}
				
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				$response = convert_from_latin1_to_utf8_recursively( $response );
				
				if( is_array($response) ) {
							
					if( $debug ) {
						echo "<pre>";
						var_dump($response );
						var_dump(json_encode( $response, JSON_THROW_ON_ERROR, 10 ) );
						echo "</pre>";
					} else {
						echo encryptData(json_encode( $response, JSON_THROW_ON_ERROR, 10 ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
				break;

			case 'PUTAG':  // !PUTAG 
		
				$query_string = "
				SELECT RESCODE AS \"PU #\",
					REMARKS AS \"REMARKS\",
					STATUS AS \"LOC/STATUS\",
					LEFT((SELECT CURRENT_ZONE_DESC FROM PUNIT WHERE PUNIT.UNIT_ID = SE_DATA.RESCODE),30) AS \"LOCATION\"  FROM SE_DATA WHERE  SECODE = 'REDTAG' AND STATUS = 'ACTIVE'
					
					AND RESCODE IN (SELECT UNIT_ID FROM PUNIT WHERE ACTIVE_IN_DISP = 'True' AND NOT(ZONE IN ('MANSTORAGE')) AND NOT( STATUS IN ('OSAVBL', 'STORAGE','MANPROP'))) 
					
					UNION
					
					(SELECT UNIT_ID AS \"PU #\",
					STATUS AS \"SE CODE\",
					(SELECT SUBSTR(STAT_COMMENT,LOCATE(':',STAT_COMMENT)+1) FROM PUSTAT WHERE CODE = UNIT_ID 
					AND STATUS IN ('OUTSERV','SHOP')  ORDER BY TIME_CHANGED DESC FETCH FIRST ROW ONLY) AS \"COMMENTS\",
					CURRENT_ZONE_DESC AS \"Call In\"
					
					FROM PUNIT WHERE STATUS IN ( 'OUTSERV','UNAVL')
					AND OWNERSHIP_TYPE in ('L','C')
					AND ACTIVE_WHERE = 'D'
					AND NOT(OWNER IN ('674','100764')))
					WITH UR
				";
			
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
						echo encryptData(json_encode( $response, 0, 10 ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
				break;

			case 'PULOCATION':  // !PULOCATION 
		
				$query_string = "
				SELECT LAST_DRIVER_1 AS \"LAST DRIVER CODE\",
					UNIT_ID AS \"PU #\",
					LAST_SAT_LOC AS \"LAST POLL LOCATION\" ,
					FINAL_DESTINATION_DESC AS \"FINAL DESTINATION DESCRIPTION\"
					
					FROM PUNIT
					WHERE LAST_SAT_ZONE IN (SELECT SUBZONE FROM FASTZONE WHERE ZONE_CODE IN ('MN','IA','WI','SD','ND')) AND FINAL_DESTINATION IN (SELECT SUBZONE FROM FASTZONE WHERE ZONE_CODE IN ('MN','IA','WI','SD','ND'))AND ACTIVE_IN_DISP = 'True' 
					AND  CLASS = 'CONSLP' AND OWNER IN ('269','674','102284')
					
					ORDER BY UNIT_ID WITH UR
				";
			
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
						echo encryptData(json_encode( $response, 0, 10 ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
				break;

			case 'PUCALLIN':  // !PUCALLIN 
		
				$query_string = "
				SELECT LOCCODE AS \"PU #\",
					CAST(NOTES as VARCHAR(200)) AS \"DRIVER REPORT\",
					NAME AS \"CALLER\",
					to_char(CALLDATE, 'mm/dd/yyyy hh:mm') AS \"DATE\",
					LOCTYPE AS \"TYPE\",
					CALLINID AS \"ID\",
					LEFT((SELECT CURRENT_ZONE_DESC FROM PUNIT WHERE PUNIT.UNIT_ID = RM_CALLIN.LOCCODE),20) AS \"LOCATION DESCRIPTION\"
					
					FROM RM_CALLIN WHERE LOCTYPE = 'PU' 
					
					AND DONE = 'False' AND LOCCODE IN (SELECT UNIT_ID FROM PUNIT WHERE ACTIVE_WHERE = 'D')  AND PRIORITY <> 99
					
					ORDER BY LOCCODE, CALLDATE DESC WITH UR
				";
			
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
						echo encryptData(json_encode( $response, 0, 10 ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
				break;

			case 'PUB':  // !PUB 
		
				$query_string = "
					SELECT RM_MAINT.LOCCODE AS \"PU #\",
					
						INT((DISTLAST + DISTINT - (SELECT DISTMETER FROM RM_MILE WHERE LOCTYPE = 'PU' AND LOCCODE =RM_MAINT.LOCCODE ORDER BY CURDATE DESC FETCH FIRST ROW ONLY))) AS \"PM\",
						DATE(DATELAST) AS \"LAST PM\"
					
					FROM RM_MAINT
					WHERE  RM_MAINT.LOCTYPE = 'PU' AND RM_MAINT.LOCCODE IN (SELECT UNIT_ID FROM PUNIT WHERE ACTIVE_WHERE = 'D' )
					AND RM_MAINT.JOBCODE IN ('B') WITH UR
				";
			
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
						echo encryptData(json_encode( $response, 0, 10 ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
				break;

			case 'PUC':  // !PUC 
		
				$query_string = "
				SELECT RM_MAINT.LOCCODE AS \"PU #\",
				DAYS((DATE(DATELAST) + DATEINT DAYS)) - DAYS(CURRENT DATE) AS \"DAYS\",
				to_char(DATE(DATELAST), 'mm/dd/yyyy') AS \"LAST PM\"

				FROM RM_MAINT
				WHERE RM_MAINT.LOCTYPE = 'PU'
				AND RM_MAINT.LOCCODE IN
					(SELECT UNIT_ID FROM PUNIT WHERE ACTIVE_WHERE = 'D' )
				AND RM_MAINT.JOBCODE IN ('C') ORDER BY DAYS WITH UR
				";
			
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
						echo encryptData(json_encode( $response, 0, 10 ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
				break;

			case 'PUE':  // !PUE 
		
				$query_string = "
				SELECT RM_MAINT.LOCCODE AS \"PU #\",
				((DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE) - 1) + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST) ) AS \"PM\",
				to_char(DATE(DATELAST), 'mm/dd/yyyy') AS \"LAST PM\"
					
					FROM RM_MAINT 
					WHERE
					RM_MAINT.LOCCODE IN (SELECT UNIT_ID FROM PUNIT WHERE ACTIVE_WHERE = 'D' ) AND RM_MAINT.LOCTYPE = 'PU'
					AND RM_MAINT.JOBCODE IN ('E') AND( (DAYS(DATELAST + DATEINT DAYS) - DAYS(CURRENT DATE) - 1) + (DAY((DATELAST + 1 MONTH) - DAY(DATELAST) DAYS)) - DAY(DATELAST)) <= 365 ORDER BY PM WITH UR
				";
			
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
						echo encryptData(json_encode( $response, 0, 10 ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
				break;

			case 'TRREPORT':  // !TRREPORT - Report by truck 
				if( $UID == "NONE" || $DT == "NONE" || $DT2 == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
		
					$query_string = "
SELECT T.BILL_NUMBER,

(SELECT CAST(LISTAGG(I.TRIP_NUMBER || '-' || L.LS_LEG_SEQ, ', ') 
WITHIN GROUP(ORDER BY I.TRIP_NUMBER ASC, L.LS_LEG_SEQ ASC) AS VARCHAR(200))
FROM ITRIPTLO I, LEGSUM L
WHERE I.BILL_NUMBER = T.BILL_NUMBER
AND L.LS_TRIP_NUMBER = I.TRIP_NUMBER) AS TRIP_LEG,

(SELECT CAST(LISTAGG(I.TRIP_NUMBER || '-' || L.LS_LEG_SEQ, ', ') 
WITHIN GROUP(ORDER BY I.TRIP_NUMBER ASC, L.LS_LEG_SEQ ASC) AS VARCHAR(200))
FROM ITRIPTLO I, LEGSUM L
WHERE I.BILL_NUMBER = T.BILL_NUMBER
AND L.LS_TRIP_NUMBER = I.TRIP_NUMBER
AND L.LS_POWER_UNIT = '".$UID."') AS TRIP_LEG_PU,

(SELECT CAST(LISTAGG(DISTINCT L.LS_POWER_UNIT, ', ') 
WITHIN GROUP(ORDER BY L.LS_POWER_UNIT ASC) AS VARCHAR(200))
FROM ITRIPTLO I, LEGSUM L
WHERE I.BILL_NUMBER = T.BILL_NUMBER
AND L.LS_TRIP_NUMBER = I.TRIP_NUMBER
AND L.LS_POWER_UNIT != '".$UID."') AS OTHER_TRUCKS,

DECIMAL(T.TOTAL_CHARGES, 8, 2) AS TOTAL_CHARGES,
USER1, USER4, USER6

FROM TLORDER T
WHERE T.CURRENT_STATUS IN( 'BILLD', 'APPRVD', 'COMPLETE')
AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
AND T.BILL_NUMBER <> '0'
AND UPPER(T.EXTRA_STOPS) <> 'CHILD'
AND T.BILL_NUMBER NOT LIKE 'Q%'
AND COALESCE(T.DELIVER_BY, T.ACTUAL_DELIVERY) BETWEEN '".$DT."' AND '".$DT2."'

AND EXISTS (SELECT L.LS_LEG_SEQ
FROM ITRIPTLO I, LEGSUM L
WHERE I.BILL_NUMBER = T.BILL_NUMBER
AND L.LS_TRIP_NUMBER = I.TRIP_NUMBER
AND L.LS_POWER_UNIT = '".$UID."')

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
				}
				break;

			case 'MP':  // !MP Macropoint 
		
				$query_string = "
					SELECT 
					
					TRIP_ID AS \"Sender.LoadId\",
					UNIT_ID  AS \"SenderVehicleId\",
					USER8 AS \"Requestor.MpId\",
					
					(SELECT TRACE_NUMBER FROM TRACE
					WHERE DETAIL_NUMBER = DETAIL_LINE_ID
					AND TRACE_TYPE = 'K') AS \"Requstor.LoadId\", 

					(SELECT TRACE_NUMBER FROM TRACE
					WHERE DETAIL_NUMBER = DETAIL_LINE_ID
					AND TRACE_TYPE = '5') AS \"Requstor.LoadId2\", 
          
					USER8 AS \"AllowAccessFrom.MpId\",
					TRIM(REPLACE(DOLLARVAL(DECIMAL(LATLONG_DMS_TO_DEG(POSLAT), 7, 4),4),'$','')) AS \"Location.Coordinates.Latitude\",
					TRIM(REPLACE(DOLLARVAL(DECIMAL(LATLONG_DMS_TO_DEG(POSLONG), 7, 4),4),'$','')) AS \"Location.Coordinates.Longitude\",
					'' AS \"Location.Coordinates.Address.Line1\",
					'' AS \"Location.Coordinates.Address.Line2\",
					'' AS \"Location.Coordinates.Address.City\",
					'' AS \"Location.Coordinates.Address.StateOrProvince\",
					'' AS \"Location.Coordinates.Address.PostalCode\",
					'' AS \"Location.Coordinates.Address.CountryCode\",
					'' AS \"Uncertainty\",
					VARCHAR_FORMAT( ROW_TIMESTAMP - CURRENT TIMEZONE, 'MM/DD/YYYY HH24:MI') AS\"CreatedDateTime\",
					'' AS \"Mobile\",
					UNIT_ID  AS \"VehicleId\",
					'' AS \"TrailerId\"
					    
					FROM (SELECT M.ROW_TIMESTAMP, M.POSLAT, M.POSLONG, UNIT_ID, TRIP_ID, I.DETAIL_LINE_ID, T.BILL_NUMBER, C.CLIENT_ID, C.USER8
						FROM
							(SELECT M1.ROW_TIMESTAMP, M2.POSLAT FROM, M2.POSLONG, M1.UNIT_ID, M1.TRIP_ID
					FROM (
					SELECT P.UNIT_ID, P.CURRENT_TRIP AS TRIP_ID, max(M.ROW_TIMESTAMP) ROW_TIMESTAMP
							
							FROM MESSAGE_RETURN M, PUNIT P
							WHERE M.ROW_TIMESTAMP > '5/2/2024'
							AND P.UNIT_ID = M.\"POWER\"
							AND P.CURRENT_TRIP > 0
					group by P.UNIT_ID, P.CURRENT_TRIP) AS M1(UNIT_ID, TRIP_ID, ROW_TIMESTAMP)
					LEFT JOIN message_reTURN M2
					ON M1.ROW_TIMESTAMP = M2.ROW_TIMESTAMP
					AND M1.UNIT_ID = M2.\"POWER\") AS M(ROW_TIMESTAMP, POSLAT, POSLONG, UNIT_ID, TRIP_ID)
						
						LEFT JOIN ITRIPTLO I
						ON M.TRIP_ID = I.TRIP_NUMBER
						
						LEFT JOIN TLORDER T
						ON I.DETAIL_LINE_ID = T.DETAIL_LINE_ID
						AND UPPER(T.EXTRA_STOPS) != 'CHILD'
						
						LEFT JOIN CLIENT C
						ON T.BILL_TO_CODE = C.CLIENT_ID)
					WHERE USER8 IS NOT NULL
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
						echo encryptData(json_encode( $response, 0, 10 ));
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
