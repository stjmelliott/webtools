<?php

// no direct access
defined('_FUZZY') or die('Restricted access');

date_default_timezone_set('America/Chicago');

// This defines the admin username & password for trace
$stc_admin_client = 'Manning';
$stc_admin_pw = 'Timber6Cup';

// Encryption
$stc_enable_encryption = false;
$stc_initial_key = "Fuzzy Likes Dim Sum";
$stc_todays_key = "Who knows?";
$stc_encryption_seed = "I6NEZoRERlmk0JxGiNSEkIQQIREREZZmq"; // Important!
$stc_encrypt_prefix = "Fuzzy";

// TMW Imaging
$stc_use_tmw_imaging = false;

// Location where the CMS code is stored.
$stc_trace_root = "http://".$_SERVER["HTTP_HOST"]."/trace";

// This defines the IP address and port number to reach the server
// Also update reload_page() in stc_functions.php
$stc_ip_address = "http://localhost/stc_trace";

// This defines the name of the server
$stc_server_name = "strongtco.dyndns.org";

// Location of private server.
$st_private_server = "http://localhost/stc_crm";

// User functions.
$st_init_functions = "/stc_crm_user.php?pw=NOTmyPassword";

$stc_company_name = 'Midway';

// This defines the terms of use shown on the home page.
$stc_terms_of_use = '<strong>Terms of use:</strong> '.$stc_company_name.' provide this page is for our clients to obtain up-to-date information on their loads, and their loads only. By logging in you agree to these terms of use.';

//$stc_banner_logo = '<img src="images/midway.png" alt="midway-logo" width="250" height="79" border="0" alt="manning">';
$stc_banner_logo = '<img src="/trace/images/midway.png" alt="midway-logo" width="250" height="79" border="0" alt="manning">';
$stc_banner_link = 'http://mid-waytransportation.com/';

$stc_contact_us_link = 'http://mid-waytransportation.com/';

// Customer group feature. Log in as any client_id in a group and see all bills for the group.
$stc_enable_groups = false;

$stc_show_bills_by_fb = true;

$stc_billing_trace_type = 'P';

// Billing report labels

$stc_label_customer_load = 'PO #';
$stc_label_company_load = 'FB #';

// Who sees the billing button
$stc_show_billing = array( 'SHERBILLTO', 'CEDHAVMA', 'CEDWARMA' );

// For Cedars, the billing is different
$cedars = array( 'CEDHAVMA', 'CEDWARMA','SHERBILLTO' );
$cedars2 = array( 'CEDHAVMA', 'CEDWARMA', 'SHERBILLTO' );
$stc_show_carrier = ! (isset($_SESSION['CLIENT_ID']) && in_array( $_SESSION['CLIENT_ID'], $cedars ));
$stc_show_tcc = ! (isset($_SESSION['CLIENT_ID']) && in_array( $_SESSION['CLIENT_ID'], $cedars2 ));
$stc_show_mgmt_fee = ! (isset($_SESSION['CLIENT_ID']) && in_array( $_SESSION['CLIENT_ID'], $cedars2 ));
$stc_show_pallets = (isset($_SESSION['CLIENT_ID']) && in_array( $_SESSION['CLIENT_ID'], $cedars ));




?>