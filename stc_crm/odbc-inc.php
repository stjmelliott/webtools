<?php
set_time_limit(0);

// no direct access
defined('_FUZZY') or die('Restricted access');

require_once( "../stc_crm/stc_config.php" );

$last_odbc_error = "";

class stc_db {
	
	private $dsn;
	private $debug = false;
	private $profiling = false;
	private $timer;
	private $time_connect;
	private $time_query;
	private $link;
	private $last_error;
	public $num_rows;

	public function __construct( $database, $debug = false  ) {
		
		global $stc_odbc_driver, $stc_user, $stc_password, $stc_host, $stc_port, $stc_schema;		
		global $last_odbc_error, $stc_profiling;
		
		$this->debug = $debug;
		$this->profiling = $stc_profiling || $this->debug;
		
		// Check odbc_connect exists
		if( function_exists( "odbc_connect" ) ) {
			if( $this->profiling ) {
				$this->timer = new stc_timer();
				$this->timer->start();
			}

			if( $this->debug ) echo "<p>odbc_connect() exists2.</p>";
			if( $this->debug ) echo "<p>Connect to database $database on host $stc_host</p>";
			
			$this->dsn="DRIVER={".$stc_odbc_driver."};HOSTNAME=$stc_host;DATABASE=$database;PROTOCOL=TCPIP;PORT=$stc_port;Uid=$stc_user;Pwd=$stc_password;CurrentSchema=$stc_schema;";
			//$this->dsn="DRIVER={".$stc_odbc_driver."}; DATABASE=$database;";
			if( $this->debug ) echo "<p>using DSN = $this->dsn</p>";
		
			$this->link = odbc_connect($this->dsn, $stc_user, $stc_password);
		
			if( $this->link <> false ) {
				$this->get_multiple_rows( "SET PATH = 'SYSIBM','SYSFUN','".$stc_schema."'" );
				if( $this->profiling ) {
					$this->timer->stop();
					$this->time_connect = $this->timer->result();
				}
				if( $this->debug ) echo "<p>odbc_connect succeeded. Time: ".$this->time_connect."</p>";
				// Ensure auto commit, to avoid locks.
				odbc_autocommit( $this->link, true );

			} else {
				$this->last_error = $last_odbc_error = odbc_errormsg($this->link);
				if( $this->debug ) echo "<p>Connection failed. ".odbc_errormsg()."<br>".odbc_errormsg($this->link)."</p>";
				$this->__destruct();
			}
			
		} else {
			$this->last_error = $last_odbc_error = "odbc_connect() does not exist.";
			if( $this->debug ) echo "<p>odbc_connect() does not exist.</p>";
			$this->__destruct();
		}
	}
	
	// When closing this object, it closes the DB connection
	function __destruct() {
		if( function_exists( "odbc_close" )  && isset($this->link) && $this->link ) {
			odbc_close ($this->link);
		}
	}
	
	// Returns all rows in an array of assoc array
	public function get_multiple_rows( $query_string ) {
		global $last_odbc_error;
		
		if( $this->profiling ) {
			$this->timer->start();
		}
		if( $this->debug ) {
			echo "<p>using query_string = </p>
			<pre>";
			var_dump($query_string);
			echo "</pre>";
		}

		$res = odbc_prepare($this->link, $query_string);
		if($res) {
			if( $this->debug ) echo "<p>odbc_prepare succeeded.</p>";
			
			odbc_setoption($res, 2, 0, 240);
	
			if(odbc_execute($res)) {
				if( $this->debug ) echo "<p>odbc_execute succeeded. Time: ".$this->timer->split()."</p>";

				$rows = array();
				while( $row = odbc_fetch_array($res) ) {
					array_push($rows, $row);
				}
				
				if( $this->profiling ) {
					$this->timer->stop();
					$this->time_query += $this->timer->result();
				}
				if( $this->debug ) echo "<p>get_multiple_rows succeeded. Time: ".$this->timer->result()."</p>";

				return $rows;
			} else {
				$this->last_error = $last_odbc_error = odbc_errormsg($this->link);
				if( $this->debug ) echo "<p>could not execute statement: ".odbc_errormsg($this->link)."</p>";
				return false;
			}
		} else {
			$this->last_error = $last_odbc_error = odbc_errormsg($this->link);
			if( $this->debug ) echo "<p>could not prepare statement: ".odbc_errormsg($this->link)."</p>";
			return false;
		}
	}

	// Profiling
	public function timer_results() {
		if( $this->profiling )
			return array( $this->time_connect, $this->time_query );
		else
			return false;
	}
	
	// Error string
	public function error() {
		return $this->last_error;
	}
	
	public function escape( $s ) {
		return str_replace( '%', '\%', $s );
	}
	

}


/*
 *	function send_odbc_query	-	query database and return result or FALSE on error
 */
function send_odbc_query( $query_string, $database, $debug = FALSE ) {

	global $stc_user, $stc_password, $stc_host, $stc_port, $stc_schema;
	global $last_odbc_error;
	
	$dbconn = new stc_db( $database, $debug );

	if( $dbconn ) {
		$result = $dbconn->get_multiple_rows( $query_string );
		unset($dbconn);
		return $result;
	} else {
		unset($dbconn);
		return false;
	}
}

?>
