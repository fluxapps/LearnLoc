<?php
$path = str_ireplace("Customizing/global/plugins/Services/Repository/RepositoryObject/LiveVoting/" . basename($_SERVER['SCRIPT_FILENAME']), "", $_SERVER['SCRIPT_FILENAME']);
chdir($path);
require_once('include/inc.ilias_version.php');
require_once('Services/Component/classes/class.ilComponent.php');

if(ilComponent::isVersionGreaterString(ILIAS_VERSION_NUMERIC, '4.2.999')) {
	require_once "Services/Context/classes/class.ilContext.php";
	ilContext::init(ilContext::CONTEXT_RSS);
	require_once("Services/Init/classes/class.ilInitialisation.php");
	ilInitialisation::initILIAS();
}
else {
	$_GET["baseClass"] = "ilStartUpGUI";
	require_once('include/inc.get_pear.php');
	require_once('./inc.header.php');
}

?>
