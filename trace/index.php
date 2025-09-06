<?php
session_start();

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./stc_config.php" );
require_once( "./stc_functions.php" );
require_once( "./process.php" );

//$_GET['debug'] = "yes";

function do_filter( $name, $list ) {
	global $_SESSION;
	
	if( $_SESSION[$name] != 'All' && is_array($list) && count($list) == 0 )
		$list[] = $_SESSION[$name];
	
	$code = '<select name="'.$name.'" class="input-medium" onchange="form.submit();" >
		<option value="All"';
	if( $_SESSION[$name] == 'All' )
		$code .= " selected";
	$code .= '>All</option>
	';
	if( is_array($list) && count($list) > 0 ) {
		foreach( $list as $choice) {
			$code .= '<option value="'.$choice.'"';
			if( $_SESSION[$name] == $choice )
				$code .= " selected";
			$code .= ' >'.$choice.'</option>
			';
		}
	}
	$code .= "</select>
	";
	
	return $code;
}

function dt( $x ) {
	return isset($x) ? str_replace(' ', '&nbsp;', date("m/d/Y H:m", strtotime($x))):'';
}


if( isset($_POST['logout']) ) {
	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
}
		
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link type="text/css" href="http://code.jquery.com/ui/1.10.2/themes/redmond/jquery-ui.min.css" rel="stylesheet">

<!-- DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.25/datatables.min.css"/>

<!-- Bootstrap -->
<link href="bootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
<link href="assets/css/bootstrap-responsive.css" rel="stylesheet">
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<title>Trace Client Loads<?php echo (isset($_SESSION['NAME']) ? " - ".$_SESSION['NAME'] : ""); ?></title>
<style type="text/css">
.TitleBlack {
	color: #000;
	font-family: Verdana, Geneva, sans-serif;
	font-size: 32px;
}
</style>
<link href="additional.css" rel="stylesheet" type="text/css" />
<script src="http://code.jquery.com/jquery.js"></script>
<script src="http://code.jquery.com/ui/1.10.2/jquery-ui.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.25/datatables.min.js"></script>

</head>

<body>
<div class="container-fluid">
<p class="text-center"><br><a href="<?php echo $stc_banner_link; ?>"><?php echo $stc_banner_logo; ?></a>
<p>
	<?php
//echo "<p>client=".$_SESSION['CLIENT_ID']."</p>";
//ini_set('default_socket_timeout',    20);  
//echo "<pre>";
//print_r($_POST);
//print_r($_GET);
//echo "</pre>";

$show_login = false;
if ( isset($_POST['logout']) || ! isset($_SESSION['CLIENT_ID']) || $_SESSION['CLIENT_ID'] == ''){
	?>
	<?php
	if( isset($_POST['logout']) ) {		

		$msg = "<p>".$_SESSION['NAME'].", you are now logged out.</p>";
		// Unset all of the session variables.
		$_SESSION = array();
		
		// Finally, destroy the session.
		session_destroy();

		$show_login = true;
	} else if(! isset($_POST['login']) ) {
		$msg = "<p>Client Portal log in.</p>";
		$show_login = true;
	} else {
		//echo "<p>". $_POST['username']. " ". $_POST['password']."</p>";
		if ( empty($_POST['username']) || empty($_POST['password']) ) {
			$msg = "<p>username and password can't be blank.</p>";
			$show_login = true;
		} elseif( $_POST['username'] == $stc_admin_client) {
			echo "<div id=\"st_loading\"><center><p><img src=\"images/loading.gif\" width=\"125\" height=\"125\" alt=\"loading...\" /></p></center></div>";
			if ( $_POST['password'] == $stc_admin_pw) {
				//update_message( "st_loading", "<p>Success.</p>" );
				$_SESSION['CLIENT_ID'] = $stc_admin_client;
				$_SESSION['NAME'] = $stc_company_name;
				reload_page ( "index.php" );
			} else {
				update_message( "st_loading", "" );
				$msg = "<p>Failed.</p>";
				$show_login = true;
			}
		} else {
			fetch_todays_key();
			$url = $stc_remote_auth."&uid=".
				encryptData($_POST['username']).
				"&upw=".encryptData($_POST['password']);
			echo "<div id=\"st_loading\"><center><p><img src=\"images/loading.gif\" width=\"125\" height=\"125\" alt=\"loading...\" /></p></center></div>";
			
			//echo "<p>key = ".$_SESSION['TODAYS_KEY2']."<br>url = $url</p>";
			$data = fetch_data( $url, "Trace Client Loads - login" );
			if( $data) {
				$info = json_decode($data, true);
				//echo "<p>info = <pre>".var_dump($info)."</pre></p>";
		
				if( isset($info[0]) && isset($info[0]["CLIENT_ID"]) ) {
					//update_message( "st_loading", "<p>Success.</p>" );
					$_SESSION['CLIENT_ID'] = $info[0]["CLIENT_ID"];
					$_SESSION['NAME'] = $info[0]["NAME"];
					if( $stc_enable_groups && isset($info[0]["CUSTOMER_GROUP"]) && $info[0]["CUSTOMER_GROUP"] <> "" )
						$_SESSION['CUSTOMER_GROUP'] = $info[0]["CUSTOMER_GROUP"];
					if( isset($info[0]["USER2"]) && $info[0]["USER2"] <> "" )
						$_SESSION['USER2'] = $info[0]["USER2"];
					reload_page ( "index.php" );
				} else {
					update_message( "st_loading", "" );
					$msg = "<p>Failed.</p>";
					$show_login = true;
				}
			} else {
				update_message( "st_loading", "<p>Failed. No response from server.</p>" );
				$show_login = true;
			}
		}

	}
	if( $show_login ) {
		?>
<div class="container">
<div class="row">
	<div class="span6">
		<form class="form-signin" action="index.php" method="post"
			enctype="multipart/form-data" name="form1" target="_top" id="form1">
			<h3 class="form-signin-heading"><?php echo $msg; ?></h3>
			<input name="username" type="text" id="username"
				maxlength="32" OnKeyup="return cUpper(this);" class="input-block-level" placeholder="Username">
			<input name="password" type="password" id="password"
				maxlength="32" class="input-block-level" placeholder="Password">
			<button class="btn btn-large btn-primary" name="login"
				id="login" type="submit">Sign in</button>
		</form>
	</div>
	<div class="span6">

		<form class="form-signin" action="trace.php" method="post"
			enctype="multipart/form-data" name="form2" target="_top" id="form2">
			<h3 class="form-signin-heading">OR Enter Trace Number</h3>
			<input name="trace" type="text" id="trace" maxlength="32"
				class="input-block-level" placeholder="trace">
			<br>&nbsp;
		
			<button class="btn btn-large btn-primary" name="track" id="track" type="submit">Trace</button>
		<h4>&nbsp;</h4>

</form>
	</div>
</div>
</div>


<p>&nbsp;</p>
<table border="0" width="70%" align="center">
<tr>
	<td><p><?php echo $stc_terms_of_use; ?></p>
		<p>For further information <a href="<?php echo $stc_contact_us_link; ?>">click here to contact us</a>.</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p class="smallText">Developed for <a href="<?php echo $stc_banner_link; ?>"><?php echo $stc_company_name; ?></a> by the cool guys at <a href="http://www.strongtoweronline.com/" target="_blank">Strong Tower Consulting</a></p>
</div>
	<?php
	}
} else {
	$stc_client = $_SESSION['CLIENT_ID'];		// Default client to show
?>
	<h4>Welcome <?php echo $_SESSION['NAME']; 
		if( isset($_SESSION['CUSTOMER_GROUP']) ) echo " (in group ".$_SESSION['CUSTOMER_GROUP'].")"; ?></h4>
	<div class="boxed">
	<form class="form-horizontal" id="form1" name="form1" method="post" action="index.php">
			<div class="btn-toolbar" >
			<div class="btn-group">
			<button class="btn btn-small btn-inverse" type="submit" name="logout" id="logout"><i class="icon-lock icon-white"></i> Logout</button>
		</div>
			<!-- /btn-group -->
			
			<div class="btn-group">
			<button class="btn btn-small btn-inverse" type="submit" name="home" id="home"><i class="icon-home icon-white"></i> Home</button>
		</div>
			<!-- /btn-group -->
			
			<?php if( in_array( $stc_client, $stc_show_billing ) ) { ?>
			<div class="btn-group">
			<button class="btn btn-small btn-inverse" type="submit" name="billing" id="billing" onClick="document.form1.action ='billing.php'"><i class="icon-home icon-white"></i> Billing</button>
		</div>
			<!-- /btn-group -->
			<?php } ?>
			</div>
		</form>
	<?php

	//echo "<pre>";
	//var_dump($_GET);
	//echo "</pre>";

	$origname = [];
	$origprov = [];
	$destname = [];
	$destprov = [];
	$status = [];
	
	if( isset($_GET['RESET']) ) {
		$_SESSION['FILTER_ORIGNAME'] = $_SESSION['FILTER_ORIGPROV'] =
			$_SESSION['FILTER_DESTNAME'] = $_SESSION['FILTER_DESTPROV'] =
			$_SESSION['FILTER_STATUS'] = $_SESSION['FILTER_FROM'] =
			$_SESSION['FILTER_TO'] = 'All';
	}

	//! Pre-load list data		
	if ( ! isset($_GET['fb']) ) {		// Show Trace for client
		unset( $_GET['fb'], $_GET['trace']);

		if( ! isset($_SESSION['FILTER_ORIGNAME']) )
			$_SESSION['FILTER_ORIGNAME'] = 'All';
		
		if( isset( $_GET['FILTER_ORIGNAME']) )
			$_SESSION['FILTER_ORIGNAME'] =  $_GET['FILTER_ORIGNAME'];

		if( ! isset($_SESSION['FILTER_ORIGPROV']) )
			$_SESSION['FILTER_ORIGPROV'] = 'All';
		
		if( isset( $_GET['FILTER_ORIGPROV']) )
			$_SESSION['FILTER_ORIGPROV'] =  $_GET['FILTER_ORIGPROV'];

		if( ! isset($_SESSION['FILTER_DESTNAME']) )
			$_SESSION['FILTER_DESTNAME'] = 'All';
		
		if( isset( $_GET['FILTER_DESTNAME']) )
			$_SESSION['FILTER_DESTNAME'] =  $_GET['FILTER_DESTNAME'];

		if( ! isset($_SESSION['FILTER_DESTPROV']) )
			$_SESSION['FILTER_DESTPROV'] = 'All';
		
		if( isset( $_GET['FILTER_DESTPROV']) )
			$_SESSION['FILTER_DESTPROV'] =  $_GET['FILTER_DESTPROV'];

		if( ! isset($_SESSION['FILTER_STATUS']) )
			$_SESSION['FILTER_STATUS'] = 'All';
		
		if( isset( $_GET['FILTER_STATUS']) )
			$_SESSION['FILTER_STATUS'] =  $_GET['FILTER_STATUS'];

		if( ! isset($_SESSION['FILTER_FROM']) )
			$_SESSION['FILTER_FROM'] = 'All';
		
		if( isset( $_GET['FILTER_FROM']) )
			$_SESSION['FILTER_FROM'] =  $_GET['FILTER_FROM'];

		if( ! isset($_SESSION['FILTER_TO']) )
			$_SESSION['FILTER_TO'] = 'All';
		
		if( isset( $_GET['FILTER_TO']) )
			$_SESSION['FILTER_TO'] =  $_GET['FILTER_TO'];



		echo "<div id=\"st_loading2\"><center><p><img src=\"images/loading.gif\" width=\"125\" height=\"125\" alt=\"loading...\" /><br>loading data from remote server...</p></center></div>";

		$url = $stc_remote_list.( isset($_SESSION['CUSTOMER_GROUP']) ? 
			"&group=".urlencode($_SESSION['CUSTOMER_GROUP']) :
			"&client=".urlencode($stc_client)).
			(isset($_SESSION['FILTER_ORIGNAME']) && $_SESSION['FILTER_ORIGNAME'] != 'All' ?
				"&ON=".urlencode($_SESSION['FILTER_ORIGNAME']) : '').
			(isset($_SESSION['FILTER_ORIGPROV']) && $_SESSION['FILTER_ORIGPROV'] != 'All' ?
				"&OP=".urlencode($_SESSION['FILTER_ORIGPROV']) : '').
			(isset($_SESSION['FILTER_DESTNAME']) && $_SESSION['FILTER_DESTNAME'] != 'All' ?
				"&DN=".urlencode($_SESSION['FILTER_DESTNAME']) : '').
			(isset($_SESSION['FILTER_DESTPROV']) && $_SESSION['FILTER_DESTPROV'] != 'All' ?
				"&DP=".urlencode($_SESSION['FILTER_DESTPROV']) : '').
			(isset($_SESSION['FILTER_STATUS']) && $_SESSION['FILTER_STATUS'] != 'All' ?
				"&ST=".urlencode($_SESSION['FILTER_STATUS']) : '').

			(isset($_SESSION['FILTER_FROM']) && $_SESSION['FILTER_FROM'] != 'All' ?
				"&dt1=".urlencode($_SESSION['FILTER_FROM']) : '').
			(isset($_SESSION['FILTER_TO']) && $_SESSION['FILTER_TO'] != 'All' ?
				"&dt2=".urlencode($_SESSION['FILTER_TO']) : '').
			"&ttype=".$stc_billing_trace_type."&show=all";
		
		$list_data = fetch_data( $url, "Trace Client Loads - list" );
		if( $list_data ) {
			$list_info = json_decode($list_data, true);
						
			//! Gather filters
			if( is_array($list_info) && count($list_info) > 0 ) {
				foreach( $list_info as $row ) {
					if( !in_array($row['ORIGNAME'], $origname) )
						$origname[] = $row['ORIGNAME'];
					if( !in_array($row['ORIGPROV'], $origprov) )
						$origprov[] = $row['ORIGPROV'];
					if( !in_array($row['DESTNAME'], $destname) )
						$destname[] = $row['DESTNAME'];
					if( !in_array($row['DESTPROV'], $destprov) )
						$destprov[] = $row['DESTPROV'];
					if( !in_array($row['MYSTATUS'], $status) )
						$status[] = $row['MYSTATUS'];
				}
				sort($origname);
				sort($origprov);
				sort($destname);
				sort($destprov);
				sort($status);
				
			}
		/*	echo "<pre>";
			var_dump($origname, $origprov, $destname, $destprov, $status);
			var_dump($_SESSION['FILTER_ORIGNAME'], $_SESSION['FILTER_ORIGPROV'], $_SESSION['FILTER_DESTNAME'], $_SESSION['FILTER_DESTPROV'], $_SESSION['FILTER_STATUS']);
			echo "</pre>";
		*/
			
		}
		update_message( "st_loading2", "" );
	}

	if( $stc_client == $stc_admin_client ) {
?>
	<form class="navbar-form" id="form3" name="form3" method="get" action="index.php">
			<span class="navbar-text">Enter freight bill numbers (comma separated)</span>
			<input name="fb" type="text" id="fb" size="50" maxlength="80" />
			<input type="submit" name="Pro" id="Pro" value="Search" />
		</form>
	<?php
	} else {
?>
	<form class="form-inline" id="form2" name="form2" method="get" action="index.php">
			<p>Enter <?php echo $stc_show_bills_by_fb ? "freight bill or PO" : "trace/BOL"; ?> numbers (comma separated)
			<input name="client" type="hidden" value="<?php echo $stc_client; ?>" />
			<input name="trace" type="text" id="trace" size="50" maxlength="80" />
			<input type="submit" name="Trace" id="Trace" value="Search" />
		</p>
<style>
td.bg-success {
  background-color: #66FF66 !important;
}
</style>
	<?php
	if ( empty($_GET['fb']) && empty($_GET['trace']) ) { //! Filter menus
		

		$code = '<p>';

		$code .= 'Origin '.do_filter( 'FILTER_ORIGNAME', $origname );

		$code .= ' State '.do_filter( 'FILTER_ORIGPROV', $origprov );

		$code .= ' Dest '.do_filter( 'FILTER_DESTNAME', $destname );

		$code .= ' State '.do_filter( 'FILTER_DESTPROV', $destprov );

		$code .= ' Status '.do_filter( 'FILTER_STATUS', $status );
		
		//! Date filters

		$url = $stc_remote_dates.( isset($_SESSION['CUSTOMER_GROUP']) ? 
			"&group=".urlencode($_SESSION['CUSTOMER_GROUP']) :
			"&client=".urlencode($stc_client));
		$dates_data = fetch_data( $url, "Trace Client Loads - dates" );
		
		if( $dates_data ) {
			$dates_info = json_decode($dates_data, true);
			
			$dates_list = [];
			foreach($dates_info as $row) {
				$dates_list[] = $row['PICK_UP_BY'];
			}
			
			if( is_array($dates_list) && count($dates_list) > 0 ) {
				$code .= '</p><p>PICK_UP_BY ';
				$code .= 'From: '.do_filter( 'FILTER_FROM', $dates_list );
				$code .= ' To: '.do_filter( 'FILTER_TO', $dates_list );
			}
		}


		
		$code .= ' <a class="btn btn-small btn-inverse" href="index.php?RESET" id="reset"><i class="icon-remove icon-white"></i> Reset</a></p>';

		echo $code;
	}
	?>		
	
		</form>
	<?php
	}
	
	if(	! empty($_GET['trace']) || 				// show detail for trace
				! empty($_GET['fb'])  ) {		// Show detail for FB
		if ( isset($_GET['trace']) && empty($_GET['trace']) ) {
			echo "<p>Trace can't be blank.</p>";
		} elseif ( isset($_GET['fb']) && empty($_GET['fb']) ) {
			echo "<p>Pro# can't be blank.</p>";
		} else {
		echo "<div id=\"st_loading3\"><center><p><img src=\"images/loading.gif\" width=\"125\" height=\"125\" alt=\"loading...\" /><br>loading data from remote server...</p></center></div>";
			if( isset($_GET['trace']) ) {
				if( isset($_GET['debug']) ) echo "<p>trace = \"".$_GET['trace']."\"</p>";
				$trace_str = $_GET['trace']; //str_replace (" ", "", $_GET['trace']);
				$url = $stc_remote_gett."&trace=".
					urlencode($trace_str)."&client=".urlencode($_GET['client']);			
			} elseif( isset($_GET['fb']) ) {
				$fb_str = str_replace (" ", "", $_GET['fb']);
				$url = $stc_remote_getfb."&fb=".
					urlencode($fb_str);			
			}

			$data = fetch_data( $url, "Trace Client Loads - gett or getfb" );
			if( $data ) {
				$info1 = json_decode($data, true);
				
				$gett = "";
				if (count($info1) == 0 ) {
					$gett .= "<p><strong>Error:</strong> no results found.</p>";
				} else {
					foreach ( $info1 as $info ) {
						//echo "<pre>";
						//var_dump($info);
						//echo "</pre>";
						
						$url2 = "index.php?client=".urlencode($info[0]["BILL_TO_CODE"]);
						if ( $stc_client <> $stc_admin_client )
							$gett .= "<h4>Trace ".$info[0]["TRACE_NO"].
								" (".count($info)." freight bills)".
								" <a href=\"index.php\">View all</a></h4>";
								
						
						foreach( $info as $fb) {
						
						$gett .=  '<div class="boxed2">
			<table class="table table-striped table-condensed table-bordered table-hover" >
				<tbody>
					<tr>
						<td><b>BILL_NUMBER</b></td>
						<td><b>'.$fb["BILL_NUMBER"].'</b></td>
						<td>NO_STOPS</td>
						<td>'.$fb["NO_STOPS"].'</td>
						<td>Status</td>
						<td>'.$fb["MYSTATUS"].'</td>
					</tr>
					<tr>
						<td>START_ZONE</td>
						<td>'.$fb["START_ZDESC"].'</td>
						<td>END_ZONE</td>
						<td>'.$fb["END_ZDESC"].'</td>
						<td>CURRENT_ZONE</td>
						<td'.($fb["END_ZDESC"] == $fb["CURRENT_ZDESC"] ? ' class="bg-success"' : '').'>'.$fb["CURRENT_ZDESC"].'</td>
					</tr>
					<tr>
						<td>PICK_UP_BY</td>
						<td>'.(isset($fb["PICK_UP_BY"]) ? date("Y-m-d h:i A", strtotime($fb["PICK_UP_BY"])) : "").'</td>
						<td>DELIVER_BY</td>
						<td>'.(isset($fb["DELIVER_BY"]) ? date("Y-m-d h:i A", strtotime($fb["DELIVER_BY"])) : "").'</td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td>ACTUAL_PICKUP</td>
						<td>'.(isset($fb["ACTUAL_PICKUP"]) ? date("Y-m-d h:i A", strtotime($fb["ACTUAL_PICKUP"])) : "").'</td>
						<td>ACTUAL_DELIVERY</td>
						<td>'.(isset($fb["ACTUAL_DELIVERY"]) ? date("Y-m-d h:i A", strtotime($fb["ACTUAL_DELIVERY"])) : "").'</td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td>ORIGNAME</td>
						<td>'.$fb["ORIGNAME"].'</td>
						<td>DESTNAME</td>
						<td>'.$fb["DESTNAME"].'</td>
						<td>BILL_TO_NAME</td>
						<td>'.$fb["BILL_TO_NAME"].'</td>
					</tr>
					';
					
				if( ! empty($fb["LAST_LOCATION_DESC"])) {
					$gett .=  '<tr>
						<td>LAST POSITION</td>
						<td colspan="5">'.$fb["LAST_LOCATION_DESC"].'</td>
					</tr>
					';
				}
					
				if( ! empty( $fb["CHILDREN"] ) && $fb["EXTRA_STOPS"] == 'True' ) {
					$kids = explode(', ', $fb["CHILDREN"]);
					if( is_array($kids) && count($kids) > 0 ) {
						$link = [];
						foreach( $kids as $kid ) {
							$link[] = '<a href="index.php?fb=' . urlencode($kid) .
							'&client=' . $fb["BILL_TO_CODE"] . '">'.$kid.'</a>';
						}
						$links = implode(' ', $link);
						
						$gett .=  '<tr>
							<td>CHILD BILLS</td>
							<td colspan="5">'.$links.'</td>
							</tr>
						';
					}
				} else if( $fb["MASTER_ORDER"] > 0 && $fb["MASTER_ORDER"] != $fb["DETAIL_LINE_ID"] ) {
					$gett .=  '<tr>
						<td>PARENT BILL</td>
						<td colspan="5">'.'<a href="index.php?fb=' . urlencode($fb["PARENT_BILL_NUMBER"]) .
							'&client=' . $fb["BILL_TO_CODE"] . '">'.$fb["PARENT_BILL_NUMBER"].'</a>'.'</td>
						</tr>
					';
				}
					
				$gett .=  '	
				<!--	<tr>
						<td>PICK_UP_PUNIT</td>
						<td>'.$fb["PICK_UP_PUNIT"].'</td>
						<td>PICK_UP_TRAILER</td>
						<td>'.$fb["PICK_UP_TRAILER"].'</td>
						<td>PICK_UP_DRIVER</td>
						<td>'.$fb["PICK_UP_DRIVER"].'</td>
					</tr>
					-->
				</table>
				<div class="row">
					    <div class="span4">';

				if( isset( $fb["TRACES"] ) ) {
					$gett .=  '		
					<table border="0" cellpadding="4" cellspacing="0"><tbody>';
					foreach( $fb["TRACES"] as $tr ) {
						$gett .= '<tr><td>'.$tr["DESC"].' ('.$tr["TRACE_TYPE"].')</td><td>&nbsp;&nbsp;</td><td align="right">'.$tr["TRACE_NUMBER"].'</td></tr>';
					}
					$gett .= "</tbody></table>";
				}
				
				$gett .=  '</div>
					    <div class="span4">';
				if( $stc_use_tmw_imaging )
					$gett .=	list_bol( $fb["BILL_NUMBER"] );
				$gett .= '</div>
					 </div>';				
				
				$gett .=  '<br />		
				<table class="table table-striped table-condensed table-bordered table-hover" >
				<thead>
				<tr>
					<th align="left">CHANGED</th>
					<th align="left">STATUS</th>
					<th align="left">COMMENTS</th>
					<th align="left">ZONE</th>
				</tr>
				</thead><tbody>';
						
						foreach( $fb["HISTORY"] as $hist ) {
							if( ! in_array($hist["STATUS_CODE"], array("APPRVD", "FB PRINTED", "PRINTED")) ) {
								$gett .= '<tr class="list">';
								$gett .= "<td width=\"150\">".date("Y-m-d h:i A", strtotime($hist["CHANGED"]))."</td>";
								$gett .= "<td>".($hist["STATUS_DESC"] <> "" ? $hist["STATUS_DESC"] : $hist["STATUS_CODE"])."</td>";
								$gett .= "<td>".str_replace(array("Carrier","carrier"), array("Driver","driver"), $hist["COMM"])."</td>";
								$gett .= "<td>".map_link($hist["POSLAT"], $hist["POSLONG"], $hist["ZDESC"])."</td>";
								$gett .= "</tr>";
							}
						} // history
						
						$gett .= "<tbody></table></div><br>";
						
						//echo "<pre>";
						//var_dump($info);
						//echo "</pre>";
			
					} // info

				}
			}
			update_message( "st_loading3", "" );
			echo $gett;
			
		} else {
			update_message( "st_loading3", "" );
			alert_server_down( $stc_ip_address, $stc_server_name, "Trace Client Loads - get" );
			echo $err;
		}
		}
	} else if ( empty($_GET['fb']) && empty($_GET['trace']) 
		&& $stc_client != $stc_admin_client ) {		//! Show Trace for client
	//	echo "<div id=\"st_loading2\"><center><p><img src=\"images/loading.gif\" width=\"125\" height=\"125\" alt=\"loading...\" /><br>loading data from remote server...</p></center></div>";

	//	$url = $stc_remote_list.( isset($_SESSION['CUSTOMER_GROUP']) ? 
	//		"&group=".urlencode($_SESSION['CUSTOMER_GROUP']) :
	//		"&client=".urlencode($stc_client))."&ttype=".$stc_billing_trace_type."&show=all";
		
	//	$data = fetch_data( $url, "Trace Client Loads - list" );
		if( isset($list_info) && is_array($list_info) ) {
			$info = $list_info;
			//echo "<pre>";
			//var_dump($info);
			//echo "</pre>";
			$list = "<h4 class=\"aboutus\">Loads (".count($info)."):</h4>";
			$list .= '<table class="table table-striped table-condensed table-bordered table-hover" id="LIST">
				<thead>
		<tr>
			<th align="left">'.$stc_label_company_load.'</th>
			<th align="left">Reference #</th>';
			if( isset($_SESSION['CUSTOMER_GROUP']) ) $list .= '<th align="left">CLIENT_ID</th>';
			$list .= '<th>Origin</th>
			<th>State</th>
			<th>Destination</th>
			<th>State</th>
			<th>Picked</th>
			<th>Delivered</th>
			<th >Status</th>
		</tr>
		</thead><tbody>';
			foreach( $info as $fb ) {
				$list .= '<tr class="list">';
				$list .= "<td><a href=\"index.php?fb=" . urlencode($fb["BILL_NUMBER"]) . "&client=" . $fb["BILL_TO_CODE"] . "\">" . $fb["BILL_NUMBER"] ."</a></td>";
			//	$list .= "<td><a href=\"index.php?trace=" . urlencode($fb["TRACE_NUMBER"]) . "&client=" . $fb["BILL_TO_CODE"] . "\">" . $fb["TRACE_NUMBER"] ."</a></td>";
			//	$list .= "<td>".$fb["BOL_NUMBER"]."</td>";
				$list .= "<td>".$fb["TRACE_NOS"]."</td>";
				if( isset($_SESSION['CUSTOMER_GROUP']) )
					$list .= "<td>".$fb["BILL_TO_CODE"]."</td>";
				$list .= "<td>".$fb["ORIGNAME"]."</td>";
				$list .= "<td>".$fb["ORIGPROV"]."</td>";
				$list .= "<td>".$fb["DESTNAME"]."</td>";
				$list .= "<td>".$fb["DESTPROV"]."</td>";
				if( ! empty($fb["ACTUAL_PICKUP"]))
					$list .= '<td class="bg-success">'.dt($fb["ACTUAL_PICKUP"]).'</td>';
				else
					$list .= '<td>'.dt($fb["PICK_UP_BY"]).'</td>';

				if( ! empty($fb["ACTUAL_DELIVERY"]))
					$list .= '<td class="bg-success">'.dt($fb["ACTUAL_DELIVERY"]).'</td>';
				else
					$list .= '<td>'.dt($fb["DELIVER_BY"]).'</td>';

				$list .= "<td>".$fb["MYSTATUS"]."</td>";
				$list .= "</tr>";

				/*
				$list .= "<p>Trace <a href=\"index.php?trace=" . $fb["TRACE_NUMBER"] . "&client=" . $stc_client . "\">" . $fb["TRACE_NUMBER"] .
				"</a> " . date("Y-m-d h:i A", strtotime($fb["PICK_UP_BY"])) .
				" -> " . $fb["DESTNAME"]  . " (" . $fb["STATUS"]. ")</p>"; // " fb=". $fb["BILL_NUMBER"] .
				*/
			}
			$list .= "</tbody></table></div>";
			update_message( "st_loading2", "" );
			echo $list;
?>
<script language="JavaScript" type="text/javascript"><!--
	//! -------------- JavaScript
	$(document).ready(function() {
		<?php if( ! isset( $_GET['debug']) ) { ?>
			document.documentElement.style.overflow = 'hidden';  // firefox, chrome
			document.body.scroll = "no"; // ie only

			$(window).bind('resize', function(e) {
				console.log('resize event triggered');
				if (window.RT) clearTimeout(window.RT);
				window.RT = setTimeout(function() {
					this.location.reload(false); /* false to get page from cache */
				}, 100);
			});

		<?php } ?>

		$('#LIST').DataTable({
			"lengthChange": false,
		//	"pageLength": 50,
			"searching": false,
			"ordering": true,
		//	"lengthMenu": [[50, 100, 250, -1], [50, 100, 250, "All"]],
		//	"order": [[ 10, "desc" ]],
			"paging": false,
		//	"pagingType": "full_numbers",
			"info": true,
			//"bAutoWidth": false,
			//"bProcessing": true,
			"scrollX": true,
			"scrollY": ($(window).height() - 460) + "px",
			"scrollXInner": "200%",
			"scrollCollapse": true,
			"orderClasses": true		
		});
	});
//--></script>
	
<?php			
		} else {
			update_message( "st_loading2", "" );
			//echo $err;
		}
	}
	//echo "<p>Customer Service Issues (Pertaining to Appointments) please email: <a href=\"mailto:cs@hutt.com\">cs@hutt.com</a></p>";
	//echo "<p>Operations Issues (Pertaining to Capacity or Delivery) please email: <a href=\"mailto:dispatch@hutt.com\">dispatch@hutt.com</a></p>";
	if( isset($_SESSION['USER2']) )
		echo "<p>Load tenders (Sending or Revisions) please email: <a href=\"mailto:".$_SESSION['USER2']."\">".$_SESSION['USER2']."</a></p>";

?>
</div>
	<?php } ?>
	<script src="bootstrap/js/bootstrap.min.js"></script> 
	<script src="bootstrap/js/bootstrap-popover.js"></script>
	
	
</body>
</html>