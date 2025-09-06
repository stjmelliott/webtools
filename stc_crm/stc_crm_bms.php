<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );
set_time_limit(0);

require_once( "./odbc-inc.php" );

	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman72";
	$option		= "NONE";
	$uid		= "NONE";
	$cid		= "NONE";
	$cnm		= "NONE";
	$own		= "NONE";
	$days		= "90";

	$LANE				= "NONE";
	$NAME				= "NONE";
	$ZONE1				= "NONE";
	$ZONE2				= "NONE";
	$RANGE1				= "50";
	$RANGE2				= "50";
	$COMMODITY			= "NONE";
	$BUSINESS_PHONE		= "NONE";
	$BUSINESS_PHONE_EXT	= "NONE";
	$FAX_PHONE			= "NONE";
	$EMAIL_ADDRESS		= "NONE";
	$COMPANY_URL		= "NONE";
	$INTERLINER_ID		= "NONE";
	$PROV				= "NONE";
	$OP					= "NONE";
	$OC					= "NONE";
	$DP					= "NONE";
	$DC					= "NONE";
	$THE_VALUE			= "NONE";
	$REGION				= "NONE";
	$TERMINAL			= "NONE";
	$EQUIPMENT			= "NONE";
	$SIZE				= "NONE";
	$SITE				= "NONE";
	$STATUS				= "NONE";
	$SCORE				= "NONE";
	$DT					= "NONE";
	$DT2				= "NONE";
	$BILLTO				= "NONE";
	$SHIPPER			= "NONE";
	$CONS				= "NONE";
	
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
		} else if( $key == "INTERLINER_ID" ) {
			$INTERLINER_ID = $value;
		} else if( $key == "PROV" ) {
			$PROV = $value;
		} else if( $key == "OP" ) {
			$OP = $value;
		} else if( $key == "OC" ) {
			$OC = $value;
		} else if( $key == "DP" ) {
			$DP = $value;
		} else if( $key == "DC" ) {
			$DC = $value;
		} else if( $key == "THE_VALUE" ) {
			$THE_VALUE = $value;
		} else if( $key == "REGION" ) {
			$REGION = $value;
		} else if( $key == "TERMINAL" ) {
			$TERMINAL = $value;
		} else if( $key == "EQUIPMENT" ) {
			$EQUIPMENT = $value;
		} else if( $key == "SIZE" ) {
			$SIZE = $value;
		} else if( $key == "SITE" ) {
			$SITE = $value;
		} else if( $key == "STATUS" ) {
			$STATUS = $value;
		} else if( $key == "SCORE" ) {
			$SCORE = $value;
		} else if( $key == "DT" ) {
			$DT = $value;
		} else if( $key == "DT2" ) {
			$DT2 = $value;
		} else if( $key == "BILLTO" ) {
			$BILLTO = $value;
		} else if( $key == "SHIPPER" ) {
			$SHIPPER = $value;
		} else if( $key == "CONS" ) {
			$CONS = $value;
		}
	}
	
	if( $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>STC CRM Backend - BMS Functions</title>
</head>

<body>
<?
	}
	
	if( $password == $valid_pw ) {
		
		switch (strtoupper($option)) {

			case 'LIST':  //! LIST - Show customers with lanes
				
				// Prepare Select
				$query_string = "select C.CLIENT_ID, C.NAME, C.SALES_REP, count(t.BILL_NUMBER) AS LANES			
					from tlorder t, client c
					where t.CURRENT_STATUS = 'QUOTE'
					and t.BILL_TO_CODE = C.CLIENT_ID
					GROUP BY C.CLIENT_ID, C.NAME, C.SALES_REP
					ORDER BY 4 DESC
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

			case 'POSS':  //! POSS - Show customers with lanes
				// Validate fields
				if( $CLIENT_ID == "NONE" ||  $ZONE1 == "NONE" || $ZONE2 == "NONE" || $COMMODITY == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
						$query_string = "select S.BILL_NUMBER, S.CURRENT_STATUS, S.DELIVERY_DATE, S.DISTANCE, 
							S.DISTANCE_UNITS, S.START_ZONE_DESC, S.END_ZONE_DESC, S.CHARGES, S.COMMODITY, S.RATE,
							COALESCE(K.USER4, S.MARGIN) AS MARGIN,
							COALESCE(K.USER5, S.MARGINP) AS MARGINP,
							K.USER4 AS RB_MARGIN, K.USER5 AS RB_MARGINP, MAX(K.BILL_DATE)
							FROM
							(SELECT T.BILL_NUMBER, T.current_status,
							DATE(COALESCE(T.ACTUAL_DELIVERY, T.DELIVER_BY)) AS DELIVERY_DATE,
							T.distance, T.DISTANCE_UNITS, T.START_ZONE_DESC, T.END_ZONE_DESC, T.charges,
							
							(SELECT D.COMMODITY FROM TLDTL D
							WHERE T.DETAIL_LINE_ID = D.ORDER_ID 
							FETCH FIRST 1 ROW ONLY) AS COMMODITY,

							round(float(T.charges)/float(T.distance),2) rate,
							CASE WHEN T.CURRENT_STATUS='BILLD' THEN T.USER4 END as MARGIN,
							CASE WHEN T.CURRENT_STATUS='BILLD' THEN T.USER5 END as MARGINP

							from tlorder T
							where T.START_ZONE = '".$ZONE1."'
							and T.END_ZONE = '".$ZONE2."'
							and T.bill_to_code = '".$CLIENT_ID."'
							and T.bill_number not like 'Q%'
							and T.current_status = 'BILLD'
							and T.document_type = 'INVOICE'
							AND COALESCE(T.INT_PAYABLE_AMT, 0.0) > 0.0
							AND COMMODITY = '".$COMMODITY."'
							and t.extra_stops <> 'Child'
							and DATE(COALESCE(T.ACTUAL_DELIVERY, T.DELIVER_BY)) > current date - ".$days." days)
							as S(BILL_NUMBER, CURRENT_STATUS, DELIVERY_DATE, DISTANCE, DISTANCE_UNITS, 
							START_ZONE_DESC, END_ZONE_DESC, CHARGES,
							COMMODITY, RATE, MARGIN, MARGINP)
							LEFT OUTER JOIN TLORDER K
							ON K.BILL_NUMBER = S.BILL_NUMBER
							AND K.DOCUMENT_TYPE = 'REBILL'
							GROUP BY S.BILL_NUMBER, S.CURRENT_STATUS, S.DELIVERY_DATE, S.DISTANCE, S.DISTANCE_UNITS, 
							S.START_ZONE_DESC, S.END_ZONE_DESC, S.CHARGES,
							S.COMMODITY, S.RATE, S.MARGIN, S.MARGINP,
							K.USER4, K.USER5
							order by 2 asc
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
				}

				break;

			case 'POTENTIAL':  //! POTENTIAL - Show potential lanes
				
				// Prepare Select
				$query_string = "SELECT BILL_TO_CODE, BILL_TO_NAME, START_ZONE, END_ZONE, 
				START_ZONE_DESC, END_ZONE_DESC, COMMODITY, LOADS, ARATE, AMARGIN, AMARGINP, C.SALES_REP
FROM
(SELECT BILL_TO_CODE, BILL_TO_NAME, START_ZONE, END_ZONE, START_ZONE_DESC, END_ZONE_DESC, COMMODITY,
COUNT(*) AS LOADS, ROUND(AVG(RATE),2) AS ARATE, ROUND(AVG(FLOAT(MARGIN)),2) AS AMARGIN, ROUND(AVG(FLOAT(MARGINP)),1) AS AMARGINP
FROM

(SELECT S.BILL_NUMBER, S.BILL_TO_CODE, S.BILL_TO_NAME, S.START_ZONE, S.END_ZONE, S.START_ZONE_DESC, S.END_ZONE_DESC, S.COMMODITY, S.RATE,
COALESCE(K.USER4, S.USER4) AS MARGIN,
COALESCE(K.USER5, S.USER5) AS MARGINP, MAX(K.BILL_DATE)

FROM
	(SELECT BILL_NUMBER, BILL_TO_CODE, BILL_TO_NAME, START_ZONE, END_ZONE, START_ZONE_DESC, END_ZONE_DESC,
			(SELECT D.COMMODITY FROM TLDTL D WHERE T.DETAIL_LINE_ID = D.ORDER_ID FETCH FIRST 1 ROW ONLY) AS COMMODITY,
			CASE WHEN T.DISTANCE>0 THEN ROUND(FLOAT(T.CHARGES)/FLOAT(T.DISTANCE),2) END AS RATE,
			USER4, USER5
		FROM TLORDER T
		WHERE DATE(COALESCE(T.ACTUAL_DELIVERY, T.DELIVER_BY)) > CURRENT DATE - 90 DAYS
		AND T.BILL_NUMBER NOT LIKE 'Q%'
		AND T.CURRENT_STATUS = 'BILLD'
		AND COALESCE(T.INT_PAYABLE_AMT, 0.0) > 0.0
		AND T.DOCUMENT_TYPE = 'INVOICE')
AS S(BILL_NUMBER, BILL_TO_CODE, BILL_TO_NAME, START_ZONE, END_ZONE, START_ZONE_DESC, END_ZONE_DESC, COMMODITY, RATE, USER4, USER5)

LEFT OUTER JOIN TLORDER K
	ON K.BILL_NUMBER = S.BILL_NUMBER
	AND K.DOCUMENT_TYPE = 'REBILL'
	
GROUP BY S.BILL_NUMBER, S.BILL_TO_CODE, S.BILL_TO_NAME, S.START_ZONE, S.END_ZONE, 
	S.START_ZONE_DESC, S.END_ZONE_DESC, S.COMMODITY, S.RATE,
	COALESCE(K.USER4, S.USER4), COALESCE(K.USER5, S.USER5))

GROUP BY BILL_TO_CODE, BILL_TO_NAME, START_ZONE, END_ZONE, START_ZONE_DESC, END_ZONE_DESC, COMMODITY),
CLIENT C
WHERE LOADS > 10
AND BILL_TO_CODE = C.CLIENT_ID
ORDER BY LOADS DESC
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

			case 'MYLANES':  //! MYLANES - Show lanes for a given sales rep				
				// Prepare Select
				$query_string = "select t.BILL_NUMBER, t.CUSTOMER, c.NAME,
					T.START_ZONE, T.END_ZONE,
					START_ZONE_DESC,
					END_ZONE_DESC,
					ORIGCITY, ORIGPROV,
					DESTCITY, DESTPROV,
					T.COMMODITY, T.PIECES, T.WEIGHT, T.LENGTH_1, 
					T.DISTANCE, T.DISTANCE_UNITS, T.CUBE, T.PALLETS, T.VOLUME, T.AREA,
					T.TIME_1, T.CHARGES, T.TOTAL_CHARGES,
					T.USER2 AS COMMITMENT, c.SALES_REP,
					
					(SELECT D.RATE FROM TLDTL D
						WHERE T.DETAIL_LINE_ID = D.ORDER_ID 
						FETCH FIRST 1 ROW ONLY) AS RATE,
						
					
					(select count(a.bill_number) / 12.0
						from tlorder a
						where a.START_ZONE = t.START_ZONE
						and a.END_ZONE = t.END_ZONE
						and a.bill_to_code = t.bill_to_code
						and (SELECT D.COMMODITY FROM TLDTL D
						WHERE a.DETAIL_LINE_ID = D.ORDER_ID 
						FETCH FIRST 1 ROW ONLY) = T.COMMODITY
						and a.bill_number not like 'Q%'
						and a.current_status <> 'CANCL'
						and a.document_type = 'INVOICE'
						and a.charges > 0.0
						and DATE(COALESCE(a.ACTUAL_DELIVERY, a.DELIVER_BY)) > current date - 90 days) AS ACTUAL,

					(select avg( ".$stc_schema.".STC_GET_RATE_PER_MILE(b.DETAIL_LINE_ID) ) AS RATE
						from tlorder b
						where b.START_ZONE = t.START_ZONE
						and b.END_ZONE = t.END_ZONE
						and b.bill_to_code = t.bill_to_code
						and (SELECT D.COMMODITY FROM TLDTL D
						WHERE b.DETAIL_LINE_ID = D.ORDER_ID
						FETCH FIRST 1 ROW ONLY) = t.COMMODITY
						and b.bill_number not like 'Q%'
						and b.current_status <> 'CANCL'
						and b.document_type = 'INVOICE'
						and b.charges > 0.0
						and DATE(COALESCE(b.ACTUAL_DELIVERY, b.DELIVER_BY)) > current date - 90 days) AS ARATE

												
					from tlorder t, client c
					where t.CURRENT_STATUS = 'QUOTE'
					and t.BILL_TO_CODE = c.client_id
					and t.extra_stops <> 'Child'";
					
				if( $uid <> "NONE")
					$query_string .= " and c.sales_rep = '".$uid."'";
				else if( $cid <> "NONE")
					$query_string .= " and c.client_id = '".$cid."'";

				if( $ZONE1 <> "NONE")
					$query_string .= " and ".$stc_schema.".DIST_BETWEEN_ZONES('".$ZONE1."', START_ZONE) < ".$RANGE1;

				if( $ZONE2 <> "NONE")
					$query_string .= " and ".$stc_schema.".DIST_BETWEEN_ZONES('".$ZONE2."', END_ZONE) < ".$RANGE2;
					
				$query_string .= " WITH UR";
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

			case 'RATES': //! RATES - Check rates for a given lane
				// Validate fields
				if( $LANE == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "select t.BILL_NUMBER, t.CUSTOMER, c.NAME,
						T.START_ZONE, T.END_ZONE,
						START_ZONE_DESC,
						END_ZONE_DESC,
						ORIGCITY, ORIGPROV,
						DESTCITY, DESTPROV,
						T.COMMODITY, T.PIECES, T.WEIGHT, T.LENGTH_1, 
						T.DISTANCE, T.DISTANCE_UNITS, T.CUBE, T.PALLETS, T.VOLUME, T.AREA,
						T.TIME_1, T.CHARGES, T.TOTAL_CHARGES,
						T.USER2 AS COMMITMENT,
						
						(SELECT D.RATE FROM TLDTL D
							WHERE T.DETAIL_LINE_ID = D.ORDER_ID 
							FETCH FIRST 1 ROW ONLY) AS RATE,
							
						(SELECT D.COMMODITY FROM TLDTL D
							WHERE T.DETAIL_LINE_ID = D.ORDER_ID 
							FETCH FIRST 1 ROW ONLY) AS COMMODITY,
							
						(select count(a.bill_number) / 12.0
							from tlorder a
							where a.START_ZONE = t.START_ZONE
							and a.END_ZONE = t.END_ZONE
							and a.bill_to_code = t.bill_to_code
							and (SELECT D.COMMODITY FROM TLDTL D
							WHERE a.DETAIL_LINE_ID = D.ORDER_ID 
							FETCH FIRST 1 ROW ONLY) = t.COMMODITY
							and a.bill_number not like 'Q%'
							and a.current_status <> 'CANCL'
							and a.document_type = 'INVOICE'
							and a.charges > 0.0
							and DATE(COALESCE(a.ACTUAL_DELIVERY, a.DELIVER_BY)) > current date - 90 days) AS ACTUAL,

					(select avg( ".$stc_schema.".STC_GET_RATE_PER_MILE(b.DETAIL_LINE_ID) ) AS RATE
						from tlorder b
						where b.START_ZONE = t.START_ZONE
						and b.END_ZONE = t.END_ZONE
						and b.bill_to_code = t.bill_to_code
						and (SELECT D.COMMODITY FROM TLDTL D
						WHERE b.DETAIL_LINE_ID = D.ORDER_ID
						FETCH FIRST 1 ROW ONLY) = t.COMMODITY
						and b.bill_number not like 'Q%'
						and b.current_status <> 'CANCL'
						and b.document_type = 'INVOICE'
						and b.charges > 0.0
						and DATE(COALESCE(b.ACTUAL_DELIVERY, b.DELIVER_BY)) > current date - 90 days) AS ARATE
						
						from tlorder t, client c
						where t.CURRENT_STATUS = 'QUOTE'
						and t.BILL_TO_CODE = c.client_id
						and t.extra_stops <> 'Child'
						and t.BILL_NUMBER = '".$LANE."'
						WITH UR";
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response1 = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response1) ) {
						if( $debug ) {
							echo "<pre>";
							var_dump($response1);
							echo "</pre>";
						}
						$customer = $response1[0]{"CUSTOMER"};
						$startz = $response1[0]{"START_ZONE"};
						$endz = $response1[0]{"END_ZONE"};
						$commodity = $response1[0]{"COMMODITY"};
						
						// Prepare Select
						$query_string = "select S.BILL_NUMBER, S.CURRENT_STATUS, S.DELIVERY_DATE, S.DISTANCE, 
							S.DISTANCE_UNITS, S.CHARGES, S.COMMODITY, S.RATE,
							COALESCE(K.USER4, S.MARGIN) AS MARGIN,
							COALESCE(K.USER5, S.MARGINP) AS MARGINP,
							K.USER4 AS RB_MARGIN, K.USER5 AS RB_MARGINP, MAX(K.BILL_DATE)
							FROM
							(SELECT T.BILL_NUMBER, T.current_status,
							DATE(COALESCE(T.ACTUAL_DELIVERY, T.DELIVER_BY)) AS DELIVERY_DATE,
							T.distance, T.DISTANCE_UNITS, T.charges,
							
							(SELECT D.COMMODITY FROM TLDTL D
							WHERE T.DETAIL_LINE_ID = D.ORDER_ID 
							FETCH FIRST 1 ROW ONLY) AS COMMODITY,
					
							".$stc_schema.".STC_GET_RATE_PER_MILE(T.DETAIL_LINE_ID) as rate,
							
							CASE WHEN T.CURRENT_STATUS='BILLD' THEN T.USER4 END as MARGIN,
							CASE WHEN T.CURRENT_STATUS='BILLD' THEN T.USER5 END as MARGINP

							from tlorder T
							where T.START_ZONE = '".$startz."'
							and T.END_ZONE = '".$endz."'
							and T.bill_to_code = '".$customer."'
							and T.bill_number not like 'Q%'
							and T.current_status <> 'CANCL'
							and T.document_type = 'INVOICE'
							AND COMMODITY = '".$commodity."'
							and t.extra_stops <> 'Child'
							and DATE(COALESCE(T.ACTUAL_DELIVERY, T.DELIVER_BY)) > current date - ".$days." days)
							as S(BILL_NUMBER, CURRENT_STATUS, DELIVERY_DATE, DISTANCE, DISTANCE_UNITS, CHARGES,
							COMMODITY, RATE, MARGIN, MARGINP)
							LEFT OUTER JOIN TLORDER K
							ON K.BILL_NUMBER = S.BILL_NUMBER
							AND K.DOCUMENT_TYPE = 'REBILL'
							GROUP BY S.BILL_NUMBER, S.CURRENT_STATUS, S.DELIVERY_DATE, S.DISTANCE, S.DISTANCE_UNITS, S.CHARGES,
							S.COMMODITY, S.RATE, S.MARGIN, S.MARGINP,
							K.USER4, K.USER5
							order by 2 asc
							for read only
							with ur";
						if( $debug ) echo "<p>using query_string = $query_string</p>";
				
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( is_array($response2) ) {
							if( $debug ) {
								echo "<pre>";
								var_dump($response2);
								echo "</pre>";
							} else {
								$response1[0]{"BILLS"} = $response2;
							}
						}

						// Prepare Select
						$query_string = "select S.BILL_NUMBER, S.CURRENT_STATUS, S.DELIVERY_DATE, S.DISTANCE, 
							S.DISTANCE_UNITS, S.CHARGES, S.COMMODITY, S.RATE,
							COALESCE(K.USER4, S.MARGIN) AS MARGIN,
							COALESCE(K.USER5, S.MARGINP) AS MARGINP,
							K.USER4 AS RB_MARGIN, K.USER5 AS RB_MARGINP, MAX(K.BILL_DATE)
							FROM
							(SELECT T.BILL_NUMBER, T.current_status,
							DATE(COALESCE(T.ACTUAL_DELIVERY, T.DELIVER_BY)) AS DELIVERY_DATE,
							T.distance, T.DISTANCE_UNITS, T.charges,

							(SELECT D.COMMODITY FROM TLDTL D
							WHERE T.DETAIL_LINE_ID = D.ORDER_ID 
							FETCH FIRST 1 ROW ONLY) AS COMMODITY,

							".$stc_schema.".STC_GET_RATE_PER_MILE(T.DETAIL_LINE_ID) as rate,
							
							CASE WHEN T.CURRENT_STATUS='BILLD' THEN T.USER4 END as MARGIN,
							CASE WHEN T.CURRENT_STATUS='BILLD' THEN T.USER5 END as MARGINP

							from tlorder T
							where T.START_ZONE = '".$startz."'
							and T.END_ZONE = '".$endz."'
							and T.bill_to_code <> '".$customer."'
							and T.bill_number not like 'Q%'
							and T.current_status <> 'CANCL'
							and T.document_type = 'INVOICE'
							AND COMMODITY = '".$commodity."'
							and t.extra_stops <> 'Child'
							and DATE(COALESCE(T.ACTUAL_DELIVERY, T.DELIVER_BY)) > current date - ".$days." days)
							as S(BILL_NUMBER, CURRENT_STATUS, DELIVERY_DATE, DISTANCE, DISTANCE_UNITS, CHARGES,
							COMMODITY, RATE, MARGIN, MARGINP)
							LEFT OUTER JOIN TLORDER K
							ON K.BILL_NUMBER = S.BILL_NUMBER
							AND K.DOCUMENT_TYPE = 'REBILL'
							GROUP BY S.BILL_NUMBER, S.CURRENT_STATUS, S.DELIVERY_DATE, S.DISTANCE, S.DISTANCE_UNITS, S.CHARGES,
							S.COMMODITY, S.RATE, S.MARGIN, S.MARGINP,
							K.USER4, K.USER5
							order by 2 asc
							for read only
							with ur";
						if( $debug ) echo "<p>using query_string = $query_string</p>";
				
						$response3 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( is_array($response3) ) {
							if( $debug ) {
								echo "<pre>";
								var_dump($response3);
								echo "</pre>";
							} else {
								$response1[0]{"BILLS2"} = $response3;
							}
						}

						// Prepare Select
						$query_string = "select S.BILL_NUMBER, S.CURRENT_STATUS, S.DELIVERY_DATE, S.DISTANCE, 
							S.DISTANCE_UNITS, S.CHARGES, S.COMMODITY, S.RATE,
							COALESCE(K.USER4, S.MARGIN) AS MARGIN,
							COALESCE(K.USER5, S.MARGINP) AS MARGINP,
							K.USER4 AS RB_MARGIN, K.USER5 AS RB_MARGINP, MAX(K.BILL_DATE)
							FROM
							(SELECT T.BILL_NUMBER, T.current_status,
							DATE(COALESCE(T.ACTUAL_DELIVERY, T.DELIVER_BY)) AS DELIVERY_DATE,
							T.distance, T.DISTANCE_UNITS, T.charges,

							(SELECT D.COMMODITY FROM TLDTL D
							WHERE T.DETAIL_LINE_ID = D.ORDER_ID 
							FETCH FIRST 1 ROW ONLY) AS COMMODITY,

							".$stc_schema.".STC_GET_RATE_PER_MILE(T.DETAIL_LINE_ID) as rate,
							
							CASE WHEN T.CURRENT_STATUS='BILLD' THEN T.USER4 END as MARGIN,
							CASE WHEN T.CURRENT_STATUS='BILLD' THEN T.USER5 END as MARGINP

							from tlorder T
							where T.START_ZONE = '".$startz."'
							and T.END_ZONE = '".$endz."'
							and T.bill_to_code = '".$customer."'
							and T.bill_number not like 'Q%'
							and T.current_status <> 'CANCL'
							and T.document_type = 'INVOICE'
							AND COMMODITY <> '".$commodity."'
							and t.extra_stops <> 'Child'
							and DATE(COALESCE(T.ACTUAL_DELIVERY, T.DELIVER_BY)) > current date - ".$days." days)
							as S(BILL_NUMBER, CURRENT_STATUS, DELIVERY_DATE, DISTANCE, DISTANCE_UNITS, CHARGES,
							COMMODITY, RATE, MARGIN, MARGINP)
							LEFT OUTER JOIN TLORDER K
							ON K.BILL_NUMBER = S.BILL_NUMBER
							AND K.DOCUMENT_TYPE = 'REBILL'
							GROUP BY S.BILL_NUMBER, S.CURRENT_STATUS, S.DELIVERY_DATE, S.DISTANCE, S.DISTANCE_UNITS, S.CHARGES,
							S.COMMODITY, S.RATE, S.MARGIN, S.MARGINP,
							K.USER4, K.USER5
							order by 2 asc
							for read only
							with ur";
						if( $debug ) echo "<p>using query_string = $query_string</p>";
				
						$response4 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( is_array($response4) ) {
							if( $debug ) {
								echo "<pre>";
								var_dump($response4);
								echo "</pre>";
							} else {
								$response1[0]{"BILLS3"} = $response4;
							}
						}

						if( ! $debug ) {
							echo encryptData(json_encode( $response1 ));
						}
						
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}

				break;

			case 'SETCOMMENTS':  // !SETCOMMENTS - Update the commment field for a vendor
		
				// Validate fields
				if( $INTERLINER_ID == "NONE" || $THE_VALUE == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$query_string = "UPDATE VENDOR
						SET COMMENTS = '".str_replace("'", "''", $THE_VALUE)."'
						WHERE VENDOR_ID = '".$INTERLINER_ID."'";
						
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

			case 'GETSCORES':  // !GETSCORES - List of Scores for menu 
		
				$query_string = "SELECT CV.LISTVAL_ID, TRIM(CV.CUST_VALUE) AS CUST_VALUE
					FROM CUSTOM_LIST_VALUES CV, CUSTOM_DEFS CD
					WHERE CV.CUSTDEF_ID = CD.CUSTDEF_ID
					AND CD.LABEL_NAME = 'SCORING'
					AND CD.TABLE_NAME = 'VENDOR'
					ORDER BY LISTVAL_ID						

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

			case 'SETSCORE':  // !SETSCORE - Set Score for a vendor 
		
				// Validate fields
				if( $INTERLINER_ID == "NONE" || $THE_VALUE == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					// First remove old value if any
					$query_string = "DELETE FROM CUSTOM_DATA
						WHERE CUSTDEF_ID = (SELECT CUSTDEF_ID
							FROM CUSTOM_DEFS WHERE LABEL_NAME = 'SCORING'
							AND TABLE_NAME = 'VENDOR')
						AND SRC_TABLE_KEY = $INTERLINER_ID";
						
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string, $stc_database);
						echo "</pre>";
					}
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) ) {
						// Next, insert new value
						$query_string2 = "INSERT INTO CUSTOM_DATA(CUSTDEF_ID, SRC_TABLE_KEY,
								SRC_TABLE_KEY_INT, DATA, DATE, ROW_TIMESTAMP)
							VALUES( (SELECT CUSTDEF_ID
								FROM CUSTOM_DEFS WHERE LABEL_NAME = 'SCORING'
								AND TABLE_NAME = 'VENDOR'),
								'".$INTERLINER_ID."',
								CAST($INTERLINER_ID AS INT),
								'".$THE_VALUE."',
								CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
							
						if( $debug ) {
							echo "<p>using query_string2 = </p>
							<pre>";
							var_dump($query_string2, $stc_database);
							echo "</pre>";
						}
						
						$response2 = send_odbc_query( $query_string2, $stc_database, $debug );
						
						if( is_array($response2) ) {
							if( $debug ) echo "<p>CHANGED SCORING</p>";							
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

			case 'INTLANES':  //! INTLANES - Show lanes for a given carrier				
				// Prepare Select
				$query_string = "SELECT TRIM(VENDOR.NAME) AS CARRIER_NAME, X.*, QUOTES,
					COALESCE(".$stc_schema.".GET_CUSTOM_DEF_VALUE('VENDOR', 'SCORING', VENDOR.VENDOR_ID), 'Not reviewed') AS SCORING,
					
					(SELECT COALESCE(LISTAGG( CD.LABEL_NAME, ', '), '')
						FROM CUSTOM_DATA CDA, CUSTOM_DEFS CD
						WHERE CDA.CUSTDEF_ID = CD.CUSTDEF_ID
						AND UPPER(CD.LABEL_NAME) in ( 'NORTHEAST', 'NORTHWEST', 'MIDWEST',
							'MN/5-STATE AREA', 'SOUTH', 'WEST', 'SOUTHWEST',
							'ALASKA', 'CANADA', 'MEXICO')
						AND CD.TABLE_NAME = 'VENDOR'
						AND CDA.DATA = 'TRUE'
						AND CDA.SRC_TABLE_KEY = VENDOR.VENDOR_ID
						) AS REGIONS,
						
					VENDOR.USER1 AS MC_NUMBER,
					VENDOR.PROVINCE AS TERMINAL1,
					(SELECT COALESCE(CDA.DATA, '')
						FROM CUSTOM_DATA CDA, CUSTOM_DEFS CD
						WHERE CDA.CUSTDEF_ID = CD.CUSTDEF_ID
						AND UPPER(CD.LABEL_NAME) = '2NDTERMINAL'
						AND CD.TABLE_NAME = 'VENDOR'
						AND CDA.SRC_TABLE_KEY = VENDOR.VENDOR_ID
						) AS TERMINAL2,
					
					(SELECT COALESCE(LISTAGG( CODEDESC, ', '), '')
						FROM VENDOR_EQUIPMENT V, EQCLASS C
						WHERE V.VENDOR_ID = VENDOR.VENDOR_ID
						AND V.EQUIPMENT_CLASS =C.CODE) AS EQUIPMENT,
					VENDOR.IS_INACTIVE
						
					FROM (
					SELECT I.INTERLINER_ID, 
						
						T.ORIGIN, T.DESTINATION, 
						T.ORIGNAME, T.ORIGCITY, T.ORIGPROV,
						T.DESTNAME, T.DESTCITY, T.DESTPROV,
						T.BILL_TO_NAME, T.BILL_TO_CODE,
						COUNT(*) AS NUM,
						LISTAGG( CAST( TRIM(T.BILL_NUMBER) AS VARCHAR(10000)), ', ' )
							WITHIN GROUP(ORDER BY T.ACTUAL_DELIVERY) AS FBS,
						MIN(T.ACTUAL_DELIVERY) AS MIN_DEL,
						MAX(T.ACTUAL_DELIVERY) AS MAX_DEL,
						
						LISTAGG( CAST( T.TOTAL_CHARGES AS VARCHAR(10000)), '+' )
							WITHIN GROUP(ORDER BY T.ACTUAL_DELIVERY DESC) AS LAST_CHARGES,

						ROUND(SUM(T.TOTAL_CHARGES) / COUNT(*),0) AS AVG_CHARGES,
						ROUND(SUM(I.AMOUNT) / COUNT(*),0) AS AVG_PAYABLE,
						ROUND((SUM(T.INT_PAYABLE_AMT) - SUM(I.AMOUNT)) / COUNT(*),0) AS AVG_MANNING,
						ROUND((SUM(T.TOTAL_CHARGES) - SUM(T.INT_PAYABLE_AMT))/ COUNT(*),0) AS AVG_MARGIN
					
					FROM TLORDER T, ORDER_INTERLINER I
					
					WHERE I.DETAIL_LINE_ID = T.DETAIL_LINE_ID
					".($INTERLINER_ID <> "NONE" ? "AND I.INTERLINER_ID = '".$INTERLINER_ID."'" : "")."
					".($cid <> "NONE" ? "AND T.BILL_TO_CODE = '".$cid."'" : "")."
					".($OP <> "NONE" ? "AND ORIGPROV = '".strtoupper($OP)."'" : "")."
					".($OC <> "NONE" ? "AND ORIGCITY = '".strtoupper($OC)."'" : "")."
					".($DP <> "NONE" ? "AND DESTPROV = '".strtoupper($DP)."'" : "")."
					".($DC <> "NONE" ? "AND DESTCITY = '".strtoupper($DC)."'" : "")."
					".($REGION <> "NONE" ? "AND EXISTS (SELECT CD.LABEL_NAME
						FROM CUSTOM_DATA CDA, CUSTOM_DEFS CD
						WHERE CDA.CUSTDEF_ID = CD.CUSTDEF_ID
						AND UPPER(CD.LABEL_NAME) = '".strtoupper($REGION)."'
						AND CD.TABLE_NAME = 'VENDOR'
						AND CDA.DATA = 'TRUE'
						AND CDA.SRC_TABLE_KEY = I.INTERLINER_ID
						)" : "")."
					".($TERMINAL <> "NONE" ? "AND ((SELECT PROVINCE FROM VENDOR
							WHERE VENDOR.VENDOR_ID = I.INTERLINER_ID) = '".strtoupper($TERMINAL)."'
						OR (SELECT COALESCE(CDA.DATA, '')
							FROM CUSTOM_DATA CDA, CUSTOM_DEFS CD
							WHERE CDA.CUSTDEF_ID = CD.CUSTDEF_ID
							AND UPPER(CD.LABEL_NAME) = '2NDTERMINAL'
							AND CD.TABLE_NAME = 'VENDOR'
							AND CDA.SRC_TABLE_KEY = I.INTERLINER_ID) = '".strtoupper($TERMINAL)."')" : "")."
					".($EQUIPMENT <> "NONE" ? "AND EXISTS
						(SELECT EQUIPMENT_CLASS FROM VENDOR_EQUIPMENT
						WHERE VENDOR_ID = I.INTERLINER_ID
						AND EQUIPMENT_CLASS = '".strtoupper($EQUIPMENT)."')" : "")."

					".($SIZE == "LTL" ? "AND T.LENGTH_1 <= 12" : "")."
					".($SIZE == "Partial" ? "AND T.LENGTH_1 > 12 AND T.LENGTH_1 <= 36" : "")."
					".($SIZE == "Truckload" ? "AND T.LENGTH_1 > 36" : "")."
					".($SIZE == "Cartage" ? "AND EXISTS (SELECT CDA.DATA
							FROM CUSTOM_DATA CDA, CUSTOM_DEFS CD
							WHERE CDA.CUSTDEF_ID = CD.CUSTDEF_ID
							AND UPPER(CD.LABEL_NAME) = 'CARTAGE OPTIONS'
							AND CD.TABLE_NAME = 'VENDOR'
							AND CDA.DATA = 'TRUE'
							AND CDA.SRC_TABLE_KEY = I.INTERLINER_ID)" : "")."

					".($STATUS == "Active" ? "AND (SELECT UPPER(IS_INACTIVE) FROM VENDOR
							WHERE VENDOR.VENDOR_ID = I.INTERLINER_ID) = 'FALSE'" : "")."

					".($STATUS == "Inactive" ? "AND (SELECT UPPER(IS_INACTIVE) FROM VENDOR
							WHERE VENDOR.VENDOR_ID = I.INTERLINER_ID) = 'TRUE'" : "")."

					".($SCORE <> "NONE" ? "AND CASE WHEN ISINTEGER(SUBSTR(".$stc_schema.".GET_CUSTOM_DEF_VALUE('VENDOR', 'SCORING', I.INTERLINER_ID),1,1)) THEN
INT(SUBSTR(".$stc_schema.".GET_CUSTOM_DEF_VALUE('VENDOR', 'SCORING', I.INTERLINER_ID),1,1))
ELSE 0 END >= $SCORE" : "")."

					and UPPER(T.EXTRA_STOPS) != 'CHILD'
					AND T.BILL_NUMBER NOT LIKE 'Q%'
					and T.DOCUMENT_TYPE = 'INVOICE'
					AND T.CURRENT_STATUS IN ('BILLD', 'COMPLETE', 'APPRVD')
					AND I.UPDATED_DATE > CURRENT TIMESTAMP - 3 YEAR
					GROUP BY I.INTERLINER_ID, T.ORIGIN, T.DESTINATION, 
						T.ORIGNAME, T.ORIGCITY, T.ORIGPROV,
						T.DESTNAME, T.DESTCITY, T.DESTPROV,
						T.BILL_TO_NAME, T.BILL_TO_CODE
					) X
					LEFT JOIN VENDOR
					ON X.INTERLINER_ID = VENDOR.VENDOR_ID
					
					          LEFT JOIN
          (SELECT  
						T.ORIGNAME, T.ORIGCITY, T.ORIGPROV,
						T.DESTNAME, T.DESTCITY, T.DESTPROV,
            LISTAGG( CAST( TRIM(T.BILL_NUMBER) AS VARCHAR(10000)), ', ' )
							WITHIN GROUP(ORDER BY T.ACTUAL_DELIVERY) AS QUOTES        
          FROM TLORDER T
          WHERE  UPPER(T.EXTRA_STOPS) != 'CHILD'
					AND T.BILL_NUMBER LIKE 'Q%'
          					GROUP BY
						T.ORIGNAME, T.ORIGCITY, T.ORIGPROV,
						T.DESTNAME, T.DESTCITY, T.DESTPROV) Y
          
          ON X.ORIGNAME = Y.ORIGNAME
          AND X.ORIGCITY = Y.ORIGCITY
           AND X.ORIGPROV = Y.ORIGPROV
           
    --       AND X.DESTNAME = Y.DESTNAME
           AND X.DESTCITY = Y.DESTCITY
           AND X.DESTPROV = Y.DESTPROV


					ORDER BY NUM DESC
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

			case 'CARRIERS':  //! CARRIERS - Get list of all carriers
				
				// Prepare Select
				$query_string = "SELECT VENDOR_ID, NAME,
					(SELECT COUNT(*)
					FROM ORDER_INTERLINER WHERE INTERLINER_ID=VENDOR_ID
						AND UPDATED_DATE > CURRENT TIMESTAMP - 3 YEAR) AS NUM
					FROM VENDOR
					WHERE IS_INACTIVE = 'False'
					AND VENDOR_TYPE = 'I'
					AND EXISTS (SELECT * FROM ORDER_INTERLINER WHERE INTERLINER_ID=VENDOR_ID
						AND UPDATED_DATE > CURRENT TIMESTAMP - 3 YEAR)
					ORDER BY NAME ASC
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

			case 'CARRIERS2':  //! CARRIERS2 - Get list of all carriers
				
				// Prepare Select
				$query_string = "SELECT DISTINCT VENDOR.VENDOR_ID,
					TRIM(VENDOR.NAME) AS NAME, NUM
						FROM (
						SELECT I.INTERLINER_ID, COUNT(*) AS NUM
						
						FROM TLORDER T, ORDER_INTERLINER I
						
						WHERE I.DETAIL_LINE_ID = T.DETAIL_LINE_ID
						and UPPER(T.EXTRA_STOPS) != 'CHILD'
						AND T.BILL_NUMBER NOT LIKE 'Q%'
						and T.DOCUMENT_TYPE = 'INVOICE'
						AND T.CURRENT_STATUS IN ('BILLD', 'COMPLETE', 'APPRVD')
						AND I.UPDATED_DATE > CURRENT TIMESTAMP - 3 YEAR
						GROUP BY I.INTERLINER_ID
						) X
						INNER JOIN VENDOR
						ON X.INTERLINER_ID = VENDOR.VENDOR_ID
						AND COALESCE(X.INTERLINER_ID, '') <> ''

						ORDER BY NAME ASC
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

			case 'ORIGPROV':  //! ORIGPROV - Get list of all ORIGPROV
				
				// Prepare Select
				$query_string = "SELECT DISTINCT T.ORIGPROV,
						COUNT(*) AS NUM
						
						FROM TLORDER T, ORDER_INTERLINER I
						WHERE I.DETAIL_LINE_ID = T.DETAIL_LINE_ID
						and UPPER(T.EXTRA_STOPS) != 'CHILD'
						AND T.BILL_NUMBER NOT LIKE 'Q%'
						and T.DOCUMENT_TYPE = 'INVOICE'
						AND T.CURRENT_STATUS IN ('BILLD', 'COMPLETE', 'APPRVD')
						AND I.UPDATED_DATE > CURRENT TIMESTAMP - 3 YEAR
						AND COALESCE(T.ORIGPROV, '') <> ''
						GROUP BY T.ORIGPROV
						ORDER BY T.ORIGPROV ASC
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

			case 'ORIGCITY':  //! ORIGCITY - Get list of all ORIGCITY
				
				// Prepare Select
				$query_string = "SELECT DISTINCT T.ORIGCITY,
						COUNT(*) AS NUM
						
						FROM TLORDER T, ORDER_INTERLINER I
						WHERE I.DETAIL_LINE_ID = T.DETAIL_LINE_ID
						and UPPER(T.EXTRA_STOPS) != 'CHILD'
						AND T.BILL_NUMBER NOT LIKE 'Q%'
						and T.DOCUMENT_TYPE = 'INVOICE'
						AND T.CURRENT_STATUS IN ('BILLD', 'COMPLETE', 'APPRVD')
						AND I.UPDATED_DATE > CURRENT TIMESTAMP - 3 YEAR
						AND COALESCE(T.ORIGCITY, '') <> ''
						".($PROV <> "NONE" ? "AND ORIGPROV = '".strtoupper($PROV)."'" : "")."
						GROUP BY T.ORIGCITY
						ORDER BY T.ORIGCITY ASC
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

			case 'DESTPROV':  //! DESTPROV - Get list of all DESTPROV
				
				// Prepare Select
				$query_string = "SELECT DISTINCT T.DESTPROV,
						COUNT(*) AS NUM
						
						FROM TLORDER T, ORDER_INTERLINER I
						WHERE I.DETAIL_LINE_ID = T.DETAIL_LINE_ID
						and UPPER(T.EXTRA_STOPS) != 'CHILD'
						AND T.BILL_NUMBER NOT LIKE 'Q%'
						and T.DOCUMENT_TYPE = 'INVOICE'
						AND T.CURRENT_STATUS IN ('BILLD', 'COMPLETE', 'APPRVD')
						AND I.UPDATED_DATE > CURRENT TIMESTAMP - 3 YEAR
						AND COALESCE(T.DESTPROV, '') <> ''
						GROUP BY T.DESTPROV
						ORDER BY T.DESTPROV ASC
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

			case 'DESTCITY':  //! DESTCITY - Get list of all DESTCITY
				
				// Prepare Select
				$query_string = "SELECT DISTINCT T.DESTCITY,
						COUNT(*) AS NUM
						
						FROM TLORDER T, ORDER_INTERLINER I
						WHERE I.DETAIL_LINE_ID = T.DETAIL_LINE_ID
						and UPPER(T.EXTRA_STOPS) != 'CHILD'
						AND T.BILL_NUMBER NOT LIKE 'Q%'
						and T.DOCUMENT_TYPE = 'INVOICE'
						AND T.CURRENT_STATUS IN ('BILLD', 'COMPLETE', 'APPRVD')
						AND I.UPDATED_DATE > CURRENT TIMESTAMP - 3 YEAR
						AND COALESCE(T.DESTCITY, '') <> ''
						".($PROV <> "NONE" ? "AND DESTPROV = '".strtoupper($PROV)."'" : "")."
						GROUP BY T.DESTCITY
						ORDER BY T.DESTCITY ASC
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

			case 'BILLTO':  //! BILLTO - Get list of all BILL_TO_CODE
				
				// Prepare Select
				$query_string = "SELECT DISTINCT T.BILL_TO_CODE, T.BILL_TO_NAME,
						COUNT(*) AS NUM
						
						FROM TLORDER T, ORDER_INTERLINER I
						WHERE I.DETAIL_LINE_ID = T.DETAIL_LINE_ID
						and UPPER(T.EXTRA_STOPS) != 'CHILD'
						AND T.BILL_NUMBER NOT LIKE 'Q%'
						and T.DOCUMENT_TYPE = 'INVOICE'
						AND T.CURRENT_STATUS IN ('BILLD', 'COMPLETE', 'APPRVD')
						AND I.UPDATED_DATE > CURRENT TIMESTAMP - 3 YEAR
						AND COALESCE(T.ORIGPROV, '') <> ''
						GROUP BY T.BILL_TO_CODE, T.BILL_TO_NAME
						ORDER BY T.BILL_TO_NAME ASC
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

			case 'GETREGIONS':  // !GETREGIONS - List of Regions for menu 
		
				$query_string = "SELECT LABEL_NAME FROM CUSTOM_DEFS
					WHERE TABLE_NAME = 'VENDOR'
					AND UPPER(LABEL_NAME) in ( 'NORTHEAST', 'NORTHWEST', 'MIDWEST',
						'MN/5-STATE AREA', 'SOUTH', 'WEST',
						'SOUTHWEST', 'ALASKA', 'CANADA', 'MEXICO')					

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

			case 'GETTERMINALS':  // !GETTERMINALS - List of Terminals (states) for menu 
		
				$query_string = "SELECT DISTINCT PROVINCE
					FROM
					(SELECT DISTINCT UPPER(PROVINCE) AS PROVINCE
						FROM VENDOR
						WHERE COALESCE(PROVINCE, '') <> ''
						AND LENGTH(PROVINCE) = 2)
					UNION ALL
					(SELECT COALESCE(CDA.DATA, '') AS PROVINCE
						FROM CUSTOM_DATA CDA, CUSTOM_DEFS CD
						WHERE CDA.CUSTDEF_ID = CD.CUSTDEF_ID
						AND UPPER(CD.LABEL_NAME) = '2NDTERMINAL'
						AND CD.TABLE_NAME = 'VENDOR'
						AND COALESCE(CDA.DATA, '') <> ''
						AND LENGTH(CDA.DATA) = 2)
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
				break;

			case 'GETEQ':  // !GETEQ - List of equipment for menu 
		
				$query_string = "SELECT DISTINCT EQUIPMENT_CLASS , COUNT(*) NUM,
					COALESCE( (SELECT CODEDESC
					FROM EQCLASS WHERE EQUIPMENT_CLASS = CODE), 'CLASS NOT FOUND') AS CODEDESC
					
					FROM VENDOR_EQUIPMENT
					GROUP BY EQUIPMENT_CLASS
					ORDER BY EQUIPMENT_CLASS ASC				

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
						PICK_UP_BY,
						BILL_TO_CODE, BILL_TO_NAME, SERVICE_LEVEL, SITE_ID,
						CURRENT_STATUS,
						ORIGIN, ORIGNAME, ORIGCITY, ORIGPROV,
						DESTINATION, DESTNAME, DESTCITY, DESTPROV,
						ACTUAL_PICKUP, ACTUAL_DELIVERY,
						COALESCE(TIMESTAMPDIFF(16, ACTUAL_DELIVERY - ACTUAL_PICKUP),0) AS DAYS_TRANSIT,
						
						(SELECT SUM(PALLETS) FROM TLDTL
							WHERE ORDER_ID = TLORDER.DETAIL_LINE_ID) AS ROLLUP_PALLETS,

						(SELECT SUM(PIECES) FROM TLDTL
							WHERE ORDER_ID = TLORDER.DETAIL_LINE_ID) AS ROLLUP_PIECES,

						(SELECT SUM(WEIGHT) FROM TLDTL
							WHERE ORDER_ID = TLORDER.DETAIL_LINE_ID) AS ROLLUP_WEIGHT,
            
						(SELECT SUM(LENGTH_1) FROM TLDTL
							WHERE ORDER_ID = TLORDER.DETAIL_LINE_ID) AS ROLLUP_LENGTH_1,
            
						(SELECT SUM(LENGTH_EST)
							FROM TLDTL
							WHERE ORDER_ID = DETAIL_LINE_ID) AS LENGTH_EST,
						
						NO_STOPS,
						
						ROLLUP_CHARGES, ROLLUP_XCHARGES, TOTAL_CHARGES, INT_PAYABLE_AMT,
						
						COALESCE((SELECT SUM(a.CHARGE_AMOUNT)
							FROM ACHARGE_TLORDER a, ACHARGE_CODE c
							WHERE a.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID
							AND a.ACODE_ID = c.ACODE_ID
							AND c.ACC_TYPE = 'FSC'),0) AS FUEL_SURCHARGE,
						
						COALESCE((SELECT DATA
							FROM CUSTOM_DATA
							WHERE CUSTDEF_ID = 66
							AND TLORDER.DETAIL_LINE_ID = SRC_TABLE_KEY_INT), 'False') = 'True' AS DEDICATED,
					
						CAST((SELECT LISTAGG(TRIM(V.NAME), ', ')
							FROM ORDER_INTERLINER O, VENDOR V
							WHERE O.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID
							AND V.VENDOR_ID = O.INTERLINER_ID) AS VARCHAR(500)) AS CARRIER_NAME,
							
						COALESCE((SELECT MANUAL_RATE
							FROM TLDTL
							WHERE ORDER_ID = DETAIL_LINE_ID
							AND SUB_COST > 0
							FETCH FIRST 1 ROWS ONLY), 'False') AS MANUAL_RATE,
							
						COALESCE((SELECT VS.UPDATED_by
							FROM 
							    LEGSUM L
							left JOIN 
							    VENDOR_LOAD_STATUS VS ON L.LS_LEG_ID = VS.leg_ID
							WHERE 
							L.LS_FREIGHT = BILL_NUMBER
							and VS.UPDATED_by is not null
				--			AND L.LS_leg_note <> 'Generated by appending a leg'
							and VS.STATUS = 'Assign'
							FETCH FIRST 1 ROWS ONLY), '') AS DISPATCHER,
							
						COALESCE((SELECT TRIM(O.UPDATED_BY)
							FROM ODRSTAT O
							WHERE O.ORDER_ID = TLORDER.DETAIL_LINE_ID
								AND O.STATUS_CODE = 'ASSGN'
							ORDER BY O.CHANGED DESC
							FETCH FIRST 1 ROWS ONLY), '') AS DISPATCHER2
							
						
						FROM TLORDER
						WHERE CREATED_TIME > ".($DT <> "NONE" ? "DATE('".$DT."')" : "CURRENT DATE")." - 90 DAYS
						AND CURRENT_STATUS <> 'CANCL'
						AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
						AND BILL_NUMBER <> '0'
						AND UPPER(EXTRA_STOPS) <> 'CHILD'
						AND BILL_NUMBER NOT LIKE 'Q%'
						AND CURRENT_STATUS IN ('BILLD', 'COMPLETE', 'APPRVD')
						".($BILLTO == "NONE" ? "" : "AND BILL_TO_CODE = '".$BILLTO."'")."
						".($SHIPPER == "NONE" ? "" : "AND ORIGIN = '".$SHIPPER."'")."
						".($CONS == "NONE" ? "" : "AND DESTINATION = '".$CONS."'")."
						".($SITE == "NONE" ? "" : "AND SITE_ID = '".$SITE."'")."
						
						AND CREATED_TIME = (
						  SELECT MAX(CREATED_TIME) FROM
						  TLORDER T
						  WHERE T.BILL_NUMBER = TLORDER.BILL_NUMBER
						  AND DOCUMENT_TYPE IN ('INVOICE','REBILL'))
						)
						
					SELECT TRIM(BILL_NUMBER) AS BILL_NUMBER, SITE_ID, DISPATCHER, DISPATCHER2,
						BILL_TO_CODE, BILL_TO_NAME, CURRENT_STATUS,
						ORIGIN, ORIGNAME, ORIGCITY, ORIGPROV,
						DESTINATION, DESTNAME, DESTCITY, DESTPROV, SERVICE_LEVEL,
						PICK_UP_BY,
						ACTUAL_PICKUP, COALESCE(ACTUAL_DELIVERY, COMPLETED) AS ACTUAL_DELIVERY,
						DAYS_TRANSIT,
						ROLLUP_PALLETS, ROLLUP_PIECES, ROLLUP_WEIGHT, ROLLUP_LENGTH_1,
						LENGTH_EST, NO_STOPS,
						ROLLUP_CHARGES, ROLLUP_XCHARGES - FUEL_SURCHARGE AS ROLLUP_XCHARGES,
						TOTAL_CHARGES, INT_PAYABLE_AMT,
						FUEL_SURCHARGE, CARRIER_NAME, MANUAL_RATE, DEDICATED,
						
						ROUND(TOTAL_CHARGES - INT_PAYABLE_AMT, 2) AS MARGIN,
						CASE WHEN TOTAL_CHARGES > 0 THEN
							ROUND((TOTAL_CHARGES - INT_PAYABLE_AMT) / TOTAL_CHARGES * 100, 2)
						ELSE 0 END AS MARGINP
					FROM MANNING_DIRECT
					WHERE DATE( COALESCE(PICK_UP_BY, ACTUAL_DELIVERY, COMPLETED) ) ".$date_part." 
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
						var_dump(json_encode( $response ));
						var_dump(encryptData(json_encode( $response )));
						echo "</pre>";
					} else {
						echo encryptData(json_encode( $response ));
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
				}
				break;

			case 'GETCLIENTS':  // !GETCLIENTS - List of matching clients 
		
				// Validate fields $_SESSION['USERID']
				if( $NAME == "NONE" || $STATUS == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					switch ( $STATUS ) {
						case 'shipper':
							$filter = "CLIENT_IS_SHIPPER = 'True'";
							break;
							
						case 'cons':
							$filter = "CLIENT_IS_CONSIGNEE = 'True'";
							break;
							
						case 'billto':
						default:
							$filter = "CLIENT_IS_BILL_TO = 'True'";
							break;
					}
					
					$query_string = "SELECT CLIENT_ID, NAME,
						ADDRESS_1, ADDRESS_2, CITY, PROVINCE, POSTAL_CODE,
						".$stc_schema.".GET_ZONE_DESC(POSTAL_CODE) ZDESC
						FROM CLIENT
						WHERE (CLIENT_ID LIKE '".$NAME."%'
						OR NAME LIKE '".strtoupper($NAME)."%')
						AND ".$filter."
						ORDER BY CLIENT_ID ASC
						FETCH FIRST 20 ROWS ONLY								
	
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

