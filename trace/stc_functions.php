<?php

// no direct access
defined('_FUZZY') or die('Restricted access');

function base64_url_encode($input) {
 return strtr(base64_encode($input), '+/=', '-_.');
}

function base64_url_decode($input) {
 return base64_decode(strtr($input, '-_.', '+/='));
}

function encryptData($value, $key = ''){
	global $_SESSION, $stc_enable_encryption, $stc_encrypt_prefix;
	if( $stc_enable_encryption ) {
		$text = $stc_encrypt_prefix.$value;
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, ($key == '' ? $_SESSION['TODAYS_KEY'] : $key), $text, MCRYPT_MODE_ECB, $iv);
		return base64_url_encode($crypttext);
	} else {
		return $value;
	}
}

function decryptData($value, $key = ''){
	global $_SESSION, $stc_enable_encryption, $stc_encrypt_prefix;
	if( $stc_enable_encryption ) {
		$crypttext = base64_url_decode($value);
		$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
		$decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, ($key == '' ? $_SESSION['TODAYS_KEY'] : $key), $crypttext, MCRYPT_MODE_ECB, $iv);
		if(substr($decrypttext, 0, strlen($stc_encrypt_prefix)) == $stc_encrypt_prefix )		
			return trim(substr($decrypttext,strlen($stc_encrypt_prefix)));
		else
			return false;
	} else {
		return $value;
	}
}

function set_functions() {
	if(! isset($_SESSION['FUNCTIONS']) )
		fetch_todays_key(); // Refresh key & grab function list
	$info = json_decode($_SESSION['FUNCTIONS'], true);
	foreach( $info{'FUNCTIONS'} as $var => $value ) {
		$GLOBALS[$var] = $value;
	}	
}

function try_update_url_epw( $url ) {

	$newurl = $url;
	if(! isset($_SESSION['FUNCTIONS']) )
		fetch_todays_key(); // Refresh key & grab function list
		
	if( isset($_SESSION['LAST_KEY']) ) {
		if( 1 == preg_match('/^([^\?]*)\?epw=([^\&]*)(.*)$/', $url, $matches1 )) {
			$new_epw = encryptData(decryptData($matches1[2], $_SESSION['LAST_KEY']));
			$newurl = $matches1[1]."?epw=".$new_epw.$matches1[3];
		}
	}

	return $newurl;
}

function fetch_todays_key() {
	global $_SESSION, $_GET, $st_private_server, $stc_server_name, $st_init_functions, $stc_initial_key;

	$url = $st_private_server.$st_init_functions."&opt=mykey";
	
	if( 1 == preg_match('/^([^\?]*)\?pw=([^\&]*)(.*)$/', $url, $matches ) )
		$newurl = $matches[1]."?epw=".encryptData($matches[2], $stc_initial_key).$matches[3];
	else
		$newurl = $url;

	try {
		if( isset($_GET['debug']) ) echo "<p>URL = $newurl</p>";
		$data = decryptData(file_get_contents($newurl),$stc_initial_key);
	}
	catch(Exception $e)
	{
		alert_server_down( $st_private_server, $stc_server_name, "CRM user - mykey" );
		$data = false;
	}

	if( $data) {
		$_SESSION['FUNCTIONS'] = $data;
		$info = json_decode($data, true);
		if( isset($_SESSION['TODAYS_KEY']) && $_SESSION['TODAYS_KEY'] <> $info{'TODAYS_KEY'} )
			$_SESSION['LAST_KEY'] = $_SESSION['TODAYS_KEY'];
		$_SESSION['TODAYS_KEY'] = $info{'TODAYS_KEY'};
		$_SESSION['TODAYS_KEY_DATE'] = gmdate('Ymd');
		set_functions();
	} else {
		send_duncan_diagnostics('fetch_todays_key: no data.',
				"url = $url
newurl = $newurl");
	}
}

function fetch_data( $url, $where ) {
	global $_SESSION, $_GET, $st_private_server, $stc_server_name;
	
	if($_SESSION['TODAYS_KEY_DATE'] <> gmdate('Ymd'))
		fetch_todays_key(); // Refresh key
	
	try {
		if( isset($_GET['debug']) ) echo "<p>URL = $url</p>";
		$data = decryptData(file_get_contents($url));
		if( ! $data ) {
			fetch_todays_key(); // Refresh key
			$newurl = try_update_url_epw( $url );
			$data = decryptData(file_get_contents( $newurl ));
			if( ! $data ) {
				send_duncan_diagnostics('fetch_data: got false twice. Probably after midnight.',
				"url = $url
newurl = $newurl
where = $where");
				reload_page( "index.php?logout" );
			}
		}
	}
	catch(Exception $e)
	{
		alert_server_down( $st_private_server, $stc_server_name, $where,
			"URL = $url
			Exception = $e
			Data = $data" );
		$data = false;
	}
	return $data;
}

function update_message( $div, $msg ) {
	echo "<script type=\"text/javascript\">
<!--
document.getElementById(\"".$div."\").innerHTML=\"".$msg."\";
-->
</script>";
}

function update_message2( $div, $msg ) {
	echo "<script type=\"text/javascript\">
<!--
	var p2 = document.getElementById(\"".$div."\");
	p2.parentNode.removeChild(p2);
-->
</script>";
	echo $msg;
}

function reload_page ( $page ) {
	global $stc_trace_root;
	echo "<script type=\"text/javascript\">
<!--
window.location = \"".$stc_trace_root."/".$page."\"
-->
</script>";
}

function getRemoteFile($url)
{
   // get the host name and url path
   $parsedUrl = parse_url($url);
   $host = $parsedUrl['host'];
   if (isset($parsedUrl['path'])) {
      $path = $parsedUrl['path'];
   } else {
      // the url is pointing to the host like http://www.mysite.com
      $path = '/';
   }

   if (isset($parsedUrl['query'])) {
      $path .= '?' . $parsedUrl['query'];
   }

   if (isset($parsedUrl['port'])) {
      $port = $parsedUrl['port'];
   } else {
      // most sites use port 80
      $port = '80';
   }

   $timeout = 10;
   $response = '';

   // connect to the remote server
   $fp = @fsockopen($host, $port, $errno, $errstr, $timeout );

   if( !$fp ) {
      echo "Cannot retrieve $url<br>Host $host<br>Port $port<br>Path $path<br>Error $errno, $errstr";
   } else {
      // send the necessary headers to get the file
      fputs($fp, "GET $path HTTP/1.0\r\n" .
                 "Host: $host\r\n" .
                 "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.3) Gecko/20060426 Firefox/1.5.0.3\r\n" .
                 "Accept: */*\r\n" .
                 "Accept-Language: en-us,en;q=0.5\r\n" .
                 "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n" .
                 "Keep-Alive: 300\r\n" .
                 "Connection: keep-alive\r\n" .
                 "Referer: http://$host\r\n\r\n");

      // retrieve the response from the remote server
      while ( $line = fread( $fp, 4096 ) ) {
         $response .= $line;
      }

      fclose( $fp );

      // strip the headers
      $pos      = strpos($response, "\r\n\r\n");
      $response = substr($response, $pos + 4);
   }

   // return the file content
   return $response;
}

function map_link( $lat, $long, $desc ) {
	$link = "";
	if( $desc <> "" ) {
		if( $lat <> "" && $long <> "" ) {
			$map = substr($lat, 0, 3) . " " . substr($lat, 3, 2) . " " . substr($lat, 5, 2) . " " . substr($lat, 7, 1) . " ";
			$map .= substr($long, 0, 3) . " " . substr($long, 3, 2) . " " . substr($long, 5, 2) . " " . substr($long, 7, 1);
			$link = " <a href=\"http://maps.google.com/maps?q=".urlencode($map)."&z=12\" target=\"_blank\">";
			$link .= $desc . "</a>";
		} else {
			$link = $desc;
		}
	}
	return $link;
}

function list_bol( $fb ) {
	global $st_private_server, $st_images_functions;
	
	$url = $st_private_server.$st_images_functions."&opt=getbol&fb=".trim($fb);
	
	$data = fetch_data( $url, "CRM images - getbol" );
	if( $data) {
		$info = json_decode($data, true);
			$code = '<table class="table2"><tbody>';
			foreach( $info as $row) {
				if( $row{'document_type'} == 'BOL' || $row{'document_type'} == 'INV' ) {
					$code .= '<tr>
					<td><a href="../webtools/bol_view.php?FB='.trim($fb).'&DOCID='.$row{'hdr_intdocid'}.'" target="_blank">'.$row{'hdr_intdocid'}.'</a></td>
					<td>'.substr($row{'pev_date'}, 0, 10).'</td>
					<td>'.($row{'document_type'} == 'BOL' ? 'BOL/POD' : 
						($row{'document_type'} == 'INV' ? 'INVOICE' : $row{'document_type'})).'</td>
					</tr>';
				}
			}
			
			
			$code .= '</tbody></table>';
	}
	return $code;
}

set_functions(); // Ensure we have functions set
if( ! isset( $GLOBALS['stc_remote_list'] ) ) fetch_todays_key();


?>