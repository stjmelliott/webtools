<?php

// no direct access
defined('_FUZZY') or die('Restricted access');

error_reporting(E_ERROR | E_PARSE);
// Profiling
$stc_profiling = false;	// Keep this false, it can be turned on elsewhere as needed
require_once( "../stc_crm/stc_timer.php" );

// DB2 Database information - change for your site.
$stc_host		= '10.128.0.7'; // 192.168.100.245, 192.168.100.244
$stc_port		= '50000';
$stc_odbc_driver = "IBM DB2 ODBC DRIVER - DB2COPY1";
$stc_database	= "M3"; // TM14DB
$stc_schema		= "TMWIN";
$stc_user		= 'db2admin';
$stc_password	= 'S0rdf1sh!';

// PostgreSQL Database information - for TMW imaging server
$stc_pg_odbc_driver      = 'PostgreSQL Unicode';
$stc_pg_odbc_database    = 'images';
$stc_pg_odbc_host        = 'tbd';
$stc_pg_odbc_user        = 'tbd';
$stc_pg_odbc_password    = '';	// currently not used

// This defines the IP address and port number to reach the server
//$stc_ip_address = $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']."/server/stc_trace";
$stc_ip_address = "localhost/stc_trace";

// These have to match the $valid_pw in each module.
$stc_functions = array(
	'st_user_functions'		=> "/stc_crm_user.php?pw=cmsyoudaman68",	// User functions.	
	'st_client_functions'	=> "/stc_crm_client.php?pw=cmsyoudaman69",	// Client functions.
	'st_contact_functions'	=> "/stc_crm_contact.php?pw=cmsyoudaman70",	// Contact functions.
	'st_notes_functions'	=> "/stc_crm_notes.php?pw=cmsyoudaman71",	// Notes functions.
	'st_bms_functions'		=> "/stc_crm_bms.php?pw=cmsyoudaman72",		// BMS functions.
	'st_rm_functions'		=> "/stc_crm_rm.php?pw=cmsyoudaman73",		// R&M functions.
	'st_pricing_functions'	=> "/stc_crm_pricing.php?pw=cmsyoudaman74",	// Pricing functions.
	'st_kpi_functions'		=> "/stc_crm_kpi.php?pw=cmsyoudaman77",		// HR functions.
	'st_hr_functions'		=> "/stc_crm_hr.php?pw=cmsyoudaman75",		// HR functions.
	'st_images_functions'	=> "/stc_crm_images.php?pw=cmsyoudaman76",	// Images functions.
	'st_recap_functions'	=> "/stc_crm_recap.php?pw=cmsyoudaman77",	// Recap functions.
	'st_quote_functions'	=> "/stc_crm_quote.php?pw=cmsyoudaman78",	// Quote functions.
	'st_drvpay_functions'	=> "/stc_crm_drvpay.php?pw=cmsyoudaman79",	// Driver Pay functions.

	// Used for login authentication
	'stc_remote_auth'	=> "http://".$stc_ip_address."/trip_trace_fb.php?pw=youdaman758&opt=auth",
	
	// Used for getting a list of trace for a client
	'stc_remote_list'	=> "http://".$stc_ip_address."/trip_trace_fb.php?pw=youdaman758&opt=list",

	'stc_remote_dates'	=> "http://".$stc_ip_address."/trip_trace_fb.php?pw=youdaman758&opt=dates",
	
	// Used for getting details for a single trace
	'stc_remote_gett'	=> "http://".$stc_ip_address."/trip_trace_fb.php?pw=youdaman758&opt=gett",
	
	// Used for getting details for a single freight bill
	'stc_remote_getfb'	=> "http://".$stc_ip_address."/trip_trace_fb.php?pw=youdaman758&opt=getfb",

	// Used for getting details for a single freight bill
	'stc_remote_caller'	=> "http://".$stc_ip_address."/trip_trace_fb.php?pw=youdaman758&opt=caller",

	// Used for getting details for a trip
	'stc_remote_gettrip' => "http://".$stc_ip_address."/trip_trace_fb.php?pw=youdaman758&opt=trip",

	// Used for getting details for an interliner
	'stc_remote_getint'	=> "http://".$stc_ip_address."/trip_trace_fb.php?pw=youdaman758&opt=int",

	// Used for getting billing
	'stc_remote_rep1'	=> "http://".$stc_ip_address."/trip_trace_fb.php?pw=youdaman758&opt=rep1"
	
);

// Fix fuel data, remove duplicates
$stc_fix_fuel = false;

// Override groups
$stc_override_groups = false;

// Client forwarding
$stc_client_forwarding_enabled = false;
$stc_client_forwarding_user_field = 'USER2';

// Client auth password
$stc_client_auth_password = 'USER9';

// Quote Notes
$stc_internal_note_type = 'O';
$stc_external_note_type = 'B';

// Client categories, using a custom defined field
$stc_custom_client_categories = false;
$stc_custom_client_categories_id = '70';
$stc_custom_client_account_rep_id = '85';

// Client types, using a custom defined field
$stc_custom_client_types = false;
$stc_custom_client_type_id = '3';

// make a note of the directory that will recieve the uploaded files
$stc_uploads_directory = $_SERVER['DOCUMENT_ROOT'] .  '/trip_images/';
$stc_image_valid_file_formats = array( 'jpg', 'JPG', 'jpeg', 'JPEG', 'gif', 'GIF', 'png', 'PNG' );

// Set the default timezone for the client location
date_default_timezone_set('Canada/Eastern');

// Do we use the Kaiser historical data
$stc_use_historical_data = false;

// Encryption Section
$stc_enable_encryption = false;

$stc_initial_key = "Fuzzy Likes Dim Sum";
$stc_encrypt_prefix = "Fuzzy";

$stc_todays_key = generate_key( "Strongtower".$stc_host );

// Uncomment this to enable
$stc_scramble_on = true;

// This function scrambles names

function stc_scramble ( $cname ) {
	
	$cname = preg_replace('/US /','MARTIAN ',$cname);
	$cname = preg_replace('/FOOD/','ROCK',$cname);
	$cname = preg_replace('/SYS/','BLOG',$cname);
	$cname = preg_replace('/HOUSE/','SHACK',$cname);
	$cname = preg_replace('/FARM/','PLANT',$cname);
	$cname = preg_replace('/TURKEY/','RODENT',$cname);
	$cname = preg_replace('/CEDAR/','PINE',$cname);
	$cname = preg_replace('/NEST/','DIG',$cname);
	$cname = preg_replace('/TRUCKING/','GOATING',$cname);
	$cname = preg_replace('/TRUCK/','CAMMEL',$cname);
	$cname = preg_replace('/LINES/','ROPES',$cname);
	$cname = preg_replace('/SUPPLY/','OASIS',$cname);
	$cname = preg_replace('/EGGS/','FIGS',$cname);
	$cname = preg_replace('/CREAMERY/','FUDGERY',$cname);
	$cname = preg_replace('/EXPRESS/','FAST',$cname);
	$cname = preg_replace('/COFFEE/','GOAT',$cname);
	$cname = preg_replace('/FACTORY/','TENT',$cname);

	$cname = preg_replace('/AUTO/','CAMMEL',$cname);
	$cname = preg_replace('/SALVAGE/','GROOMING',$cname);
	$cname = preg_replace('/DELIVERY/','VITAMINS',$cname);
	$cname = preg_replace('/LOGISTICS/','DATES',$cname);
	$cname = preg_replace('/TRANSPORTATION/','GOATS',$cname);
	$cname = preg_replace('/ABSOLUTE/','TOTAL',$cname);
	$cname = preg_replace('/ACME/','ZEEMA',$cname);
	$cname = preg_replace('/INC/','FOUNDATION',$cname);
	$cname = preg_replace('/PARTS/','BITS',$cname);

	return $cname;
}

// This function replaces a missing PHONE record for a CLIENT record.

function fix_missing_phone( $CLIENTID, $debug ) {
	
	global $stc_database, $stc_schema;
	
	// Prepare Select
	$query_string = "SELECT count(*) AS NUM
		FROM PHONE
		WHERE PHONE.CLIENTID = '".$CLIENTID."'
		WITH UR";
	if( $debug ) echo "<p>using query_string = $query_string</p>";

	$response1 = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( $debug )  {
		echo "<pre>";
		var_dump($responsec);
		echo "</pre>";
	}
	
	if( is_array($responsec) && ! isset($responsec[0]['NUM']) && $responsec[0]['NUM'] == 0 ) {


		if( $debug ) echo "<p>fix_missing_phone $CLIENTID</p>";
		
		// Prepare Select
		$query_string = "select CLIENT_ID, NAME, 
			ADDRESS_1, ADDRESS_2, CITY, PROVINCE, POSTAL_CODE, BUSINESS_PHONE,
			BUSINESS_PHONE_EXT,
			(SELECT PHONEID FROM PHONE
				WHERE PHONE.CLIENTID = CLIENT_ID)
			
			FROM CLIENT
			WHERE CLIENT_ID = '".$CLIENTID."'
			WITH UR";
		if( $debug ) echo "<p>using query_string = $query_string</p>";
	
		$response1 = send_odbc_query( $query_string, $stc_database, $debug );
		
		if( $debug )  {
			echo "<pre>";
			var_dump($response1);
			echo "</pre>";
		}
		
		if( is_array($response1) && ! isset($response1[0]['PHONEID']) ) {
			if( $debug ) echo "<p>Got data from CLIENT table.</p>";
	
			// Get PHONE ID
			$query_string = "CALL ".$stc_schema.".CUSTOM_GEN_ID('GEN_PHONE_ID')";
			 
			if( $debug ) echo "<p>using query_string = $query_string</p>";
			
			$response2 = send_odbc_query( $query_string, $stc_database, $debug );
			
			if( $debug ) echo "<p>response2 ".gettype($response2)."</p>";
	
			if( $response2 ) {
				$nextid = $response2[0]["NEXTID"];
				if( $debug ) echo "<p>NEXTID is $nextid</p>";
	
				// Create PHONE record
				$query_string = "INSERT INTO PHONE (PHONEID, COMPANYNAME, CATEGORY, 
					CLIENTID, ADDRESS_1, ADDRESS_2, CITY, 
					PROVINCE, POSTAL_CODE, MASTERPHONENUMBER, BUSINESS_PHONE_EXT) 
					VALUES (".$nextid.",'".$response1[0]["NAME"]."','CLIENT',
					'".$CLIENTID."','".$response1[0]["ADDRESS_1"]."','".$response1[0]["ADDRESS_2"]."','".$response1[0]["CITY"]."',
					'".$response1[0]["PROVINCE"]."','".$response1[0]["POSTAL_CODE"]."','".$response1[0]["BUSINESS_PHONE"]."',
					'".$response1[0]["BUSINESS_PHONE_EXT"]."')";
					 
				if( $debug ) echo "<p>using query_string = $query_string</p>";
				
				$response3 = send_odbc_query( $query_string, $stc_database, $debug );
				
				if( $debug ) echo "<p>response3 ".gettype($response3)."</p>";
	
				if( $debug )  {
					echo "<pre>";
					var_dump($response3);
					echo "</pre>";
				}
				if( is_array($response3) ) {
					if( $debug ) echo "<p>ADDED</p>";
					return true;
	
				} else {
					if( $debug ) echo "<p>Error - send_odbc_query 3 failed. $last_odbc_error</p>";
					else echo "NOT OK: send_odbc_query failed: " . $last_odbc_error;
					return false;
				}
			}
		}
	}
	
	return false;			
}

function fix_duplicate_phone( $dbconn, $CLIENTID, $debug ) {
	
	if( $debug ) echo "<p>fix_duplicate_phone $CLIENTID</p>";

	// Prepare Select
	$query_string = "SELECT count(*) AS NUM
		FROM PHONE
		WHERE PHONE.CLIENTID = '".$CLIENTID."'
		WITH UR";
	
	$response1 = false; //$dbconn->get_multiple_rows( $query_string );
	
	if( is_array($response1) && ! isset($response1[0]['NUM']) && $response1[0]['NUM'] > 1 ) {
		if( $debug ) echo "<p>Need to remove duplicate.</p>";
		$query_string = "DELETE FROM PHONE
			WHERE PHONE.CLIENTID = '".$CLIENTID."'
			AND PHONEID = (SELECT MAX(PHONEID)
				FROM PHONE P
				WHERE PHONE.CLIENTID = P.CLIENTID)";
				
		$response2 = $dbconn->get_multiple_rows( $query_string );
	}
}	


function generate_key( $seed ) {
	return substr($seed.gmdate('Ymd'),0,32);
}

function base64_url_encode($input) {
 return strtr(base64_encode($input), '+/=', '-_.');
}

function base64_url_decode($input) {
 return base64_decode(strtr($input, '-_.', '+/='));
}


function encryptData($value, $key = ''){
	global $stc_todays_key, $stc_enable_encryption, $stc_encrypt_prefix;
	if( $stc_enable_encryption ) {
		$text = $stc_encrypt_prefix.$value;
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, ($key == '' ? $stc_todays_key : $key), $text, MCRYPT_MODE_ECB, $iv);
		return base64_url_encode($crypttext);
	} else {
		return $value;
	}
}

function decryptData($value, $key = ''){
	global $stc_todays_key, $stc_enable_encryption, $stc_encrypt_prefix;
	if( $stc_enable_encryption ) {
		$crypttext = base64_url_decode($value);
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, ($key == '' ? $stc_todays_key : $key), $crypttext, MCRYPT_MODE_ECB, $iv);
		if(substr($decrypttext, 0, strlen($stc_encrypt_prefix)) == $stc_encrypt_prefix )		
			return trim(substr($decrypttext,strlen($stc_encrypt_prefix)));
		else 
			return false;
	} else {
		return $value;
	}
}

function update_client_date( $uid, $CLIENTID, $debug  ) {
	global $stc_database;

	// Prepare Select
	$query_string = "UPDATE CLIENT
		SET MODIFIED_BY = '".$uid."',
		MODIFIED_DATE = '".date("Y-m-d H:i:s.000000")."'
		WHERE CLIENT_ID = '".$CLIENTID."'";
	
	if( $debug ) echo "<p>using query_string = $query_string</p>";
	
	$response = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( is_array($response) ) {
		if( $debug ) echo "<p>Updated client $CLIENTID.</p>";
		return true;
	} else {
		if( $debug ) echo "<p>Failed to update client $CLIENTID.</p>";
		return false;
	}

}

function update_due_date( $CLIENTID, $debug  ) {
	global $stc_database;

	// Prepare Select
	$query_string = "SELECT CAST(COALESCE(p.USERNUM1, 0) AS INTEGER) AS USERNUM1, 
		date(MAX(PHONEDATE)) LAST_NOTE
		FROM PHONE p, PHONENOTES n
		WHERE p.PHONEID = n.PHONEID
		and p.CLIENTID = '".$CLIENTID."'
		GROUP BY p.USERNUM1";
	
	if( $debug ) echo "<p>using query_string = $query_string</p>";
	
	$response = send_odbc_query( $query_string, $stc_database, $debug );
	
	if( is_array($response) && count($response) == 1 ) {
		if( $debug )  {
			echo "<pre>";
			var_dump($response);
			echo "</pre>";
		}

		if( isset($response[0]["USERNUM1"]) && $response[0]["USERNUM1"] > 0 ) {
			$days = $response[0]["USERNUM1"];
			$last_note = isset($response[0]["LAST_NOTE"]) ? $response[0]["LAST_NOTE"] : date('Y-m-d');
			
			if( strtotime($last_note) < strtotime(date('Y-m-d')) )
				$last_note = date('Y-m-d');
			
			$new_date = date('Y-m-d', strtotime($last_note. ' + '.$days.' days'));
			if( $debug ) echo "<p>new date = $new_date</p>";

			$query_string = "UPDATE PHONE
				SET USERDATE1 = DATE('".$new_date."')
				WHERE CLIENTID = '".$CLIENTID."'";
			
			if( $debug ) echo "<p>using query_string = $query_string</p>";
			
			$response2 = send_odbc_query( $query_string, $stc_database, $debug );
			if( is_array($response2) ) {
				if( $debug ) echo "<p>REMEMBERED</p>";
				else return true;
			} else {
				if( $debug ) echo "<p>Error - send_odbc_query 2 failed. $last_odbc_error</p>";
				else return false;
			}

		}
		else return true;	// If USERNUM1 is NULL or zero we don't repeat
		
	} else {
		if( $debug ) echo "<p>Failed to update client $CLIENTID. $last_odbc_error</p>";
		return false;
	}

}

function error_response( $type, $parameter, $debug ) {
	$response = array();
	$response['OUTCOME'] = 'ERROR';
	$response['TYPE'] = $type;
	$response['PARAMETER'] = $parameter;
	
	if( $debug ) {
		return "<pre>" . var_export($response, true) . "</pre>";
	} else {
		return encryptData(json_encode( $response ));
	}
}

function other_response( $opt, $outcome, $result, $dbconn, $timer, $debug ) {
	global $stc_profiling;
	
	$response = array();
	$response['OUTCOME'] = $outcome;
	$response['RESULT'] = $result;
	if( $stc_profiling && $dbconn && $timer ) {
		list($connect, $query) = $dbconn->timer_results();
		$overall = $timer->split();
		$response['TIMING'] = array( 'OPT' => $opt, 'CONNECT' => $connect, 'QUERY' => $query, 'OVERALL' => $overall);
	}
	
	if( $debug ) {
		return "<pre>" . var_export($response, true) . "</pre>";
	} else {
		return encryptData(json_encode( $response ));
	}
}

function ok_response( $opt, $result, $dbconn, $timer, $debug ) {
	return other_response( $opt, 'OK', $result, $dbconn, $timer, $debug );
}



?>