<?php
session_start();

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./stc_config.php" );
require_once( "./stc_functions.php" );
require_once( "./process.php" );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<title>Billing Report<?php echo (isset($_SESSION['NAME']) ? " - ".$_SESSION['NAME'] : ""); ?></title>
<style type="text/css">
.TitleBlack {
	color: #000;
	font-family: Verdana, Geneva, sans-serif;
	font-size: 32px;
}
</style>
<link href="mm_coastal.css" rel="stylesheet" type="text/css" />
<link type="text/css" href="css/redmond/jquery-ui-1.8.12.custom.css" rel="stylesheet">
<script type="text/javascript" src="js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.12.custom.min.js"></script>

</head>

<body>
<p class="iAgree">
<a href="<?php echo $stc_banner_link; ?>"><?php echo $stc_banner_logo; ?></a><p>

<?php

//echo "<p>client=".$_SESSION['CLIENT_ID']."</p>";

$show_login = false;
if ( isset($_POST['logout']) || ! isset($_SESSION['CLIENT_ID']) || $_SESSION['CLIENT_ID'] == ''){

} else {
	$stc_client = $_SESSION['CLIENT_ID'];		// Default client to show
	
?>
<table width="98%" border="0" align="center" cellpadding="4" cellspacing="0">
	<tr>
		<td>
<form id="form1" name="form1" method="post" action="index.php">
		<p class="aboutus">Welcome <?php echo $_SESSION['NAME']; ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="submit" name="home" id="home" value="Home" />
	<?php if( in_array( $stc_client, $stc_show_billing ) ) { ?>
	<input type="submit" name="billing" id="billing" value="Billing" onClick="document.form1.action ='billing.php'" />
	<?php } ?>
		<input type="submit" name="logout" id="logout" value="Log out" />
		</p>
		</form>

<script language="JavaScript" type="text/javascript"><!--
	$(function() {
		$( "#datepicker" ).datepicker({
			changeMonth: true,
			dateFormat: "yy-mm-dd",
			changeYear: true,
			yearRange: '2000:+0'
		});
		$( "#datepicker2" ).datepicker({
			changeMonth: true,
			dateFormat: "yy-mm-dd",
			changeYear: true,
			yearRange: '2000:+0'			
		});
	});
//--></script>

<form id="form2" name="form2" method="get" action="billing.php">
	<p>Search date range (YYYY-MM-DD to YYYY-MM-DD)
		<input name="dt1" type="text" id="datepicker" size="10" />
		to
		<input name="dt2" type="text" id="datepicker2" size="10" />
		Search and sort by 
		<input type="submit" name="pickup" id="Trace" value="Pickup Date" />
		or by
		<input type="submit" name="delivery" id="Trace" value="Delivery Date" />
	&nbsp;&nbsp; ( Show only BILLED
	<input type="checkbox" name="billed" id="billed" />
	)
	</p>
</form>
<?php
	
	if ( isset($_GET['dt1']) && isset($_GET['dt2'])) {		// Show Trace for client
		echo "<div id=\"st_loading2\"><center><p><img src=\"images/loading.gif\" width=\"125\" height=\"125\" alt=\"loading...\" /><br>loading data from remote server...</p></center></div>";

		$search_by = (isset($_GET['pickup']) ? "pickup" : "delivery");
		$url = $stc_remote_rep1."&client=".urlencode($stc_client).
			"&dt1=".urlencode($_GET['dt1'])."&dt2=".urlencode($_GET['dt2'])."&src=".$search_by;
		if( isset($_GET['billed']) ) $url .= "&billed";
		
		try {
//			$data = getRemoteFile($url);
			if( isset($_GET['debug']) ) echo "<p>URL = $url</p>";
			$data = file_get_contents($url);
		}
		catch(Exception $e)
		{
			$err = "<p><strong>Error:</strong> Problem connecting to the remote server.</p>";
			$data = false;
		}
		if( $data ) {
			$info = json_decode($data, true);
			$list = '';
			//echo "<pre>";
			//var_dump($info);
			//echo "</pre>";
			//$list = "<h1 class=\"aboutus\">Bill Of Lading numbers (".count($info)."):</h1>";
			$list .= '<div class="boxed"><table width="100%" border="0" cellpadding="4" cellspacing="0">
			<tr bgcolor="#BBBBBB">
				<th colspan="15">Report for '.$search_by.' between '.$_GET['dt1'].' and '.$_GET['dt2'].
				(isset($_GET['billed']) ? " (BILLED invoices only)" : "").'</th>
			</tr>
			<tr bgcolor="#BBBBBB">
				<th align="left">'.$stc_label_customer_load.'</th>
				<th align="left">'.$stc_label_company_load.'</th>
				<th align="left">STATUS</th>
				<th align="left" width="90">PICK UP'.($search_by == "pickup" ? '<img src="images/up-triangle.gif" width="11" height="10" />' : '').'</th>';
				if( $stc_show_carrier ) $list .= '<th align="left">CARRIER</th>';
				$list .= '<th align="left">DESTINATION</th>
				<th align="left" width="90">DELIVERY'.($search_by <> "pickup" ? '<img src="images/up-triangle.gif" width="11" height="10" />' : '').'</th>
				<th class="numeric" align="right">LINEHAUL</th>
				<th class="numeric" align="right">FUEL</th>
				<th class="numeric" align="right">LUMPER</th>
				<th class="numeric" align="right">DETENTION</th>
				<th class="numeric" align="right">MISC</th>';
				if( $stc_show_tcc ) $list .= '<th class="numeric" align="right">TCC</th>';
				if( $stc_show_mgmt_fee ) $list .= '<th class="numeric" align="right">MGMT FEE</th>';
				$list .= '<th class="numeric" align="right">INVOICE AMT</th>';
				if( $stc_show_pallets ) $list .= '<th class="numeric" align="right">PALLETS</th>
				<th class="numeric" align="right">INV/PALLETS</th>';
			$list .= '</tr>';
			$tot_linehaul	= 0.0;
			$tot_fuel		= 0.0;
			$tot_lumper		= 0.0;
			$tot_detention	= 0.0;
			$tot_misc		= 0.0;
			$tot_tcc		= 0.0;
			$tot_mgmt_fee	= 0.0;
			$tot_invoice	= 0.0;
			$tot_pallets	= 0.0;
			
			foreach( $info as $fb ) {
				if( $fb["STATUS_DESC"] == "THE TRIP IS DONE") $fb["STATUS_DESC"] = "DONE";
				else if( $fb["STATUS_DESC"] == "APPROVED FOR BILLING") $fb["STATUS_DESC"] = "APPROVED";
				$list .= '<tr class="list">';
				$list .= "<td>".$fb["HOF_NUMBER"]."</td>";
				$list .= "<td><a href=\"index.php?fb=" . urlencode($fb["BILL_NUMBER"]) . "\" target=\"_blank\">" . $fb["BILL_NUMBER"] ."</a></td>";
				$list .= "<td>".$fb["STATUS_DESC"]."</td>";
				$list .= "<td>".$fb["PICKUP_DATE"]."</td>";
				if( $stc_show_carrier ) $list .= "<td>".$fb["INTERLINER_NAME"]."</td>";
				$list .= "<td>".$fb["DESTNAME"]."</td>";
				$list .= "<td>".$fb["DELIVERY_DATE"]."</td>";
				$list .= "<td  class=\"numeric\" align=\"right\">".(isset($fb["CHARGES"]) && $fb["CHARGES"] <> 0 ? number_format((float) $fb["CHARGES"],2):"")."</td>";
				$list .= "<td class=\"numeric\" align=\"right\">".(isset($fb["FUEL_COST"]) ? number_format((float) $fb["FUEL_COST"],2):"")."</td>";
				$list .= "<td class=\"numeric\" align=\"right\">".(isset($fb["LUMPER"]) ? number_format((float) $fb["LUMPER"],2):"")."</td>";
				$list .= "<td class=\"numeric\" align=\"right\">".(isset($fb["DETENTION"]) ? number_format((float) $fb["DETENTION"],2):"")."</td>";
				$list .= "<td class=\"numeric\" align=\"right\">".(isset($fb["MISCCH"]) ? number_format((float) $fb["MISCCH"],2):"")."</td>";
				if( $stc_show_tcc ) $list .= "<td class=\"numeric\" align=\"right\">".(isset($fb["INT_PAYABLE_AMT"]) ? number_format((float) $fb["INT_PAYABLE_AMT"],2):"")."</td>";
				if( $stc_show_mgmt_fee ) $list .= "<td class=\"numeric\" align=\"right\">".(isset($fb["MANAGEMENT_FEE"]) ? number_format((float) $fb["MANAGEMENT_FEE"],2):"")."</td>";
				$list .= "<td class=\"numeric\" align=\"right\">".(isset($fb["TOTAL_CHARGES"]) ? number_format((float) $fb["TOTAL_CHARGES"],2):"")."</td>";
				if( $stc_show_pallets ) {
					$list .= "<td class=\"numeric\" align=\"right\">".(isset($fb["PALLETS"]) ? number_format((float) $fb["PALLETS"],0):"")."</td>";
					$list .= "<td class=\"numeric\" align=\"right\">".((isset($fb["PALLETS"]) && $fb["PALLETS"] <> 0) ? 
						number_format(((float) $fb["TOTAL_CHARGES"] / (float) $fb["PALLETS"]),2):"")."</td>";
				}
				$list .= "</tr>";

				$tot_linehaul	+= (isset($fb["CHARGES"]) ? (float) $fb["CHARGES"]:0.0);
				$tot_fuel		+= (isset($fb["FUEL_COST"]) ? (float) $fb["FUEL_COST"]:0.0);
				$tot_lumper		+= (isset($fb["LUMPER"]) ? (float) $fb["LUMPER"]:0.0);;
				$tot_detention	+= (isset($fb["DETENTION"]) ? (float) $fb["DETENTION"]:0.0);
				$tot_misc		+= (isset($fb["MISCCH"]) ? (float) $fb["MISCCH"]:0.0);
				$tot_tcc		+= (isset($fb["INT_PAYABLE_AMT"]) ? (float) $fb["INT_PAYABLE_AMT"]:0.0);
				$tot_mgmt_fee	+= (isset($fb["MANAGEMENT_FEE"]) ? (float) $fb["MANAGEMENT_FEE"]:0.0);
				$tot_invoice	+= (isset($fb["TOTAL_CHARGES"]) ? (float) $fb["TOTAL_CHARGES"]:0.0);
				$tot_pallets	+= (isset($fb["PALLETS"]) ? (float) $fb["PALLETS"]:0.0);
			}
			$list .= '<tr bgcolor="#BBBBBB">
				<th align="left"></th>
				<th align="left"></th>
				<th align="left"></th>
				<th align="left"></th>';
				if( $stc_show_carrier ) $list .= '<th align="left"></th>';
				$list .= '<th align="left"></th>
				<th align="left"></th>
				<th class="numeric" align="right">'.number_format((float)$tot_linehaul,2).'</th>
				<th class="numeric" align="right">'.number_format((float)$tot_fuel,2).'</th>
				<th class="numeric" align="right">'.number_format((float)$tot_lumper,2).'</th>
				<th class="numeric" align="right">'.number_format((float)$tot_detention,2).'</th>
				<th class="numeric" align="right">'.number_format((float)$tot_misc,2).'</th>';
				if( $stc_show_tcc ) $list .= '<th class="numeric" align="right">'.number_format((float)$tot_tcc,2).'</th>';
				if( $stc_show_mgmt_fee ) $list .= '<th class="numeric" align="right">'.number_format((float)$tot_mgmt_fee,2).'</th>';
				$list .= '<th class="numeric" align="right">'.number_format((float)$tot_invoice,2).'</th>';
				if( $stc_show_pallets ) $list .= '<th class="numeric" align="right">'.number_format((float)$tot_pallets,0).'</th>
					<th class="numeric" align="right">'.number_format(((float)$tot_invoice/(float)$tot_pallets),2).'</th>';
			$list .= '</tr>';

			$list .= "</table></div>";
			update_message( "st_loading2", "" );
			echo $list;
			
		} else {
			update_message( "st_loading2", "" );
			alert_server_down( $stc_ip_address, $stc_server_name, "Trace Client Loads - billing" );
			echo $err;
		}
	}
	echo "<p><b>Loads which are not in BILLED status, may or may not have accurate linehaul, fuel and or accessorial charges.</b></p>";
	//echo "<p>Customer Service Issues (Pertaining to Appointments) please email: <a href=\"mailto:cs@hutt.com\">cs@hutt.com</a></p>";
	//echo "<p>Operations Issues (Pertaining to Capacity or Delivery) please email: <a href=\"mailto:dispatch@hutt.com\">dispatch@hutt.com</a></p>";
	if( false && isset($_SESSION['USER2']) )
		echo "<p>Load tenders (Sending or Revisions) please email: <a href=\"mailto:".$_SESSION['USER2']."\">".$_SESSION['USER2']."</a></p>";

?>
</td>
	</tr>
</table>

<?php } ?>
</body>
</html>