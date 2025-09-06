<?php
session_start();

// Set flag that this is a parent file
define( '_FUZZY', 1 );

require_once( "./stc_config.php" );
require_once( "./stc_functions.php" );
require_once( "./process.php" );

		
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Bootstrap -->
<link href="bootstrap/css/bootstrap.css" rel="stylesheet" media="screen">
<link href="assets/css/bootstrap-responsive.css" rel="stylesheet">
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<title>Trace Client Loads</title>
<style type="text/css">
.TitleBlack {
	color: #000;
	font-family: Verdana, Geneva, sans-serif;
	font-size: 32px;
}
.bg-primary {
  color: #fff;
  background-color: #428bca;
}

</style>
<link href="additional.css" rel="stylesheet" type="text/css" />
<link type="text/css" href="https://code.jquery.com/ui/1.10.2/themes/redmond/jquery-ui.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery.js"></script>
<script src="https://code.jquery.com/ui/1.10.2/jquery-ui.min.js"></script>
</head>

<body>
<div class="container">
<?php
	//HTTP_HOST HTTP_REFERER
if( isset($_SERVER["HTTP_HOST"]) ) {
	if( strpos($_SERVER["HTTP_HOST"], 'manninglogistics.com') !== false ) {
		echo '<p class="text-center"><br><a href="http://mid-waytransportation.com//"><img src="images/manning-logistics-logo.png" alt="manning-logistics-logo" width="250" height="79" ></a>
		';

	} else 	if( strpos($_SERVER["HTTP_HOST"], 'manningtransfer.com') !== false ) {
		echo '<p class="text-center"><br><a href="http://mid-waytransportation.com/"><img src="images/manning-transfer-logo.png" alt="manning-transfer-logo" width="250" height="79" ></a>
		';
	}
	echo '<br>Phone (254) 666-6161  </p>';
}
?>
<p>
	<?php
		
	$back_key = '';
		
	if( isset($_POST) && isset($_POST['track']) && isset($_POST['trace'])) {
		$_GET['trace'] = $_POST['trace'];
		$back_key = ' <a href="./index.php" class="btn btn-small"><i class="icon-arrow-left"></i> Back</a>';
	} else if( isset($_GET) && isset($_GET['bk']) ){
		$back_key = ' <a href="./index.php" class="btn btn-small"><i class="icon-arrow-left"></i> Back</a>';
		
	}

	$err = "<h3>Your shipment did not match our records please contact us at $back_key</h3>";
	if( strpos($_SERVER["HTTP_HOST"], 'manninglogistics.com') !== false ) {
		$err .= '<h4>Phone Number: (254) 666-6161   or
Email us at: <a href="mailto:tracking@mid-waytransportation.com">tracking@mid-waytransportation.com</a></h4>';
	} else {
		$err .= '<h4>Phone Number: (254) 666-6161   or
Email us at: <a href="mailto:tracking@mid-waytransportation.com">tracking@mid-waytransportation.com</a></h4>';
	}
	
	if(	isset($_GET['trace']) || isset($_GET['fb']) ) {		// Show detail for FB
		echo "<div id=\"st_loading3\"><center><p><img src=\"images/loading.gif\" width=\"125\" height=\"125\" alt=\"loading...\" /><br>loading data from remote server...</p></center></div>";
		if( isset($_GET['trace']) ) {
			if( isset($_GET['debug']) ) echo "<p>trace = \"".$_GET['trace']."\"</p>";
			$trace_str = $_GET['trace']; //str_replace (" ", "", $_GET['trace']);
			$url = $stc_remote_gett."&trace=".urlencode($trace_str);
		} else {
				$fb_str = str_replace (" ", "", $_GET['fb']);
				$url = $stc_remote_getfb."&fb=".urlencode($fb_str);			
		}		

			$data = fetch_data( $url, "Trace Client Loads - gett or getfb" );
			if( $data ) {
				$info1 = json_decode($data, true);
				
				$gett = "";
				if (count($info1) == 0 || (
					count($info1) == 1 &&
					is_array($info1[0]) &&
					is_array($info1[0][0]) &&
					isset($info1[0][0]['CURRENT_STATUS']) &&
					$info1[0][0]['CURRENT_STATUS'] == 'CANCL') ) {
					$gett .= $err;
				} else {
					foreach ( $info1 as $info ) {
						//echo "<pre>";
						//var_dump($info);
						//echo "</pre>";
						if( isset($_GET['trace']) )
							$gett .= "<h3>Trace ".$_GET['trace'].
								" (".count($info)." freight bills) $back_key</h3>";
								
						if (count($info) == 0 ) {
							$gett .= $err;
						} else if (count($info) > 1 ) {
							$url = 'https://'.$_SERVER["HTTP_HOST"].'/trace/trace.php?bk&fb=';
							
							$gett .= '<div class="row bg-primary">
								<div class="span5">Shipper</div>
								<div class="span5">Consignee</div>
								<div class="span2">Bill#</div>
							</div>
							';
							foreach( $info as $fb) {
								if( $fb['CURRENT_STATUS'] != 'CANCL' )
									$gett .= '<div class="row">
							<div class="span5">'.$fb["ORIGNAME"].', '.$fb["ORIGCITY"].', '.$fb["ORIGPROV"].'</div>
							<div class="span5">'.$fb["DESTNAME"].', '.$fb["DESTCITY"].', '.$fb["DESTPROV"].'</div>
							<div class="span2"><a href="'.$url.$fb["BILL_NUMBER"].'">'.$fb["BILL_NUMBER"].'</a></div>
							</div>
							';
							}
						} else {
						
						foreach( $info as $fb) {
							
						$pos = array();
						$wos = array();
						if( isset($fb["TRACES"]) && 
							is_array($fb["TRACES"]) && 
							count($fb["TRACES"]) > 0) {
							foreach($fb["TRACES"] as $tr) {
								if( $tr["TRACE_TYPE"] == 'P' ) {
									$pos[] = $tr["TRACE_NUMBER"];
								}
								else if( $tr["TRACE_TYPE"] == '7' ) {
									$wos[] = $tr["TRACE_NUMBER"];
								}
							}
						}
						
						if( false && is_array($fb["CUSTOM_DATA"]) && count($fb["CUSTOM_DATA"]) > 0 ) {
							$REQPUST = $REQPUEND = $REQDELVST = $REQDELVEND = '';
							foreach( $fb["CUSTOM_DATA"] as $cd_row) {
								
								switch( $cd_row["LABEL_NAME"] ) {
									case 'REQPUST':
										$REQPUST = date("Y-m-d h:i A", strtotime($cd_row["DATA"]));
										break;
									case 'REQPUEND':
										$REQPUEND = date("Y-m-d h:i A", strtotime($cd_row["DATA"]));
										break;
									case 'REQDELVST':
										$REQDELVST = date("Y-m-d h:i A", strtotime($cd_row["DATA"]));
										break;
									case 'REQDELVEND':
										$REQDELVEND = date("Y-m-d h:i A", strtotime($cd_row["DATA"]));
										break;
									
									default:
										break;									
								}
							}
							
							$r1 = '<th colspan="2" style="width: 33%;">Requested Pickup</th>
									<th colspan="2" style="width: 33%;">Requested Delivery</th>';
							$r2 = '<td>From:</td>
									<td>'.$REQPUST.'</td>
									<td>From:</td>
									<td>'.$REQDELVST.'</td>';
							$r3 = '<td>To:</td>
									<td>'.$REQPUEND.'</td>
									<td>To:</td>
									<td>'.$REQDELVEND.'</td>';

						} else {
							$r1 = '<td colspan="4" style="border: none;"></td>';
							$r2 = '<td colspan="4" style="border: none;"></td>';
							$r3 = '<td colspan="4" style="border: none;"></td>';
						}
						
						$gett .=  '<div class="row">
							<div class="span6"><h3>BILL '.$fb["BILL_NUMBER"].$back_key.'</h3></div>
						</div>
						<div class="row">
							<div class="span12 bg-primary">Contacts</div>
						</div>
						<div class="row">
							<div class="span8">
							<table class="table table-condensed table-bordered table-hover">
							<head>
								<tr>
									<th style="width: 50%;">Shipper</th>
									<th style="width: 50%;">Consignee</th>
								</tr>
							</head>
							<body>
								<tr>
									<td>'.$fb["ORIGNAME"].'</td>
									<td>'.$fb["DESTNAME"].'</td>
								</tr>
								<tr>
									<td>'.$fb["ORIGCITY"].'</td>
									<td>'.$fb["DESTCITY"].'</td>
								</tr>
								<tr>
									<td>'.$fb["ORIGPROV"].'</td>
									<td>'.$fb["DESTPROV"].'</td>
								</tr>
								<tr>
									<td>'.$fb["ORIGPC"].'</td>
									<td>'.$fb["DESTPC"].'</td>
								</tr>
							</body>
							</table>
					</div>
					</div>	
						<div class="row">
							<div class="span12 bg-primary">Summary Info</div>
						</div>
							<table class="table table-condensed table-bordered table-hover">
							<head>
								<tr>
									<th colspan="2" style="width: 33%;">'.(isset($fb["PICK_UP_APPT_MADE"]) && trim($fb["PICK_UP_APPT_MADE"]) == 'True' ? 'Scheduled' : 'Estimated').' Pickup</th>
									<th colspan="2" style="width: 33%;">'.(isset($fb["DELIVERY_APPT_MADE"]) && trim($fb["DELIVERY_APPT_MADE"]) == 'True' ? 'Scheduled' : 'Estimated').' Delivery</th>
									<th colspan="2" style="width: 33%;">Other</th>
								</tr>
							</head>
							<body>
								<tr>

									<td>From:</td>
									<td>'.(isset($fb["PICK_UP_BY"]) ? date("Y-m-d h:i A", strtotime($fb["PICK_UP_BY"])) : "").'</td>
									
									<td>From:</td>
									<td>'.(isset($fb["DELIVER_BY"]) ? date("Y-m-d h:i A", strtotime($fb["DELIVER_BY"])) : "").'</td>
									<td>PO #s:</td>
									<td>'.implode(', ', $pos).'</td>
								</tr>
								<tr>
									<td>To:</td>
									<td>'.(isset($fb["PICK_UP_BY_END"]) ? date("Y-m-d h:i A", strtotime($fb["PICK_UP_BY_END"])) : "").'</td>
									
									<td>To:</td>
									<td>'.(isset($fb["DELIVER_BY_END"]) ? date("Y-m-d h:i A", strtotime($fb["DELIVER_BY_END"])) : "").'</td>

									<td>WO #s:</td>
									<td>'.implode(', ', $wos).'</td>

								</tr>
								<tr>
									'.$r1.'
									<td>Pallets:</td>
									<td style="text-align: right;">'.number_format($fb["PALLETS2"], 0).'</td>
								</tr>
								<tr>
									'.$r2.'
									<td>Pieces:</td>
									<td style="text-align: right;">'.number_format($fb["PIECES2"], 0).'</td>
								</tr>
								<tr>
									'.$r3.'
									<td>Weight:</td>
									<td style="text-align: right;">'.number_format($fb["WEIGHT2"], 0).'</td>
								</tr>
							</body>
							</table>
						';
							//echo "<pre>";
							//var_dump($fb);
							//echo "</pre>";
						
						if( false && isset($fb["INTERLINERS"]) && count($fb["INTERLINERS"]) > 0 ) {
							
							$gett .=  '<div class="row">
								<div class="span12 bg-primary">Carriers</div>
							</div>
								<table class="table table-condensed table-bordered table-hover">
								<head>
									<tr>
										<th>Carrier</th>
										<th>From</th>
										<th>To</th>
									</tr>
								</head>
								<body>
								';
							foreach( $fb["INTERLINERS"] as $row ) {
								$gett .=  '<tr>
										<td>'.$row["NAME"].'</td>
										<td>'.$row["FROM_ZDESC"].'</td>
										<td>'.$row["TO_ZDESC"].'</td>
									</tr>
								';
							}
							$gett .=  '</body>
								</table>
							';
						}

							$gett .=  '<div class="row">
								<div class="span12 bg-primary">Status History</div>
							</div>
								<table class="table table-condensed table-bordered table-hover">
								<head>
									<tr>
										<th align="left">Date/Time</th>
										<th align="left">Comments</th>
										<th align="left">Code</th>
										<th align="left">Status</th>
										<th align="left">Trip</th>
										<th align="left">Location</th>
									</tr>
								</head>
								<body>
								';
						
						$last_dt = false;
						$atleast12hours = 12*60*60;
						$arrived = false;
						
						//echo "<pre>";
						//var_dump($fb["HISTORY"]);
						//echo "</pre>";

						// Reverse loop
						$index = count($fb["HISTORY"]);
						while($index) {
							$hist = $fb["HISTORY"][--$index];
							
							$fb["HISTORY"][$index]['SHOW_POSITION'] = false;
							if( $hist["STATUS_CODE"] == 'ARRCONS') {
								$arrived = true;
							} else if( $hist["STATUS_CODE"] == 'DEPSHIP') {
								$last_dt = strtotime($hist["CHANGED"]);
							} else if( $hist["STATUS_CODE"] == 'POSITION') {
								$dt = strtotime($hist["CHANGED"]);
								if( ! $arrived && $last_dt && ($dt - $last_dt) > $atleast12hours ) {
									$last_dt = $dt;
									$fb["HISTORY"][$index]['SHOW_POSITION'] = true;
								}
							}
						}
							
						//echo "<pre>";
						//var_dump($fb["HISTORY"]);
						//echo "</pre>";
						
						
						foreach( $fb["HISTORY"] as $hist ) {
							
							if( $hist['SHOW_POSITION'] ||
								in_array($hist["STATUS_CODE"], array("PICKD", "DEPSHIP",
							"CHECKCALL", "CCOT", "CCLT", "DELVD",
							"DEPCONS", 'ENRTE', 'ENRTE1', 'ENRTE2',
							'ENRTE3', 'ENRTE4', 'ENRTE5',
							'ARRSHIP', 'ARRCONS' )) ) {
								$gett .= '<tr class="list">';
								$gett .= "<td width=\"150\">".date("Y-m-d h:i A", strtotime($hist["CHANGED"]))."</td>";
								$gett .= "<td>".(empty($hist["COMM"]) ? "" : $hist["COMM"])."</td>";
								$gett .= "<td>".(empty($hist["MYSTATUS"]) ? $hist["STATUS_CODE"] : $hist["MYSTATUS"])."</td>";
								$gett .= "<td>".(empty($hist["STATUS_DESC"]) ? "" : $hist["STATUS_DESC"])."</td>";
								$gett .= "<td>".($hist["TNO"] > 0 ? $hist["TNO"] : "")."</td>";
								$gett .= "<td>".(empty($hist["ZDESC"]) ? "" : $hist["ZDESC"])."</td>";
								
								$gett .= "</tr>";
							}
						} // history
						
						$gett .= "<tbody></table><br>";



						
						//echo "<pre>";
						//var_dump($info);
						//echo "</pre>";
					}
					} // info

				}
			}
			update_message( "st_loading3", "" );
			echo $gett;
	}
?>
</div>
	<?php } ?>
	<script src="bootstrap/js/bootstrap.min.js"></script> 
	<script src="bootstrap/js/bootstrap-popover.js"></script>
</body>
</html>