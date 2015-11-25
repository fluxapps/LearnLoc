<?php
/**
 * Loggs the User in and redirects
 */
$base_dir = realpath(dirname(__FILE__) . '/../../../../../../../..');
chdir($base_dir);
$_POST['username'] = $_GET['username'];
$_POST['password'] = $_GET['password'];

if (isset($_GET["client_id"])) {
	setcookie("ilClientId", $_GET["client_id"], 0, '/', '');
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}
$GLOBALS['COOKIE_PATH'] = "/";
require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();
$str = strstr($_SERVER['SCRIPT_URI'], 'Customizing', true) . $_GET['goto'];
if ($_GET['redirect']) {
	$str = strstr(ILIAS_HTTP_PATH, 'Customizing', true) . urldecode($_GET['redirect']);
}
ilUtil::redirect($str);
