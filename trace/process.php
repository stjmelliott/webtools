<?php

// no direct access
defined('_FUZZY') or die('Restricted access');

require_once( "./stc_config.php" );

error_reporting(E_ALL);

function alert_server_down( $ip, $server, $where ) {
		
	$message="Alert, unable to contact the remote server $server at $ip.
	
From ".$where."

To test if you can reach the server through the firewall, try this URL:

http://".$stc_ip_address."

";
	
	$to = "duncan@strongtco.com"; //,selliott@strongtco.com";
	
	$subject = $_SERVER["HTTP_HOST"]." - Server Down Alert";
	
	
	$headers = "From: noreply@".$_SERVER["HTTP_HOST"]."\r\n" ;

	mail($to, $subject, $message, $headers);
}

?>