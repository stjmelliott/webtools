<?php

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./odbc-inc.php" );
require_once( "./stc_config.php" );

	$debug		= FALSE;
	$password	= "";
	$valid_pw	= "cmsyoudaman77";
	$option		= "NONE";
	$uid		= "NONE";
	$cid		= "NONE";
	$cnm		= "NONE";
	$own		= "NONE";
	$days		= "90";

	$M1			= "NONE";
	$Y1			= "NONE";
	$M2			= "NONE";
	$Y2			= "NONE";
	$COMPANY	= "NONE";
	$SITE		= "NONE";
	$FB			= "NONE";
	$OP_CODE	= "NONE";
	$AGENT		= "NONE";

	$DDATE					= "NONE";
	$NAME					= "NONE";
	$CTYPE					= "NONE";
	$CCAT					= "NONE";
	$HREP					= "NONE";
	$HCOMP					= "NONE";
	$RANGE1					= "50";
	$RANGE2					= "50";
	$COMMODITY				= "NONE";
	$YEAR					= "NONE";
	$BUSINESS_PHONE_EXT		= "NONE";
	$FAX_PHONE				= "NONE";
	$EMAIL_ADDRESS			= "NONE";
	$COMPANY_URL			= "NONE";
	$THE_OPTION				= "NONE";
	$THE_VALUE				= "NONE";
	$DATECHOICE				= "NONE";
	$NOGROUP				= false;
	$GROUP					= "NONE";
	
	function date_choice( $datechoice ) {
		switch (strtoupper($datechoice)) {
			case 'CREATED': return "CREATED_TIME";
			case 'PICKUP': return "ACTUAL_PICKUP";
			case 'DELIVER': return "ACTUAL_DELIVERY";
			default: return "CREATED_TIME";
		}	
	}
	
	function daily_sql( $terminal, $datechoice, $ddate ) {
	
		return "WITH MYDATA AS
			(SELECT DISPATCH_AGENT,
			(CASE WHEN SUBSTR(OP_CODE,1,1) = 'J' THEN 'J DISPATCH'
			WHEN SUBSTR(OP_CODE,1,1) = 'O' THEN 'O DISPATCH'
			END) JOB_DESCRIPTION,
			BILL_NUMBER, CUSTOMER, OP_CODE, TOTAL_CHARGES,
			(CASE WHEN CUSTOMER = 'SOUTCARAN' THEN
			'SWI'
			WHEN (SELECT C.CUSTOMER_GROUP FROM CLIENT C
			WHERE C.CLIENT_ID = CUSTOMER) = 'HAAS' THEN
			'HAAS'
			ELSE
			'OTHER'
			END) AS CTYPE
			
			FROM TLORDER
			WHERE DATE(".date_choice( $datechoice ).") = DATE(".($ddate <> "NONE" ? "'".$ddate."'" : "CURRENT DATE").")
			AND CURRENT_STATUS <> 'CANCL'
			AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
			AND EXTRA_STOPS <> 'Child'
			AND BILL_NUMBER NOT LIKE 'Q%'
			AND SITE_ID IN ('SITE1', 'SITE4')
			AND DOCUMENT_TYPE = 'INVOICE'
			
			ORDER by BILL_NUMBER ASC)
			
			SELECT
			(CASE WHEN GROUPING( DISPATCH_AGENT ) = 1 THEN
			'TOTAL'
			ELSE
			(SELECT NAME FROM VENDOR WHERE DISPATCH_AGENT = VENDOR_ID)
			END) AS DISPATCHER,
			SUM(CASE WHEN CTYPE='HAAS' THEN TOTAL_CHARGES ELSE 0 END ) HAAS,
			SUM(CASE WHEN CTYPE='SWI' THEN TOTAL_CHARGES ELSE 0 END ) SWI,
			SUM(CASE WHEN CTYPE='OTHER' THEN TOTAL_CHARGES ELSE 0 END ) INDIVIDUAL,
			SUM(CASE WHEN OP_CODE IN('O','J') THEN TOTAL_CHARGES ELSE 0 END ) OUTBOUND,
			SUM(CASE WHEN OP_CODE IN('OB','JB') THEN TOTAL_CHARGES ELSE 0 END ) BACKHAUL,
			SUM(CASE WHEN OP_CODE IN('OF','JF') THEN TOTAL_CHARGES ELSE 0 END ) FILL,
			SUM(CASE WHEN OP_CODE IN('OL','JL') THEN TOTAL_CHARGES ELSE 0 END ) LOCAL,
			SUM(CASE WHEN OP_CODE NOT IN('O','J','OB','JB','OF','JF','OL','JL') THEN TOTAL_CHARGES ELSE 0 END ) OTHER,
			SUM(TOTAL_CHARGES) TOTALS,
			COUNT(CASE WHEN CTYPE='HAAS' THEN TOTAL_CHARGES END ) \"BOOKED HAAS\",
			COUNT(CASE WHEN OP_CODE IN('O','J') THEN TOTAL_CHARGES END ) \"BOOKED OUTBOUND\",
			COUNT(CASE WHEN OP_CODE IN('OB','JB') THEN TOTAL_CHARGES END ) \"BOOKED BACKHAUL\",
			COUNT(CASE WHEN OP_CODE IN('OF','JF') THEN TOTAL_CHARGES END ) \"BOOKED FILL\",
			COUNT(CASE WHEN OP_CODE IN('OL','JL') THEN TOTAL_CHARGES END ) \"BOOKED LOCAL\",
			COUNT(CASE WHEN OP_CODE NOT IN('O','J','OB','JB','OF','JF','OL','JL') THEN TOTAL_CHARGES END ) \"BOOKED OTHER\",
			COUNT(TOTAL_CHARGES) \"BOOKED TOTALS\"
			
			FROM MYDATA
			WHERE JOB_DESCRIPTION = '$terminal'
			
			GROUP BY GROUPING SETS ( (DISPATCH_AGENT), () )
			ORDER BY DISPATCH_AGENT
			FOR READ ONLY
			WITH UR";
	}
	
	function daily_sql_bills( $datechoice, $ddate ) {
	
		return "SELECT DISPATCH_AGENT,
			(SELECT NAME FROM VENDOR WHERE DISPATCH_AGENT = VENDOR_ID) AGENT_NAME,
			(CASE WHEN SITE_ID = 'SITE2' OR SUBSTR(OP_CODE,1,1) = 'J' THEN 'J DISPATCH'
			WHEN SITE_ID = 'SITE5' OR SUBSTR(OP_CODE,1,1) = 'O' THEN 'O DISPATCH'
			END) JOB_DESCRIPTION,
			(SELECT ODRSTAT.UPDATED_BY
				FROM ODRSTAT
				WHERE ODRSTAT.ORDER_ID = TLORDER.DETAIL_LINE_ID
				AND ODRSTAT.STATUS_CODE = 'ASSGN'
				ORDER BY CHANGED DESC
				FETCH FIRST 1 ROW ONLY) ASSGN_DISP,
			BILL_NUMBER, CUSTOMER, BILL_TO_CODE, BILL_TO_NAME,
			OP_CODE, TOTAL_CHARGES, SITE_ID, 
			(SELECT S.SITE_NAME FROM SITE S WHERE S.SITE_ID = TLORDER.SITE_ID),
			CURRENCY_CODE, DOCUMENT_TYPE,
			(CASE WHEN CUSTOMER = 'SOUTCARAN' THEN
			'SWI'
			WHEN (SELECT C.CUSTOMER_GROUP FROM CLIENT C
			WHERE C.CLIENT_ID = CUSTOMER) = 'HAAS' THEN
			'HAAS'
			ELSE
			'OTHER'
			END) AS CTYPE
			
			FROM TLORDER
			WHERE DATE(".date_choice( $datechoice ).") = DATE(".($ddate <> "NONE" ? "'".$ddate."'" : "CURRENT DATE").")
			AND CURRENT_STATUS <> 'CANCL'
			AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
			AND EXTRA_STOPS <> 'Child'
			AND BILL_NUMBER NOT LIKE 'Q%'
			AND SITE_ID IN ('SITE1', 'SITE4', 'SITE2', 'SITE5')
			AND DOCUMENT_TYPE = 'INVOICE'
			
			
			ORDER by BILL_NUMBER ASC
			FOR READ ONLY
			WITH UR";
	}
	
	function weekly_sql( $terminal, $datechoice, $ddate ) {
	
		return "WITH MYDATA AS
			(SELECT DISPATCH_AGENT,
			DATE(".date_choice( $datechoice ).") DUE,
			(CASE WHEN SUBSTR(OP_CODE,1,1) = 'J' THEN 'J DISPATCH'
			WHEN SUBSTR(OP_CODE,1,1) = 'O' THEN 'O DISPATCH'
			END) JOB_DESCRIPTION,
			TOTAL_CHARGES
			FROM TLORDER
			WHERE WEEK(".date_choice( $datechoice ).") = WEEK(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE").")
			AND YEAR(".date_choice( $datechoice ).") = YEAR(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE").")
			AND CURRENT_STATUS <> 'CANCL'
			AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
			AND EXTRA_STOPS <> 'Child'
			AND BILL_NUMBER NOT LIKE 'Q%'
			AND SITE_ID IN ('SITE1', 'SITE4')
			AND DOCUMENT_TYPE = 'INVOICE'
			ORDER by BILL_NUMBER ASC)
			
			SELECT (CASE WHEN GROUPING( DISPATCH_AGENT ) = 1 THEN
			'TOTAL'
			ELSE
			(SELECT NAME FROM VENDOR WHERE DISPATCH_AGENT = VENDOR_ID)
			END) AS DISPATCHER,
			SUM(CASE WHEN DAYOFWEEK(DATE(DUE)) = 2 THEN TOTAL_CHARGES ELSE 0 END ) MONDAY,
			SUM(CASE WHEN DAYOFWEEK(DATE(DUE)) = 3 THEN TOTAL_CHARGES ELSE 0 END ) TUESDAY,
			SUM(CASE WHEN DAYOFWEEK(DATE(DUE)) = 4 THEN TOTAL_CHARGES ELSE 0 END ) WEDNESDAY,
			SUM(CASE WHEN DAYOFWEEK(DATE(DUE)) = 5 THEN TOTAL_CHARGES ELSE 0 END ) THURSDAY,
			SUM(CASE WHEN DAYOFWEEK(DATE(DUE)) = 6 THEN TOTAL_CHARGES ELSE 0 END ) FRIDAY,
			SUM(TOTAL_CHARGES) TOTAL_CHARGES
			FROM MYDATA
			WHERE JOB_DESCRIPTION = '$terminal'
			GROUP BY GROUPING SETS ( (DISPATCH_AGENT), () )
			ORDER BY DISPATCH_AGENT
			FOR READ ONLY
			WITH UR";
	}
	
	function weekly_sql_details( $terminal, $datechoice, $ddate ) {
	
		return "WITH MYDATA AS
			(SELECT DISPATCH_AGENT,
			(CASE WHEN SUBSTR(OP_CODE,1,1) = 'J' THEN 'J DISPATCH'
			WHEN SUBSTR(OP_CODE,1,1) = 'O' THEN 'O DISPATCH'
			END) JOB_DESCRIPTION,
			BILL_NUMBER, CUSTOMER, OP_CODE, TOTAL_CHARGES,
			(CASE WHEN CUSTOMER = 'SOUTCARAN' THEN
			'SWI'
			WHEN (SELECT C.CUSTOMER_GROUP FROM CLIENT C
			WHERE C.CLIENT_ID = CUSTOMER) = 'HAAS' THEN
			'HAAS'
			ELSE
			'OTHER'
			END) AS CTYPE
			
			FROM TLORDER
			WHERE WEEK(".date_choice( $datechoice ).") = WEEK(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE").")
			AND YEAR(".date_choice( $datechoice ).") = YEAR(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE").")
			AND CURRENT_STATUS <> 'CANCL'
			AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
			AND EXTRA_STOPS <> 'Child'
			AND BILL_NUMBER NOT LIKE 'Q%'
			AND SITE_ID IN ('SITE1', 'SITE4')
			AND DOCUMENT_TYPE = 'INVOICE'
			
			ORDER by BILL_NUMBER ASC)
			
			SELECT
			(CASE WHEN GROUPING( DISPATCH_AGENT ) = 1 THEN
			'TOTAL'
			ELSE
			(SELECT NAME FROM VENDOR WHERE DISPATCH_AGENT = VENDOR_ID)
			END) AS DISPATCHER,
			SUM(CASE WHEN CTYPE='HAAS' THEN TOTAL_CHARGES ELSE 0 END ) HAAS,
			SUM(CASE WHEN CTYPE='SWI' THEN TOTAL_CHARGES ELSE 0 END ) SWI,
			SUM(CASE WHEN CTYPE='OTHER' THEN TOTAL_CHARGES ELSE 0 END ) INDIVIDUAL,
			SUM(CASE WHEN OP_CODE IN('O','J') THEN TOTAL_CHARGES ELSE 0 END ) OUTBOUND,
			SUM(CASE WHEN OP_CODE IN('OB','JB') THEN TOTAL_CHARGES ELSE 0 END ) BACKHAUL,
			SUM(CASE WHEN OP_CODE IN('OF','JF') THEN TOTAL_CHARGES ELSE 0 END ) FILL,
			SUM(CASE WHEN OP_CODE IN('OL','JL') THEN TOTAL_CHARGES ELSE 0 END ) LOCAL,
			SUM(CASE WHEN OP_CODE NOT IN('O','J','OB','JB','OF','JF','OL','JL') THEN TOTAL_CHARGES ELSE 0 END ) OTHER,
			SUM(TOTAL_CHARGES) TOTALS,
			COUNT(CASE WHEN CTYPE='HAAS' THEN TOTAL_CHARGES END ) \"BOOKED HAAS\",
			COUNT(CASE WHEN OP_CODE IN('O','J') THEN TOTAL_CHARGES END ) \"BOOKED OUTBOUND\",
			COUNT(CASE WHEN OP_CODE IN('OB','JB') THEN TOTAL_CHARGES END ) \"BOOKED BACKHAUL\",
			COUNT(CASE WHEN OP_CODE IN('OF','JF') THEN TOTAL_CHARGES END ) \"BOOKED FILL\",
			COUNT(CASE WHEN OP_CODE IN('OL','JL') THEN TOTAL_CHARGES END ) \"BOOKED LOCAL\",
			COUNT(CASE WHEN OP_CODE NOT IN('O','J','OB','JB','OF','JF','OL','JL') THEN TOTAL_CHARGES END ) \"BOOKED OTHER\",
			COUNT(TOTAL_CHARGES) \"BOOKED TOTALS\"
			
			FROM MYDATA
			WHERE JOB_DESCRIPTION = '$terminal'
			
			GROUP BY GROUPING SETS ( (DISPATCH_AGENT), () )
			ORDER BY DISPATCH_AGENT
			FOR READ ONLY
			WITH UR";
	}
	
	function weekly_logistics_sql( $datechoice, $ddate ) {
			//	(SELECT S.SITE_NAME FROM SITE S WHERE S.SITE_ID = TLORDER.SITE_ID),

		return "WITH MYDATA AS
			(SELECT DISPATCH_AGENT,
			DATE(".date_choice( $datechoice ).") DUE,
			SITE_ID,
			TOTAL_CHARGES
			FROM TLORDER
			WHERE WEEK(".date_choice( $datechoice ).") = WEEK(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE").")
			AND YEAR(".date_choice( $datechoice ).") = YEAR(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE").")
			AND CURRENT_STATUS <> 'CANCL'
			AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
			AND EXTRA_STOPS <> 'Child'
			AND BILL_NUMBER NOT LIKE 'Q%'
			AND SITE_ID IN ('SITE2', 'SITE5')
			AND DOCUMENT_TYPE = 'INVOICE'
			ORDER by BILL_NUMBER ASC)
			
			SELECT (CASE WHEN GROUPING( SITE_ID ) = 1 THEN
				'GRAND TOTAL' 
				WHEN GROUPING( DISPATCH_AGENT ) = 1 THEN
				(SELECT S.SITE_NAME FROM SITE_ALL S WHERE S.SITE_ID = MYDATA.SITE_ID) || ' TOTAL'
				ELSE 
				(SELECT S.SITE_NAME FROM SITE_ALL S WHERE S.SITE_ID = MYDATA.SITE_ID)
				END) AS TERMINAL,
			(CASE WHEN GROUPING( DISPATCH_AGENT ) = 1 THEN
				'' ELSE
				(SELECT NAME FROM VENDOR WHERE DISPATCH_AGENT = VENDOR_ID)
				END) AS SALES_AGENT,
			SUM(CASE WHEN DAYOFWEEK(DATE(DUE)) = 2 THEN TOTAL_CHARGES ELSE 0 END ) MONDAY,
			SUM(CASE WHEN DAYOFWEEK(DATE(DUE)) = 3 THEN TOTAL_CHARGES ELSE 0 END ) TUESDAY,
			SUM(CASE WHEN DAYOFWEEK(DATE(DUE)) = 4 THEN TOTAL_CHARGES ELSE 0 END ) WEDNESDAY,
			SUM(CASE WHEN DAYOFWEEK(DATE(DUE)) = 5 THEN TOTAL_CHARGES ELSE 0 END ) THURSDAY,
			SUM(CASE WHEN DAYOFWEEK(DATE(DUE)) = 6 THEN TOTAL_CHARGES ELSE 0 END ) FRIDAY,
			SUM(TOTAL_CHARGES) TOTAL_CHARGES,
			COUNT(CASE WHEN DAYOFWEEK(DATE(DUE)) = 2 THEN TOTAL_CHARGES END ) LOADS_MONDAY,
			COUNT(CASE WHEN DAYOFWEEK(DATE(DUE)) = 3 THEN TOTAL_CHARGES END ) LOADS_TUESDAY,
			COUNT(CASE WHEN DAYOFWEEK(DATE(DUE)) = 4 THEN TOTAL_CHARGES END ) LOADS_WEDNESDAY,
			COUNT(CASE WHEN DAYOFWEEK(DATE(DUE)) = 5 THEN TOTAL_CHARGES END ) LOADS_THURSDAY,
			COUNT(CASE WHEN DAYOFWEEK(DATE(DUE)) = 6 THEN TOTAL_CHARGES END ) LOADS_FRIDAY,
			COUNT(TOTAL_CHARGES) LOADS_TOTAL

			FROM MYDATA
			GROUP BY GROUPING SETS ( (SITE_ID), (SITE_ID, DISPATCH_AGENT), () )
			ORDER BY SITE_ID, DISPATCH_AGENT
			FOR READ ONLY
			WITH UR";
	}
	
	function org_weekly_haas( $datechoice, $ddate, $debug ) {
	
	//AND SUBSTR(BILL_NUMBER,1,1) = 'O'
	//AND SITE_ID IN ('SITE1', 'SITE4')

	//if( $debug )
		return "WITH WEEKLY_AVERAGE AS (SELECT CUSTOMER, 
			ROUND(SUM(WKLY_LOADS)/WEEK(CURRENT DATE),0) AS WKLY_AVG_LOADS,
			ROUND(SUM(WKLY_REVENUE)/WEEK(CURRENT DATE),0) AS WKLY_AVG_REVENUE
			FROM
				(SELECT CASE WHEN COALESCE(C.USER2, '') <> '' THEN C.USER2 
				ELSE C.CLIENT_ID END AS CUSTOMER,
				WEEK(".date_choice( $datechoice ).") WKNO, 
				COUNT(*) AS WKLY_LOADS, SUM(TOTAL_CHARGES) AS WKLY_REVENUE
				FROM TLORDER, CLIENT C
				WHERE YEAR(".date_choice( $datechoice ).") = YEAR(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE").")
				
				AND C.CLIENT_ID = COALESCE(BILL_TO_CODE,CUSTOMER)
				AND C.CUSTOMER_GROUP = 'HAAS'
				AND CURRENT_STATUS NOT IN ('CANCL', 'QUOTE')
				AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
				AND EXTRA_STOPS <> 'Child'
				AND BILL_NUMBER NOT LIKE 'Q%'
				
				AND DOCUMENT_TYPE IN ('INVOICE','REBILL')
				AND CREATED_TIME = (
						SELECT MAX(J.CREATED_TIME) FROM
						TLORDER J
						WHERE TLORDER.BILL_NUMBER = J.BILL_NUMBER
						AND J.DOCUMENT_TYPE IN ('INVOICE','REBILL'))
				GROUP BY CASE WHEN COALESCE(C.USER2, '') <> '' THEN C.USER2 ELSE C.CLIENT_ID END, WEEK(".date_choice( $datechoice ).")
				ORDER by 1 ASC, 2 ASC)
			GROUP BY CUSTOMER
			ORDER BY CUSTOMER ASC),
		
		RAW_DATA AS
			(SELECT CASE WHEN COALESCE(C.USER2, '') <> '' THEN C.USER2 
			ELSE COALESCE(BILL_TO_CODE,CUSTOMER) END AS CUSTOMER, 
			BILL_NUMBER, DATE(".date_choice( $datechoice ).") DUE, TOTAL_CHARGES,
			(CASE WHEN WEEK(".date_choice( $datechoice ).") = WEEK(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE").") THEN
							'THIS WEEK' ELSE 'LAST WEEK' END) AS WEEK_DESC
			FROM TLORDER, CLIENT C
			WHERE ((WEEK(".date_choice( $datechoice ).") = WEEK(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE").")
			AND YEAR(".date_choice( $datechoice ).") = YEAR(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE")."))
			OR (WEEK(".date_choice( $datechoice ).") = WEEK(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE")." - 7 DAYS)
			AND YEAR(".date_choice( $datechoice ).") = YEAR(".($ddate <> "NONE" ? "DATE('".$ddate."')" : "CURRENT DATE")." - 7 DAYS)))
			AND SUBSTR(BILL_NUMBER,1,1) = 'O'
			AND C.CLIENT_ID = CUSTOMER
			AND C.CUSTOMER_GROUP = 'HAAS'
			AND CURRENT_STATUS NOT IN ('CANCL', 'QUOTE')
			AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
			AND EXTRA_STOPS <> 'Child'
			AND BILL_NUMBER NOT LIKE 'Q%'
			AND SITE_ID IN ('SITE1', 'SITE4')
			AND DOCUMENT_TYPE = 'INVOICE'
			ORDER by ".date_choice( $datechoice )." ASC),

		LAST_WEEK AS
			(SELECT CUSTOMER, COUNT(TOTAL_CHARGES) LAST_LOADS, 
				ROUND(SUM(TOTAL_CHARGES),0) LAST_REVENUE
			FROM RAW_DATA
			WHERE WEEK_DESC = 'LAST WEEK'
			GROUP BY CUSTOMER
			ORDER BY CUSTOMER),
						
		THIS_WEEK AS
			(SELECT CUSTOMER, COUNT(TOTAL_CHARGES) THIS_LOADS, 
				ROUND(SUM(TOTAL_CHARGES),0) THIS_REVENUE
			FROM RAW_DATA
			WHERE WEEK_DESC = 'THIS WEEK'
			GROUP BY CUSTOMER
			ORDER BY CUSTOMER)


		SELECT GROUPING(NAME) AS SORT1,
			CASE WHEN GROUPING(NAME) = 1 THEN 'TOTAL' ELSE NAME END AS NAME,
			CUSTOMER, 
			SUM(WKLY_AVG_LOADS) AS WKLY_AVG_LOADS,
			SUM(WKLY_AVG_REVENUE) AS WKLY_AVG_REVENUE,
			SUM(LAST_LOADS) AS LAST_LOADS, SUM(LAST_REVENUE) AS LAST_REVENUE,
			SUM(THIS_LOADS) AS THIS_LOADS, SUM(THIS_REVENUE) AS THIS_REVENUE,
			SUM((COALESCE(THIS_LOADS,0) - WKLY_AVG_LOADS)) AS THIS_LOADS_VARIANCE,
			SUM((COALESCE(THIS_REVENUE,0) - WKLY_AVG_REVENUE)) AS THIS_REVENUE_VARIANCE,
			SUM((COALESCE(LAST_LOADS,0) - WKLY_AVG_LOADS)) AS LAST_LOADS_VARIANCE,
			SUM((COALESCE(LAST_REVENUE,0) - WKLY_AVG_REVENUE)) AS LAST_REVENUE_VARIANCE
		FROM
			(SELECT WEEKLY_AVERAGE.CUSTOMER,
				(SELECT NAME FROM CLIENT WHERE CLIENT_ID = WEEKLY_AVERAGE.CUSTOMER),
				WKLY_AVG_LOADS,
				WKLY_AVG_REVENUE,
				LAST_LOADS, LAST_REVENUE,
				THIS_LOADS, THIS_REVENUE
	
			FROM WEEKLY_AVERAGE
	          LEFT OUTER JOIN LAST_WEEK
	          ON WEEKLY_AVERAGE.CUSTOMER = LAST_WEEK.CUSTOMER
	          LEFT OUTER JOIN THIS_WEEK
	          ON WEEKLY_AVERAGE.CUSTOMER = THIS_WEEK.CUSTOMER)
	      GROUP BY GROUPING SETS ( (CUSTOMER, NAME), ())
	      ORDER BY SORT1 ASC, WKLY_AVG_LOADS DESC
          			
			FOR READ ONLY
			WITH UR";	

	}
	
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
		} else if( $key == "DATE" ) {
			$DDATE = $value;
		} else if( $key == "CID" ) {
			$cid = $value;
		} else if( $key == "OWN" ) {
			$own = $value;
		} else if( $key == "CLIENT_ID" ) {
			$CLIENT_ID = $value;
		} else if( $key == "NAME" ) {
			$NAME = $value;
		} else if( $key == "CTYPE" ) {
			$CTYPE = $value;
		} else if( $key == "CCAT" ) {
			$CCAT = $value;
		} else if( $key == "HREP" ) {
			$HREP = $value;
		} else if( $key == "HCOMP" ) {
			$HCOMP = $value;
		} else if( $key == "RANGE2" ) {
			$RANGE2 = $value;
		} else if( $key == "COMMODITY" ) {
			$COMMODITY = $value;
		} else if( $key == "YEAR" ) {
			$YEAR = $value;
		} else if( $key == "BUSINESS_PHONE_EXT" ) {
			$BUSINESS_PHONE_EXT = $value;
		} else if( $key == "FAX_PHONE" ) {
			$FAX_PHONE = $value;
		} else if( $key == "EMAIL_ADDRESS" ) {
			$EMAIL_ADDRESS = $value;
		} else if( $key == "COMPANY_URL" ) {
			$COMPANY_URL = $value;
		} else if( $key == "THE_OPTION" ) {
			$THE_OPTION = $value;
		} else if( $key == "THE_VALUE" ) {
			$THE_VALUE = $value;
		} else if( $key == "DATECHOICE" ) {
			$DATECHOICE = $value;
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
		} else if( $key == "COMPANY" ) {
			$COMPANY = $value;
		} else if( $key == "SITE" ) {
			$SITE = $value;
		} else if( $key == "FB" ) {
			$FB = $value;
		} else if( $key == "OP_CODE" ) {
			$OP_CODE = $value;
		} else if( $key == "AGENT" ) {
			$AGENT = $value;
		} else if( $key == "GROUP" ) {
			$GROUP = $value;
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

			case 'GETOPTS':  // !GETOPTS - Recap Options
			
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
					$query_string2 = "INSERT INTO CONFIG 
					(PROG_NAME, THE_OPTION, OPTION_HINT, THE_VALUE, VALUE_HINT, HIDDEN, COMPANY_ID)
					VALUES ('STRONGTOWER.EXE', 'Orange_Daily', 'Daily sales goal for Orange Terminal', '47575', 'number', 'False', 1),
							('STRONGTOWER.EXE', 'Janesville_Daily', 'Daily sales goal for Janesville Terminal', '47575', 'number', 'False', 1),
							('STRONGTOWER.EXE', 'Rev_Length_Mile_Low', 'Low revenue/foot/mile', '0.05', 'number', 'False', 1),
							('STRONGTOWER.EXE', 'Rev_Length_Mile_High', 'High revenue/foot/mile', '0.15', 'number', 'False', 1),
							('STRONGTOWER.EXE', 'Rev_Weight_Mile_Low', 'Low revenue/1000lb/mile', '0.10', 'number', 'False', 1),
							('STRONGTOWER.EXE', 'Rev_Weight_Mile_High', 'High revenue/1000lb/mile', '0.30', 'number', 'False', 1)";
													
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
							else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
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
						SET THE_VALUE = '".$THE_VALUE."', MODIFIED_DATE = CURRENT TIMESTAMP
						WHERE PROG_NAME = 'STRONGTOWER.EXE'
						AND THE_OPTION = '".$THE_OPTION."'";
						
					if( $debug ) echo "<p>using query_string = $query_string</p>";
					
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) ) {
							if( $debug ) echo "<p>CHANGED OPTION</p>";							
							else echo encryptData("CHANGED");
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}
				break;

			case 'DAILY':  // !DAILY - Show daily stats
				
				$query_string1 = daily_sql( 'J DISPATCH', $DATECHOICE, $DDATE );
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
				$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
				
				if( is_array($response1) ) {
					
					$query_string2 = daily_sql( 'O DISPATCH', $DATECHOICE, $DDATE );
										
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string2);
						echo "</pre>";
					}
		
					$response2 = send_odbc_query( $query_string2, $stc_database, $debug );
				
					if( is_array($response2) ) {
						
						$query_string3 = org_weekly_haas( $DATECHOICE, $DDATE, $debug );
											
						if( $debug ) {
							echo "<p>using query_string = </p>
							<pre>";
							var_dump($query_string3);
							echo "</pre>";
						}
			
						$response3 = send_odbc_query( $query_string3, $stc_database, $debug );
					
						if( is_array($response3) ) {
							
							$terminals = array();
							$terminals{'Orange Terminal'} = $response2;
							$terminals{'Janesville Terminal'} = $response1;
							$response = array();
							$response{'Terminals'} = $terminals;
							$response{'Orange Weekly HAAS'} = $response3;
	
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
					else echo encryptData("NOT OK: send_odbc_query 2 failed: " . $last_odbc_error);
				}

				break;

			case 'BILLS':  // !BILLS - Show daily bills
				
				$query_string1 = daily_sql_bills( $DATECHOICE, $DDATE );
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
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

				break;
				
			case 'WEEKLY':  // !WEEKLY - Show weekly stats
				
				$query_string1 = weekly_sql( 'J DISPATCH', $DATECHOICE, $DDATE );
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
				$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
				
				if( is_array($response1) ) {
					
					$query_string2 = weekly_sql( 'O DISPATCH', $DATECHOICE, $DDATE );
										
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string2);
						echo "</pre>";
					}
		
					$response2 = send_odbc_query( $query_string2, $stc_database, $debug );
				
					if( is_array($response2) ) {

						$query_string3 = weekly_sql_details( 'J DISPATCH', $DATECHOICE, $DDATE );
												
						if( $debug ) {
							echo "<p>using query_string = </p>
							<pre>";
							var_dump($query_string3);
							echo "</pre>";
						}
				
						$response3 = send_odbc_query( $query_string3, $stc_database, $debug );
						
						if( is_array($response3) ) {
							
							$query_string4 = weekly_sql_details( 'O DISPATCH', $DATECHOICE, $DDATE );
												
							if( $debug ) {
								echo "<p>using query_string = </p>
								<pre>";
								var_dump($query_string4);
								echo "</pre>";
							}
				
							$response4 = send_odbc_query( $query_string4, $stc_database, $debug );

							if( is_array($response4) ) {
								
								$response = array();
								$response{'Totals'} = array( 
									'Orange Terminal' => $response2,
									'Janesville Terminal' => $response1 );
								$response{'Details'} = array( 
									'Orange Terminal' => $response4,
									'Janesville Terminal' => $response3 );
		
								if( $debug ) {
									echo "<pre>";
									var_dump($response);
									echo "</pre>";
								} else {
									echo encryptData(json_encode( $response ));
								}
							} else {
								if( $debug ) echo "<p>Error - send_odbc_query 4 failed. $last_odbc_error</p>";
								else echo encryptData("NOT OK: send_odbc_query 4 failed: " . $last_odbc_error);
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

			case 'KLI':  // !KLI - Show weekly logistics
				
				$query_string1 = weekly_logistics_sql( $DATECHOICE, $DDATE );
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
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

				break;
				
			case 'HYEARS':  // !HYEARS - Historical - list of years
				
				$query_string1 = "SELECT DISTINCT QB_INV_YEAR
					FROM KAISER_QB_INVOICES
					for read only
					with ur";
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
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

				break;
				
			case 'CTYPE':  // !CTYPE - Client types
				
				$query_string1 = "select CUST_VALUE 
					from CUSTOM_LIST_VALUES
					where custdef_id = 3
					for read only
					with ur";
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
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

				break;
				
			case 'HREPS':  // !HREPS - Sales Reps
				
				$query_string1 = "select DISTINCT C.SALES_REP 
					from CLIENT C
					WHERE COALESCE(C.SALES_REP, '') <> ''
					for read only
					with ur";
					
					//, KAISER_QB_INVOICES K
					//where C.CLIENT_ID = QB_TMCLIENT
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
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

				break;
				
			case 'HCOMP':  // !HCOMP - Company
				
				$query_string1 = "select DISTINCT QB_COMPANY 
					from KAISER_QB_INVOICES
					for read only
					with ur";
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
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

				break;
				
			case 'HSUM':  // !HSUM - Historical - sum by year and month
				
				$query_string1 = "SELECT QB_TMCLIENT, QB_NAME, QB_INV_YEAR, MONTH(QB_INV_DATE)  INV_MONTH,
					SUM(QB_INV_AMT) QB_REVENUE
					FROM KAISER_QB_INVOICES
					WHERE 1 = 1
					".($YEAR <> "NONE" ? "AND QB_INV_YEAR = '".$YEAR."'" : "")."
					".($CTYPE <> "NONE" ? "AND (SELECT DATA
						FROM CUSTOM_DATA
						WHERE QB_TMCLIENT = SRC_TABLE_KEY
						AND CUSTDEF_ID = 3) = '".$CTYPE."'" : "")."
					".($HREP <> "NONE" ? "AND (SELECT SALES_REP
						FROM CLIENT
						WHERE QB_TMCLIENT = CLIENT_ID) = '".$HREP."'" : "")."
					".($HCOMP <> "NONE" ? "AND QB_COMPANY = '".$HCOMP."'" : "")."
					GROUP BY QB_TMCLIENT, QB_NAME, QB_INV_YEAR, MONTH(QB_INV_DATE)
					ORDER BY QB_TMCLIENT, QB_NAME, QB_INV_YEAR, MONTH(QB_INV_DATE)
					for read only
					with ur";
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
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

				break;
				
			case 'HSUM2':  // !HSUM2 - Historical - sum by year and month
				// Validate fields
				if( $M1 == "NONE" || $Y1 == "NONE" || $M2 == "NONE" || $Y2 == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$has_groups = false;		
					// Prepare Select
					$query_string = "SELECT DISTINCT COALESCE(C.CUSTOMER_GROUP, '') CUSTOMER_GROUP
						FROM CLIENT C
						WITH UR";
					
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string);
						echo "</pre>";
					}
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) && count($response) > 1 ) {
						if( $debug ) echo "<p>CUSTOMER_GROUP is used</p>";
						$has_groups = true;
					} else {
						if( $debug ) echo "<p>CUSTOMER_GROUP not used</p>";
					}
					
					if( $stc_override_groups ) $has_groups = false;
				
					if( $has_groups && ! $NOGROUP ) {
						$group_code = "(CASE WHEN COALESCE(C.CUSTOMER_GROUP, '') <> '' THEN
								C.CUSTOMER_GROUP
							WHEN COALESCE(C.USER2, '') <> '' THEN
								C.USER2
							ELSE C.CLIENT_ID END)";
						$group_name = "(CASE WHEN COALESCE(C.CUSTOMER_GROUP, '') <> '' THEN
							'IS_A_GROUP'
							WHEN COALESCE(C.USER2, '') <> '' THEN
								C.USER2
							ELSE C.CLIENT_ID END)";
					} else {
						$group_code = "(CASE WHEN COALESCE(C.USER2, '') <> '' THEN
								C.USER2
							ELSE C.CLIENT_ID END)";
						$group_name = "(CASE WHEN COALESCE(C.USER2, '') <> '' THEN
								(SELECT NAME FROM CLIENT D WHERE D.CLIENT_ID = C.USER2)
							ELSE C.NAME END)";
					}
					
					if( $COMPANY <> "NONE" )
						switch( $COMPANY ) {
						case '1':
							$company_match = "AND QB_COMPANY = 'QT'";
							break;
						case '3':
							$company_match = "AND QB_COMPANY = 'QL'";
							break;
						default:
							$company_match = "AND QB_COMPANY = 'NONE'";
						}

					switch( $SITE ) {
						case 'SITE1':	// JVL Transport
							$site_match = "AND QB_COMPANY = 'QT' AND SUBSTR(QB_INV_NUMBER,1,1) IN ('5', '6')";
							break;
						case 'SITE4':	// ORG Transport
							$site_match = "AND QB_COMPANY = 'QT' AND SUBSTR(QB_INV_NUMBER,1,1) IN ('7', '8')";
							break;
						case 'SITE2':	// JVL Logistics
							$site_match = "AND QB_COMPANY = 'QL' AND SUBSTR(QB_INV_NUMBER,1,1) = '2'";
							break;
						case 'SITE5':	// ORG Logistics
							$site_match = "AND QB_COMPANY = 'QL' AND SUBSTR(QB_INV_NUMBER,1,1) = '5'";
							break;
						default:
							$site_match = "";
					}
				
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
	
					if( $HREP <> "NONE" )
						$hrep_match = "AND C.SALES_REP = '".$HREP."'";
					else
						$hrep_match = "";
					
					if( $GROUP <> "NONE" )
						$group_match = "AND C.CUSTOMER_GROUP = '".$GROUP."'";
					else
						$group_match = "";
					
					$query_string1 = "WITH MY_TLORDER AS
						(SELECT COALESCE(C.CUSTOMER_GROUP, '') AS CUSTOMER_GROUP,
						(CASE WHEN COALESCE(C.USER2, '') <> '' THEN
							C.USER2 ELSE C.CLIENT_ID END) AS CLIENT_ID, 
						(CASE WHEN COALESCE(C.USER2, '') <> '' THEN
								(SELECT NAME FROM CLIENT D WHERE D.CLIENT_ID = C.USER2)
							ELSE C.NAME END) AS NAME,
						QB_INV_YEAR INV_YEAR, MONTH(QB_INV_DATE)  INV_MONTH, 
						QB_INV_AMT
						
						FROM KAISER_QB_INVOICES, CLIENT C
						WHERE ((MONTH(QB_INV_DATE) >= ".$M1." AND YEAR(QB_INV_DATE) = ".$Y1.")
							OR YEAR(QB_INV_DATE) > ".$Y1.")							
						AND ((MONTH(QB_INV_DATE) <= ".$M2." AND YEAR(QB_INV_DATE) = ".$Y2.")
							OR YEAR(QB_INV_DATE) < ".$Y2.")
						AND QB_TMCLIENT = C.CLIENT_ID
						".$site_match."
						".$company_match."
						".$hrep_match." 
						".$type_match." 
						".$cat_match."
						".$group_match." )
					
						SELECT INV_YEAR, INV_MONTH, CUSTOMER_GROUP, CLIENT_ID, NAME, REVENUE, IS_A_GROUP
						FROM
						(SELECT INV_YEAR, INV_MONTH,
							CUSTOMER_GROUP, CLIENT_ID, NAME,
							ROUND(COALESCE(SUM(QB_INV_AMT),0),0) REVENUE,
							CASE WHEN GROUPING(CLIENT_ID) = 1 THEN 'True' ELSE '' END AS IS_A_GROUP
						
						FROM MY_TLORDER T
											
						GROUP BY GROUPING SETS ( (INV_YEAR, INV_MONTH, CUSTOMER_GROUP), 
							(INV_YEAR, INV_MONTH, CUSTOMER_GROUP, CLIENT_ID, NAME) )
						ORDER BY INV_YEAR, INV_MONTH, CUSTOMER_GROUP, CLIENT_ID, NAME)
						WHERE NOT (CUSTOMER_GROUP = '' AND IS_A_GROUP = 'True')

						for read only
						with ur";
											
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string1);
						echo "</pre>";
					}
			
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
				
			case 'TMSUM':  // !TMSUM - Truckmate - sum by year and month
				// Validate fields
				if( $M1 == "NONE" || $Y1 == "NONE" || $M2 == "NONE" || $Y2 == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					$has_groups = false;		
					// Prepare Select
					$query_string = "SELECT DISTINCT COALESCE(C.CUSTOMER_GROUP, '') CUSTOMER_GROUP
						FROM CLIENT C
						WITH UR";
					
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string);
						echo "</pre>";
					}
			
					$response = send_odbc_query( $query_string, $stc_database, $debug );
					
					if( is_array($response) && count($response) > 1 ) {
						if( $debug ) echo "<p>CUSTOMER_GROUP is used</p>";
						$has_groups = true;
					} else {
						if( $debug ) echo "<p>CUSTOMER_GROUP not used</p>";
					}
					
					if( $stc_override_groups ) $has_groups = false;
				
					if( $has_groups && ! $NOGROUP ) {
						$group_code = "(CASE WHEN COALESCE(C.CUSTOMER_GROUP, '') <> '' THEN
								C.CUSTOMER_GROUP
							WHEN COALESCE(C.USER2, '') <> '' THEN
								C.USER2
							ELSE C.CLIENT_ID END)";
						$group_name = "(CASE WHEN COALESCE(C.CUSTOMER_GROUP, '') <> '' THEN
							'IS_A_GROUP'
							WHEN COALESCE(C.USER2, '') <> '' THEN
								(SELECT NAME FROM CLIENT D WHERE D.CLIENT_ID = C.USER2)
							ELSE C.NAME END)";
					} else {
						$group_code = "(CASE WHEN COALESCE(C.USER2, '') <> '' THEN
								C.USER2
							ELSE C.CLIENT_ID END)";
						$group_name = "(CASE WHEN COALESCE(C.USER2, '') <> '' THEN
								(SELECT NAME FROM CLIENT D WHERE D.CLIENT_ID = C.USER2)
							ELSE C.NAME END)";
					}
									
					if( $GROUP <> "NONE" )
						$group_match = "AND C.CUSTOMER_GROUP = '".$GROUP."'";
					else
						$group_match = "";
						
					if( $COMPANY <> "NONE" )
						$company_match = "AND T.COMPANY_ID = ".$COMPANY;
					else
						$company_match = "";
						
					if( $SITE <> "NONE" )
						$site_match = "AND T.SITE_ID = '".$SITE."'";
					else
						$site_match = "";

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
	
					if( $HREP <> "NONE" )
						$hrep_match = "AND C.SALES_REP = '".$HREP."'";
					else
						$hrep_match = "";
					
					if( $GROUP <> "NONE" )
						$group_match = "AND C.CUSTOMER_GROUP = '".$GROUP."'";
					else
						$group_match = "";
					
					$query_string1 = "WITH MY_TLORDER AS
						(SELECT COALESCE(C.CUSTOMER_GROUP, '') AS CUSTOMER_GROUP,
						(CASE WHEN COALESCE(C.USER2, '') <> '' THEN
							C.USER2 ELSE C.CLIENT_ID END) AS CLIENT_ID, 
						(CASE WHEN COALESCE(C.USER2, '') <> '' THEN
								(SELECT NAME FROM CLIENT D WHERE D.CLIENT_ID = C.USER2)
							ELSE C.NAME END) AS NAME,
							BILL_DATE, ACTUAL_DELIVERY,
							(SELECT MAX(O.CHANGED)
							FROM ODRSTAT O
							WHERE O.ORDER_ID = T.DETAIL_LINE_ID
							AND O.STATUS_CODE = 'COMPLETE') COMPLETED,
						T.INTERFACE_STATUS_F, TOTAL_CHARGES, T.BILL_TO_CODE
												
						FROM TLORDER T, CLIENT C
						WHERE C.CLIENT_ID = COALESCE(T.BILL_TO_CODE,T.CUSTOMER)
						AND T.CURRENT_STATUS NOT IN ('CANCL', 'QUOTE')
						AND T.DOCUMENT_TYPE = 'INVOICE'
						AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
						AND T.EXTRA_STOPS <> 'Child'
						AND T.BILL_NUMBER NOT LIKE 'Q%'
						AND T.CREATED_TIME = (
							SELECT MAX(J.CREATED_TIME) FROM
							TLORDER J
							WHERE T.BILL_NUMBER = J.BILL_NUMBER
							AND J.DOCUMENT_TYPE IN ('INVOICE','REBILL'))
						".$site_match."
						".$company_match."
						".$hrep_match." 
						".$type_match." 
						".$cat_match."
						".$group_match." )
					
						SELECT INV_YEAR, INV_MONTH, CUSTOMER_GROUP, CLIENT_ID, NAME, REVENUE, IS_A_GROUP
						FROM
						(SELECT YEAR(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)) INV_YEAR,
							MONTH(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED))  INV_MONTH,
							CUSTOMER_GROUP, CLIENT_ID, NAME,
							ROUND(COALESCE(SUM(TOTAL_CHARGES),0),0) REVENUE,
							CASE WHEN GROUPING(CLIENT_ID) = 1 THEN 'True' ELSE '' END AS IS_A_GROUP
						
						FROM MY_TLORDER T
						WHERE (T.INTERFACE_STATUS_F >= 0 OR T.INTERFACE_STATUS_F IS NULL)
						AND ((MONTH(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)) >= ".$M1." AND 
							YEAR(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)) = ".$Y1.")
							OR YEAR(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)) > ".$Y1.")
							
						AND ((MONTH(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)) <= ".$M2." AND 
							YEAR(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)) = ".$Y2.")
							OR YEAR(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)) < ".$Y2.")
											
						GROUP BY GROUPING SETS ( (YEAR(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)),
							MONTH(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)),
							CUSTOMER_GROUP), 
							(YEAR(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)),
							MONTH(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)),
							CUSTOMER_GROUP, CLIENT_ID, NAME) )
						ORDER BY YEAR(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)),
							MONTH(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)),
							CUSTOMER_GROUP, CLIENT_ID, NAME)
						WHERE NOT (CUSTOMER_GROUP = '' AND IS_A_GROUP = 'True')
						for read only
						with ur";
						/*
						GROUP BY YEAR(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)),
							MONTH(COALESCE(T.ACTUAL_DELIVERY, T.COMPLETED)),
							CUSTOMER_GROUP, CLIENT_ID, NAME*/

											
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string1);
						echo "</pre>";
					}
			
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
				
			case 'TREPORT':  // !TREPORT - Trip report
				switch( $SITE ) {
					case 'ALL':
						$match = "";
						break;
					case 'CAINBOUND':
						$match = "AND T.OP_CODE = 'OB'";
						break;
					case 'WIINBOUND':
						$match = "AND T.OP_CODE  = 'JB'";
						break;
					case 'CAFILL':
						$match = "AND T.OP_CODE = 'OF'";
						break;
					case 'WIFILL':
						$match = "AND T.OP_CODE  = 'JF'";
						break;
					case 'CAOUTBOUND':
						$match = "AND T.OP_CODE  = 'O'";
						break;
					case 'WIOUTBOUND':
						$match = "AND T.OP_CODE  = 'J'";
						break;
				}
				
				$query_string1 = "SELECT TRIP_NUMBER, ORIGIN_ZONE, DESTINATION_ZONE, ORIG_ZONE_DESC, DEST_ZONE_DESC, 
					(SELECT LS_DRIVER
					FROM LEGSUM
					WHERE LS_TRIP_NUMBER = TRIP_NUMBER
					fetch first row only) DRIVER,
					WEIGHT, LENGTH_1,
					DISTANCE, GROSS_REVENUE,
					(CASE WHEN DISTANCE > 0 THEN
					ROUND(GROSS_REVENUE/DISTANCE,2)
					ELSE 0 END) CPM
					FROM
					
					(SELECT R.TRIP_NUMBER, R.ORIGIN_ZONE, R.DESTINATION_ZONE, R.ORIG_ZONE_DESC, R.DEST_ZONE_DESC,
					(SELECT ROUND(SUM(LS_LEG_DIST),0)
					FROM LEGSUM
					WHERE LS_TRIP_NUMBER = R.TRIP_NUMBER) DISTANCE,
					ROUND(SUM(TOTAL_CHARGES),0) GROSS_REVENUE,
					SUM(T.ROLLUP_WEIGHT) WEIGHT, SUM(T.ROLLUP_LENGTH_1) LENGTH_1
					
					FROM TLORDER T, TRIP R, ITRIPTLO I
					WHERE WEEK(R.TE_DATE) = WEEK(".($DDATE <> "NONE" ? "DATE('".$DDATE."')" : "CURRENT DATE").")
					AND YEAR(R.TE_DATE) = YEAR(".($DDATE <> "NONE" ? "DATE('".$DDATE."')" : "CURRENT DATE").")
					AND I.TRIP_NUMBER = R.TRIP_NUMBER
					AND I.bill_number = T.bill_number
					AND T.CURRENT_STATUS <> 'CANCL'
					AND COALESCE(T.BILL_NUMBER, 'NA') <> 'NA'
					AND T.EXTRA_STOPS <> 'Child'
					AND T.BILL_NUMBER NOT LIKE 'Q%'
					AND T.SITE_ID IN ('SITE1', 'SITE4')
					AND T.DOCUMENT_TYPE = 'INVOICE'
					".$match."
					GROUP BY R.TRIP_NUMBER, R.ORIGIN_ZONE, R.DESTINATION_ZONE, R.ORIG_ZONE_DESC, R.DEST_ZONE_DESC
					ORDER by R.TRIP_NUMBER ASC)
					WHERE DISTANCE > 0
					for read only
					with ur";
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
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

				break;
				
			case 'FIXOP':  // !FIXOP - Fix Op Code
				// Validate fields
				if( $FB == "NONE" || $OP_CODE == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					$query_string1 = "UPDATE TLORDER
						SET OP_CODE = '".$OP_CODE."'
						WHERE BILL_NUMBER = '".$FB."'";
											
					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string1);
						echo "</pre>";
					}
			
					$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
					
					if( is_array($response1) ) {
						
						if( $debug ) echo "<p>DONE</p>";
						else echo encryptData("DONE");
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}

				break;

			case 'OPCODES':  // !OPCODES - List of Operation Codes
				
				$query_string1 = "SELECT OP_CODE, DESCRIPTION, COMPANY_ID
					FROM OPERATION_CODES
					WHERE COMPANY_ID = 1
					ORDER BY OP_CODE
					for read only
					with ur";
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
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

				break;
				
			case 'AGENTS':  // !AGENTS - List of Agents
				
				$query_string1 = "SELECT VENDOR_ID, NAME
					FROM VENDOR 
					WHERE VENDOR_TYPE='G'
					for read only
					with ur";
										
				if( $debug ) {
					echo "<p>using query_string = </p>
					<pre>";
					var_dump($query_string1);
					echo "</pre>";
				}
		
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

				break;
				
			case 'FIXAGENT':  // !FIXAGENT - Fix Agent
				// Validate fields
				if( $FB == "NONE" || $AGENT == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
				
					$query_string1 = "SELECT NAME
						FROM VENDOR 
						WHERE VENDOR_TYPE='G'
						AND VENDOR_ID = '".$AGENT."'
						for read only
						with ur";

					if( $debug ) {
						echo "<p>using query_string = </p>
						<pre>";
						var_dump($query_string1);
						echo "</pre>";
					}
			
					$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
					
					if( is_array($response1) && count($response1) == 1 ) {
					
						$query_string1 = "UPDATE TLORDER
							SET DISPATCH_AGENT = '".$AGENT."'
							WHERE BILL_NUMBER = '".$FB."'";
												
						if( $debug ) echo "<p>using query_string = $query_string1</p>";
				
						$response1 = send_odbc_query( $query_string1, $stc_database, $debug );
						
						if( is_array($response1) ) {
							
							if( $debug ) echo "<p>DONE</p>";
							else echo encryptData("DONE");
						} else {
							if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
							else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
						}
					} else {
						if( $debug ) echo "<p>Error - send_odbc_query 1 failed. $last_odbc_error</p>";
						else echo encryptData("NOT OK: send_odbc_query failed: " . $last_odbc_error);
					}
				}

				break;

			case 'WKLY':	// !WKLY - Get weekly loads & revenue for a client
				// Validate fields
				if( $cid == "NONE" ) {
					if( $debug ) echo "<p>Error - Required field missing or blank.</p>";
					else echo encryptData("NOT OK: Required field missing or blank.");
				} else {
					
					// Prepare Select
					$query_string = "SELECT CASE WHEN COALESCE(C.USER2, '') <> '' THEN C.USER2
						ELSE C.CLIENT_ID END AS CUSTOMER,
						WEEK(CREATED_TIME) WKNO,
						DATE(CREATED_TIME - ( DAYOFWEEK(CREATED_TIME) - 2 ) DAYS) AS MON,
						DATE(CREATED_TIME - ( DAYOFWEEK(CREATED_TIME) - 2 ) DAYS + 4 DAYS) AS FRI,
						COUNT(*) AS WKLY_LOADS, SUM(TOTAL_CHARGES) AS WKLY_REVENUE
						FROM TLORDER, CLIENT C
						WHERE YEAR(CREATED_TIME) = YEAR(CURRENT DATE)
		
						AND C.CLIENT_ID = COALESCE(BILL_TO_CODE,CUSTOMER)
						AND CURRENT_STATUS NOT IN ('CANCL', 'QUOTE')
						AND COALESCE(BILL_NUMBER, 'NA') <> 'NA'
						AND EXTRA_STOPS <> 'Child'
						AND BILL_NUMBER NOT LIKE 'Q%'
						AND (CASE WHEN COALESCE(C.USER2, '') <> '' THEN C.USER2
						ELSE C.CLIENT_ID END) = '".$cid."'
		
						AND DOCUMENT_TYPE IN ('INVOICE','REBILL')
						AND CREATED_TIME = (
								SELECT MAX(J.CREATED_TIME) FROM
								TLORDER J
								WHERE TLORDER.BILL_NUMBER = J.BILL_NUMBER
								AND J.DOCUMENT_TYPE IN ('INVOICE','REBILL'))
						GROUP BY CASE WHEN COALESCE(C.USER2, '') <> '' THEN C.USER2 ELSE C.CLIENT_ID END, WEEK(CREATED_TIME),
						DATE(CREATED_TIME - ( DAYOFWEEK(CREATED_TIME) - 2 ) DAYS),
						DATE(CREATED_TIME - ( DAYOFWEEK(CREATED_TIME) - 2 ) DAYS + 4 DAYS)
						ORDER by 1 ASC, 2 ASC
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

