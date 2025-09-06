<?php
/*
 * Connecting to PostgreSQL using ODBC
 * Version: 160408
 * Written by Piotr Polak
 */
 

/* Connection data */
$odbc_driver      = 'PostgreSQL Unicode';
$odbc_database    = 'images';
$odbc_host        = 'ktl-newimaging';
$odbc_user        = 'kaiser';
$odbc_password    = 'Maddox1';

/* ************************************************** */



/* Building DSN */
$dsn =  'DRIVER={'.$odbc_driver.'};'.
		'Server='.$odbc_host.';'.
		'Database='.$odbc_database.';'.
		'uid='.$odbc_user; //.'; pwd='.$odbc_password;

/* Connecting */
$connection = @odbc_connect($dsn, '', '') or die('Connection error: '.htmlspecialchars(odbc_errormsg()));

/* Prepares query */
$query = 'SELECT VERSION() AS ver';

/* Executes query */
$result = @odbc_exec($connection, $query) or die('Query error: '.htmlspecialchars(odbc_errormsg()));;

/* Fetches array */
$row = odbc_fetch_array($result);

/* Printing message */
echo '<h1>Success!</h1> <p>Server version is: '.$row['ver'].'</p>';
?>