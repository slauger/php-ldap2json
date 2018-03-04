<?php
/**
 * php-ldap2json wrapper for Citrix NetScaler
 * @author Simon Lauger <simon@lauger.de>
 * @date   2018-03-04
 */

// Debug
error_reporting(E_ALL);
ini_set( 'display_errors', '1' );

// set application/json header
header('Content-Type:application/json');

// configuration
$ldap_server = 'ldap://customer.local';
$ldap_basedn = 'DC=customer,DC=local';
$ldap_binddn_default = 'domain\username';
$ldap_bindpw_default = 'password';

// attributes
$ldap_attributes = array(
	'objectclass',
	'cn',
	'sn',
	'givenname',
	'distinguishedname',
	'displayname',
	'memberof',
	'company',
	'samaccountname',
	'userprincipalname',
	'mail',
	'dn',
);

// bind with custom credentails
$ldap_binddn = (isset($_REQUEST['binddn']) && !empty($_REQUEST['binddn'])) ? $_REQUEST['binddn'] : $ldap_binddn_default;
$ldap_bindpw = (isset($_REQUEST['bindpw']) && !empty($_REQUEST['bindpw'])) ? $_REQUEST['bindpw'] : $ldap_bindpw_default;

// ldap search (e.g. |(sAMAccountName=%s)(UserPrincipalName=%s))
$ldap_search = (isset($_REQUEST['search']) && !empty($_REQUEST['search'])) ? $_REQUEST['search'] : null;

//$ldap_search = '(|(sAMAccountName=simon.lauger)(UserPrincipalName=simon.lauger*))';

// check for required arguments
if (is_null($ldap_binddn) || is_null($ldap_bindpw) || is_null($ldap_search)) {
	header('HTTP/1.0 500 Internal Server Error');
	die('ERROR: missing argument');
}

// build ldap connection
$connection = ldap_connect($ldap_server);

// check if connection is valid
if (!$connection) {
	header('HTTP/1.0 500 Internal Server Error');
	die('ERROR: ldap connection failed');
}

// LDAP Settings for Microsoft Active Directory
ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

if (!ldap_bind($connection, $ldap_binddn, $ldap_bindpw)) {
	header('HTTP/1.0 500 Internal Server Error');
	die('ERROR: ldap bind failed');
}  

$result = ldap_search(
	$connection,
	$ldap_basedn,
	$ldap_search,
	$ldap_attributes
);

$result = ldap_get_entries($connection, $result);

ldap_close($connection);

$response = array();

foreach ($result[0] as $key => $value) {
	if (count($value) <= 2) {
		$response[$key] = $value[0];
	} else {
		unset($value['count']);
		$response[$key] = $value;
	}
}

echo json_encode($response);

?>
