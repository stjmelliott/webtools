<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );

	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman73";
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
	$PARTCODE				= "NONE";
	$SHOP					= "NONE";
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
		} else if( $key == "PARTCODE" ) {
			$PARTCODE = $value;
		} else if( $key == "SHOP" ) {
			$SHOP = $value;
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

			case 'LISTPU':  // List power units
				
				// Prepare Select
				$query_string = "SELECT P.CLASS, P.UNIT_ID, P.OWNERSHIP_TYPE, F.FLEET_NAME, P.LOCATION,
					P.VIN, DATE(P.INSURANCE_EXPIRY) INSURANCE_EXPIRY, DATE(P.WDATE) WDATE,
					P.MAXWEIGHT, P.MAXWEIGHTUNITS, P.EMPTY_WEIGHT, P.EMPTY_WEIGHT_UNIT, 
					E.MAKE ENG_MAKE, E.MODEL ENG_MODEL, T.MAKE TR_MAKE, T.MODEL TR_MODEL, 
					P.TIRE_SIZE, P.PLATE, P.MODEL PU_MODEL, DATE(P.ACQUISITION_DATE) ACQUISITION_DATE, P.FUEL_TYPE, 
					DD.DD_DESCRIPTION FUEL_DESC, P.NET_BOOK_VALUE
					FROM (((PUNIT P
					LEFT OUTER JOIN ENGINE E
					ON P.ENGINE = E.ENGINE_CODE)
					LEFT OUTER JOIN TRNSMSSN T
					ON P.TRANSMISSION = T.TRANSMISSION_CODE)
					LEFT OUTER JOIN FLEET F
					ON P.FLEET_ID = F.FLEET_ID)
					LEFT OUTER JOIN DROPDOWN DD
					ON DD.DD_TYPE = 'FUEL' AND DD.DD_ID = P.FUEL_TYPE
					WHERE
					ACTIVE_WHERE = 'D' ";
					if( $UNIT_ID <> "NONE" ) $query_string .= "AND P.UNIT_ID = '".$UNIT_ID."' ";
					$query_string .= "ORDER BY P.CLASS, P.UNIT_ID
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
						echo json_encode( $response );
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
				}

				break;

			case 'HIST':  //! HIST -  Maintenance history
				
				// Prepare Select
				$query_string = "SELECT RT.NUMBER,RW.DESCRIPTION, RT.PART_DESCRIPTION, RW.LOCCODE, RW.LOCTYPE, 
					RW.MECHCODE, RT.COMPONENT, RC.PARTTYPE, RT.PARTCLASS,
					CASE WHEN RC.PARTTYPE IN ('PT', 'TR', 'FL') THEN RT.TOTAL_COST END AS COST_PARTS,
					CASE WHEN RC.PARTTYPE = 'LB' THEN RT.TOTAL_COST END AS COST_LABOUR,
					DATE(RW.STARTDATE) TRANSDATE, RT.TOTAL_COST, RW.WOTYPE, RW.TOTAL_CHARGES,
					RW.WARRANTY_WORK, RT.PARTCODE, RW.NOTES
					
					FROM
					  RM_TRANSACTION RT, RM_WORKORDER RW, RM_CLASS RC
					WHERE
					  RT.POSTED = 'True' AND
					  RT.NUMBER = RW.WONUMBER AND
					  RT.PARTCLASS = RC.PARTCLASS AND
					  RW.LOCCODE = '".$UNIT_ID."' AND
					  RW.LOCTYPE = '".$LOCTYPE."'";
					  
				if( $YR <> "NONE" && $MN <> "NONE" )
					$query_string .= " AND YEAR(RW.STARTDATE) = ".$YR."
					AND MONTH(RW.STARTDATE) = ".$MN;
				$query_string .= " order by RW.STARTDATE ASC, RT.NUMBER ASC
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
						echo json_encode( $response, JSON_INVALID_UTF8_IGNORE );
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
				}

				break;

			case 'LCODES':  // Maintenance history
				
				// Prepare Select
				$query_string = "select partcode, description from RM_PART
					where partclass = 'LB'";
					  
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
						echo json_encode( $response );
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
				}

				break;

			case 'HISTPC':  // Maintenance history based on labor code
				
				// Prepare Select
				$query_string = "SELECT RT.NUMBER,RW.DESCRIPTION, RT.PART_DESCRIPTION, RW.LOCCODE, RW.LOCTYPE,
					RW.MECHCODE, RT.COMPONENT, RC.PARTTYPE, RT.PARTCLASS, RC.DESCRIPTION,
					CASE WHEN RC.PARTTYPE IN ('PT', 'TR', 'FL') THEN RT.TOTAL_COST END AS COST_PARTS,
					CASE WHEN RC.PARTTYPE = 'LB' THEN RT.TOTAL_COST END AS COST_LABOUR,
					DATE(RW.STARTDATE) TRANSDATE, RT.TOTAL_COST, RW.WOTYPE, RW.TOTAL_CHARGES,
					RW.WARRANTY_WORK, RT.PARTCODE, RW.NOTES
					
					FROM
					  RM_TRANSACTION RT, RM_WORKORDER RW, RM_CLASS RC
					WHERE
					  RT.POSTED = 'True' AND
					  RT.NUMBER = RW.WONUMBER AND
					  ((SELECT T.PARTCODE  
					  FROM RM_TRANSACTION T
					  WHERE T.PARTCLASS IN ('LB', 'OI', 'OU', 'AM')
					  AND T.PARTCODE = '".$PARTCODE."'
					  AND T.NUMBER = RT.NUMBER
					  FETCH FIRST ROW ONLY) = '".$PARTCODE."')
					  AND RT.PARTCLASS = RC.PARTCLASS";
					  
				if( $YR <> "NONE" && $MN <> "NONE" )
					$query_string .= " AND YEAR(RW.STARTDATE) = ".$YR."
					AND MONTH(RW.STARTDATE) = ".$MN;
				if( $SHOP <> "NONE" )
					$query_string .= " AND RW.SHOPCODE = '".$SHOP."'";
				$query_string .= " order by RW.STARTDATE ASC, RT.NUMBER ASC
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
						echo json_encode( $response );
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
				}

				break;

			case 'FHIST':  // Fuel history
				
				// Prepare Select
				$query_string = "SELECT DATE(F.DATETIME) ACTIVITY_DATE, T.TRIP_ID,
					(
					CASE EA.LOCTYPE
						WHEN 'PU' THEN VOL_PFUEL
						WHEN 'TR' THEN VOL_RFUEL
						ELSE 0
					END
					) FUEL_VOL,
					(
					CASE EA.LOCTYPE
						WHEN 'PU' THEN RATE_PFUEL
						WHEN 'TR' THEN RATE_RFUEL
						ELSE 0
					END
					) FUEL_RATE,(
					CASE EA.LOCTYPE
						WHEN 'PU' THEN COST_PFUEL
						WHEN 'TR' THEN COST_RFUEL
						ELSE 0
					END
					) FUEL_COST, LOCATION, STATION_NAME
					 FROM   FT_TRIP T, FT_FUEL F, RPT_RM_ACTIVE_EQUIP EA
					 WHERE T.TRIP_ID = F.TRIP_ID
					AND EA.LOCTYPE = '".$LOCTYPE."'
					AND EA.LOCCODE = '".$UNIT_ID."'";
				if( $YR <> "NONE" && $MN <> "NONE" )
					$query_string .= " AND YEAR(F.DATETIME) = ".$YR."
					AND MONTH(F.DATETIME) = ".$MN;
					
				$query_string .= " AND ( (EA.LOCTYPE = 'PU' AND T.UNIT_ID = EA.LOCCODE)
					OR
						(EA.LOCTYPE = 'TR' AND T.TRAILER_ID = EA.LOCCODE) )
					AND EA.EQ_ACTIVE = 'Y'
					
					order by F.DATETIME ASC
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
						echo json_encode( $response );
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
				}

				break;

			case 'MILES':  // Get miles
				
				// Prepare Select
				$query_string = "SELECT LOCTYPE, CLASS, LOCCODE, MN, SUM(DIST) DIST
					FROM
					(
					SELECT M.LOCTYPE,
					(
					CASE M.LOCTYPE
						WHEN 'PU' THEN (SELECT CLASS FROM PUNIT WHERE UNIT_ID = M.LOCCODE)
						WHEN 'TR' THEN (SELECT CLASS FROM TRAILER WHERE TRAILER_ID = M.LOCCODE)
						WHEN 'EQ' THEN (SELECT CLASS FROM EQUIP WHERE EQUIPMENT_ID = M.LOCCODE)
					END
					) CLASS, M.LOCCODE, YEAR(M.CURDATE) || '-' || RIGHT( '0' || MONTH(M.CURDATE), 2) MN, M.DISTADD DIST
					 FROM   RM_MILE M, RPT_RM_ACTIVE_EQUIP EA
					 WHERE  M.LOCTYPE IN ('PU', 'TR', 'EQ')
					 and ( YEAR(M.CURDATE) = year(current date)
					 or ( (YEAR(M.CURDATE) = year(current date) - 1)
						AND (MONTH(M.CURDATE) > MONTH(current date)) ) )
					AND M.LOCTYPE = EA.LOCTYPE
					AND M.LOCCODE = EA.LOCCODE
					AND M.DISTADD <> 0
					 order by M.LOCTYPE, CLASS ASC, M.LOCCODE ASC, MONTH(M.CURDATE) ASC
					 )
					 GROUP BY LOCTYPE, CLASS, LOCCODE, MN
					 ORDER BY LOCTYPE ASC, CLASS ASC, LOCCODE ASC, MN ASC
					for read only
					with ur";
					//AND EA.EQ_ACTIVE LIKE 'Y'
										
				if( $debug ) echo "<p>using query_string = $query_string</p>";
		
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response) ) {
					if( $debug ) {
						echo "<pre>";
						var_dump($response);
						echo "</pre>";
					} else {
						echo json_encode( $response );
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
				}

				break;

			case 'COSTS':  // Get maintenance costs
				
				// Prepare Select
				$query_string = "SELECT LOCTYPE, CLASS, LOCCODE, MN, SUM(COST) COST
					FROM
					(
					SELECT RW.LOCTYPE,
					(
					CASE RW.LOCTYPE
						WHEN 'PU' THEN (SELECT CLASS FROM PUNIT WHERE UNIT_ID = RW.LOCCODE)
						WHEN 'TR' THEN (SELECT CLASS FROM TRAILER WHERE TRAILER_ID = RW.LOCCODE)
						WHEN 'EQ' THEN (SELECT CLASS FROM EQUIP WHERE EQUIPMENT_ID = RW.LOCCODE)
					END
					) CLASS, RW.LOCCODE, YEAR(RW.STARTDATE) || '-' || RIGHT( '0' || MONTH(RW.STARTDATE), 2) MN, RT.TOTAL_COST COST
					 FROM   RM_TRANSACTION RT, RM_WORKORDER RW, RM_CLASS RC, RPT_RM_ACTIVE_EQUIP EA
					 WHERE  RW.LOCTYPE IN ('PU', 'TR', 'EQ')
					 and ( YEAR(RW.STARTDATE) = year(current date)
					 or ( (YEAR(RW.STARTDATE) = year(current date) - 1)
						AND (MONTH(RW.STARTDATE) > MONTH(current date)) ) )
					AND RT.POSTED = 'True'
					AND RT.NUMBER = RW.WONUMBER
					AND RT.PARTCLASS = RC.PARTCLASS
					AND RW.LOCTYPE = EA.LOCTYPE
					AND RW.LOCCODE = EA.LOCCODE";
					//AND EA.EQ_ACTIVE LIKE 'Y'
					
				if( $UNIT_ID <> "NONE" && $LOCTYPE <> "NONE")
					$query_string .= "AND RW.LOCCODE = '".$UNIT_ID."' AND
						RW.LOCTYPE = '".$LOCTYPE."'";

					
				$query_string .= " order by RW.LOCTYPE, CLASS ASC, RW.LOCCODE ASC, MONTH(RW.STARTDATE) ASC
					)
					GROUP BY LOCTYPE, CLASS, LOCCODE, MN
					ORDER BY LOCTYPE ASC, CLASS ASC, LOCCODE ASC, MN ASC
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
						echo json_encode( $response );
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
				}

				break;

			case 'COSTPC':  // Get maintenance costs by part code
				
				// Prepare Select
				$query_string = "SELECT PARTCODE, MN, SUM(COST) COST
					FROM
					(
					SELECT DISTINCT RW.WONUMBER, RT.PARTCODE,
					
					 YEAR(RW.STARTDATE) || '-' || RIGHT( '0' || MONTH(RW.STARTDATE), 2) MN, RW.TOTAL_CHARGES COST
					 FROM   RM_TRANSACTION RT, RM_WORKORDER RW, RM_CLASS RC
					 WHERE  RW.LOCTYPE IN ('PU', 'TR', 'EQ')
					 and ( YEAR(RW.STARTDATE) = year(current date)
					 or ( (YEAR(RW.STARTDATE) = year(current date) - 1)
						AND (MONTH(RW.STARTDATE) > MONTH(current date)) ) )
					AND RT.POSTED = 'True'
					AND RT.NUMBER = RW.WONUMBER
					AND RT.PARTCLASS = RC.PARTCLASS
					AND RT.PARTCLASS IN ('LB', 'OI', 'OU', 'AM')";
					
				$query_string .= " 
					)
					GROUP BY PARTCODE, MN
					ORDER BY PARTCODE ASC, MN ASC
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
						echo json_encode( $response );
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
				}

				break;

			case 'COSTPCL':  // Get maintenance costs by part code & location
				
				// Prepare Select
				$query_string = "SELECT PARTCODE, SHOPCODE, MN, SUM(COST) COST
					FROM
					(
					SELECT DISTINCT RW.WONUMBER, RW.SHOPCODE, RT.PARTCODE,
					
					 YEAR(RW.STARTDATE) || '-' || RIGHT( '0' || MONTH(RW.STARTDATE), 2) MN, RW.TOTAL_CHARGES COST
					 FROM   RM_TRANSACTION RT, RM_WORKORDER RW, RM_CLASS RC
					 WHERE  RW.LOCTYPE IN ('PU', 'TR', 'EQ')
					 and ( YEAR(RW.STARTDATE) = year(current date)
					 or ( (YEAR(RW.STARTDATE) = year(current date) - 1)
						AND (MONTH(RW.STARTDATE) > MONTH(current date)) ) )
					AND RT.POSTED = 'True'
					AND RT.NUMBER = RW.WONUMBER
					AND RT.PARTCLASS = RC.PARTCLASS
					AND RT.PARTCLASS IN ('LB', 'OI', 'OU', 'AM')";
					
				$query_string .= " 
					)
					GROUP BY PARTCODE, SHOPCODE, MN
					ORDER BY PARTCODE ASC, SHOPCODE ASC, MN ASC
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
						echo json_encode( $response );
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
				}

				break;

			case 'FUEL':  // Get Fuel cost
				
				// Prepare Select
				/* Old one, subselects took too long. Dropped trailer fuel.
				$query_string = "SELECT LOCTYPE, CLASS, LOCCODE, MN, SUM(FUEL) FUEL
					FROM
					(
					SELECT EA.LOCTYPE,
					(
					CASE EA.LOCTYPE
						WHEN 'PU' THEN (SELECT CLASS FROM PUNIT WHERE UNIT_ID = T.UNIT_ID)
						WHEN 'TR' THEN (SELECT CLASS FROM TRAILER WHERE TRAILER_ID = T.UNIT_ID)
						ELSE ''
					END
					) CLASS, EA.LOCCODE, YEAR(T.ACTIVITY_DATE) || '-' || RIGHT( '0' || MONTH(T.ACTIVITY_DATE), 2) MN, 
					(
					CASE EA.LOCTYPE
						WHEN 'PU' THEN COST_PFUEL
						WHEN 'TR' THEN COST_RFUEL
						ELSE 0
					END
					) FUEL
					 FROM   FT_TRIP T, FT_FUEL F, RPT_RM_ACTIVE_EQUIP EA, PUNIT P
					 WHERE T.TRIP_ID = F.TRIP_ID
					AND ( YEAR(T.ACTIVITY_DATE) = year(current date)
					 or ( (YEAR(T.ACTIVITY_DATE) = year(current date) - 1)
						AND (MONTH(T.ACTIVITY_DATE) > MONTH(current date)) ) )
					AND EA.LOCTYPE IN ('PU', 'TR')
					
					AND ( (EA.LOCTYPE = 'PU' 
						AND P.UNIT_ID = T.UNIT_ID  
						AND P.OWNERSHIP_TYPE = 'C'
						AND T.UNIT_ID = EA.LOCCODE)
					OR
						(EA.LOCTYPE = 'TR' AND T.TRAILER_ID = EA.LOCCODE) )
					
					order by EA.LOCTYPE, CLASS ASC, EA.LOCCODE ASC, MONTH(T.ACTIVITY_DATE) ASC
					)
					GROUP BY LOCTYPE, CLASS, LOCCODE, MN
					ORDER BY LOCTYPE ASC, CLASS ASC, LOCCODE ASC, MN ASC
					for read only
					with ur"; */
				$query_string = "SELECT LOCTYPE, CLASS, LOCCODE, MN, SUM(FUEL) FUEL
					FROM
					(
					SELECT EA.LOCTYPE,
					(
					CASE EA.LOCTYPE
						WHEN 'PU' THEN P.CLASS
						ELSE ''
					END
					) CLASS, 
					EA.LOCCODE, 
					YEAR(F.DATETIME) || '-' || RIGHT( '0' || MONTH(F.DATETIME), 2) MN, 
					(
					CASE EA.LOCTYPE
						WHEN 'PU' THEN COST_PFUEL
						WHEN 'TR' THEN COST_RFUEL
						ELSE 0
					END
					) FUEL
					 FROM   FT_TRIP T, FT_FUEL F, RPT_RM_ACTIVE_EQUIP EA, PUNIT P
					 WHERE T.TRIP_ID = F.TRIP_ID
					AND ( YEAR(F.DATETIME) = year(current date)
					 or ( (YEAR(F.DATETIME) = year(current date) - 1)
						AND (MONTH(F.DATETIME) > MONTH(current date)) ) )
					
					AND EA.LOCTYPE = 'PU' 
					AND P.UNIT_ID = T.UNIT_ID  
					AND P.OWNERSHIP_TYPE = 'C'
					AND T.UNIT_ID = EA.LOCCODE
					
					order by EA.LOCTYPE, CLASS ASC, EA.LOCCODE ASC, MONTH(F.DATETIME) ASC
					)
					GROUP BY LOCTYPE, CLASS, LOCCODE, MN
					ORDER BY LOCTYPE ASC, CLASS ASC, LOCCODE ASC, MN ASC
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
						echo json_encode( $response );
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
				}

				break;

			case 'FIXFUEL':  // Fix duplicate fuel entries
				
				if( $stc_fix_fuel ) {
				// Prepare Select
				$query_string = "DELETE
					FROM
						 FT_FUEL
					WHERE
						 fuel_id IN
						 (
							  SELECT
								   T1.fuel_id
							  FROM
								   FT_FUEL T1
							  INNER JOIN FT_FUEL T2 ON
								   T2.trip_id = T1.trip_id AND
								   T2.datetime = T1.datetime AND
							T2.cost_pfuel = T1.cost_pfuel AND
								   T2.fuel_id > T1.fuel_id AND
								   T2.LOCATION = T1.LOCATION
						 )";
										
				if( $debug ) echo "<p>using query_string = $query_string</p>";
		
				$response = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( is_array($response) ) {
					if( $debug ) echo "<p>FIXED</p>";
					else echo "FIXED";
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
				}
				} else {
					echo "FIXED";
				}


				break;

			case 'LABOR':  // Get maintenance costs by part code
				
				// Prepare Select
				$query_string = "SELECT SHOPCODE, MECHCODE, MN, SUM(QUANTITY) LABOR
					FROM
					(
					SELECT RW.SHOPCODE, RW.MECHCODE, 
					YEAR(RW.STARTDATE) || '-' || RIGHT( '0' || MONTH(RW.STARTDATE), 2) MN,
					RT.QUANTITY, RT.RATE, RT.TOTAL_COST AS COST_LABOUR
					
					FROM RM_TRANSACTION RT, RM_WORKORDER RW, RM_CLASS RC
					WHERE RT.POSTED = 'True'
					AND RT.NUMBER = RW.WONUMBER
					AND RT.PARTCLASS = RC.PARTCLASS
					and ( YEAR(RW.STARTDATE) = year(current date)
					or ( (YEAR(RW.STARTDATE) = year(current date) - 1)
					  AND (MONTH(RW.STARTDATE) > MONTH(current date)) ) )
					
					AND RC.PARTTYPE = 'LB'
					";
					
				$query_string .= " 
					)
					GROUP BY SHOPCODE, MECHCODE, MN
					ORDER BY SHOPCODE ASC, MECHCODE ASC, MN ASC
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
						echo json_encode( $response );
					}
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
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

