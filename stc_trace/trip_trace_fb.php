<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "../stc_crm/stc_config.php" );
require_once( "../stc_crm/odbc-inc.php" );

	$debug = FALSE;
	$password = "";
	$valid_pw = "youdaman758";
	$client		= "NONE";
	$trace		= "NONE";
	$ttype		= "NONE";
	$fb			= "NONE";
	$option		= "NONE";
	$uid		= "NONE";
	$upw		= "NONE";
	$days		= "90";
	$show		= "NONE";
	$dt1		= "NONE";
	$dt2		= "NONE";
	$src		= "NONE";
	$billed		= "NONE";
	$group		= "NONE";
	$TRIP_NUMBER = "NONE";
	$INTERLINER_ID = "NONE";
	
	$ON			= "NONE";
	$OP			= "NONE";
	$DN			= "NONE";
	$DP			= "NONE";
	$ST			= "NONE";
	
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
		} else if( $key == "CLIENT" ) {
			$client = $value;
		} else if( $key == "TRACE" ) {
			$trace = $value;
		} else if( $key == "TTYPE" ) {
			$ttype = $value;
		} else if( $key == "OPT" ) {
			$option = $value;
		} else if( $key == "UID" ) {
			$uid = $value;
		} else if( $key == "UPW" ) {
			$upw = $value;
		} else if( $key == "DAYS" ) {
			$days = $value;
		} else if( $key == "SHOW" ) {
			$show = $value;
		} else if( $key == "DT1" ) {
			$dt1 = $value;
		} else if( $key == "DT2" ) {
			$dt2 = $value;
		} else if( $key == "SRC" ) {
			$src = $value;
		} else if( $key == "BILLED" ) {
			$billed = $value;
		} else if( $key == "GROUP" ) {
			$group = $value;
		} else if( $key == "TRIP_NUMBER" ) {
			$TRIP_NUMBER = $value;
		} else if( $key == "INTERLINER_ID" ) {
			$INTERLINER_ID = $value;
		} else if( $key == "FB" ) {
			$fb = $value;
		} else if( $key == "ON" ) {
			$ON = $value;
		} else if( $key == "OP" ) {
			$OP = $value;
		} else if( $key == "DN" ) {
			$DN = $value;
		} else if( $key == "DP" ) {
			$DP = $value;
		} else if( $key == "ST" ) {
			$ST = $value;
		}
	}
	
	if( $debug ) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>DB2 Trace Functions</title>
</head>

<body>
<?
	}
	
	if( $password == $valid_pw ) {
			
		switch (strtoupper($option)) {
			case 'LIST':	// !LIST - List Freight bills
				//if( $trace == "NONE" && $client <> "NONE" ) {

				if( $group <> "NONE" ) {
					$query_string = "select pick_up_by, trim(bill_number) as bill_number, 
								(SELECT MAX(O.CHANGED)
									FROM ODRSTAT O
									WHERE O.ORDER_ID = T.DETAIL_LINE_ID) AS LAST_CHANGED,
								T.DETAIL_LINE_ID,
								 DESTNAME, ORIGNAME, s.short_description status,
								 t.bill_to_code, t.total_charges,
								(select tr.trace_number
								from trace tr
								where T.DETAIL_LINE_ID = tr.DETAIL_NUMBER
								and COALESCE(tr.trace_number, '') <> ''";
								
				//	if( $ttype <> "NONE" ) $query_string .= " and tr.trace_type = '".$ttype."'";
					// 7/8/2024 - allow these trace types
					$query_string .= " and trace_type in ('1', '2', '5', '7', 'B', 'I', 'P')";

				
					$query_string .= "FETCH FIRST 1 ROW ONLY),
								  '".($ttype <> "NONE" ? $ttype : 'P')."' as trace_type,
								  
								(select tr.trace_number
								from trace tr
								where T.DETAIL_LINE_ID = tr.DETAIL_NUMBER
								and COALESCE(tr.trace_number, '') <> ''
								and tr.trace_type = 'B'
								FETCH FIRST 1 ROW ONLY) AS BOL_NUMBER,
								ORIGPROV, DESTPROV,
								
								CASE WHEN CURRENT_STATUS IN ('ENTRY','AVAIL') THEN 'Tendered'
								WHEN CURRENT_STATUS IN ('ASSGN', 'DISP', 'ARRSHIP') THEN 'Assigned'
								WHEN CURRENT_STATUS IN ('PICKD') THEN 'Picked'
								WHEN CURRENT_STATUS IN ('DEPSHIP') THEN 'In Route'
								WHEN CURRENT_STATUS IN ('ARRCONS') THEN 'Arrived Consignee'
								WHEN CURRENT_STATUS IN ('DELVD', 'COMPLETE', 'APPRVD') THEN 'Delivered'
								WHEN CURRENT_STATUS IN ('BILLD') THEN 'Complete'
								ELSE CURRENT_STATUS END AS MYSTATUS
								
								from tlorder t, status s, client c
								where t.current_status = s.status_code
								and t.bill_to_code = c.CLIENT_ID
								and c.CUSTOMER_GROUP = '".$group."'
								and t.extra_stops <> 'Child'
								and t.bill_number not like 'Q%'
								and COALESCE(t.bill_number, 'NA') <> 'NA'
								and t.current_status <> 'CANCL'
								and pick_up_by > current date - ".$days." days";
				} else {
					$query_string = "with RAW as (select trim(bill_number) as bill_number, 
								(SELECT MAX(O.CHANGED)
									FROM ODRSTAT O
									WHERE O.ORDER_ID = T.DETAIL_LINE_ID) AS LAST_CHANGED,
								T.DETAIL_LINE_ID,
								DESTNAME, ORIGNAME, s.short_description status,
								t.bill_to_code, t.total_charges,
								(select tr.trace_number
								from trace tr
								where T.DETAIL_LINE_ID = tr.DETAIL_NUMBER
								and COALESCE(tr.trace_number, '') <> ''";
								
				//	if( $ttype <> "NONE" ) $query_string .= " and tr.trace_type = '".$ttype."'";
					// 7/8/2024 - allow these trace types
					$query_string .= " and trace_type in ('1', '2', '5', '7', 'B', 'I', 'P')";
				
					$query_string .= " FETCH FIRST 1 ROW ONLY),
								 '".($ttype <> "NONE" ? $ttype : 'P')."' as trace_type,
								
								(SELECT CAST(LISTAGG(tr.trace_type || '-' || tr.trace_number, ', ')
								WITHIN GROUP(ORDER BY tr.trace_number ASC) AS VARCHAR(200))
								from trace tr
								where T.DETAIL_LINE_ID = tr.DETAIL_NUMBER) AS TRACE_NOS,
							
								(select tr.trace_number
								from trace tr
								where T.DETAIL_LINE_ID = tr.DETAIL_NUMBER
								and COALESCE(tr.trace_number, '') <> ''
								and tr.trace_type = 'B'
								FETCH FIRST 1 ROW ONLY) AS BOL_NUMBER,
								ORIGPROV, DESTPROV,
								
								T.PICK_UP_BY, T.ACTUAL_PICKUP,
								T.DELIVER_BY, T.ACTUAL_DELIVERY,
								
								CASE WHEN CURRENT_STATUS IN ('ENTRY','AVAIL') THEN 'Tendered'
								WHEN CURRENT_STATUS IN ('ASSGN', 'DISP', 'ARRSHIP') THEN 'Assigned'
								WHEN CURRENT_STATUS IN ('PICKD') THEN 'Picked'
								WHEN CURRENT_STATUS IN ('DEPSHIP') THEN 'In Route'
								WHEN CURRENT_STATUS IN ('ARRCONS') THEN 'Arrived Consignee'
								WHEN CURRENT_STATUS IN ('DELVD', 'COMPLETE', 'APPRVD') THEN 'Delivered'
								WHEN CURRENT_STATUS IN ('BILLD') THEN 'Complete'
								ELSE CURRENT_STATUS END AS MYSTATUS
								
								from tlorder t, status s
								 where t.current_status = s.status_code
								 and t.bill_to_code = '".$client."'
								 and t.extra_stops <> 'Child'
								 and t.bill_number not like 'Q%'
								 and COALESCE(t.bill_number, 'NA') <> 'NA'
								 and t.current_status <> 'CANCL'
								 and pick_up_by > current date - ".$days." days
						".($ON <> "NONE" ? "AND ORIGNAME = '".$ON."'" : '')."		 
						".($OP <> "NONE" ? "AND ORIGPROV = '".$OP."'" : '')."		 
						".($DN <> "NONE" ? "AND DESTNAME = '".$DN."'" : '')."		 
						".($DP <> "NONE" ? "AND DESTPROV = '".$DP."'" : '')."		 

						".($dt1 <> "NONE" ? "AND DATE(T.PICK_UP_BY) >= DATE('".$dt1."')" : '')."		 
						".($dt2 <> "NONE" ? "AND DATE(T.PICK_UP_BY) <= DATE('".$dt2."')" : '')."		 
								 ";
				}
							
				if( $show == "NONE" ) 
					$query_string .= " and t.current_status not in ('CANCL', 'BILLD', 'APPRVD', 'FB PRINTED', 'PRINTED')";
				
				$query_string .= ")
				
				select * from RAW
				".($ST <> "NONE" ? "WHERE MYSTATUS = '".$ST."'" : '')."		 

				";
				
				$query_string .= " order by 3 desc";
						
				//echo "<p>using query_string = $query_string</p>";
				
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

			case 'TEST': 	//! TEST
				
				// Prepare Select
				$query_string = "select * from tlorder where bill_number='JVL05663S1'
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

			case 'DATES':	// !DATES - get caller info for a quote
				
				// Prepare Select
				$query_string = "with RAW as (select trim(bill_number) as bill_number, 
								
								T.PICK_UP_BY, T.ACTUAL_PICKUP
								
								from tlorder t, status s
								 where t.current_status = s.status_code
								 and t.bill_to_code = '".$client."'
								 and t.extra_stops <> 'Child'
								 and t.bill_number not like 'Q%'
								 and COALESCE(t.bill_number, 'NA') <> 'NA'
								 and t.current_status <> 'CANCL'
								 and pick_up_by > current date - ".$days." days
								 ";

				if( $show == "NONE" ) 
					$query_string .= " and t.current_status not in ('CANCL', 'BILLD', 'APPRVD', 'FB PRINTED', 'PRINTED')";
				
				$query_string .= ")
				
					select distinct date(PICK_UP_BY) as PICK_UP_BY
					from RAW
					order by 1 asc
				
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

			case 'CALLER':	// !CALLER - get caller info for a quote
				
				// Prepare Select
				$query_string = "SELECT T.BILL_NUMBER, T.CUSTOMER, T.CALLNAME, 
					T.CALLCONTACT, T.CALLEMAIL, T.BILL_TO_CODE, T.BILL_TO_NAME
					FROM TLORDER T
					WHERE T.BILL_NUMBER = '".$fb."'
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

			case 'GETT':	// !GETT - Get FB based on trace #
				$trace_arr = explode(',',$trace);	// break up comma separated trace numbers
				$response_arr = array();			// To gather responses
				
				foreach( $trace_arr as $trace_item ) {

//							WHEN T.CURRENT_STATUS IN ('ARRCONS') THEN 'Arrived Consignee'
//							WHEN T.CURRENT_STATUS IN ('ASSGN', 'DISP', 'ARRSHIP') THEN 'Assigned'
		
					$query_string = "SELECT TRACE_NUMBER FROM TRACE WHERE TRACE_NUMBER = '".$trace_item."'
					--	AND ROW_TIMESTAMP > CURRENT DATE - 6 MONTHS";
				//	if( $ttype <> "NONE" ) $query_string .= " and trace_type = '".$ttype."'";
				// 7/8/2024 - allow these trace types
					$query_string .= " and trace_type in ('1', '2', '5', '7', 'B', 'I', 'P')";
						
					if( $debug ) echo "<p>CHECK TRACE NUMBER $trace_item EXISTS</p>";
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( $response ) {
						if( $debug ) echo "<p>FOUND TRACE NUMBER $trace_item</p>";
						
						$query_string = "SELECT DISTINCT T.BILL_NUMBER, T.DETAIL_LINE_ID,
							T.NO_STOPS, T.CURRENT_STATUS, 
							".$stc_schema.".GET_STATUS_DESC(CURRENT_STATUS) STATUS_DESC,
							T.START_ZONE,
							T.END_ZONE, T.CURRENT_ZONE, 
							".$stc_schema.".GET_ZONE_DESC(T.START_ZONE) START_ZDESC,
							".$stc_schema.".GET_ZONE_DESC(T.END_ZONE) END_ZDESC,
							".$stc_schema.".GET_ZONE_DESC(T.CURRENT_ZONE) CURRENT_ZDESC,

							(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = T.START_ZONE) AS START_LAT,
							(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = T.START_ZONE) AS START_LONG,
							(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = T.END_ZONE) AS END_LAT,
							(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = T.END_ZONE) AS END_LONG,
							(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = T.CURRENT_ZONE) AS CURRENT_LAT,
							(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = T.CURRENT_ZONE) AS CURRENT_LONG,

							CASE WHEN T.CURRENT_STATUS IN ('ENTRY','AVAIL') THEN 'Tendered'
							WHEN T.CURRENT_STATUS IN ('ASSGN', 'DISP') THEN 'Assigned'
							WHEN T.CURRENT_STATUS IN ('PICKD') THEN 'Picked'
							WHEN T.CURRENT_STATUS IN ('POSITION') THEN 'LOCATION'
							WHEN T.CURRENT_STATUS IN ('DEPSHIP') THEN 'In Route'
							WHEN T.CURRENT_STATUS IN ('DELVD', 'COMPLETE', 'APPRVD') THEN 'Delivered'
							WHEN T.CURRENT_STATUS IN ('BILLD') THEN 'Complete'
							ELSE T.CURRENT_STATUS END AS MYSTATUS,
							
							T.ORIGCITY, T.DESTCITY,
							T.ORIGPROV, T.DESTPROV, T.ORIGPC, T.DESTPC,
							T.ORIGCONTACT, T.DESTCONTACT,
							T.ORIGPHONE, T.DESTPHONE,
							
							T.PICK_UP_APPT_MADE, T.DELIVERY_APPT_MADE,

							T.PICK_UP_BY, 
							T.DELIVER_BY, T.ORIGIN, T.ORIGNAME, T.DESTINATION, 
							T.DESTNAME, T.BILL_TO_CODE, T.BILL_TO_NAME,
							T.COMMODITY, 
							(SELECT CODEDESC FROM CMMCLASS 
								WHERE CODE = T.COMMODITY) AS COMM_DESC,
							
							(SELECT SUM(PALLETS) FROM TLDTL
							WHERE ORDER_ID = T.DETAIL_LINE_ID
							AND SUPPRESS='False' AND PICK_ID = 0 AND MP_SEQ=0) AS PALLETS2,

							(SELECT SUM(PIECES) FROM TLDTL
							WHERE ORDER_ID = T.DETAIL_LINE_ID
							AND SUPPRESS='False' AND PICK_ID = 0 AND MP_SEQ=0) AS PIECES2,

							(SELECT SUM(WEIGHT) FROM TLDTL
							WHERE ORDER_ID = T.DETAIL_LINE_ID
							AND SUPPRESS='False' AND PICK_ID = 0 AND MP_SEQ=0) AS WEIGHT2,
															
							T.PIECES, T.WEIGHT, T.LENGTH_1, 
							T.DISTANCE, T.CUBE, T.PALLETS, T.VOLUME, T.AREA,
							T.TIME_1, T.CHARGES, T.TOTAL_CHARGES, T.CREATED_BY,
							T.CREATED_TIME, T.TRACE_NO,
							T.CURRENCY_CODE,
							T.ROUTE_DESIGNATION, T.ROUTE_SEQUENCE,
							T.INT_PAYABLE_AMT, T.COD_AMOUNT, T.XCHARGES,
							T.INT_PAYABLE_ADJUST_AMT,
							T.INTERFACE_STATUS_F, T.NEXT_TERMINAL_ZONE,
							T.EXTRA_STOPS,
							T.APPROVED,
							T.PICK_UP_PUNIT, T.PICK_UP_PUNIT2, T.PICK_UP_TRAILER, T.PICK_UP_TRAILER2,
							T.PICK_UP_DRIVER, T.PICK_UP_DRIVER2, T.PICK_UP_TRIP, T.ACTUAL_PICKUP,
							T.DELIVERY_PUNIT, T.DELIVERY_PUNIT2, T.DELIVERY_TRAILER, T.DELIVERY_TRAILER2,
							T.DELIVERY_DRIVER, T.DELIVERY_DRIVER2, T.DELIVERY_TRIP, T.ACTUAL_DELIVERY,
							T.SERVICE_LEVEL, T.SITE_ID, (SELECT S.SITE_NAME FROM SITE S WHERE S.SITE_ID = T.SITE_ID),
							T.REQUESTED_EQUIPMEN, T.PICK_UP_BY_END, T.DELIVER_BY_END,
							T.USER1, T.USER2, T.USER3, T.USER4, T.USER5, T.USER6, T.USER7, T.USER8, T.USER9, T.USER10,
							T.OP_CODE,
							(SELECT DESCRIPTION
								FROM OPERATION_CODES C
								WHERE C.OP_CODE = T.OP_CODE) AS OP_CODE_DESC,

							T.COMPANY_ID,
							".$stc_schema.".TRC_SUB_TRPNUM_INT(T.DETAIL_LINE_ID) TRIP_NUMBER,
							".$stc_schema.".TRACE_SUB_LEGSEQ(T.DETAIL_LINE_ID) LEG_SEQUENCE,
							".$stc_schema.".TRACE_SUB_IL(T.DETAIL_LINE_ID) INTERLINER_ID,
							".$stc_schema.".TRACE_SUB_IL_NAME(T.DETAIL_LINE_ID) INTERLINER_NAME,
							".$stc_schema.".TRACE_SUB_IL_PHONE(T.DETAIL_LINE_ID) INTERLINER_PHONE,
							(SELECT MAX(ILD_RES_ID) FROM ILEGDTL
							WHERE ILD_RES_TYPE = 'O' AND ILD_TRIP_NUMBER = (".$stc_schema.".TRC_SUB_TRPNUM_INT(T.DETAIL_LINE_ID))) AS CONTAINER,
							(SELECT MAX(ILD_RES_ID) FROM ILEGDTL
							WHERE ILD_RES_TYPE = 'H' AND ILD_TRIP_NUMBER = (".$stc_schema.".TRC_SUB_TRPNUM_INT(T.DETAIL_LINE_ID))) AS CHASSIS,
							".$stc_schema.".GET_CUSTOM_DEF('NONE','NONE',T.DETAIL_LINE_ID) CUSTDEF_1
							FROM TLORDER T
							, TRACE
							WHERE( T.DETAIL_LINE_ID=TRACE.DETAIL_NUMBER AND
							TRACE.TRACE_NUMBER = '".$trace_item."'";
					//	if( $ttype <> "NONE" ) $query_string .= " AND TRACE.TRACE_TYPE = '".$ttype."'
					// 7/8/2024 - allow these trace types
					$query_string .= " and TRACE.TRACE_TYPE in ('1', '2', '5', '7', 'B', 'I', 'P')

						--	AND T.ROW_TIMESTAMP > CURRENT DATE - 6 MONTHS"; 
						if( $client <> "NONE" ) $query_string .= " AND T.BILL_TO_CODE = '".$client."'";
						$query_string .= " AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
						and t.CREATED_TIME > CURRENT DATE - 150 DAYS
							and T.DOCUMENT_TYPE IN ('INVOICE', 'SPLIT') )
							ORDER BY T.BILL_NUMBER ASC
							WITH UR";
							//and t.extra_stops <> 'Child'
							
							// and t.current_status not in ('CANCL', 'BILLD')
			
						if( $debug ) echo "<p>GET TRIP INFO</p>";
						
						$response1 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( $debug ) {
							echo "<pre>response1\n";
							var_dump($response1);
							echo "</pre>";
						}
						if( is_array($response1) ) {
							for( $c=0; $c < count($response1) ; $c++ ) {
								$dtl = $response1[$c]["DETAIL_LINE_ID"];
								
								$query_string = "SELECT CHANGED, STATUS_CODE, ".$stc_schema.".GET_STATUS_DESC(STATUS_CODE) STATUS_DESC ,
				STAT_COMMENT COMM, UPDATED_BY USR, TRIP_NUMBER TNO, ZONE_ID ZID, ".$stc_schema.".GET_ZONE_DESC(ZONE_ID) ZDESC,
				(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = ODRSTAT.ZONE_ID) AS POSLAT,
				(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = ODRSTAT.ZONE_ID) AS POSLONG,
						CASE WHEN STATUS_CODE IN ('ENTRY','AVAIL') THEN 'Tendered'
						WHEN STATUS_CODE IN ('ASSGN', 'DISP') THEN 'Assigned'
						WHEN STATUS_CODE IN ('PICKD') THEN 'Picked'
						WHEN STATUS_CODE IN ('DEPSHIP') THEN 'In Route'
						WHEN STATUS_CODE IN ('POSITION') THEN 'LOCATION'
						WHEN STATUS_CODE IN ('DELVD', 'COMPLETE', 'APPRVD') THEN 'Delivered'
						WHEN STATUS_CODE IN ('BILLD') THEN 'Complete'
						WHEN STATUS_CODE IN ('CHECKCALL', 'ENRTE', 'ENRTE1', 'ENRTE2',
							'ENRTE3', 'ENRTE4', 'ENRTE5' ) THEN 'Checkcall'
						ELSE STATUS_CODE END AS MYSTATUS
				FROM ODRSTAT WHERE ODRSTAT.ORDER_ID = ".$dtl." ORDER BY CHANGED DESC";
				
								if( $debug ) echo "<p>GET TRIP HISTORY $dtl</p>";
								
								$response2 = send_odbc_query( $query_string, $stc_database, $debug );
								
								if( $response2 ) {
									if( $debug ) {
										echo "<pre>";
										var_dump($response2);
										echo "</pre>";
									}
									
									$response1[$c]["HISTORY"] =  $response2;
														
								} else {
									if( $debug ) echo "<p>Error - send_odbc_query2 failed. $last_odbc_error</p>";
									else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
								}
								$query_string = "SELECT TRACE.TRACE_NUMBER, TRACE.TRACE_TYPE, TRACE_TYPE.DESC
								FROM TRACE, TRACE_TYPE WHERE TRACE_TYPE.ID = TRACE.TRACE_TYPE AND DETAIL_NUMBER = ".$dtl;
				
								if( $debug ) echo "<p>GET TRACE NUMBERS</p>";
								
								$response3 = send_odbc_query( $query_string, $stc_database, $debug );
								
								if( $response3 ) {
									if( $debug ) {
										echo "<pre>";
										var_dump($response3);
										echo "</pre>";
									}
									
									$response1[$c]["TRACES"] =  $response3;
														
								}

								$query_string = "SELECT LABEL_NAME, DATA
									FROM CUSTOM_DATA, CUSTOM_DEFS
									WHERE CUSTOM_DATA.CUSTDEF_ID = CUSTOM_DEFS.CUSTDEF_ID
									AND LABEL_NAME IN
											('REQPUST', 'REQPUEND', 'REQDELVST', 'REQDELVEND')
									AND TABLE_NAME = 'TLORDER'
									AND SRC_TABLE_KEY = '".$dtl."'";
				
								if( $debug ) echo "<p>GET CUSTOM_DATA</p>";
								
								$response3a = send_odbc_query( $query_string, $stc_database, $debug );
								
								if( $response3a ) {
									if( $debug ) {
										echo "<pre>";
										var_dump($response3a);
										echo "</pre>";
									}
									
									$response1[$c]["CUSTOM_DATA"] =  $response3a;
								}
								
								$query_string = "SELECT INTERLINER_ID,
										(SELECT NAME FROM VENDOR WHERE VENDOR_ID = INTERLINER_ID),
										SUB_AMOUNT, EXTRA_AMOUNT,
										(SUB_AMOUNT + EXTRA_AMOUNT) AS AMOUNT,
										FROM_ZONE, TO_ZONE,
										".$stc_schema.".GET_ZONE_DESC(FROM_ZONE) FROM_ZDESC,
										".$stc_schema.".GET_ZONE_DESC(TO_ZONE) TO_ZDESC,
							(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = FROM_ZONE) AS FROM_LAT,
							(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = FROM_ZONE) AS FROM_LONG,
							(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = TO_ZONE) AS TO_LAT,
							(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = TO_ZONE) AS TO_LONG,
										PROBILL
									FROM
								
										(SELECT O.INTERLINER_ID, 
										O.SUB_AMOUNT, 
										(SELECT COALESCE(SUM(A.CHARGE_AMOUNT), 0)
										FROM ACHARGE_INTERLINER A
										WHERE O.ORDER_INTERLINER_ID = A.ORDER_INTERLINER_ID
										) AS EXTRA_AMOUNT,
										O.AMOUNT,
										O.FROM_ZONE, O.TO_ZONE, 
										
										O.PROBILL,
										CASE MOVEMENT_TYPE WHEN 'A' THEN 1 
										WHEN 'L' THEN 2 
										WHEN 'O' THEN  3 
										WHEN 'C' THEN 4 
										WHEN 'B' THEN 5 
										END AS SORT_ORDER
	
										FROM ORDER_INTERLINER O 
										WHERE O.DETAIL_LINE_ID = ".$dtl."
										ORDER BY SORT_ORDER)
									FOR READ ONLY
									WITH UR";
									//AND A.CHARGE_AMOUNT > 0.0
				
								if( $debug ) echo "<p>GET INTERLINERS</p>"; 
								
								$response6 = send_odbc_query( $query_string, $stc_database, $debug );
								
								if( $response6 ) {
									if( $debug ) {
										echo "<pre>";
										var_dump($response6);
										echo "</pre>";
									}
									
									$response1[$c]["INTERLINERS"] =  $response6;
														
								}
								
							}
							array_push($response_arr, $response1);
			
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
						} // if response
			
					} else {
						if( $debug ) echo "<p>MISSING TRACE NUMBER $trace</p>";
						//else echo "MISSING TRACE NUMBER $trace";
					}
				
				} // foreach
				if( $debug ) {
					echo "<p>RESPONSE</p>";
					echo "<pre>";
					var_dump($response_arr);
					echo "</pre>";
				} else {
					echo encryptData(json_encode( $response_arr ));
					//echo json_encode( $response_arr );
				}
	
				break;

			case 'GETFB':	// !GETFB - Get FB info based on FB#
				$fb_arr = explode(',',$fb);			// break up comma separated freight bills
				$response_arr = array();			// To gather responses
				
				foreach( $fb_arr as $fb_item ) {
		
					$query_string = "SELECT BILL_NUMBER FROM TLORDER WHERE BILL_NUMBER = '".$fb_item."'";
						
					if( $debug ) echo "<p>CHECK FB $fb_item EXISTS</p>";
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( $response ) {
						if( $debug ) echo "<p>FOUND FB $fb_item</p>";
						
						$query_string = "SELECT DISTINCT T.BILL_NUMBER, T.DETAIL_LINE_ID,
							T.MASTER_ORDER,
							(SELECT RTRIM(T2.BILL_NUMBER)
							FROM TLORDER T2
							WHERE T2.DETAIL_LINE_ID = T.MASTER_ORDER) AS PARENT_BILL_NUMBER,
							
							T.NO_STOPS, T.CURRENT_STATUS, 
							".$stc_schema.".GET_STATUS_DESC(CURRENT_STATUS) STATUS_DESC,
							T.START_ZONE,
							T.END_ZONE, T.CURRENT_ZONE, T.DESTADDR1, T.DESTCITY,
							".$stc_schema.".GET_ZONE_DESC(T.START_ZONE) START_ZDESC,
							".$stc_schema.".GET_ZONE_DESC(T.END_ZONE) END_ZDESC,
							".$stc_schema.".GET_ZONE_DESC(T.CURRENT_ZONE) CURRENT_ZDESC,

							(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = T.START_ZONE) AS START_LAT,
							(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = T.START_ZONE) AS START_LONG,
							(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = T.END_ZONE) AS END_LAT,
							(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = T.END_ZONE) AS END_LONG,
							(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = T.CURRENT_ZONE) AS CURRENT_LAT,
							(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = T.CURRENT_ZONE) AS CURRENT_LONG,

							CASE WHEN T.CURRENT_STATUS IN ('ENTRY','AVAIL') THEN 'Tendered'
							WHEN T.CURRENT_STATUS IN ('ASSGN', 'DISP') THEN 'Assigned'
							WHEN T.CURRENT_STATUS IN ('PICKD') THEN 'Picked'
							WHEN T.CURRENT_STATUS IN ('POSITION') THEN 'LOCATION'
							WHEN T.CURRENT_STATUS IN ('DEPSHIP') THEN 'In Route'
							WHEN T.CURRENT_STATUS IN ('DELVD', 'COMPLETE', 'APPRVD') THEN 'Delivered'
							WHEN T.CURRENT_STATUS IN ('BILLD') THEN 'Complete'
							ELSE T.CURRENT_STATUS END AS MYSTATUS,

							T.ORIGADDR1, T.DESTADDR1,
							T.ORIGADDR2, T.DESTADDR2,
							T.ORIGCITY, T.DESTCITY,
							T.ORIGPROV, T.DESTPROV, T.ORIGPC, T.DESTPC,
							T.ORIGCONTACT, T.DESTCONTACT,
							T.ORIGPHONE, T.DESTPHONE,

							T.CUSTOMER, T.CALLNAME, T.CALLADDR1, T.CALLADDR2, T.CALLCITY, T.CALLPROV,
							T.CALLPC, T.CALLPHONE, T.CALLCONTACT, T.CALLPHONEEXT,
							T.CALLEMAIL,
							
							(SELECT SUM(PALLETS) FROM TLDTL
							WHERE ORDER_ID = T.DETAIL_LINE_ID
							AND SUPPRESS='False' AND PICK_ID = 0 AND MP_SEQ=0) AS PALLETS2,

							(SELECT SUM(PIECES) FROM TLDTL
							WHERE ORDER_ID = T.DETAIL_LINE_ID
							AND SUPPRESS='False' AND PICK_ID = 0 AND MP_SEQ=0) AS PIECES2,

							(SELECT SUM(WEIGHT) FROM TLDTL
							WHERE ORDER_ID = T.DETAIL_LINE_ID
							AND SUPPRESS='False' AND PICK_ID = 0 AND MP_SEQ=0) AS WEIGHT2,
															
							(SELECT CAST(THE_NOTE AS VARCHAR(6000))
								FROM NOTES
								WHERE  LTRIM(RTRIM(CHAR(T.DETAIL_LINE_ID))) = ID_KEY
								AND PROG_TABLE = 'TLORDER'
								AND NOTE_TYPE = '".$stc_internal_note_type."')  AS QUOTE_NOTE,
							
							(SELECT DATA
								FROM CUSTOM_DATA
								WHERE CUSTDEF_ID = ".$stc_custom_client_account_rep_id."
								AND T.BILL_TO_CODE = SRC_TABLE_KEY) AS ACCOUNT_REP,

							(SELECT CAST(THE_NOTE AS VARCHAR(6000))
								FROM NOTES
								WHERE  LTRIM(RTRIM(CHAR(T.DETAIL_LINE_ID))) = ID_KEY
								AND PROG_TABLE = 'TLORDER'
								AND NOTE_TYPE = '".$stc_external_note_type."')  AS QUOTE_EXT_NOTE,

							T.PICK_UP_APPT_MADE, T.DELIVERY_APPT_MADE,

							T.PICK_UP_BY, 
							T.DELIVER_BY, T.ORIGIN, T.ORIGNAME, T.DESTINATION, 
							T.DESTNAME, T.BILL_TO_CODE, T.BILL_TO_NAME,
							T.COMMODITY, 
							(SELECT SHORT_DESCRIPTION
								FROM CMODTY
								WHERE COMMODITY_CODE = T.COMMODITY) AS COMM_DESC,
							(SELECT WIDTH
								FROM TLDTL
								WHERE ORDER_ID = T.DETAIL_LINE_ID
								FETCH FIRST 1 ROW ONLY) AS WIDTH,
							(SELECT HEIGHT
								FROM TLDTL
								WHERE ORDER_ID = T.DETAIL_LINE_ID
								FETCH FIRST 1 ROW ONLY) AS HEIGHT,
							(SELECT EXPIRY_DATE
								FROM QUOTE
								WHERE QUOTE_ID = T.DETAIL_LINE_ID) AS EXPIRY_DATE,
							T.DECLARED_VALUE,
							T.PIECES, T.WEIGHT, T.LENGTH_1, 
							T.DISTANCE, T.CUBE, T.PALLETS, T.VOLUME, T.AREA,
							T.TIME_1, T.CHARGES, T.TOTAL_CHARGES, T.CREATED_BY,
							T.CREATED_TIME, T.TRACE_NO,
							T.CURRENCY_CODE,
							T.ROUTE_DESIGNATION, T.ROUTE_SEQUENCE,
							T.INT_PAYABLE_AMT, T.COD_AMOUNT, T.XCHARGES,
							T.INT_PAYABLE_ADJUST_AMT,
							T.INTERFACE_STATUS_F, T.NEXT_TERMINAL_ZONE,
							TRIM(T.EXTRA_STOPS) AS EXTRA_STOPS,
							T.APPROVED, T.DLID_QUOTE, T.COPY_DLID,
							LTRIM( (SELECT Q.BILL_NUMBER FROM TLORDER Q
							WHERE Q.DETAIL_LINE_ID = T.DLID_QUOTE) ) AS QBILL,
							LTRIM( (SELECT C.BILL_NUMBER FROM TLORDER C
							WHERE C.DETAIL_LINE_ID = T.COPY_DLID) ) AS CBILL,
							T.PICK_UP_PUNIT, T.PICK_UP_PUNIT2, T.PICK_UP_TRAILER, T.PICK_UP_TRAILER2,
							T.PICK_UP_DRIVER, T.PICK_UP_DRIVER2, T.PICK_UP_TRIP, T.ACTUAL_PICKUP,
							T.DELIVERY_PUNIT, T.DELIVERY_PUNIT2, T.DELIVERY_TRAILER, T.DELIVERY_TRAILER2,
							T.DELIVERY_DRIVER, T.DELIVERY_DRIVER2, T.DELIVERY_TRIP, T.ACTUAL_DELIVERY,
							T.SERVICE_LEVEL, 
							(SELECT CODEDESC FROM SVCLEVEL WHERE CODE = T.SERVICE_LEVEL) SVCDESC,
							(SELECT CODEDESC FROM EQCLASS WHERE CODE = T.REQUESTED_EQUIPMEN) REQDESC,
							T.SITE_ID, (SELECT S.SITE_NAME FROM SITE S WHERE S.SITE_ID = T.SITE_ID),
							T.REQUESTED_EQUIPMEN, T.PICK_UP_BY_END, T.DELIVER_BY_END,
							T.USER1, T.USER2, T.USER3, T.USER4, T.USER5, T.USER6, T.USER7, T.USER8, T.USER9, T.USER10,
							T.OP_CODE, T.TARP,
							(SELECT DESCRIPTION
								FROM OPERATION_CODES C
								WHERE C.OP_CODE = T.OP_CODE) AS OP_CODE_DESC,
							T.COMPANY_ID,
							".$stc_schema.".TRC_SUB_TRPNUM_INT(T.DETAIL_LINE_ID) TRIP_NUMBER,
							".$stc_schema.".TRACE_SUB_LEGSEQ(T.DETAIL_LINE_ID) LEG_SEQUENCE,
							".$stc_schema.".TRACE_SUB_IL(T.DETAIL_LINE_ID) INTERLINER_ID,
							".$stc_schema.".TRACE_SUB_IL_NAME(T.DETAIL_LINE_ID) INTERLINER_NAME,
							".$stc_schema.".TRACE_SUB_IL_PHONE(T.DETAIL_LINE_ID) INTERLINER_PHONE,
							(SELECT MAX(ILD_RES_ID) FROM ILEGDTL
							WHERE ILD_RES_TYPE = 'O' AND ILD_TRIP_NUMBER = (".$stc_schema.".TRC_SUB_TRPNUM_INT(T.DETAIL_LINE_ID))) AS CONTAINER,
							(SELECT MAX(ILD_RES_ID) FROM ILEGDTL
							WHERE ILD_RES_TYPE = 'H' AND ILD_TRIP_NUMBER = (".$stc_schema.".TRC_SUB_TRPNUM_INT(T.DETAIL_LINE_ID))) AS CHASSIS,
							".$stc_schema.".GET_CUSTOM_DEF('NONE','NONE',T.DETAIL_LINE_ID) CUSTDEF_1,
							(SELECT CAST(LISTAGG(T2.BILL_NUMBER, ', ')
								WITHIN GROUP(ORDER BY T2.BILL_NUMBER ASC) AS VARCHAR(200))
								from TLORDER T2
								where T2.BILL_NUMBER LIKE TRIM(T.BILL_NUMBER) || '%'
								AND EXTRA_STOPS = 'Child') AS CHILDREN,

							(SELECT O.STAT_COMMENT
								FROM ODRSTAT O
								WHERE O.ORDER_ID = T.DETAIL_LINE_ID
								AND O.STATUS_CODE = 'POSITION'
								AND O.CHANGED = (SELECT MAX(O2.CHANGED)
									FROM ODRSTAT O2
									WHERE O2.ORDER_ID = O.ORDER_ID
									AND O2.STATUS_CODE = 'POSITION')
								) AS LAST_LOCATION_DESC
													
							FROM TLORDER T
							WHERE( T.BILL_NUMBER = '".$fb_item."'";
							if( $client <> "NONE" ) $query_string .= " AND T.BILL_TO_CODE = '".$client."'";
							$query_string .= " AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
								
								and T.DOCUMENT_TYPE IN ('INVOICE', 'SPLIT', 'RETURN') )
							ORDER BY 1 DESC
							FETCH FIRST 100 ROWS ONLY
							WITH UR";
							//and t.extra_stops <> 'Child'
			
						if( $debug ) echo "<p>GET TRIP INFO</p>";
						
						$response1 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( $response1 ) {
							if( $debug ) {
								echo "<pre>";
								var_dump($response1);
								echo "</pre>";
							}
							for( $c=0; $c < count($response1) ; $c++ ) {
								$dtl = $response1[$c]["DETAIL_LINE_ID"];
							
								$query_string = "SELECT CHANGED, STATUS_CODE, ".$stc_schema.".GET_STATUS_DESC(STATUS_CODE) STATUS_DESC ,
				STAT_COMMENT COMM, UPDATED_BY USR, TRIP_NUMBER TNO, ZONE_ID ZID, ".$stc_schema.".GET_ZONE_DESC(ZONE_ID) ZDESC,
				(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = ODRSTAT.ZONE_ID) AS POSLAT,
				(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = ODRSTAT.ZONE_ID) AS POSLONG,
						CASE WHEN STATUS_CODE IN ('ENTRY','AVAIL') THEN 'Tendered'
						WHEN STATUS_CODE IN ('ASSGN', 'DISP') THEN 'Assigned'
						WHEN STATUS_CODE IN ('PICKD') THEN 'Picked'
						WHEN STATUS_CODE IN ('DEPSHIP') THEN 'In Route'
						WHEN STATUS_CODE IN ('POSITION') THEN 'LOCATION'
						WHEN STATUS_CODE IN ('DELVD', 'COMPLETE', 'APPRVD') THEN 'Delivered'
						WHEN STATUS_CODE IN ('BILLD') THEN 'Complete'
						WHEN STATUS_CODE IN ('CHECKCALL', 'ENRTE', 'ENRTE1', 'ENRTE2',
							'ENRTE3', 'ENRTE4', 'ENRTE5' ) THEN 'Checkcall'
						ELSE STATUS_CODE END AS MYSTATUS
				FROM ODRSTAT WHERE ODRSTAT.ORDER_ID = ".$dtl." 
				AND STATUS_CODE IN ('ENTRY', 'AVAIL', 'ASSGN', 'DISP', 'ARRSHIP', 'PICKD',
					'ARRCONS', 'DELVD', 'COMPLETE', 'BILLD')
				ORDER BY CHANGED DESC";
				
								if( $debug ) echo "<p>GET TRIP HISTORY</p>";
								
								$response2 = send_odbc_query( $query_string, $stc_database, $debug );
								
								if( $response2 ) {
									if( $debug ) {
										echo "<pre>";
										var_dump($response2);
										echo "</pre>";
									}
									
									$response1[$c]["HISTORY"] =  $response2;
														
								} else {
									if( $debug ) echo "<p>Error - send_odbc_query2 failed. $last_odbc_error</p>";
									else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
								}

								$query_string = "SELECT TRACE.TRACE_NUMBER, TRACE.TRACE_TYPE, TRACE_TYPE.DESC
								FROM TRACE, TRACE_TYPE WHERE TRACE_TYPE.ID = TRACE.TRACE_TYPE AND DETAIL_NUMBER = ".$dtl;
				
								if( $debug ) echo "<p>GET TRACE NUMBERS</p>";
								
								$response3 = send_odbc_query( $query_string, $stc_database, $debug );
								
								if( $response3 ) {
									if( $debug ) {
										echo "<pre>";
										var_dump($response3);
										echo "</pre>";
									}
									
									$response1[$c]["TRACES"] =  $response3;
														
								}
								
								$query_string = "SELECT I.TRIP_NUMBER, T.LS_NUM_LEGS,
									".$stc_schema.".GET_ZONE_DESC(T.ORIGIN_ZONE) ORIGIN,
									".$stc_schema.".GET_ZONE_DESC(T.DESTINATION_ZONE) DESTINATION 
									FROM ITRIPTLO I, TRIP T
									WHERE I.DETAIL_LINE_ID = ".$dtl."
									AND I.TRIP_NUMBER = T.TRIP_NUMBER
									ORDER BY TRIP_NUMBER ASC";
				
								if( $debug ) echo "<p>GET TRIP INFO</p>";
								
								$response4 = send_odbc_query( $query_string, $stc_database, $debug );
								
								if( $response4 ) {
									if( $debug ) {
										echo "<pre>";
										var_dump($response4);
										echo "</pre>";
									}
									
									$response1[$c]["TRIPS"] =  $response4;
														
								}
								
								$query_string = "SELECT ORDER_ID, SEQUENCE, REQUESTED_EQUIPMEN, COMMODITY,
									RATE,
									PALLETS, PIECES, PIECES_UNITS, WEIGHT, WEIGHT_UNITS, LENGTH_1, LENGTH_UNITS, 
									HEIGHT, HEIGHT_UNITS, WIDTH, WIDTH_UNITS, DESCRIPTION, SUB_COST, MANUAL_RATE
									FROM TLDTL
									WHERE ORDER_ID =  ".$dtl."
									ORDER BY ORDER_ID,SEQUENCE

									FOR READ ONLY
									WITH UR";
				
								if( $debug ) echo "<p>GET DETAIL INFO</p>"; // !HERE
								
								$response5 = send_odbc_query( $query_string, $stc_database, $debug );
								
								if( $response5 ) {
									if( $debug ) {
										echo "<pre>";
										var_dump($response5);
										echo "</pre>";
									}
									
									$response1[$c]["DETAIL"] =  $response5;
														
								}
								
							}
							array_push($response_arr, $response1);
			
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
						}
			
					} else {
						if( $debug ) echo "<p>MISSING FB $fb</p>";
						else echo encryptData("MISSING FB $fb");
					}
				} // foreach
				if( $debug ) {
					echo "<p>RESPONSE</p>";
					echo "<pre>";
					var_dump($response_arr);
					echo "</pre>";
				} else {
					echo encryptData(json_encode( $response_arr ));
				}
				break;

			case 'TRIP':  // !TRIP - Get Trip info
				
				// Validate fields
				if( $TRIP_NUMBER == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
			
					// Prepare Select
					$query_string = "SELECT TRIP.TRIP_NUMBER, TRIP.STATUS, TRIP.LS_NUM_LEGS NUM_LEGS,
						".$stc_schema.".GET_ZONE_DESC(TRIP.ORIGIN_ZONE) ORIGIN,
						".$stc_schema.".GET_ZONE_DESC(TRIP.DESTINATION_ZONE) DESTINATION, 
						(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = TRIP.ORIGIN_ZONE) AS ORIGIN_LAT,
						(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = TRIP.ORIGIN_ZONE) AS ORIGIN_LONG,
						(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = TRIP.DESTINATION_ZONE) AS DEST_LAT,
						(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = TRIP.DESTINATION_ZONE) AS DEST_LONG,
						ACTIVE_REC, DELIVER_BY, DISPATCH_AGENT, DISPATCH_AGENT_DATE, DRIVER,
						ETA_DATE, LATEST_PICK_UP_BY , LATE_LASTCALC, LATE_LASTSUBMIT,
						LS_ACTIVE_LEG, PLAN_DEPART, REPTR_DATE, TE_DATE,

						(SELECT ROLLUP_COMMODITY
							FROM ITRIPTLO, TLORDER
							WHERE ITRIPTLO.TRIP_NUMBER = TRIP.TRIP_NUMBER
							AND ITRIPTLO.bill_number = TLORDER.bill_number
							fetch first row only) COMMODITY,
						(SELECT CODEDESC 
							FROM ITRIPTLO I, TLORDER J, CMMCLASS C
							WHERE I.TRIP_NUMBER = TRIP.TRIP_NUMBER
							AND I.bill_number = J.bill_number
							AND C.CODE = J.COMMODITY
							fetch first row only) AS COMM_DESC,
						(SELECT SUM(LS_LEG_DIST)
							FROM LEGSUM
							WHERE LS_TRIP_NUMBER = TRIP_NUMBER) DISTANCE,
						(SELECT COUNT(ITRIPTLO.BILL_NUMBER) FROM ITRIPTLO, TLORDER
							WHERE ITRIPTLO.TRIP_NUMBER = TRIP.TRIP_NUMBER
							AND	ITRIPTLO.DETAIL_LINE_ID = TLORDER.DETAIL_LINE_ID
							 ) BILLS
						
						FROM TRIP
						WHERE TRIP.TRIP_NUMBER = '".$TRIP_NUMBER."'
						ORDER BY TRIP.TRIP_NUMBER ASC
						for read only
						with ur";
						// AND TLORDER.extra_stops <> 'Child'
											
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response1 = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response1) ) {
						$query_string = "SELECT LS_LEG_ID, LS_LEG_SEQ, LS_LEG_DIST,
							LS_LEG_STAT, LS_FROM_ZONE, LS_TO_ZONE, LEGO_ZONE_DESC, LEGD_ZONE_DESC,
							LS_FREIGHT, LS_NUM_PU, LS_NUM_DEL, LS_NUM_TOTAL,
							(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = LEGSUM.LS_FROM_ZONE) AS LS_FROM_LAT,
							(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = LEGSUM.LS_FROM_ZONE) AS LS_FROM_LONG,
							(SELECT Z.POSLAT FROM ZONE Z WHERE Z.ZONE_ID = LEGSUM.LS_TO_ZONE) AS LS_TO_LAT,
							(SELECT Z.POSLONG FROM ZONE Z WHERE Z.ZONE_ID = LEGSUM.LS_TO_ZONE) AS LS_TO_LONG,
							LS_EXPECTED_DATE, LS_ACTIVE_DATE, LS_ACTUAL_DATE, LS_PLANNED_DEPARTURE, LS_DELIVER_BY, 
							LS_ETA_DATE, LS_ETA_DATE_PICKUP, LS_ETA_DEPARTURE, LS_PICKUP_BY, 
							LS_CLEARED_DATE, LS_CUTOFF_DATE, LS_NOTIFY_DATE, 
							LS_LEG_NOTE, LS_MT_LOADED,
							LS_DRIVER, LS_POWER_UNIT, LS_TRAILER1, LS_TRAILER2,
							LS_INTERLINER, LS_INTERLINER_NAME  
							FROM LEGSUM
							WHERE LS_TRIP_NUMBER = '".$TRIP_NUMBER."'
							ORDER BY LS_LEG_SEQ ASC
							for read only
							with ur";
						if( $debug ) echo "<p>using query_string = $query_string</p>";
				
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						if( is_array($response2) ) {
							for($c=0; $c<count($response2); $c++) {
								$query_string = "SELECT BILL_NUMBER, COMMODITY, NEXT_TERMINAL_ZONE, PICKUP_DONE, 
									LD_PD, LD_PD_ORDER,
									(SELECT MAX(J.TRIP_NUMBER) FROM ITRIPTLO J
										WHERE J.DETAIL_LINE_ID = T1.DETAIL_LINE_ID
										AND J.TRIP_NUMBER < ".$TRIP_NUMBER.") AS PREV_TRIP,
									(SELECT MIN(J.TRIP_NUMBER) FROM ITRIPTLO J
										WHERE J.DETAIL_LINE_ID = T1.DETAIL_LINE_ID
										AND J.TRIP_NUMBER > ".$TRIP_NUMBER.") AS NEXT_TRIP
									FROM
									TABLE( ".$stc_schema.".GETTRIPBILLS_LEG(".$TRIP_NUMBER.", ".$response2[$c]["LS_LEG_ID"].", 1) ) T1
									ORDER BY LD_PD_ORDER";
									if( $debug ) echo "<p>using query_string = $query_string</p>";
				
									$response3 = send_odbc_query( $query_string, $stc_database, $debug );
									if( is_array($response3) ) {
										$response2[$c]["BILLS"] = $response3;
									}
							}
							
							$response = array();
							$response{'TRIP'} = $response1;
							$response{'LEGS'} = $response2;
							
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

			case 'INT':  // !INT - Get Interliner info
				
				// Validate fields
				if( $INTERLINER_ID == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
			
					// Prepare Select
					$query_string = "SELECT NAME, ADDRESS_1, ADDRESS_2, CITY, PROVINCE,
						POSTAL_CODE, BUSINESS_PHONE, BUSINESS_PHONE_EXT, FAX_PHONE,
						DATE(VENDOR_SINCE) VENDOR_SINCE, CONTACT, FED_ID_NUM, ICCNUMBER,
						EMAIL_NOTIFY, COMMENTS,
						COALESCE(".$stc_schema.".GET_CUSTOM_DEF('VENDOR', 'SCORING', vendor_id), 'Not reviewed') AS SCORING,
						
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
						
						FROM VENDOR
						WHERE VENDOR_ID = '".$INTERLINER_ID."'

						for read only
						with ur";
											
					if( $debug ) echo "<p>using query_string = $query_string</p>";
			
					$response1 = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response1) ) {

						// Prepare Select
						$query_string = "SELECT T.BILL_NUMBER, T.CURRENT_STATUS, 
							T.ORIGIN, T.ORIGNAME, T.ORIGCITY, T.ORIGPROV,
							T.DESTINATION, T.DESTNAME, T.DESTCITY, T.DESTPROV,
							T.BILL_TO_NAME, T.BILL_TO_CODE,
							T.TOTAL_CHARGES, T.INT_PAYABLE_AMT, I.AMOUNT,
							T.INT_PAYABLE_AMT - I.AMOUNT AS MANNING_AMOUNT,
							(SELECT J.TRIP_NUMBER
								FROM ITRIPTLO J
								WHERE T.DETAIL_LINE_ID = J.DETAIL_LINE_ID
							FETCH FIRST ROW ONLY) TRIP_NUMBER
							FROM TLORDER T, ORDER_INTERLINER I
							WHERE I.INTERLINER_ID = '".$INTERLINER_ID."'
							AND I.DETAIL_LINE_ID = T.DETAIL_LINE_ID
							and t.extra_stops <> 'Child'
							and T.DOCUMENT_TYPE = 'INVOICE'
							AND T.CURRENT_STATUS <> 'CANCL'
							AND I.UPDATED_DATE > CURRENT TIMESTAMP - 1 YEAR
							ORDER BY 6 DESC
	
							for read only
							with ur";
												
						if( $debug ) echo "<p>using query_string = $query_string</p>";
				
						$response2 = send_odbc_query( $query_string, $stc_database, $debug );
						
						if( is_array($response2) ) {
							$response = array();
							$response{'VENDOR'} = $response1;
							$response{'BILLS'} = $response2;
						
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

			case 'AUTH':	// !AUTH - Login authentication
				// Validate fields
				if( $uid == "NONE" || $upw == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
					// Prepare Select
					$query_string = "SELECT CLIENT_ID, NAME, $stc_client_forwarding_user_field FROM CLIENT
					WHERE CLIENT_ID = '".decryptData($uid)."' AND $stc_client_auth_password = '".decryptData($upw)."'
					AND COALESCE($stc_client_auth_password, '') <> ''";
					
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

			case 'AUTH2':	// !AUTH2 - Login authentication
				// Validate fields
				if( $uid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {
				
					// Prepare Select
					$query_string = "SELECT CLIENT_ID, NAME, $stc_client_forwarding_user_field, $stc_client_auth_password FROM CLIENT
					WHERE CLIENT_ID = '".$uid."'";
					
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

			case 'REP1':	// !REP1 - Billing report
				// Validate fields
				if( $dt1 == "NONE" || $dt2 == "NONE" || $src == "NONE" ) {
					if( $debug ) echo "<p>Error - Required fields missing or blank.</p>";
					else echo encryptData("NOT OK: Required fields missing or blank.");
				} else {				
					// Prepare Select
						$query_string = "SELECT DISTINCT T.BILL_NUMBER, T.CURRENT_STATUS, 
							".$stc_schema.".GET_STATUS_DESC(T.CURRENT_STATUS) AS STATUS_DESC, T.DETAIL_LINE_ID,
							(SELECT TRACE.TRACE_NUMBER FROM TRACE
							WHERE T.DETAIL_LINE_ID=TRACE.DETAIL_NUMBER AND TRACE.TRACE_TYPE = '".($ttype <> "NONE" ? $ttype : 'K')."') AS HOF_NUMBER,
							DATE(COALESCE(T.ACTUAL_PICKUP, T.PICK_UP_BY)) AS PICKUP_DATE,
							".$stc_schema.".TRACE_SUB_IL(T.DETAIL_LINE_ID) INTERLINER_ID,
							".$stc_schema.".TRACE_SUB_IL_NAME(T.DETAIL_LINE_ID) INTERLINER_NAME,
							T.ORIGIN, T.ORIGNAME, 
							T.DESTINATION, T.DESTNAME,
							DATE(COALESCE(T.ACTUAL_DELIVERY, T.DELIVER_BY)) AS DELIVERY_DATE,
							
							(SELECT SUM(SUB_AMOUNT) FROM ORDER_INTERLINER o 
								WHERE o.DETAIL_LINE_ID = t.detail_line_id) AS LINEHAUL_COST, 
							
							(SELECT SUM(a.CHARGE_AMOUNT) FROM ACHARGE_TLORDER a, ACHARGE_CODE ac
								WHERE a.DETAIL_LINE_ID = t.DETAIL_LINE_ID
								AND a.CHARGE_AMOUNT > 0.0
								AND a.ACODE_ID = ac.ACODE_ID
								AND ac.ACC_TYPE = 'FSC') AS FUEL_COST,

							(SELECT SUM(a.CHARGE_AMOUNT) FROM ACHARGE_TLORDER a
								WHERE a.DETAIL_LINE_ID = t.DETAIL_LINE_ID
								AND a.CHARGE_AMOUNT > 0.0
								AND a.ACODE_ID IN ('BRO-LUMPER', 'BRO-HANDLE') ) AS LUMPER,

							(SELECT SUM(a.CHARGE_AMOUNT) FROM ACHARGE_TLORDER a
								WHERE a.DETAIL_LINE_ID = t.DETAIL_LINE_ID
								AND a.CHARGE_AMOUNT > 0.0
								AND a.ACODE_ID IN ('BRO-DETN', 'BRO-DET')) AS DETENTION,

							(SELECT SUM(a.CHARGE_AMOUNT) FROM ACHARGE_TLORDER a, ACHARGE_CODE ac
								WHERE a.DETAIL_LINE_ID = t.DETAIL_LINE_ID
								AND a.CHARGE_AMOUNT > 0.0
								AND a.ACODE_ID = ac.ACODE_ID
								AND ac.ACC_TYPE <> 'FSC'
								AND a.ACODE_ID NOT IN ('BRO-LUMPER', 'BRO-HANDLE', 'BRO-DETN', 'BRO-DET', 'MAN-BRO') ) AS MISCCH,
							
							T.INT_PAYABLE_AMT, T.USER4, 
							
							(SELECT SUM(A.CHARGE_AMOUNT) FROM ACHARGE_TLORDER A
								WHERE A.ACODE_ID = 'MAN-BRO'
 								AND A.OTHER_DLID IS NULL 
								AND DETAIL_LINE_ID = t.DETAIL_LINE_ID) AS MANAGEMENT_FEE,
							
							T.CHARGES, T.TOTAL_CHARGES, T.CURRENCY_CODE,
							T.BILL_TO_CODE, T.BILL_TO_NAME, T.INT_PAYABLE_ADJUST_AMT,
							T.PALLETS, T.PIECES
							
							FROM TLORDER T
							WHERE( ".
							($src=="pickup" ? "DATE(COALESCE(T.ACTUAL_PICKUP, T.PICK_UP_BY))" : 
								"DATE(COALESCE(T.ACTUAL_DELIVERY, T.DELIVER_BY)) ")."
								BETWEEN '".$dt1."' AND '".$dt2."'";
							if( $client <> "NONE" ) $query_string .= " AND T.BILL_TO_CODE = '".$client."'";
							$query_string .= " AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
								and t.extra_stops <> 'Child'								
								".($billed=="NONE" ? " and t.current_status <> 'CANCL'":" and t.current_status = 'BILLD'")."
								and T.DOCUMENT_TYPE = 'INVOICE')
							ORDER BY ".($src=="pickup" ? "6" : "13")." ASC
							WITH UR";
							
					//$query_string = "SELECT DISTINCT ACODE_ID, NOTATION FROM ACHARGE_INTERLINER";
					
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
		if( $debug ) echo "<p>Password error.</p>";
	}

	if( $debug ) {
?>
</body>
</html>
<?	
	}
?>

