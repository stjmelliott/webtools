<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Untitled Document</title>
</head>
<?php
error_reporting(E_ALL);
set_time_limit(0);

//echo "<pre>";
//var_dump($_SERVER);
//echo "</pre>";
echo "<p>". $_SERVER["HTTP_USER_AGENT"]."</p>";
if(strpos($_SERVER["HTTP_USER_AGENT"],"iPod") <> false)
	echo "<p>iPod detected</p>";
else if(strpos($_SERVER["HTTP_USER_AGENT"],"iPhone") <> false)
	echo "<p>iPhone detected</p>";
else if(strpos($_SERVER["HTTP_USER_AGENT"],"iPad") <> false)
	echo "<p>iPad detected</p>";
else
	echo "<p>other detected</p>";
	
if(class_exists("PEAR")) {
	echo "<p>PEAR detected</p>";
	require_once "Mail.php";
} else
	echo "<p>PEAR not detected</p>";

if( function_exists( "mail") ) {
	echo "<p>Sending e-mail</p>";
	$message="Test email.
	
This is a test.

";
	
	$to = "scott.elliott@hutt.com";
	
	$subject = "www.hutt.com - Test";
	
	
	$headers = "From: noreply@hutt.com\r\n" ;
	//$headers.="Return-Path: scott.elliott@hutt.com\r\n";
	
	$server = "10.0.0.247";
	
	ini_set ( "SMTP", $server ); 
	//ini_set ( "smtp_port", 465 );	//smtp_port 465
	date_default_timezone_set('America/New_York');
	
	echo "<p>Trying ".$server."...";
	$result = mail($to, $subject, $message, $headers , "-fscott.elliott@hutt.com" );

	echo " Mail result = ".($result ? "true" : "false")."</p>";
	echo "<pre>";
	print_r(error_get_last());
	echo "</pre>";
	
	if( 0 ) {
	$c = 1;
	do {
		echo "<p>Trying 10.0.0.".$c."...";
		ini_set ( "SMTP", "10.0.0.".$c ); 
		$result = mail($to, $subject, $message, $headers /*, "-fscott.elliott@hutt.com" */);
	
		echo " Mail result = ".($result ? "true" : "false")."</p>";
		flush();
	    ob_flush();
	    sleep(1);
		$c++;
	} while( ! $result && $c < 256 );
	}
	
} else {
	echo "<p>mail() does not exist</p>";
}
	
?>

<body>
</body>
</html>