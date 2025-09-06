<?php
session_start();

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./stc_config.php" );
require_once( "./stc_functions.php" );
require_once( "./process.php" );
ini_set('default_socket_timeout',    120);  
//print_r(ini_get_all());

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta content="yes" name="apple-mobile-web-app-capable" />
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />
<link href="css/style.css" rel="stylesheet" media="screen" type="text/css" />
<script src="javascript/functions.js" type="text/javascript"></script>

<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="apple-touch-icon" href="images/kaiser_home01.gif"/>
<title>Trace Client Loads<?php echo (isset($_SESSION['NAME']) ? " - ".$_SESSION['NAME'] : ""); ?></title>
<style type="text/css">
.TitleBlack {
	color: #000;
	font-family: Verdana, Geneva, sans-serif;
	font-size: 32px;
}
</style>
<script type="text/javascript">
<!--
// This function is used to change lower to upper case for the Input text
function cUpper(cObj)
{
cObj.value=cObj.value.toUpperCase();
}

// This displays a confirmation before going to another page
function confirmation(message, url) {
	var answer = confirm(message)
	if (answer){
		window.location = url;
	}
}
//-->
</script>

</head>

<body>

<div id="topbar">
	<div id="title">Trace Client Loads</div>
	<?php if( isset($_SESSION['CLIENT_ID']) && ! isset($_GET['logout']) ) { ?>
	<div id="leftnav">
		<a href="iphone.php"><img alt="home" src="images/home.png" /></a>
	</div>
	<div id="rightnav">
		<a href="iphone.php?logout"><img alt="home" src="images/053-Lock-Icon.png" /></a>
	</div>
	<?php } else { ?>
	<div id="leftbutton">
		<a href="http://www.kaisertransport.com" class="noeffect">www.kaisertransport.com</a> 
	</div>
	<?php } ?>
</div>


<?php if( ! isset($_SESSION['CLIENT_ID']) || isset($_GET['logout']) ) { ?>
<p class="center">
<a href="<?php echo $stc_banner_link; ?>"><?php echo $stc_banner_logo; ?></a><p>
<?php } ?>

		<div id="content">
	<?php

$show_login = false;
if ( isset($_GET['logout']) || ! isset($_SESSION['CLIENT_ID']) || $_SESSION['CLIENT_ID'] == ''){
	?>
		<ul class="pageitem">
	<?php

	if( isset($_GET['logout']) ) {		

		echo "<span class=\"graytitle\">".$_SESSION['NAME'].", you are now logged out.</span>";
		unset( $_SESSION['CLIENT_ID'], $_SESSION['NAME'], $_SESSION['USER2'] );
		$show_login = true;
	} else if(! isset($_POST['login']) ) {
		echo "<span class=\"graytitle\">Please log in.</span>";
		$show_login = true;
	} else {
		//echo "<p>". $_POST['username']. " ". $_POST['password']."</p>";
		if ( empty($_POST['username']) || empty($_POST['password']) ) {
			echo "<span class=\"graytitle\">username and password can't be blank.</span>";
			$show_login = true;
		} elseif( $_POST['username'] == $stc_admin_client) {
			echo "<div id=\"st_loading\"><center><p><img src=\"images/loading.gif\" width=\"125\" height=\"125\" alt=\"loading...\" /></p></center></div>";
			if ( $_POST['password'] == $stc_admin_pw) {
				update_message2( "st_loading", "<span class=\"graytitle\">Success.</span>" );
				$_SESSION['CLIENT_ID'] = $stc_admin_client;
				$_SESSION['NAME'] = $stc_company_name;
				reload_page ( "iphone.php" );
			} else {
				update_message2( "st_loading", "<span class=\"graytitle\">Login Failed.</span>" );
				$show_login = true;
			}
		} else {
			$url = $stc_remote_auth."&uid=".
				encryptData($_POST['username']).
				"&upw=".encryptData($_POST['password']);
			echo "<div id=\"st_loading\"><center><p><img src=\"images/loading.gif\" width=\"125\" height=\"125\" alt=\"loading...\" /></p></center></div>";
			
			$data = fetch_data( $url, "Trace Client Loads - login" );
			if( $data) {
				$info = json_decode($data, true);
				//echo "<p>info = <pre>".var_dump($info)."</pre></p>";
		
				if( isset($info[0]) && isset($info[0]{"CLIENT_ID"}) ) {
					update_message2( "st_loading", "<p>Success.</p>" );
					$_SESSION['CLIENT_ID'] = $info[0]{"CLIENT_ID"};
					$_SESSION['NAME'] = $info[0]{"NAME"};
					if( isset($info[0]{"USER2"}) && $info[0]{"USER2"} <> "" )
						$_SESSION['USER2'] = $info[0]{"USER2"};
					reload_page ( "iphone.php" );
				} else {
					update_message2( "st_loading", "<span class=\"graytitle\">Login Failed.</span>" );
					$show_login = true;
				}
			} else {
				update_message2( "st_loading", "<span class=\"graytitle\">Login Failed.</span>" );
				$show_login = true;
			}
		}

	}
	if( $show_login ) {
		?>
	</ul>
		<form action="iphone.php" method="post" enctype="multipart/form-data" name="form1" target="_top" id="form1">
		<fieldset>
		<ul class="pageitem">
			<li class="bigfield"><input placeholder="Username" name="username" type="text" id="username" maxlength="10" OnKeyup="return cUpper(this);" /></li>
			<li class="bigfield">
			<input placeholder="Password" name="password" type="password" id="password" maxlength="20" /></li>
			<li class="button">
			<input type="submit" name="login" id="login" value="Submit" /></li>
		</ul>
		</fieldset></form>

	<ul class="pageitem">
		<li class="textbox"><p><?php echo $stc_terms_of_use; ?></p>
		<p>&nbsp;</p>
	<p>For further information <a href="<?php echo $stc_contact_us_link; ?>">click here to contact us</a>.</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
	<p class="smallText">Developed by the cool guys at <a href="http://www.strongtoweronline.com/" target="_blank">Strong Tower Consulting</a></p>
</li>
	</ul>

<?php
	}
} else {
	$stc_client = $_SESSION['CLIENT_ID'];		// Default client to show
?>
<span class="graytitle">Welcome <?php echo $_SESSION['NAME']; ?></span>
<?php
	if( $stc_client == $stc_admin_client ) {
?>
<form id="form3" name="form3" method="get" action="iphone.php">
<fieldset>
<ul class="pageitem">
		<li class="bigfield"><input placeholder="PRO# numbers (comma separated)" name="fb" type="text" id="fb" size="20" maxlength="80" /></li>
		<li class="button"><input type="submit" name="Pro" id="Pro" value="Search" /></li>
</ul>
</fieldset></form>
<?php
	} else {
?>
<form id="form2" name="form2" method="get" action="iphone.php">
<fieldset>
<ul class="pageitem">
		<li class="bigfield"><input placeholder="trace/BOL numbers (comma separated)" name="trace" type="text" id="trace" size="20" maxlength="80" /></li>
		<li class="button"><input type="submit" name="Trace" id="Trace" value="Search" /></li>
</ul>
</fieldset></form>
<?php
	}
	
	if ( $stc_client <> $stc_admin_client && ! isset($_GET['trace']) ) {		// Show Trace for client
		echo "<div id=\"st_loading2\"><center><p><img src=\"images/loading.gif\" width=\"125\" height=\"125\" alt=\"loading...\" /><br>loading data from remote server...</p></center></div>";

		$url = $stc_remote_list."&client=".urlencode($stc_client).
		"&ttype=".$stc_billing_trace_type;
		
		$data = fetch_data( $url, "Trace Client Loads - list" );
		if( $data ) {
			$info = json_decode($data, true);
			//echo "<pre>";
			//var_dump($info);
			//echo "</pre>";
			$list = '<p><span class="graytitle">Loads ('.count($info).'):</span></p>';
			$list .= '<div class="list"><div id="content"><ul>';
			foreach( $info as $fb ) {
				$list .= '<li>';
				$list .= '<a class="noeffect" href="iphone.php?trace=' . $fb["TRACE_NUMBER"] . '">' . $fb["TRACE_NUMBER"]. ' ('.$fb["STATUS"] .')</a></li>';
				//$list .= "<td>".date("Y-m-d h:i A", strtotime($fb["PICK_UP_BY"]))."</td>";
				//$list .= "<td>".$fb["ORIGNAME"]."</td>";
				//$list .= "<td>".$fb["DESTNAME"]."</td>";
				//$list .= "<td>".$fb["STATUS"]."</td>";
				//$list .= "</tr>";

			}
			$list .= "</ul></div></div>";
			update_message2( "st_loading2", "" );
			echo $list;
			
		} else {
			update_message2( "st_loading2", "" );
			//echo $err;
		}
	} elseif( isset($_GET['trace']) || 										// show detail for trace
		(isset($_GET['fb']) && $stc_client == $stc_admin_client) ) {		// Show detail for FB
		if ( isset($_GET['trace']) && empty($_GET['trace']) ) {
			echo "<p>Trace can't be blank.</p>";
		} elseif ( isset($_GET['fb']) && empty($_GET['fb']) ) {
			echo "<p>Pro# can't be blank.</p>";
		} else {
		echo "<div id=\"st_loading3\"><center><p><img src=\"images/loading.gif\" width=\"125\" height=\"125\" alt=\"loading...\" /><br>loading data from remote server...</p></center></div>";
			if( isset($_GET['trace']) ) {
				$trace_str = str_replace (" ", "", $_GET['trace']);
				$url = $stc_remote_gett."&trace=".
					urlencode($trace_str)."&client=".urlencode($stc_client);			
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
					$gett .= '<ul class="pageitem"><li class="textbox"><p><strong>Error:</strong> no results found.</p></li></ul>';
				} else {
					foreach ( $info1 as $info ) {
						//echo "<pre>";
						//var_dump($info);
						//echo "</pre>";
						
						$url2 = "iphone.php?client=".urlencode($info[0]["BILL_TO_CODE"]);
						if ( $stc_client <> $stc_admin_client )
							$gett .= '<span class="graytitle">Trace '.$info[0]["TRACE_NO"].
								" (".count($info)." freight bills)".
								" <a href=\"iphone.php\">View all</a></span>";
		
						//$gett .=  '<ul class="pageitem">';
						
						
						foreach( $info as $fb) {
						
						$gett .=  '<ul class="pageitem">
						<li class="textbox"><span class="label">BILL_NUMBER</span>'.$fb["BILL_NUMBER"].'</li>
						<li class="textbox"><span class="label">NO_STOPS</span>'.$fb["NO_STOPS"].'</li>
						<li class="textbox"><span class="label">CURRENT_STATUS</span>'.$fb["STATUS_DESC"].'</li>
						<li class="textbox"><span class="label">START_ZONE</span>'.$fb["START_ZDESC"].'</li>
						<li class="textbox"><span class="label">END_ZONE</span>'.$fb["END_ZDESC"].'</li>
						<li class="textbox"><span class="label">CURRENT_ZONE</span>'.$fb["CURRENT_ZDESC"].'</li>
					</ul>';
						$gett .=  '<ul class="pageitem">
						<li class="textbox"><span class="label">PICK_UP_BY</span>'.
						(isset($fb["PICK_UP_BY"]) ? date("Y-m-d h:i A", strtotime($fb["PICK_UP_BY"])) : "").'</li>
						<li class="textbox"><span class="label">DELIVER_BY</span>'.
						(isset($fb["DELIVER_BY"]) ? date("Y-m-d h:i A", strtotime($fb["DELIVER_BY"])) : "").'</li>
						<li class="textbox"><span class="label">ACTUAL_PICKUP</span>'.
						(isset($fb["ACTUAL_PICKUP"]) ? date("Y-m-d h:i A", strtotime($fb["ACTUAL_PICKUP"])) : "").'</li>
						<li class="textbox"><span class="label">ORIGNAME</span>'.$fb["ORIGNAME"].'</li>
						<li class="textbox"><span class="label">DESTNAME</span>'.$fb["DESTNAME"].'</li>
						<li class="textbox"><span class="label">BILL_TO_NAME</span>'.$fb["BILL_TO_NAME"].'</li>
					</ul>';
						$gett .=  '<ul class="pageitem">
						<li class="textbox"><span class="label">PICK_UP_PUNIT</span>'.$fb["PICK_UP_PUNIT"].'</li>
						<li class="textbox"><span class="label">PICK_UP_TRAILER</span>'.$fb["PICK_UP_TRAILER"].'</li>
						<li class="textbox"><span class="label">PICK_UP_DRIVER</span>'.$fb["PICK_UP_DRIVER"].'</li>
					</ul>';

				if( isset( $fb["TRACES"] ) ) {
					$gett .=  '<ul class="pageitem">';
					foreach( $fb["TRACES"] as $tr ) {
						$gett .= '<li class="textbox"><span class="label">'.$tr["DESC"].' ('.$tr["TRACE_TYPE"].')</span>'.$tr["TRACE_NUMBER"].'</li>';
					}
					$gett .= "</ul>";
				}
				
				$gett .=  '<br /><span class="graytitle">History</span><ul class="pageitem1">';
						
						foreach( $fb["HISTORY"] as $hist ) {
							if( ! in_array($hist["STATUS_CODE"], array("APPRVD", "FB PRINTED", "PRINTED")) ) {
								$gett .= '<ul class="pageitem">';
								$gett .= '<li class="textbox"><span class="label">'.date("Y-m-d h:i A", strtotime($hist["CHANGED"])).'</span>';
								$gett .= ($hist["STATUS_DESC"] <> "" ? $hist["STATUS_DESC"] : $hist["STATUS_CODE"]).'</li>';
								$gett .= '<li class="textbox">'.str_replace(array("Carrier","carrier"), array("Driver","driver"), $hist["COMM"]).'</li>';
								$gett .= '<li class="textbox">'.($hist["ZID"] <> "" ? $hist["ZDESC"] : "").'</li>';
								$gett .= "</ul>";
							}
						} // history
						$gett .= "</ul>";
						
						
						//echo "<pre>";
						//var_dump($info);
						//echo "</pre>";
			
					} // info
				}
			}
			update_message2( "st_loading3", "" );
			echo $gett;
			
		} else {
			update_message2( "st_loading3", "" );
			//echo $err;
		}
		}
	}
	if( $stc_client <> $stc_admin_client ) {
		echo '<ul class="pageitem"><li class="textbox">';
		//echo "<p>Customer Service Issues (Pertaining to Appointments) please email: <a href=\"mailto:cs@hutt.com\">cs@hutt.com</a></p><br>";
		//echo "<p>Operations Issues (Pertaining to Capacity or Delivery) please email: <a href=\"mailto:dispatch@hutt.com\">dispatch@hutt.com</a></p><br>";
		if( isset($_SESSION['USER2']) )
			echo "<p>Load tenders (Sending or Revisions) please email: <a href=\"mailto:".$_SESSION['USER2']."\">".$_SESSION['USER2']."</a></p>";
		echo '</li></ul>';
	}

?>
</td>
	</tr>
</table>

<?php } ?>
</body>
</html>